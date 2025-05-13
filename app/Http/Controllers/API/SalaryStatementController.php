<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SalaryStatement;
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

    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isAdmin() && $request->has('user_id')) {
            $statements = SalaryStatement::with(['user', 'school'])
                           ->where('user_id', $request->user_id)
                           ->orderBy('year', 'desc')
                           ->orderBy('month', 'desc')
                           ->paginate(10);
        } else {
            $statements = SalaryStatement::with(['school'])
                          ->where('user_id', $user->id)
                          ->orderBy('year', 'desc')
                          ->orderBy('month', 'desc')
                          ->paginate(10);
        }

        return response()->json($statements);
    }

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

        $validated['issued_by'] = Auth::id();

        $statement = $this->salaryService->createSalaryStatement($validated);

        return response()->json($statement, 201);
    }

    public function show(SalaryStatement $statement)
    {
        $this->authorize('view', $statement);

        $statement->load(['user', 'school', 'salaryDetail', 'deductionDetail']);

        $actualPayment = $statement->getActualPayment();

        $paymentInfo = $statement->user->paymentInformation()->where('is_default', true)->first();

        return response()->json([
            'statement' => $statement,
            'actualPayment' => $actualPayment,
            'paymentInfo' => $paymentInfo
        ]);
    }

    public function download(SalaryStatement $statement)
    {
        $this->authorize('download', $statement);

        if (!$statement->pdf_path) {
            $pdfPath = $this->salaryService->generatePDF($statement);
            $statement->pdf_path = $pdfPath;
            $statement->save();
        }

        $fullPath = storage_path('app/public/' . $statement->pdf_path);

        return response()->download($fullPath, $statement->certificate_number . '.pdf');
    }
}
