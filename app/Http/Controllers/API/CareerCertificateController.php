<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CareerCertificate;
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

    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isAdmin() && $request->has('user_id')) {
            $certificates = CareerCertificate::with(['user', 'issuerPosition'])
                            ->where('user_id', $request->user_id)
                            ->orderBy('created_at', 'desc')
                            ->paginate(10);
        } else {
            $certificates = CareerCertificate::with(['issuerPosition'])
                            ->where('user_id', $user->id)
                            ->orderBy('created_at', 'desc')
                            ->paginate(10);
        }

        return response()->json($certificates);
    }

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

        $validated['issued_by'] = Auth::id();

        $certificate = $this->certificateService->createCertificate($validated);

        return response()->json($certificate, 201);
    }

    public function show(CareerCertificate $certificate)
    {
        $this->authorize('view', $certificate);

        $certificate->load(['user', 'issuerPosition', 'careerRecords.school', 'careerRecords.subject']);

        $totalExperience = $this->calculationService->calculateTotalExperience($certificate->user);

        return response()->json([
            'certificate' => $certificate,
            'totalExperience' => $totalExperience
        ]);
    }

    public function download(CareerCertificate $certificate)
    {
        $this->authorize('download', $certificate);

        if (!$certificate->pdf_path) {
            $pdfPath = $this->certificateService->generatePDF($certificate);
            $certificate->pdf_path = $pdfPath;
            $certificate->save();
        }

        $fullPath = storage_path('app/public/' . $certificate->pdf_path);

        return response()->download($fullPath, $certificate->certificate_number . '.pdf');
    }
}
