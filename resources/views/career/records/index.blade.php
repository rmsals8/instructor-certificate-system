# 간단한 내용의 경력 기록 index 뷰 생성
@"
@extends('layouts.app')

@section('content')
<div class="container">
    <h2>경력 기록 목록</h2>
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>학교</th>
                        <th>과목</th>
                        <th>시작일</th>
                        <th>종료일</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(\$records as \$record)
                    <tr>
                        <td>{{ \$record->school->name ?? '정보 없음' }}</td>
                        <td>{{ \$record->subject->name ?? '정보 없음' }}</td>
                        <td>{{ \$record->start_date->format('Y-m-d') }}</td>
                        <td>{{ \$record->end_date ? \$record->end_date->format('Y-m-d') : '현재' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
"@ | Out-File -FilePath resources\views\career\records\index.blade.php -Encoding utf8
