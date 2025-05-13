<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>경력증명서</title>
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
        .career-list {
            margin-bottom: 30px;
        }
        .career-list h3 {
            margin-bottom: 10px;
        }
        .career-list table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #ddd;
        }
        .career-list th {
            background-color: #f5f5f5;
            text-align: center;
            padding: 8px;
            border: 1px solid #ddd;
        }
        .career-list td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .total-exp {
            margin-bottom: 30px;
            text-align: right;
            font-weight: bold;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
        }
        .signature {
            margin-top: 50px;
            text-align: right;
        }
        .certificate-number {
            margin-top: 50px;
            text-align: left;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="title">경력증명서</h1>
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
                    <th>주 소</th>
                    <td>{{ $user->address }}</td>
                </tr>
            </table>
        </div>

        <div class="career-list">
            <h3>[경력사항]</h3>
            <table>
                <thead>
                    <tr>
                        <th>근무기간</th>
                        <th>직책</th>
                        <th>과목</th>
                        <th>근무기관</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($careerRecords as $record)
                    <tr>
                        <td>
                            {{ $record->start_date->format('Y-m-d') }} ~
                            {{ $record->end_date ? $record->end_date->format('Y-m-d') : '현재' }}
                        </td>
                        <td>{{ $record->position }}</td>
                        <td>{{ $record->subject->name }}</td>
                        <td>{{ $record->school->name }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="total-exp">
            총 경력: {{ $totalExperience['text'] }}
        </div>

        <p>위와 같이 경력이 있음을 증명합니다.</p>

        <div class="signature">
            {{ $certificate->issue_date->format('Y년 m월 d일') }}<br><br>
            {{ $issuerPosition->title }} {{ $issuerPosition->name }} (인)
        </div>

        <div class="certificate-number">
            증명서 번호: {{ $certificate->certificate_number }}
        </div>
    </div>
</body>
</html>
