<?php

namespace App\Policies;

use App\Models\CareerCertificate;
use App\Models\User;

class CareerCertificatePolicy
{
    /**
     * 경력증명서 목록 보기 권한
     */
    public function viewAny(User $user): bool
    {
        return true; // 모든 인증된 사용자가 목록 볼 수 있음 (자신의 것만)
    }

    /**
     * 경력증명서 상세 보기 권한
     */
    public function view(User $user, CareerCertificate $certificate): bool
    {
        return $user->isAdmin() || $user->id === $certificate->user_id;
    }

    /**
     * 경력증명서 생성 권한
     */
    public function create(User $user): bool
    {
        return $user->isAdmin(); // 관리자만 생성 가능
    }

    /**
     * 경력증명서 다운로드 권한
     */
    public function download(User $user, CareerCertificate $certificate): bool
    {
        return $user->isAdmin() || $user->id === $certificate->user_id;
    }
}
