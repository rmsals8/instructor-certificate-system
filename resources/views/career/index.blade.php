@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800">
        경력 기록
    </h2>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">경력 기록 목록</h5>
            <a href="{{ route('career.records.create') }}" class="btn btn-primary btn-sm">새 경력 추가</a>
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
                            <th>학교</th>
                            <th>과목</th>
                            <th>강사 유형</th>
                            <th>시작일</th>
                            <th>종료일</th>
                            <th>경력 기간</th>
                            <th>관리</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $index => $record)
                            <tr>
                                <td>{{ $records->firstItem() + $index }}</td>
                                <td>{{ $record->school->name ?? '정보 없음' }}</td>
                                <td>{{ $record->subject->name ?? '정보 없음' }}</td>
                                <td>{{ $record->instructorType->name ?? '정보 없음' }}</td>
                                <td>{{ $record->start_date->format('Y-m-d') }}</td>
                                <td>{{ $record->end_date ? $record->end_date->format('Y-m-d') : '현재' }}</td>
                                <td>{{ $record->getDurationText() }}</td>
                                <td>
                                    <a href="{{ route('career.records.edit', $record) }}" class="btn btn-sm btn-info">수정</a>
                                    <form method="POST" action="{{ route('career.records.destroy', $record) }}" class="d-inline" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">삭제</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">등록된 경력 기록이 없습니다.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $records->links() }}
        </div>
    </div>
@endsection
