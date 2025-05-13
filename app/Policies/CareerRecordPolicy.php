<?php

namespace App\Policies;

use App\Models\CareerRecord;
use App\Models\User;

class CareerRecordPolicy
{
    /**
     * 경력 기록 목록 보기 권한
     */
    public function viewAny(User $user): bool
    {
        return true; // 모든 인증된 사용자가 목록 볼 수 있음 (자신의 것만)
    }

    /**
     * 경력 기록 상세 보기 권한
     */
    public function view(User $user, CareerRecord $careerRecord): bool
    {
        return $user->isAdmin() || $user->id === $careerRecord->user_id;
    }

    /**
     * 경력 기록 생성 권한
     */
    public function create(User $user): bool
    {
        return true; // 모든 인증된 사용자가 생성 가능
    }

    /**
     * 경력 기록 업데이트 권한
     */
    public function update(User $user, CareerRecord $careerRecord): bool
    {
        return $user->isAdmin() || $user->id === $careerRecord->user_id;
    }

    /**
     * 경력 기록 삭제 권한
     */
    public function delete(User $user, CareerRecord $careerRecord): bool
    {
        return $user->isAdmin() || $user->id === $careerRecord->user_id;
    }
}
