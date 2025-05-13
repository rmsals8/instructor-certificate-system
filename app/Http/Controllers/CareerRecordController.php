<?php

namespace App\Http\Controllers;

use App\Models\CareerRecord;
use App\Models\School;
use App\Models\Subject;
use App\Models\InstructorType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CareerRecordController extends Controller
{
    /**
     * 경력 기록 목록 표시
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            // 관리자는 모든 사용자의 경력 기록을 볼 수 있음
            $userId = $request->query('user_id');
            if ($userId) {
                $records = CareerRecord::with(['school', 'subject', 'instructorType'])
                            ->where('user_id', $userId)
                            ->orderBy('start_date', 'desc')
                            ->paginate(10);
            } else {
                $records = CareerRecord::with(['user', 'school', 'subject', 'instructorType'])
                            ->orderBy('created_at', 'desc')
                            ->paginate(10);
            }
        } else {
            // 일반 사용자는 자신의 경력 기록만 볼 수 있음
            $records = CareerRecord::with(['school', 'subject', 'instructorType'])
                        ->where('user_id', $user->id)
                        ->orderBy('start_date', 'desc')
                        ->paginate(10);
        }

        return view('career.records.index', compact('records'));
    }

    /**
     * 경력 기록 생성 폼 표시
     */
    public function create()
    {
        $schools = School::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $instructorTypes = InstructorType::orderBy('name')->get();

        return view('career.records.create', compact('schools', 'subjects', 'instructorTypes'));
    }

    /**
     * 경력 기록 저장
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required_if:is_admin,1', 'exists:users,id'],
            'school_id' => ['required', 'exists:schools,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'instructor_type_id' => ['nullable', 'exists:instructor_types,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_current' => ['boolean'],
            'position' => ['nullable', 'string', 'max:100'],
            'hours_per_week' => ['nullable', 'integer', 'min:1', 'max:168'],
            'description' => ['nullable', 'string'],
        ]);

        // 현재 근무 중이면 종료일 제거
        if ($request->boolean('is_current')) {
            $validated['end_date'] = null;
        }

        // 사용자 ID 설정
        $user = Auth::user();
        if ($user->isAdmin() && isset($validated['user_id'])) {
            $userId = $validated['user_id'];
        } else {
            $userId = $user->id;
        }
        $validated['user_id'] = $userId;

        CareerRecord::create($validated);

        return redirect()->route('career.records.index')
                         ->with('success', '경력 기록이 생성되었습니다.');
    }

    /**
     * 경력 기록 수정 폼 표시
     */
    public function edit(CareerRecord $record)
    {
        // 권한 확인
        $this->authorize('update', $record);

        $schools = School::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $instructorTypes = InstructorType::orderBy('name')->get();

        return view('career.records.edit', compact('record', 'schools', 'subjects', 'instructorTypes'));
    }

    /**
     * 경력 기록 업데이트
     */
    public function update(Request $request, CareerRecord $record)
    {
        // 권한 확인
        $this->authorize('update', $record);

        $validated = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'instructor_type_id' => ['nullable', 'exists:instructor_types,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_current' => ['boolean'],
            'position' => ['nullable', 'string', 'max:100'],
            'hours_per_week' => ['nullable', 'integer', 'min:1', 'max:168'],
            'description' => ['nullable', 'string'],
        ]);

        // 현재 근무 중이면 종료일 제거
        if ($request->boolean('is_current')) {
            $validated['end_date'] = null;
        }

        $record->update($validated);

        return redirect()->route('career.records.index')
                         ->with('success', '경력 기록이 업데이트되었습니다.');
    }

    /**
     * 경력 기록 삭제
     */
    public function destroy(CareerRecord $record)
    {
        // 권한 확인
        $this->authorize('delete', $record);

        $record->delete();

        return redirect()->route('career.records.index')
                         ->with('success', '경력 기록이 삭제되었습니다.');
    }
}
