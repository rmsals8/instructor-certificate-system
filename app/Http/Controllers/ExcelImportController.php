<?php

namespace App\Http\Controllers;

use App\Services\ExcelImportService;
use Illuminate\Http\Request;

class ExcelImportController extends Controller
{
    protected $importService;

    public function __construct(ExcelImportService $importService)
    {
        $this->importService = $importService;
        $this->middleware('admin'); // 관리자 권한 필요
    }

    /**
     * 엑셀 업로드 폼 표시
     */
    public function index()
    {
        return view('admin.excel.import');
    }

    /**
     * 강사 정보 엑셀 업로드 처리
     */
    public function importInstructors(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        $result = $this->importService->importInstructors($path);

        if ($result['error'] > 0) {
            return redirect()->route('admin.excel.import')
                             ->with('errors', $result['errors'])
                             ->with('success', "{$result['success']}개의 강사 정보가 성공적으로 처리되었습니다.");
        }

        return redirect()->route('admin.excel.import')
                         ->with('success', "{$result['success']}개의 강사 정보가 성공적으로 처리되었습니다.");
    }

    /**
     * 경력 정보 엑셀 업로드 처리
     */
    public function importCareerRecords(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        $result = $this->importService->importCareerRecords($path);

        if ($result['error'] > 0) {
            return redirect()->route('admin.excel.import')
                             ->with('errors', $result['errors'])
                             ->with('success', "{$result['success']}개의 경력 정보가 성공적으로 처리되었습니다.");
        }

        return redirect()->route('admin.excel.import')
                         ->with('success', "{$result['success']}개의 경력 정보가 성공적으로 처리되었습니다.");
    }
}
