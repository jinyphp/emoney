@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '이머니 관리')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">이머니 관리</h2>
                    <p class="text-muted mb-0">사용자 전자지갑 목록</p>
                </div>
            </div>

            <!-- 통계 카드 -->
            @if(isset($stats))
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="text-muted mb-2">전체 잔액</h6>
                                <h3 class="mb-0">{{ number_format($stats['total_balance'] ?? 0) }} 원</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="text-muted mb-2">전체 포인트</h6>
                                <h3 class="mb-0">{{ number_format($stats['total_points'] ?? 0) }} P</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="text-muted mb-2">활성 지갑</h6>
                                <h3 class="mb-0">{{ number_format($stats['active_wallets'] ?? 0) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="text-muted mb-2">전체 지갑</h6>
                                <h3 class="mb-0">{{ number_format($stats['total_wallets'] ?? 0) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- 지갑 목록 -->
            <div class="card">
                <div class="card-body">
                    @if(isset($wallets) && $wallets->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>사용자</th>
                                        <th>잔액</th>
                                        <th>포인트</th>
                                        <th>상태</th>
                                        <th>생성일</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($wallets as $wallet)
                                        <tr>
                                            <td>{{ $wallet->id }}</td>
                                            <td>{{ $wallet->user->name ?? '-' }}</td>
                                            <td>{{ number_format($wallet->balance) }} 원</td>
                                            <td>{{ number_format($wallet->points) }} P</td>
                                            <td>
                                                <span class="badge bg-{{ $wallet->status === 'active' ? 'success' : 'secondary' }}">
                                                    {{ $wallet->status }}
                                                </span>
                                            </td>
                                            <td>{{ $wallet->created_at->format('Y-m-d') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if(method_exists($wallets, 'links'))
                            <div class="mt-3">
                                {{ $wallets->links() }}
                            </div>
                        @endif
                    @else
                        <p class="text-muted mb-0">이머니 지갑이 없습니다.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
