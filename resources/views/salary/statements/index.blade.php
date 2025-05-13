@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800">
        급여명세서
    </h2>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">급여명세서 목록</h5>
            @if(Auth::user()->isAdmin())
                <a href="{{ route('salary.statements.create') }}" class="btn btn-primary btn-sm">새 급여명세서 발급</a>
            @endif
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>번호</th>
                            <th>증명서 번호</th>
                            <th>지급년월</th>
                            <th>지급일</th>
                            <th>학교</th>
                            <th>상태</th>
                            <th>관리</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($statements as $index => $statement)
                            <tr>
                                <td>{{ $statements->firstItem() + $index }}</td>
                                <td>{{ $statement->certificate_number }}</td>
                                <td>{{ $statement->year }}년 {{ $statement->month }}월</td>
                                <td>{{ $statement->payment_date->format('Y-m-d') }}</td>
                                <td>{{ $statement->school->name ?? '정보 없음' }}</td>
                                <td>
                                    @if($statement->status == 'draft')
                                        <span class="badge bg-warning">초안</span>
                                    @elseif($statement->status == 'issued')
                                        <span class="badge bg-primary">발급됨</span>
                                    @elseif($statement->status == 'viewed')
                                        <span class="badge bg-success">열람됨</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('salary.statements.show', $statement) }}" class="btn btn-sm btn-info">상세</a>
                                    <a href="{{ route('salary.statements.download', $statement) }}" class="btn btn-sm btn-success">다운로드</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">등록된 급여명세서가 없습니다.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $statements->links() }}
        </div>
    </div>
@endsection
