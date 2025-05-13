<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>급여명세서</title>
    <style>
        @font-face {
            font-family: 'NanumGothic';
            src: url({{ storage_path('fonts/NanumGothic.ttf') }});
            font-weight: normal;
            font-style: normal;
        }
        body {
            font-family: 'NanumGothic', sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 30px;
            text-align: center;
        }
        .personal-info {
            margin-bottom: 30px;
        }
        .personal-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .personal-info th {
            width: 120px;
            text-align: left;
            padding: 8px 10px;
        }
        .personal-info td {
            padding: 8px 10px;
        }
        .salary-section {
            margin-bottom: 20px;
        }
        .salary-section h3 {
            margin-bottom: 10px;
        }
        .salary-section table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #ddd;
        }
        .salary-section th {
            background-color: #f5f5f5;
            text-align: left;
            padding: 8px;
            border: 1px solid #ddd;
            width: 50%;
        }
        .salary-section td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: right;
        }
        .total {
            margin-top: 20px;
            text-align: right;
            font-weight: bold;
            font-size: 16px;
        }
        .account-info {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .footer {
            margin-top: 50px;
            font-size: 12px;
        }
        .certificate-number {
            margin-top: 20px;
            text-align: left;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="title">급여명세서</h1>
        </div>

        <div class="personal-info">
            <table>
                <tr>
                    <th>성 명</th>
                    <td>{{ $user->name }}</td>
                </tr>
                <tr>
                    <th>생년월일</th>
                    <td>{{ $user->birth_date ? $user->birth_date->format('Y년 m월 d일') : '' }}</td>
                </tr>
                <tr>
                    <th>학교명</th>
                    <td>{{ $school->name }}</td>
                </tr>
                <tr>
                    <th>지급년월</th>
                    <td>{{ $statement->year }}년 {{ $statement->month }}월</td>
                </tr>
            </table>
        </div>

        <div class="salary-section">
            <h3>[급여 내역]</h3>
            <table>
                <tr>
                    <th>학생 1인당 인건비 (A)</th>
                    <td>{{ number_format($salaryDetail->per_student_fee) }}원</td>
                </tr>
                <tr>
                    <th>수업 인원 수 (B)</th>
                    <td>{{ number_format($salaryDetail->student_count) }}명</td>
                </tr>
                <tr>
                    <th>보전금 (C)</th>
                    <td>{{ number_format($salaryDetail->subsidy_amount) }}원</td>
                </tr>
                <tr>
                    <th>추가지급 (D)</th>
                    <td>{{ number_format($salaryDetail->additional_payment) }}원</td>
                </tr>
                <tr>
                    <th>취소 환불 (E)</th>
                    <td>{{ number_format($salaryDetail->cancellation_refund) }}원</td>
                </tr>
                <tr>
                    <th>기타 환불 (F)</th>
                    <td>{{ number_format($salaryDetail->other_refund) }}원</td>
                </tr>
                <tr>
                    <th>총액</th>
                    <td>{{ number_format($salaryDetail->total_amount) }}원</td>
                </tr>
            </table>
            <p>총액 계산: ({{ number_format($salaryDetail->per_student_fee) }}×{{ $salaryDetail->student_count }})+{{ number_format($salaryDetail->subsidy_amount) }}+{{ number_format($salaryDetail->additional_payment) }}-{{ number_format($salaryDetail->cancellation_refund) }}-{{ number_format($salaryDetail->other_refund) }} = {{ number_format($salaryDetail->total_amount) }}원</p>
        </div>

        <div class="salary-section">
            <h3>[공제 내역]</h3>
            <table>
                <tr>
                    <th>산재보험</th>
                    <td>{{ number_format($deductionDetail->industrial_insurance) }}원</td>
                </tr>
                <tr>
                    <th>고용보험</th>
                    <td>{{ number_format($deductionDetail->employment_insurance) }}원</td>
                </tr>
                <tr>
                    <th>소득세</th>
                    <td>{{ number_format($deductionDetail->income_tax) }}원</td>
                </tr>
                <tr>
                    <th>지방소득세</th>
                    <td>{{ number_format($deductionDetail->local_income_tax) }}원</td>
                </tr>
                <tr>
                    <th>기타 공제</th>
                    <td>{{ number_format($deductionDetail->other_deduction) }}원</td>
                </tr>
                <tr>
                    <th>공제 합계액</th>
                    <td>{{ number_format($deductionDetail->total_deduction) }}원</td>
                </tr>
            </table>
        </div>

        <div class="total">
            실지급액: {{ number_format($actualPayment) }}원
        </div>

        <div class="account-info">
            <h3>[지급 정보]</h3>
            @if($paymentInfo)
            <p>
                은행명: {{ $paymentInfo->bank }}<br>
                계좌번호: {{ $paymentInfo->account_number }}<br>
                예금주: {{ $paymentInfo->account_holder }}
            </p>
            @else
            <p>등록된 계좌 정보가 없습니다.</p>
            @endif
        </div>

        <div class="footer">
            <p>
                지급일: {{ $statement->payment_date->format('Y년 m월 d일') }}<br>
                명세서 번호: {{ $statement->certificate_number }}
            </p>
        </div>
    </div>
</body>
</html>
