<?php

namespace App\Services;

use App\Models\CareerRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CareerCalculationService
{
    /**
     * 강사의 총 경력 기간을 계산
     *
     * @param User|int $user 강사 또는 강사 ID
     * @return array ['years' => int, 'months' => int, 'days' => int, 'text' => string]
     */
    public function calculateTotalExperience($user)
    {
        $userId = $user instanceof User ? $user->id : $user;

        // 강사의 모든 경력 기록 가져오기
        $careerRecords = CareerRecord::where('user_id', $userId)
                                    ->orderBy('start_date')
                                    ->get();

        if ($careerRecords->isEmpty()) {
            return [
                'years' => 0,
                'months' => 0,
                'days' => 0,
                'text' => '0년 0개월'
            ];
        }

        // 중복 기간을 고려하여 총 경력 기간 계산
        $mergedPeriods = $this->mergeDateRanges($careerRecords);

        // 총 일수 계산
        $totalDays = 0;
        foreach ($mergedPeriods as $period) {
            $start = Carbon::parse($period['start_date']);
            $end = Carbon::parse($period['end_date']);
            $totalDays += $end->diffInDays($start) + 1; // +1은 시작일도 포함
        }

        // 일수를 년, 월로 변환
        $years = floor($totalDays / 365);
        $remainingDays = $totalDays % 365;
        $months = floor($remainingDays / 30);

        // 텍스트 표현
        $result = [];
        if ($years > 0) {
            $result[] = $years . '년';
        }
        if ($months > 0) {
            $result[] = $months . '개월';
        }
        if (count($result) == 0) {
            $result[] = $remainingDays % 30 . '일';
        }

        return [
            'years' => $years,
            'months' => $months,
            'days' => $totalDays,
            'text' => implode(' ', $result)
        ];
    }

    /**
     * 중복되는 날짜 범위를 병합하는 함수
     *
     * @param Collection $records 경력 기록 컬렉션
     * @return array 병합된 날짜 범위 배열
     */
    private function mergeDateRanges($records)
    {
        if ($records->isEmpty()) {
            return [];
        }

        // 날짜 범위를 배열로 변환
        $ranges = [];
        foreach ($records as $record) {
            $ranges[] = [
                'start_date' => $record->start_date->format('Y-m-d'),
                'end_date' => ($record->end_date ?? Carbon::today())->format('Y-m-d')
            ];
        }

        // 시작일 기준으로 정렬
        usort($ranges, function ($a, $b) {
            return strcmp($a['start_date'], $b['start_date']);
        });

        // 병합된 범위 배열
        $merged = [$ranges[0]];

        // 범위 병합 알고리즘
        foreach (array_slice($ranges, 1) as $range) {
            $lastMergedIndex = count($merged) - 1;
            $lastMerged = $merged[$lastMergedIndex];

            // 현재 범위의 시작일이 마지막 병합 범위의 종료일보다 이후인 경우 (겹치지 않음)
            if (Carbon::parse($range['start_date'])->isAfter(Carbon::parse($lastMerged['end_date'])->addDay())) {
                $merged[] = $range;
            }
            // 범위가 겹치는 경우, 종료일을 더 나중 날짜로 업데이트
            else {
                $merged[$lastMergedIndex]['end_date'] = Carbon::parse($lastMerged['end_date'])
                    ->max(Carbon::parse($range['end_date']))
                    ->format('Y-m-d');
            }
        }

        return $merged;
    }
}
