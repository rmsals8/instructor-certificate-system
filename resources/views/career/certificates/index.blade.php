@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800">
        경력증명서
    </h2>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">경력증명서 목록</h5>
            @if(Auth::user()->isAdmin())
                <a href="{{ route('career.certificates.create') }}" class="btn btn-primary btn-sm">새 경력증명서 발급</a>
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
                            <th>발급일</th>
                            <th>목적</th>
                            <th>발급자</th>
                            <th>관리</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($certificates as $index => $certificate)
                            <tr>
                                <td>{{ $certificates->firstItem() + $index }}</td>
                                <td>{{ $certificate->certificate_number }}</td>
                                <td>{{ $certificate->issue_date->format('Y-m-d') }}</td>
                                <td>{{ $certificate->purpose }}</td>
                                <td>{{ $certificate->issuerPosition->title ?? '' }} {{ $certificate->issuerPosition->name ?? '' }}</td>
                                <td>
                                    <a href="{{ route('career.certificates.show', $certificate) }}" class="btn btn-sm btn-info">상세</a>
                                    <a href="{{ route('career.certificates.download', $certificate) }}" class="btn btn-sm btn-success">다운로드</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">등록된 경력증명서가 없습니다.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $certificates->links() }}
        </div>
    </div>
@endsection
