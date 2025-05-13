@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800">
        경력증명서 상세
    </h2>
@endsection

@section('content')
    <div class="mb-3">
        <a href="{{ route('career.certificates.index') }}" class="btn btn-secondary btn-sm">목록으로</a>
        <a href="{{ route('career.certificates.download', $certificate) }}" class="btn btn-primary btn-sm">PDF 다운로드</a>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">경력증명서 정보</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3 fw-bold">증명서 번호:</div>
                <div class="col-md-9">{{ $certificate->certificate_number }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-3 fw-bold">발급일:</div>
                <div class="col-md-9">{{ $certificate->issue_date->format('Y년 m월 d일') }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-3 fw-bold">발급 목적:</div>
                <div class="col-md-9">{{ $certificate->purpose }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-3 fw-bold">발급자:</div>
                <div class="col-md-9">{{ $certificate->issuerPosition->title ?? '' }} {{ $certificate->issuerPosition->name ?? '' }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-3 fw-bold">총 경력:</div>
                <div class="col-md-9">{{ $totalExperience['text'] }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">경력 상세</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>근무기간</th>
                            <th>학교명</th>
                            <th>과목</th>
                            <th>직책</th>
                            <th>경력기간</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($certificate->careerRecords as $record)
                        <tr>
                            <td>{{ $record->start_date->format('Y-m-d') }} ~
                                {{ $record->end_date ? $record->end_date->format('Y-m-d') : '현재' }}
                            </td>
                            <td>{{ $record->school->name ?? '정보 없음' }}</td>
                            <td>{{ $record->subject->name ?? '정보 없음' }}</td>
                            <td>{{ $record->position ?? '정보 없음' }}</td>
                            <td>{{ $record->getDurationText() }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">등록된 경력 정보가 없습니다.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
