<?php

namespace App\Services;

use App\Models\SalaryStatement;
use App\Models\SalaryDetail;
use App\Models\DeductionDetail;
use App\Models\User;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class SalaryStatementService
{
    /**
     * 급여명세서 생성
     *
     * @param array $data 급여명세서 데이터
     * @return SalaryStatement
     */
    public function createSalaryStatement(array $data)
    {
        // 명세서 번호 생성
        $certificateNumber = $this->generateCertificateNumber();

        // 급여명세서 기본 정보 생성
        $statement = SalaryStatement::create([
            'user_id' => $data['user_id'],
            'school_id' => $data['school_id'] ?? null,
            'year' => $data['year'],
            'month' => $data['month'],
            'payment_date' => $data['payment_date'] ?? now(),
            'certificate_number' => $certificateNumber,
            'issued_by' => $data['issued_by'] ?? null,
            'status' => $data['status'] ?? 'draft',
        ]);

        // 급여 상세 정보 생성
        $salaryDetail = SalaryDetail::create([
            'salary_statement_id' => $statement->id,
            'per_student_fee' => $data['per_student_fee'],
            'student_count' => $data['student_count'],
            'subsidy_amount' => $data['subsidy_amount'] ?? 0,
            'additional_payment' => $data['additional_payment'] ?? 0,
            'cancellation_refund' => $data['cancellation_refund'] ?? 0,
            'other_refund' => $data['other_refund'] ?? 0,
            'total_amount' => $this->calculateTotalAmount($data),
        ]);

        // 공제 상세 정보 생성
        $deductionDetail = DeductionDetail::create([
            'salary_statement_id' => $statement->id,
            'industrial_insurance' => $data['industrial_insurance'] ?? 0,
            'employment_insurance' => $data['employment_insurance'] ?? 0,
            'income_tax' => $data['income_tax'] ?? 0,
            'local_income_tax' => $data['local_income_tax'] ?? 0,
            'other_deduction' => $data['other_deduction'] ?? 0,
            'total_deduction' => $this->calculateTotalDeduction($data),
        ]);

        // PDF 생성 및 저장
        $pdfPath = $this->generatePDF($statement);
        $statement->pdf_path = $pdfPath;
        $statement->save();

        return $statement;
    }

    /**
     * 명세서 번호 생성
     *
     * @return string
     */
    private function generateCertificateNumber()
    {
        $prefix = 'S-' . now()->format('Ymd');
        $lastStatement = SalaryStatement::where('certificate_number', 'like', $prefix . '%')
                                        ->orderBy('certificate_number', 'desc')
                                        ->first();

        if ($lastStatement) {
            $lastNumber = (int) substr($lastStatement->certificate_number, -3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . '-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * 총액 계산
     *
     * @param array $data
     * @return float
     */
    private function calculateTotalAmount(array $data)
    {
        return ($data['per_student_fee'] * $data['student_count']) +
               ($data['subsidy_amount'] ?? 0) +
               ($data['additional_payment'] ?? 0) -
               ($data['cancellation_refund'] ?? 0) -
               ($data['other_refund'] ?? 0);
    }

    /**
     * 총 공제액 계산
     *
     * @param array $data
     * @return float
     */
    private function calculateTotalDeduction(array $data)
    {
        return ($data['industrial_insurance'] ?? 0) +
               ($data['employment_insurance'] ?? 0) +
               ($data['income_tax'] ?? 0) +
               ($data['local_income_tax'] ?? 0) +
               ($data['other_deduction'] ?? 0);
    }

    /**
     * PDF 생성 및 저장
     *
     * @param SalaryStatement $statement
     * @return string 저장된 PDF 경로
     */
    public function generatePDF(SalaryStatement $statement)
    {
        // 사용자 정보
        $user = $statement->user;

        // 학교 정보
        $school = $statement->school;

        // 급여 상세
        $salaryDetail = $statement->salaryDetail;

        // 공제 상세
        $deductionDetail = $statement->deductionDetail;

        // 실수령액 계산
        $actualPayment = $salaryDetail->total_amount - $deductionDetail->total_deduction;

        // 사용자의 지급 정보 가져오기 (기본 계좌)
        $paymentInfo = $user->paymentInformation()->where('is_default', true)->first();

        // PDF 생성
        $pdf = PDF::loadView('statements.salary', [
            'statement' => $statement,
            'user' => $user,
            'school' => $school,
            'salaryDetail' => $salaryDetail,
            'deductionDetail' => $deductionDetail,
            'actualPayment' => $actualPayment,
            'paymentInfo' => $paymentInfo
        ]);

        // 저장 경로 생성
        $filePath = 'statements/' . $user->id . '/' . $statement->certificate_number . '.pdf';
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
