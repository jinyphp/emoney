@extends($layout ?? 'jiny-auth::layouts.home')

@section('title', '포인트 관리')

@section('content')
<div class="container-fluid">
    <!-- 헤더 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-2">
                        <i class="bi bi-star-fill text-primary"></i>
                        포인트 관리
                    </h2>
                    <p class="text-muted mb-0">내 포인트 현황을 확인하고 관리하세요</p>
                </div>
                <div>
                    <a href="{{ route('home.emoney.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> 이머니 대시보드로
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 포인트 현황 카드 -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.875rem; font-weight: 600;">현재 포인트</h6>
                            <h1 class="mb-1" style="font-size: 3rem; font-weight: 700; color: #1a1a1a;">
                                {{ number_format($statistics['balance']) }}P
                            </h1>
                            <p class="text-muted mb-0" style="font-size: 0.875rem;">사용 가능한 포인트</p>
                        </div>
                        <div class="text-end">
                            <div class="mb-2">
                                <small class="text-muted">총 적립</small><br>
                                <span class="text-success fw-bold">+{{ number_format($statistics['total_earned']) }}P</span>
                            </div>
                            <div>
                                <small class="text-muted">총 사용</small><br>
                                <span class="text-danger fw-bold">-{{ number_format(abs($statistics['total_used'])) }}P</span>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-4 mb-2">
                            <a href="{{ route('home.emoney.point.log') }}" class="btn btn-primary w-100">
                                <i class="bi bi-clock-history"></i> 포인트 내역
                            </a>
                        </div>
                        <div class="col-md-4 mb-2">
                            <a href="{{ route('home.emoney.point.expiry') }}" class="btn btn-outline-warning w-100">
                                <i class="bi bi-clock"></i> 만료 예정
                            </a>
                        </div>
                        <div class="col-md-4 mb-2">
                            <a href="{{ route('home.emoney.index') }}" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-arrow-left"></i> 돌아가기
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="bi bi-exclamation-triangle"></i> 만료 예정 포인트
                    </h6>
                </div>
                <div class="card-body">
                    @forelse($expiringPoints as $expiry)
                        <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div>
                                <div class="fw-semibold">{{ number_format($expiry->amount) }}P</div>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($expiry->expires_at)->format('Y-m-d') }} 만료</small>
                            </div>
                            <div class="text-end">
                                @php
                                    $daysLeft = \Carbon\Carbon::parse($expiry->expires_at)->diffInDays(now());
                                @endphp
                                @if($daysLeft <= 7)
                                    <span class="badge bg-danger">{{ $daysLeft }}일 남음</span>
                                @elseif($daysLeft <= 30)
                                    <span class="badge bg-warning">{{ $daysLeft }}일 남음</span>
                                @else
                                    <span class="badge bg-success">{{ $daysLeft }}일 남음</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="bi bi-check-circle text-success display-4"></i>
                            <p class="text-muted mt-2 mb-0">만료 예정 포인트가 없습니다.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- 최근 포인트 거래 내역 -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-clock-history"></i> 최근 포인트 거래
                    </h6>
                    <a href="{{ route('home.emoney.point.log') }}" class="btn btn-sm btn-outline-secondary">전체보기</a>
                </div>
                <div class="card-body">
                    @forelse($recentLogs as $log)
                        <div class="d-flex justify-content-between align-items-center py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div>
                                <div class="d-flex align-items-center">
                                    @if($log->transaction_type == 'earn')
                                        <span class="badge bg-success me-2">적립</span>
                                    @elseif($log->transaction_type == 'use')
                                        <span class="badge bg-info me-2">사용</span>
                                    @elseif($log->transaction_type == 'refund')
                                        <span class="badge bg-primary me-2">환불</span>
                                    @elseif($log->transaction_type == 'expire')
                                        <span class="badge bg-danger me-2">만료</span>
                                    @elseif($log->transaction_type == 'admin')
                                        <span class="badge bg-warning me-2">관리자</span>
                                    @endif
                                    <div class="fw-semibold">{{ $log->reason ?? '포인트 거래' }}</div>
                                </div>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i') }}</small>
                            </div>
                            <div class="text-end">
                                <span class="fw-bold {{ $log->amount > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $log->amount > 0 ? '+' : '' }}{{ number_format($log->amount) }}P
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="bi bi-inbox text-muted display-1"></i>
                            <p class="text-muted mt-3 mb-0">포인트 거래 내역이 없습니다.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection