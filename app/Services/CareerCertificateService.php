<?php

namespace App\Services;

use App\Models\CareerCertificate;
use App\Models\CareerRecord;
use App\Models\CertificateCareerDetail;
use App\Models\IssuerPosition;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class CareerCertificateService
{
    protected $careerCalculationService;

    public function __construct(CareerCalculationService $careerCalculationService)
    {
        $this->careerCalculationService = $careerCalculationService;
    }

    /**
     * 경력증명서 생성
     *
     * @param array $data 경력증명서 데이터
     * @return CareerCertificate
     */
    public function createCertificate(array $data)
    {
        // 증명서 번호 생성
        $certificateNumber = $this->generateCertificateNumber();

        // 경력증명서 기본 정보 생성
        $certificate = CareerCertificate::create([
            'user_id' => $data['user_id'],
            'issue_date' => $data['issue_date'] ?? now(),
            'certificate_number' => $certificateNumber,
            'purpose' => $data['purpose'] ?? null,
            'issuer_position_id' => $data['issuer_position_id'] ?? null,
            'issued_by' => $data['issued_by'] ?? null,
        ]);

        // 포함할 경력 정보 연결
        if (isset($data['career_record_ids'])) {
            foreach ($data['career_record_ids'] as $recordId) {
                CertificateCareerDetail::create([
                    'certificate_id' => $certificate->id,
                    'career_record_id' => $recordId
                ]);
            }
        } else {
            // 사용자의 모든 경력 정보 연결
            $careerRecords = CareerRecord::where('user_id', $data['user_id'])->get();
            foreach ($careerRecords as $record) {
                CertificateCareerDetail::create([
                    'certificate_id' => $certificate->id,
                    'career_record_id' => $record->id
                ]);
            }
        }

        // PDF 생성 및 저장
        $pdfPath = $this->generatePDF($certificate);
        $certificate->pdf_path = $pdfPath;
        $certificate->save();

        return $certificate;
    }

    /**
     * 증명서 번호 생성
     *
     * @return string
     */
    private function generateCertificateNumber()
    {
        $prefix = 'C-' . now()->format('Ymd');
        $lastCertificate = CareerCertificate::where('certificate_number', 'like', $prefix . '%')
                                            ->orderBy('certificate_number', 'desc')
                                            ->first();

        if ($lastCertificate) {
            $lastNumber = (int) substr($lastCertificate->certificate_number, -3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . '-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * PDF 생성 및 저장
     *
     * @param CareerCertificate $certificate
     * @return string 저장된 PDF 경로
     */
    public function generatePDF(CareerCertificate $certificate)
    {
        // 사용자 정보
        $user = $certificate->user;

        // 총 경력 계산
        $totalExperience = $this->careerCalculationService->calculateTotalExperience($user);

        // 경력 기록
        $careerRecords = $certificate->careerRecords;

        // 발급자 정보
        $issuerPosition = $certificate->issuerPosition;

        // PDF 생성
        $pdf = PDF::loadView('certificates.career', [
            'certificate' => $certificate,
            'user' => $user,
            'careerRecords' => $careerRecords,
            'totalExperience' => $totalExperience,
            'issuerPosition' => $issuerPosition
        ]);

        // 저장 경로 생성
        $filePath = 'certificates/' . $user->id . '/' . $certificate->certificate_number . '.pdf';
        $fullPath = storage_path('app/public/' . $filePath);

        // 디렉토리 생성
        $directory = dirname($fullPath);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // PDF 저장
        $pdf->save($fullPath);

        return $filePath;
    }
}
