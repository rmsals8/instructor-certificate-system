@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800">
        급여명세서 상세
    </h2>
@endsection

@section('content')
    <div class="mb-3">
        <a href="{{ route('salary.statements.index') }}" class="btn btn-secondary btn-sm">목록으로</a>
        <a href="{{ route('salary.statements.download', $statement) }}" class="btn btn-primary btn-sm">PDF 다운로드</a>

        @if($statement->status == 'issued' && Auth::id() == $statement->user_id)
            <form action="{{ route('salary.statements.viewed', $statement) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success btn-sm">열람 확인</button>
            </form>
        @endif
    </div>

    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">기본 정보</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">증명서 번호:</div>
                        <div class="col-md-9">{{ $statement->certificate_number }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">지급년월:</div>
                        <div class="col-md-9">{{ $statement->year }}년 {{ $statement->month }}월</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">지급일:</div>
                        <div class="col-md-9">{{ $statement->payment_date->format('Y년 m월 d일') }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">학교:</div>
                        <div class="col-md-9">{{ $statement->school->name ?? '정보 없음' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">상태:</div>
                        <div class="col-md-9">
                            @if($statement->status == 'draft')
                                <span class="badge bg-warning">초안</span>
                            @elseif($statement->status == 'issued')
                                <span class="badge bg-primary">발급됨</span>
                            @elseif($statement->status == 'viewed')
                                <span class="badge bg-success">열람됨</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">급여 내역</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>학생 1인당 인건비 (A)</th>
                            <td class="text-end">{{ number_format($statement->salaryDetail->per_student_fee) }}원</td>
                        </tr>
                        <tr>
                            <th>수업 인원 수 (B)</th>
                            <td class="text-end">{{ number_format($statement->salaryDetail->student_count) }}명</td>
                        </tr>
                        <tr>
                            <th>보전금 (C)</th>
                            <td class="text-end">{{ number_format($statement->salaryDetail->subsidy_amount) }}원</td>
                        </tr>
                        <tr>
                            <th>추가지급 (D)</th>
                            <td class="text-end">{{ number_format($statement->salaryDetail->additional_payment) }}원</td>
                        </tr>
                        <tr>
                            <th>취소 환불 (E)</th>
                            <td class="text-end">{{ number_format($statement->salaryDetail->cancellation_refund) }}원</td>
                        </tr>
                        <tr>
                            <th>기타 환불 (F)</th>
                            <td class="text-end">{{ number_format($statement->salaryDetail->other_refund) }}원</td>
                        </tr>
                        <tr class="table-primary">
                            <th>총액</th>
                            <td class="text-end fw-bold">{{ number_format($statement->salaryDetail->total_amount) }}원</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">공제 내역</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>산재보험</th>
                            <td class="text-end">{{ number_format($statement->deductionDetail->industrial_insurance) }}원</td>
                        </tr>
                        <tr>
                            <th>고용보험</th>
                            <td class="text-end">{{ number_format($statement->deductionDetail->employment_insurance) }}원</td>
                        </tr>
                        <tr>
                            <th>소득세</th>
                            <td class="text-end">{{ number_format($statement->deductionDetail->income_tax) }}원</td>
                        </tr>
                        <tr>
                            <th>지방소득세</th>
                            <td class="text-end">{{ number_format($statement->deductionDetail->local_income_tax) }}원</td>
                        </tr>
                        <tr>
                            <th>기타 공제</th>
                            <td class="text-end">{{ number_format($statement->deductionDetail->other_deduction) }}원</td>
                        </tr>
                        <tr class="table-danger">
                            <th>공제 합계액</th>
                            <td class="text-end fw-bold">{{ number_format($statement->deductionDetail->total_deduction) }}원</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">실지급액</h5>
                </div>
                <div class="card-body">
                    <h3 class="text-end">{{ number_format($actualPayment) }}원</h3>
                </div>
            </div>
        </div>

        @if($paymentInfo)
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">지급 정보</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">은행명:</div>
                        <div class="col-md-9">{{ $paymentInfo->bank }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">계좌번호:</div>
                        <div class="col-md-9">{{ $paymentInfo->account_number }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3 fw-bold">예금주:</div>
                        <div class="col-md-9">{{ $paymentInfo->account_holder }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection
