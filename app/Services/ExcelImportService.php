<?php

namespace App\Services;

use App\Models\User;
use App\Models\School;
use App\Models\Subject;
use App\Models\InstructorType;
use App\Models\CareerRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

class ExcelImportService
{
    /**
     * 강사 정보 엑셀 파일 일괄 업로드
     *
     * @param string $filePath
     * @return array 처리 결과 ['success' => int, 'error' => int, 'errors' => array]
     */
    public function importInstructors(string $filePath)
    {
        $result = [
            'success' => 0,
            'error' => 0,
            'errors' => []
        ];

        // 엑셀 파일 로드
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // 헤더 행 분리
        $headers = array_shift($rows);

        // 트랜잭션 시작
        DB::beginTransaction();

        try {
            foreach ($rows as $rowIndex => $row) {
                $rowNumber = $rowIndex + 2; // 1-based index + header row

                // 빈 행 건너뛰기
                if (empty(array_filter($row))) {
                    continue;
                }

                // 데이터 매핑
                $data = array_combine($headers, $row);

                // 필수 필드 검증
                if (empty($data['이름']) || empty($data['이메일']) || empty($data['생년월일'])) {
                    $result['error']++;
                    $result['errors'][] = "행 {$rowNumber}: 이름, 이메일, 생년월일은 필수입니다.";
                    continue;
                }

                // 이메일 중복 확인
                $existingUser = User::where('email', $data['이메일'])->first();

                if ($existingUser) {
                    // 기존 사용자 정보 업데이트
                    $existingUser->update([
                        'name' => $data['이름'],
                        'birth_date' => Carbon::parse($data['생년월일']),
                        'phone_number' => $data['휴대폰번호'] ?? null,
                        'address' => $data['주소'] ?? null,
                    ]);

                    $user = $existingUser;
                } else {
                    // 새 사용자 생성
                    $user = User::create([
                        'name' => $data['이름'],
                        'email' => $data['이메일'],
                        'birth_date' => Carbon::parse($data['생년월일']),
                        'phone_number' => $data['휴대폰번호'] ?? null,
                        'address' => $data['주소'] ?? null,
                        'password' => Hash::make('password123'), // 초기 비밀번호 설정
                        'role' => 'user',
                        'status' => 'active',
                    ]);
                }

                $result['success']++;
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $result['error']++;
            $result['errors'][] = "오류 발생: " . $e->getMessage();
        }

        return $result;
    }

    /**
     * 경력 정보 엑셀 파일 일괄 업로드
     *
     * @param string $filePath
     * @return array 처리 결과 ['success' => int, 'error' => int, 'errors' => array]
     */
    public function importCareerRecords(string $filePath)
    {
        $result = [
            'success' => 0,
            'error' => 0,
            'errors' => []
        ];

        // 엑셀 파일 로드
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // 헤더 행 분리
        $headers = array_shift($rows);

        // 트랜잭션 시작
        DB::beginTransaction();

        try {
            foreach ($rows as $rowIndex => $row) {
                $rowNumber = $rowIndex + 2; // 1-based index + header row

                // 빈 행 건너뛰기
                if (empty(array_filter($row))) {
                    continue;
                }

                // 데이터 매핑
                $data = array_combine($headers, $row);

                // 필수 필드 검증
                if (empty($data['이메일']) || empty($data['시작일']) || empty($data['학교명']) || empty($data['과목명'])) {
                    $result['error']++;
                    $result['errors'][] = "행 {$rowNumber}: 이메일, 시작일, 학교명, 과목명은 필수입니다.";
                    continue;
                }

                // 사용자 찾기
                $user = User::where('email', $data['이메일'])->first();

                if (!$user) {
                    $result['error']++;
                    $result['errors'][] = "행 {$rowNumber}: 이메일({$data['이메일']})에 해당하는 사용자가 없습니다.";
                    continue;
                }

                // 학교 찾기 또는 생성
                $school = School::firstOrCreate(
                    ['name' => $data['학교명']],
                    [
                        'region' => $data['지역'] ?? null,
                        'type' => $data['학교유형'] ?? null,
                    ]
                );

                // 과목 찾기 또는 생성
                $subject = Subject::firstOrCreate(
                    ['name' => $data['과목명']],
                    ['category' => $data['과목카테고리'] ?? null]
                );

                // 강사 유형 찾기 또는 생성
                $instructorType = null;
                if (!empty($data['강사유형'])) {
                    $instructorType = InstructorType::firstOrCreate(
                        ['name' => $data['강사유형']]
                    );
                }

                // 종료일 처리
                $endDate = !empty($data['종료일']) ? Carbon::parse($data['종료일']) : null;
                $isCurrent = empty($endDate);

                // 경력 기록 생성
                CareerRecord::create([
                    'user_id' => $user->id,
                    'school_id' => $school->id,
                    'subject_id' => $subject->id,
                    'instructor_type_id' => $instructorType ? $instructorType->id : null,
                    'start_date' => Carbon::parse($data['시작일']),
                    'end_date' => $endDate,
                    'is_current' => $isCurrent,
                    'position' => $data['직책'] ?? null,
                    'hours_per_week' => $data['주당시간'] ?? null,
                    'description' => $data['업무설명'] ?? null,
                ]);

                $result['success']++;
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $result['error']++;
            $result['errors'][] = "오류 발생: " . $e->getMessage();
        }

        return $result;
    }
}
