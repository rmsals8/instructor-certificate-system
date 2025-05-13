<?php

namespace App\Http\Controllers;

use App\Models\SalaryStatement;
use App\Models\School;
use App\Models\User;
use App\Services\SalaryStatementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalaryStatementController extends Controller
{
    protected $salaryService;

    public function __construct(SalaryStatementService $salaryService)
    {
        $this->salaryService = $salaryService;
    }

    /**
     * 급여명세서 목록 표시
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            // 관리자는 모든 사용자의 급여명세서를 볼 수 있음
            $userId = $request->query('user_id');
            if ($userId) {
                $statements = SalaryStatement::with(['user', 'school'])
                               ->where('user_id', $userId)
                               ->orderBy('year', 'desc')
                               ->orderBy('month', 'desc')
                               ->paginate(10);
            } else {
                $statements = SalaryStatement::with(['user', 'school'])
                               ->orderBy('created_at', 'desc')
                               ->paginate(10);
            }
        } else {
            // 일반 사용자는 자신의 급여명세서만 볼 수 있음
            $statements = SalaryStatement::with(['school'])
                          ->where('user_id', $user->id)
                          ->orderBy('year', 'desc')
                          ->orderBy('month', 'desc')
                          ->paginate(10);
        }

        return view('salary.statements.index', compact('statements'));
    }

    /**
     * 급여명세서 생성 폼 표시
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $userId = $request->query('user_id');
            $selectedUser = $userId ? User::findOrFail($userId) : null;
            $users = User::where('role', 'user')->orderBy('name')->get();
        } else {
            return redirect()->route('salary.statements.index')
                             ->with('error', '권한이 없습니다.');
        }

        $schools = School::orderBy('name')->get();

        return view('salary.statements.create', compact('users', 'selectedUser', 'schools'));
    }

    /**
     * 급여명세서 저장
     */
    public function store(Request $request)
   {
       $validated = $request->validate([
           'user_id' => ['required', 'exists:users,id'],
           'school_id' => ['required', 'exists:schools,id'],
           'year' => ['required', 'integer', 'min:2000', 'max:2100'],
           'month' => ['required', 'integer', 'min:1', 'max:12'],
           'payment_date' => ['required', 'date'],
           'per_student_fee' => ['required', 'numeric', 'min:0'],
           'student_count' => ['required', 'integer', 'min:0'],
           'subsidy_amount' => ['nullable', 'numeric', 'min:0'],
           'additional_payment' => ['nullable', 'numeric', 'min:0'],
           'cancellation_refund' => ['nullable', 'numeric', 'min:0'],
           'other_refund' => ['nullable', 'numeric', 'min:0'],
           'industrial_insurance' => ['nullable', 'numeric', 'min:0'],
           'employment_insurance' => ['nullable', 'numeric', 'min:0'],
           'income_tax' => ['nullable', 'numeric', 'min:0'],
           'local_income_tax' => ['nullable', 'numeric', 'min:0'],
           'other_deduction' => ['nullable', 'numeric', 'min:0'],
           'status' => ['nullable', 'in:draft,issued,viewed'],
       ]);

       // 발급자 정보 추가
       $validated['issued_by'] = Auth::id();

       // 기본값 설정
       $validated['subsidy_amount'] = $validated['subsidy_amount'] ?? 0;
       $validated['additional_payment'] = $validated['additional_payment'] ?? 0;
       $validated['cancellation_refund'] = $validated['cancellation_refund'] ?? 0;
       $validated['other_refund'] = $validated['other_refund'] ?? 0;
       $validated['industrial_insurance'] = $validated['industrial_insurance'] ?? 0;
       $validated['employment_insurance'] = $validated['employment_insurance'] ?? 0;
       $validated['income_tax'] = $validated['income_tax'] ?? 0;
       $validated['local_income_tax'] = $validated['local_income_tax'] ?? 0;
       $validated['other_deduction'] = $validated['other_deduction'] ?? 0;
       $validated['status'] = $validated['status'] ?? 'draft';

       // 급여명세서 생성
       $statement = $this->salaryService->createSalaryStatement($validated);

       return redirect()->route('salary.statements.show', $statement)
                        ->with('success', '급여명세서가 성공적으로 발급되었습니다.');
   }

   /**
    * 급여명세서 상세 정보 표시
    */
   public function show(SalaryStatement $statement)
   {
       // 권한 확인
       $this->authorize('view', $statement);

       $statement->load(['user', 'school', 'salaryDetail', 'deductionDetail']);

       $actualPayment = $statement->getActualPayment();

       // 사용자의 지급 정보 가져오기 (기본 계좌)
       $paymentInfo = $statement->user->paymentInformation()->where('is_default', true)->first();

       return view('salary.statements.show', compact('statement', 'actualPayment', 'paymentInfo'));
   }

   /**
    * 급여명세서 상태 업데이트 (조회 시)
    */
   public function markAsViewed(SalaryStatement $statement)
   {
       // 권한 확인
       $this->authorize('view', $statement);

       if ($statement->status == 'issued') {
           $statement->status = 'viewed';
           $statement->save();
       }

       return redirect()->route('salary.statements.show', $statement);
   }

   /**
    * 급여명세서 PDF 다운로드
    */
   public function download(SalaryStatement $statement)
   {
       // 권한 확인
       $this->authorize('download', $statement);

       if (!$statement->pdf_path) {
           // PDF가 없으면 생성
           $pdfPath = $this->salaryService->generatePDF($statement);
           $statement->pdf_path = $pdfPath;
           $statement->save();
       }

       $fullPath = storage_path('app/public/' . $statement->pdf_path);

       return response()->download($fullPath, $statement->certificate_number . '.pdf');
   }
}
