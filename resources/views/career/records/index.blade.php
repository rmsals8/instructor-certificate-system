@extends('layouts.app')

@section('content')
<div class="container">
    <h2>경력 기록 목록</h2>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">경력 기록</h5>
            <a href="{{ route('career.records.create') }}" class="btn btn-primary btn-sm">새 경력 추가</a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>학교</th>
                            <th>과목</th>
                            <th>시작일</th>
                            <th>종료일</th>
                            <th>관리</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            <tr>
                                <td>{{ $record->school->name ?? '정보 없음' }}</td>
                                <td>{{ $record->subject->name ?? '정보 없음' }}</td>
                                <td>{{ $record->start_date->format('Y-m-d') }}</td>
                                <td>{{ $record->end_date ? $record->end_date->format('Y-m-d') : '현재' }}</td>
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
                                <td colspan="5" class="text-center">등록된 경력 기록이 없습니다.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($records) && method_exists($records, 'links'))
                {{ $records->links() }}
            @endif
        </div>
    </div>
</div>
@endsection
