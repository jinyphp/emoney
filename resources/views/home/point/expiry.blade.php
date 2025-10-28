@extends($layout ?? 'jiny-auth::layouts.home')

@section('title', '포인트 만료 관리')

@section('content')
<div class="container-fluid">
    <!-- 성공/에러 메시지 표시 -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- 헤더 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-2">
                        <i class="bi bi-clock-history text-warning"></i>
                        포인트 만료 관리
                    </h2>
                    <p class="text-muted mb-0">포인트 만료 예정일을 확인하고 관리하세요</p>
                </div>
                <div>
                    <a href="{{ route('home.emoney.point.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> 포인트 관리로
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 만료 통계 카드 -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-2" style="font-size: 0.75rem;">7일 내 만료</h6>
                            <h4 class="mb-0">
                                @php
                                    $expiring7Days = $pointExpiry->where('expires_at', '<=', now()->addDays(7))->sum('amount');
                                @endphp
                                {{ number_format($expiring7Days) }}P
                            </h4>
                        </div>
                        <i class="bi bi-exclamation-triangle fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-white-50 mb-2" style="font-size: 0.75rem;">30일 내 만료</h6>
                            <h4 class="mb-0">
                                @php
                                    $expiring30Days = $pointExpiry->where('expires_at', '<=', now()->addDays(30))->sum('amount');
                                @endphp
                                {{ number_format($expiring30Days) }}P
                            </h4>
                        </div>
                        <i class="bi bi-clock fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-white-50 mb-2" style="font-size: 0.75rem;">총 만료 예정</h6>
                            <h4 class="mb-0">{{ number_format($pointExpiry->sum('amount')) }}P</h4>
                        </div>
                        <i class="bi bi-calendar-event fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-white-50 mb-2" style="font-size: 0.75rem;">이미 만료</h6>
                            <h4 class="mb-0">{{ number_format($expiredPoints->sum('amount')) }}P</h4>
                        </div>
                        <i class="bi bi-x-circle fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 만료 예정 포인트 -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-clock-history"></i> 만료 예정 포인트</h6>
                        @if(method_exists($pointExpiry, 'total'))
                            <span class="text-muted">총 {{ $pointExpiry->total() }}건</span>
                        @endif
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($pointExpiry->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>포인트</th>
                                    <th>만료일</th>
                                    <th>남은 기간</th>
                                    <th>상태</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pointExpiry as $expiry)
                                <tr>
                                    <td>
                                        <strong>{{ number_format($expiry->amount) }}P</strong>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ \Carbon\Carbon::parse($expiry->expires_at)->format('Y-m-d') }}</strong>
                                            <br>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($expiry->expires_at)->format('H:i') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $daysLeft = \Carbon\Carbon::parse($expiry->expires_at)->diffInDays(now());
                                            $isToday = \Carbon\Carbon::parse($expiry->expires_at)->isToday();
                                            $isPast = \Carbon\Carbon::parse($expiry->expires_at)->isPast();
                                        @endphp

                                        @if($isPast)
                                            <span class="text-danger">만료됨</span>
                                        @elseif($isToday)
                                            <span class="text-danger fw-bold">오늘 만료</span>
                                        @elseif($daysLeft <= 7)
                                            <span class="text-warning fw-bold">{{ $daysLeft }}일 남음</span>
                                        @elseif($daysLeft <= 30)
                                            <span class="text-info">{{ $daysLeft }}일 남음</span>
                                        @else
                                            <span class="text-muted">{{ $daysLeft }}일 남음</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($isPast)
                                            <span class="badge bg-danger">만료</span>
                                        @elseif($isToday)
                                            <span class="badge bg-warning">오늘 만료</span>
                                        @elseif($daysLeft <= 7)
                                            <span class="badge bg-warning">긴급</span>
                                        @elseif($daysLeft <= 30)
                                            <span class="badge bg-info">주의</span>
                                        @else
                                            <span class="badge bg-success">정상</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- 페이지네이션 -->
                    @if(method_exists($pointExpiry, 'links') && $pointExpiry->hasPages())
                    <div class="card-footer">
                        <nav aria-label="포인트 만료 페이지네이션">
                            {{ $pointExpiry->links() }}
                        </nav>
                    </div>
                    @endif
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-check-circle display-1 text-success"></i>
                        <h5 class="mt-3 mb-3">만료 예정인 포인트가 없습니다</h5>
                        <p class="text-muted mb-4">현재 만료 예정인 포인트가 없습니다.</p>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="{{ route('home.emoney.point.index') }}" class="btn btn-warning">
                                <i class="bi bi-star"></i> 포인트 관리로
                            </a>
                            <a href="{{ route('home.emoney.point.log') }}" class="btn btn-info">
                                <i class="bi bi-list-ul"></i> 거래 내역 보기
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 이미 만료된 포인트 -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-x-circle"></i> 최근 만료된 포인트</h6>
                </div>
                <div class="card-body">
                    @if($expiredPoints->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($expiredPoints->take(10) as $expired)
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <div class="fw-semibold text-danger">{{ number_format($expired->amount) }}P</div>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($expired->expires_at)->format('Y-m-d') }} 만료
                                    </small>
                                </div>
                                <span class="badge bg-danger">만료</span>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-check-circle text-success fs-1"></i>
                            <p class="text-muted mt-2 mb-0">최근 만료된 포인트가 없습니다.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 만료 안내 -->
            <div class="card mt-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> 포인트 만료 안내</h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="mb-2">
                            <i class="bi bi-dot text-primary"></i>
                            <strong>포인트 유효기간:</strong> 적립일로부터 1년
                        </div>
                        <div class="mb-2">
                            <i class="bi bi-dot text-warning"></i>
                            <strong>만료 알림:</strong> 만료 30일, 7일, 1일 전 알림
                        </div>
                        <div class="mb-2">
                            <i class="bi bi-dot text-success"></i>
                            <strong>사용 권장:</strong> 만료일이 가까운 포인트부터 자동 사용
                        </div>
                        <div>
                            <i class="bi bi-dot text-danger"></i>
                            <strong>만료 후:</strong> 복구 불가능
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 빠른 액션 -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">빠른 액션</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('home.emoney.point.log') }}" class="btn btn-outline-primary w-100">
                                <i class="bi bi-list-ul fs-4 d-block mb-2"></i>
                                포인트 내역
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('home.emoney.point.index') }}" class="btn btn-outline-warning w-100">
                                <i class="bi bi-star fs-4 d-block mb-2"></i>
                                포인트 관리
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-outline-success w-100" onclick="alert('포인트 사용 기능은 준비 중입니다.')">
                                <i class="bi bi-cart fs-4 d-block mb-2"></i>
                                포인트 사용
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-outline-info w-100" onclick="alert('알림 설정 기능은 준비 중입니다.')">
                                <i class="bi bi-bell fs-4 d-block mb-2"></i>
                                알림 설정
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// 만료 경고 알림
document.addEventListener('DOMContentLoaded', function() {
    const expiring7Days = {{ $pointExpiry->where('expires_at', '<=', now()->addDays(7))->sum('amount') }};

    if (expiring7Days > 0) {
        console.log(`7일 이내에 ${expiring7Days.toLocaleString()}P가 만료될 예정입니다.`);
    }
});
</script>
@endsection