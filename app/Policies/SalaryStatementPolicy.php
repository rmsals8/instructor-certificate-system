<?php

namespace App\Policies;

use App\Models\SalaryStatement;
use App\Models\User;

class SalaryStatementPolicy
{
    /**
     * 급여명세서 목록 보기 권한
     */
    public function viewAny(User $user): bool
    {
        return true; // 모든 인증된 사용자가 목록 볼 수 있음 (자신의 것만)
    }

    /**
     * 급여명세서 상세 보기 권한
     */
    public function view(User $user, SalaryStatement $statement): bool
    {
        return $user->isAdmin() || $user->id === $statement->user_id;
    }

    /**
     * 급여명세서 생성 권한
     */
    public function create(User $user): bool
    {
        return $user->isAdmin(); // 관리자만 생성 가능
    }

    /**
     * 급여명세서 다운로드 권한
     */
    public function download(User $user, SalaryStatement $statement): bool
    {
        return $user->isAdmin() || $user->id === $statement->user_id;
    }
}
