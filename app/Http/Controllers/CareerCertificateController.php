<?php

namespace App\Http\Controllers;

use App\Models\CareerCertificate;
use App\Models\CareerRecord;
use App\Models\IssuerPosition;
use App\Models\User;
use App\Services\CareerCertificateService;
use App\Services\CareerCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CareerCertificateController extends Controller
{
    protected $certificateService;
    protected $calculationService;

    public function __construct(
        CareerCertificateService $certificateService,
        CareerCalculationService $calculationService
    ) {
        $this->certificateService = $certificateService;
        $this->calculationService = $calculationService;
    }

    /**
     * 경력증명서 목록 표시
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            // 관리자는 모든 사용자의 경력증명서를 볼 수 있음
            $userId = $request->query('user_id');
            if ($userId) {
                $certificates = CareerCertificate::with(['user', 'issuerPosition'])
                                ->where('user_id', $userId)
                                ->orderBy('created_at', 'desc')
                                ->paginate(10);
            } else {
                $certificates = CareerCertificate::with(['user', 'issuerPosition'])
                                ->orderBy('created_at', 'desc')
                                ->paginate(10);
            }
        } else {
            // 일반 사용자는 자신의 경력증명서만 볼 수 있음
            $certificates = CareerCertificate::with(['issuerPosition'])
                            ->where('user_id', $user->id)
                            ->orderBy('created_at', 'desc')
                            ->paginate(10);
        }

        return view('career.certificates.index', compact('certificates'));
    }

    /**
     * 경력증명서 생성 폼 표시
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $userId = $request->query('user_id');
            $selectedUser = $userId ? User::findOrFail($userId) : null;
            $users = User::where('role', 'user')->orderBy('name')->get();
        } else {
            $selectedUser = $user;
            $users = collect([$user]);
        }

        $issuerPositions = IssuerPosition::where(function ($query) {
            $query->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
        })->orderBy('title')->get();

        if ($selectedUser) {
            $careerRecords = CareerRecord::with(['school', 'subject'])
                            ->where('user_id', $selectedUser->id)
                            ->orderBy('start_date', 'desc')
                            ->get();

            $totalExperience = $this->calculationService->calculateTotalExperience($selectedUser);
        } else {
            $careerRecords = collect();
            $totalExperience = null;
        }

        return view('career.certificates.create', compact(
            'users', 'selectedUser', 'issuerPositions', 'careerRecords', 'totalExperience'
        ));
    }

    /**
     * 경력증명서 저장
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'issue_date' => ['nullable', 'date'],
            'issuer_position_id' => ['required', 'exists:issuer_positions,id'],
            'career_record_ids' => ['nullable', 'array'],
            'career_record_ids.*' => ['exists:career_records,id'],
        ]);

        // 발급자 정보 추가
        $validated['issued_by'] = Auth::id();

        // 경력증명서 생성
        $certificate = $this->certificateService->createCertificate($validated);

        return redirect()->route('career.certificates.show', $certificate)
                         ->with('success', '경력증명서가 성공적으로 발급되었습니다.');
    }

    /**
     * 경력증명서 상세 정보 표시
     */
    public function show(CareerCertificate $certificate)
    {
        // 권한 확인
        $this->authorize('view', $certificate);

        $certificate->load(['user', 'issuerPosition', 'careerRecords.school', 'careerRecords.subject']);

        $totalExperience = $this->calculationService->calculateTotalExperience($certificate->user);

        return view('career.certificates.show', compact('certificate', 'totalExperience'));
    }

    /**
     * 경력증명서 PDF 다운로드
     */
    public function download(CareerCertificate $certificate)
    {
        // 권한 확인
        $this->authorize('download', $certificate);

        if (!$certificate->pdf_path) {
            // PDF가 없으면 생성
            $pdfPath = $this->certificateService->generatePDF($certificate);
            $certificate->pdf_path = $pdfPath;
            $certificate->save();
        }

        $fullPath = storage_path('app/public/' . $certificate->pdf_path);

        return response()->download($fullPath, $certificate->certificate_number . '.pdf');
    }
}
