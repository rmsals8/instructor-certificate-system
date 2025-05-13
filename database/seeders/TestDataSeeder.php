<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\School;
use App\Models\Subject;
use App\Models\InstructorType;
use App\Models\CareerRecord;
use App\Models\IssuerPosition;
use App\Models\CareerCertificate;
use App\Models\CertificateCareerDetail;
use App\Models\SalaryStatement;
use App\Models\SalaryDetail;
use App\Models\DeductionDetail;
use App\Models\PaymentInformation;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TestDataSeeder extends Seeder
{
    public function run()
    {
        // 1. 테스트 사용자 생성
        $user = User::firstOrCreate(
            ['email' => 'instructor@example.com'],
            [
                'name' => '홍길동',
                'birth_date' => '1990-01-15',
                'phone_number' => '010-1234-5678',
                'address' => '서울시 강남구 테헤란로 123',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'status' => 'active',
            ]
        );

        // 2. 관리자 사용자 생성
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => '관리자',
                'birth_date' => '1985-05-20',
                'phone_number' => '010-9876-5432',
                'address' => '서울시 서초구 강남대로 456',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );

        // 3. 학교 정보 생성
        $school1 = School::firstOrCreate(
            ['name' => '문현초등학교'],
            [
                'address' => '서울시 강남구 문현로 100',
                'type' => '초등학교',
                'region' => '서울',
            ]
        );

        $school2 = School::firstOrCreate(
            ['name' => '다운초등학교'],
            [
                'address' => '서울시 서초구 다운로 200',
                'type' => '초등학교',
                'region' => '서울',
            ]
        );

        // 4. 과목 정보 생성
        $subject1 = Subject::firstOrCreate(
            ['name' => '수학'],
            ['category' => '방과후']
        );

        $subject2 = Subject::firstOrCreate(
            ['name' => '국어'],
            ['category' => '방과후']
        );

        // 5. 강사 유형 생성
        $instructorType = InstructorType::firstOrCreate(
            ['name' => '방과후강사'],
            ['description' => '정규 수업 이후 방과후 특별활동을 담당하는 강사']
        );

        // 6. 경력 기록 생성
        $careerRecord1 = CareerRecord::firstOrCreate(
            [
                'user_id' => $user->id,
                'school_id' => $school1->id,
                'start_date' => '2023-03-01',
            ],
            [
                'subject_id' => $subject1->id,
                'instructor_type_id' => $instructorType->id,
                'end_date' => '2023-12-31',
                'position' => '주임강사',
                'hours_per_week' => 10,
                'description' => '초등 수학 방과후 수업',
                'is_current' => false,
            ]
        );

        $careerRecord2 = CareerRecord::firstOrCreate(
            [
                'user_id' => $user->id,
                'school_id' => $school2->id,
                'start_date' => '2024-03-01',
            ],
            [
                'subject_id' => $subject2->id,
                'instructor_type_id' => $instructorType->id,
                'end_date' => null,
                'position' => '전임강사',
                'hours_per_week' => 15,
                'description' => '초등 국어 방과후 수업',
                'is_current' => true,
            ]
        );

        // 7. 발급자 직책 생성
        $issuerPosition = IssuerPosition::firstOrCreate(
            ['title' => '교육원장'],
            [
                'name' => '김철수',
                'start_date' => '2022-01-01',
                'end_date' => null,
            ]
        );

        // 8. 경력증명서 생성
        $certificate = CareerCertificate::firstOrCreate(
            [
                'user_id' => $user->id,
                'certificate_number' => 'C-' . date('Ymd') . '-001',
            ],
            [
                'issue_date' => Carbon::now(),
                'purpose' => '취업 지원용',
                'issuer_position_id' => $issuerPosition->id,
                'issued_by' => $admin->id,
            ]
        );

        // 9. 증명서-경력 연결
        CertificateCareerDetail::firstOrCreate(
            [
                'certificate_id' => $certificate->id,
                'career_record_id' => $careerRecord1->id,
            ]
        );

        CertificateCareerDetail::firstOrCreate(
            [
                'certificate_id' => $certificate->id,
                'career_record_id' => $careerRecord2->id,
            ]
        );

        // 10. 지급 정보 생성
        PaymentInformation::firstOrCreate(
            [
                'user_id' => $user->id,
                'account_number' => '110-123-456789',
            ],
            [
                'bank' => '신한은행',
                'account_holder' => '홍길동',
                'is_default' => true,
            ]
        );

        // 11. 급여명세서 생성 (2024년 3월)
        $salaryStatement1 = SalaryStatement::firstOrCreate(
            [
                'user_id' => $user->id,
                'year' => 2024,
                'month' => 3,
            ],
            [
                'school_id' => $school2->id,
                'payment_date' => '2024-03-25',
                'certificate_number' => 'S-20240325-001',
                'issued_by' => $admin->id,
                'status' => 'issued',
            ]
        );

        // 12. 급여 상세 정보
        SalaryDetail::firstOrCreate(
            ['salary_statement_id' => $salaryStatement1->id],
            [
                'per_student_fee' => 30000,
                'student_count' => 15,
                'subsidy_amount' => 50000,
                'additional_payment' => 20000,
                'cancellation_refund' => 0,
                'other_refund' => 0,
                'total_amount' => 520000, // (30000*15)+50000+20000
            ]
        );

        // 13. 공제 상세 정보
        DeductionDetail::firstOrCreate(
            ['salary_statement_id' => $salaryStatement1->id],
            [
                'industrial_insurance' => 5000,
                'employment_insurance' => 3000,
                'income_tax' => 7000,
                'local_income_tax' => 700,
                'other_deduction' => 0,
                'total_deduction' => 15700, // 5000+3000+7000+700
            ]
        );

        // 14. 급여명세서 생성 (2024년 4월)
        $salaryStatement2 = SalaryStatement::firstOrCreate(
            [
                'user_id' => $user->id,
                'year' => 2024,
                'month' => 4,
            ],
            [
                'school_id' => $school2->id,
                'payment_date' => '2024-04-25',
                'certificate_number' => 'S-20240425-001',
                'issued_by' => $admin->id,
                'status' => 'issued',
            ]
        );

        // 15. 급여 상세 정보
        SalaryDetail::firstOrCreate(
            ['salary_statement_id' => $salaryStatement2->id],
            [
                'per_student_fee' => 30000,
                'student_count' => 18,
                'subsidy_amount' => 50000,
                'additional_payment' => 30000,
                'cancellation_refund' => 0,
                'other_refund' => 0,
                'total_amount' => 620000, // (30000*18)+50000+30000
            ]
        );

        // 16. 공제 상세 정보
        DeductionDetail::firstOrCreate(
            ['salary_statement_id' => $salaryStatement2->id],
            [
                'industrial_insurance' => 6000,
                'employment_insurance' => 3600,
                'income_tax' => 8000,
                'local_income_tax' => 800,
                'other_deduction' => 0,
                'total_deduction' => 18400, // 6000+3600+8000+800
            ]
        );

        $this->command->info('테스트 데이터가 성공적으로 생성되었습니다.');
    }
}
