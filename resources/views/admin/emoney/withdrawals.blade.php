@extends('jiny-admin::layouts.admin.sidebar')

@section('title', '출금 내역')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">출금 내역</h2>
                    <p class="text-muted mb-0">이머니 출금 목록</p>
                </div>
                <a href="{{ route('admin.emoney.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> 이머니 관리
                </a>
            </div>

            <!-- 출금 목록 -->
            <div class="card">
                <div class="card-body">
                    @if(isset($withdrawals) && $withdrawals->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>사용자</th>
                                        <th>금액</th>
                                        <th>상태</th>
                                        <th>날짜</th>
                                        <th>메모</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($withdrawals as $withdrawal)
                                        <tr>
                                            <td>{{ $withdrawal->id }}</td>
                                            <td>{{ $withdrawal->user->name ?? '-' }}</td>
                                            <td>{{ number_format($withdrawal->amount) }} 원</td>
                                            <td>
                                                <span class="badge bg-{{ $withdrawal->status === 'completed' ? 'success' : 'warning' }}">
                                                    {{ $withdrawal->status }}
                                                </span>
                                            </td>
                                            <td>{{ $withdrawal->created_at->format('Y-m-d H:i') }}</td>
                                            <td>{{ $withdrawal->memo ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if(method_exists($withdrawals, 'links'))
                            <div class="mt-3">
                                {{ $withdrawals->links() }}
                            </div>
                        @endif
                    @else
                        <p class="text-muted mb-0">출금 내역이 없습니다.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
