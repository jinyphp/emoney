@extends($layout ?? 'jiny-auth::layouts.home')

@section('title', '이머니 & 포인트 관리')

@section('content')
<div class="container-fluid">
    <!-- 헤더 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-2">
                        <i class="bi bi-wallet2 text-success"></i>
                        이머니 & 포인트
                    </h2>
                    <p class="text-muted mb-0">내 이머니와 포인트 현황을 확인하고 관리하세요</p>
                </div>
                <div>
                    <a href="{{ route('home.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> 대시보드로
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 이머니 & 포인트 요약 카드 -->
    <div class="row mb-4">
        <!-- 이머니 잔액 -->
        <div class="col-lg-4 col-md-12 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.875rem; font-weight: 600;">이머니 잔액</h6>
                            <h2 class="mb-1" style="font-size: 2rem; font-weight: 700; color: #1a1a1a;">
                                ₩{{ number_format($emoney->balance ?? 0) }}
                            </h2>
                            <p class="text-muted mb-0" style="font-size: 0.875rem;">Current balance</p>
                        </div>
                        <div>
                            <a href="{{ route('home.emoney.deposit') }}" class="btn btn-success btn-sm">
                                <i class="bi bi-plus-circle"></i> 충전
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 포인트 잔액 -->
        <div class="col-lg-4 col-md-12 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.875rem; font-weight: 600;">포인트 잔액</h6>
                            <h2 class="mb-1" style="font-size: 2rem; font-weight: 700; color: #1a1a1a;">
                                {{ number_format($point->balance ?? 0) }}P
                            </h2>
                            <p class="text-muted mb-0" style="font-size: 0.875rem;">Available points</p>
                        </div>
                        <div>
                            <a href="{{ route('home.emoney.point.index') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-star"></i> 관리
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 총 사용액 -->
        <div class="col-lg-4 col-md-12 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.875rem; font-weight: 600;">총 사용액</h6>
                            <h2 class="mb-1" style="font-size: 2rem; font-weight: 700; color: #1a1a1a;">
                                ₩{{ number_format(($emoney->total_used ?? 0) + ($point->total_used ?? 0)) }}
                            </h2>
                            <p class="text-muted mb-0" style="font-size: 0.875rem;">Total spending</p>
                        </div>
                        <div>
                            <a href="{{ route('home.emoney.log') }}" class="btn btn-warning btn-sm">
                                <i class="bi bi-clock-history"></i> 내역
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 빠른 액션 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">빠른 액션</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('home.emoney.deposit') }}" class="btn btn-success w-100">
                                <i class="bi bi-plus-circle"></i><br>
                                이머니 충전
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('home.emoney.withdraw') }}" class="btn btn-outline-success w-100">
                                <i class="bi bi-arrow-down-circle"></i><br>
                                이머니 출금
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('home.emoney.bank.index') }}" class="btn btn-info w-100">
                                <i class="bi bi-bank"></i><br>
                                계좌 관리
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('home.emoney.log') }}" class="btn btn-outline-info w-100">
                                <i class="bi bi-list-ul"></i><br>
                                전체 내역
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 이머니 거래 내역 -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-clock-history"></i> 최근 이머니 거래
                    </h6>
                    <a href="{{ route('home.emoney.log') }}" class="btn btn-sm btn-outline-secondary">전체보기</a>
                </div>
                <div class="card-body">
                    @forelse($emoneyLogs as $log)
                        <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div>
                                <div class="fw-semibold">{{ $log->description ?? '거래' }}</div>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i') }}</small>
                            </div>
                            <div class="text-end">
                                @php
                                    $amount = 0;
                                    if ($log->type === 'deposit' && $log->deposit) {
                                        $amount = (int) $log->deposit;
                                    } elseif ($log->type === 'withdraw' && $log->withdraw) {
                                        $amount = -(int) $log->withdraw;
                                    }
                                @endphp
                                <span class="fw-bold {{ $amount > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $amount > 0 ? '+' : '' }}{{ number_format($amount) }}원
                                </span>
                                <div class="small text-muted">{{ $log->type === 'deposit' ? '충전' : '출금' }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="bi bi-inbox text-muted"></i>
                            <p class="text-muted mt-2 mb-0">거래 내역이 없습니다.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- 포인트 거래 내역 -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-clock-history"></i> 최근 포인트 거래
                    </h6>
                    <a href="{{ route('home.emoney.point.log') }}" class="btn btn-sm btn-outline-secondary">전체보기</a>
                </div>
                <div class="card-body">
                    @forelse($pointLogs as $log)
                        <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div>
                                <div class="fw-semibold">{{ $log->description ?? '포인트 거래' }}</div>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i') }}</small>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold {{ ($log->points ?? 0) > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ ($log->points ?? 0) > 0 ? '+' : '' }}{{ number_format($log->points ?? 0) }}P
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="bi bi-inbox text-muted"></i>
                            <p class="text-muted mt-2 mb-0">포인트 거래 내역이 없습니다.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- 등록된 은행 계좌 -->
    @if($bankAccounts->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-bank"></i> 등록된 은행 계좌
                    </h6>
                    <a href="{{ route('home.emoney.bank.index') }}" class="btn btn-sm btn-outline-primary">계좌 관리</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($bankAccounts as $account)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $account->bank_name ?? '은행' }}</h6>
                                    <p class="card-text">
                                        <span class="text-muted">계좌번호:</span><br>
                                        {{ $account->account_number ?? '' }}
                                    </p>
                                    <p class="card-text">
                                        <span class="text-muted">예금주:</span><br>
                                        {{ $account->account_holder ?? '' }}
                                    </p>
                                    @if($account->is_default)
                                        <span class="badge bg-success">기본 계좌</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection