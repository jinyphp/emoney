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
                <div>
                    <a href="{{ route('admin.auth.emoney.deposits.index') }}" class="btn btn-primary me-2">
                        <i class="fas fa-plus-circle me-1"></i>충전관리
                    </a>
                    <a href="{{ route('admin.auth.emoney.withdrawals.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-minus-circle me-1"></i>출금관리
                    </a>
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

            <!-- 검색 및 필터 -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.auth.emoney.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">검색</label>
                            <input type="text" class="form-control" id="search" name="search"
                                   placeholder="이름, 이메일, ID 검색"
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">상태</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">전체</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>활성</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>비활성</option>
                                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>정지</option>
                                <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>차단</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="sort_by" class="form-label">정렬</label>
                            <select class="form-select" id="sort_by" name="sort_by">
                                <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>생성일</option>
                                <option value="balance" {{ request('sort_by') === 'balance' ? 'selected' : '' }}>잔액</option>
                                <option value="points" {{ request('sort_by') === 'points' ? 'selected' : '' }}>포인트</option>
                                <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>이름</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                                <a href="{{ route('admin.auth.emoney.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-undo"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 지갑 목록 -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-wallet me-2"></i>이머니 지갑 목록
                        <span class="badge bg-primary ms-2">{{ $wallets->total() ?? 0 }}개</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if(isset($wallets) && $wallets->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-hashtag me-1 text-muted"></i>
                                                ID
                                            </div>
                                        </th>
                                        <th class="border-0">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user me-1 text-muted"></i>
                                                사용자
                                            </div>
                                        </th>
                                        <th class="border-0 text-end">
                                            <div class="d-flex align-items-center justify-content-end">
                                                <i class="fas fa-won-sign me-1 text-muted"></i>
                                                잔액
                                            </div>
                                        </th>
                                        <th class="border-0 text-end">
                                            <div class="d-flex align-items-center justify-content-end">
                                                <i class="fas fa-coins me-1 text-muted"></i>
                                                포인트
                                            </div>
                                        </th>
                                        <th class="border-0 text-center">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <i class="fas fa-info-circle me-1 text-muted"></i>
                                                상태
                                            </div>
                                        </th>
                                        <th class="border-0">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-calendar me-1 text-muted"></i>
                                                생성일
                                            </div>
                                        </th>
                                        <th class="border-0 text-center">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <i class="fas fa-cogs me-1 text-muted"></i>
                                                액션
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($wallets as $wallet)
                                        <tr>
                                            <td class="align-middle">
                                                <span class="fw-bold text-primary">#{{ $wallet->id }}</span>
                                            </td>
                                            <td class="align-middle">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm rounded-circle bg-primary text-white me-2">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <div>
                                                        @if($wallet->name || $wallet->email)
                                                            <div class="fw-semibold">{{ $wallet->name ?: ($wallet->user->name ?? '-') }}</div>
                                                            @if($wallet->email)
                                                                <small class="text-muted">{{ $wallet->email }}</small>
                                                            @endif
                                                        @else
                                                            <div class="fw-semibold">{{ $wallet->user->name ?? '-' }}</div>
                                                            @if($wallet->user && $wallet->user->email)
                                                                <small class="text-muted">{{ $wallet->user->email }}</small>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="align-middle text-end">
                                                <div class="fw-bold text-success fs-6">
                                                    {{ number_format($wallet->balance) }} 원
                                                </div>
                                                @if(($wallet->total_deposit ?? 0) > 0)
                                                    <small class="text-muted">
                                                        입금: {{ number_format($wallet->total_deposit ?? 0) }}원
                                                    </small>
                                                @endif
                                            </td>
                                            <td class="align-middle text-end">
                                                <div class="fw-bold text-warning fs-6">
                                                    {{ number_format($wallet->points ?? 0) }} P
                                                </div>
                                            </td>
                                            <td class="align-middle text-center">
                                                @php
                                                    $statusColors = [
                                                        'active' => 'success',
                                                        'inactive' => 'secondary',
                                                        'suspended' => 'warning',
                                                        'blocked' => 'danger'
                                                    ];
                                                    $statusLabels = [
                                                        'active' => '활성',
                                                        'inactive' => '비활성',
                                                        'suspended' => '정지',
                                                        'blocked' => '차단'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $statusColors[$wallet->status] ?? 'secondary' }}">
                                                    {{ $statusLabels[$wallet->status] ?? $wallet->status }}
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                <div>{{ $wallet->created_at->format('Y-m-d') }}</div>
                                                <small class="text-muted">{{ $wallet->created_at->format('H:i') }}</small>
                                            </td>
                                            <td class="align-middle text-center">
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                                            onclick="viewWallet({{ $wallet->id }})"
                                                            title="지갑 상세보기">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-info"
                                                            onclick="viewTransactions({{ $wallet->id }})"
                                                            title="거래 내역">
                                                        <i class="fas fa-history"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-warning"
                                                            onclick="adjustBalance({{ $wallet->id }})"
                                                            title="잔액 조정">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if(method_exists($wallets, 'links'))
                            <div class="card-footer bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-muted">
                                        {{ $wallets->firstItem() ?? 0 }} - {{ $wallets->lastItem() ?? 0 }} / {{ $wallets->total() ?? 0 }}개 표시
                                    </div>
                                    {{ $wallets->links() }}
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-wallet fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">이머니 지갑이 없습니다</h5>
                            <p class="text-muted mb-0">등록된 이머니 지갑이 없습니다.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS 스타일 -->
<style>
.avatar {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

.avatar-sm {
    width: 28px;
    height: 28px;
    font-size: 12px;
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-group .btn {
    margin: 0 1px;
}
</style>

<!-- JavaScript 함수들 -->
<script>
function viewWallet(walletId) {
    // 지갑 상세 정보 모달 또는 페이지로 이동
    const url = `{{ route('admin.auth.emoney.index') }}/${walletId}`;
    window.location.href = url;
}

function viewTransactions(walletId) {
    // 거래 내역 페이지로 이동 또는 모달 표시
    const url = `{{ route('admin.auth.emoney.index') }}/${walletId}/transactions`;
    window.location.href = url;
}

function adjustBalance(walletId) {
    // 잔액 조정 모달 표시
    if (confirm('이 지갑의 잔액을 조정하시겠습니까?')) {
        // 모달 또는 별도 페이지로 이동
        const url = `{{ route('admin.auth.emoney.index') }}/${walletId}/edit`;
        window.location.href = url;
    }
}

// 검색 폼 자동 제출 (Enter 키)
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });
    }

    // 상태 및 정렬 변경시 자동 제출
    const statusSelect = document.getElementById('status');
    const sortSelect = document.getElementById('sort_by');

    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            this.form.submit();
        });
    }

    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            this.form.submit();
        });
    }
});

// 툴팁 초기화 (Bootstrap 5)
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection
