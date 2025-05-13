<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CareerRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CareerRecordController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isAdmin() && $request->has('user_id')) {
            $records = CareerRecord::with(['school', 'subject', 'instructorType'])
                        ->where('user_id', $request->user_id)
                        ->orderBy('start_date', 'desc')
                        ->paginate(10);
        } else {
            $records = CareerRecord::with(['school', 'subject', 'instructorType'])
                        ->where('user_id', $user->id)
                        ->orderBy('start_date', 'desc')
                        ->paginate(10);
        }

        return response()->json($records);
    }

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

        if ($request->boolean('is_current')) {
            $validated['end_date'] = null;
        }

        $user = Auth::user();
        if ($user->isAdmin() && isset($validated['user_id'])) {
            $userId = $validated['user_id'];
        } else {
            $userId = $user->id;
        }
        $validated['user_id'] = $userId;

        $record = CareerRecord::create($validated);

        return response()->json($record, 201);
    }

    public function show(CareerRecord $careerRecord)
    {
        $this->authorize('view', $careerRecord);

        $careerRecord->load(['school', 'subject', 'instructorType']);

        return response()->json($careerRecord);
    }

    public function update(Request $request, CareerRecord $careerRecord)
    {
        $this->authorize('update', $careerRecord);

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

        if ($request->boolean('is_current')) {
            $validated['end_date'] = null;
        }

        $careerRecord->update($validated);

        return response()->json($careerRecord);
    }

    public function destroy(CareerRecord $careerRecord)
    {
        $this->authorize('delete', $careerRecord);

        $careerRecord->delete();

        return response()->json(null, 204);
    }
}
