<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // 기본 사용자 생성
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'birth_date' => '1990-01-01',
            'phone_number' => '010-1234-5678',
            'address' => '서울시 강남구 테헤란로 123',
            'role' => 'user',
            'status' => 'active',
        ]);

        // 관리자 계정 생성
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'birth_date' => '1985-05-20',
            'phone_number' => '010-9876-5432',
            'address' => '서울시 서초구 강남대로 456',
            'role' => 'admin',
            'status' => 'active',
        ]);

        // 테스트 데이터 시더 호출
        $this->call([
            TestDataSeeder::class,
        ]);
    }
}
