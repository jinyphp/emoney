@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '포인트 통계')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">포인트 통계</h2>
                    <p class="text-muted mb-0">포인트 시스템 종합 통계 및 분석</p>
                </div>
                <div>
                    <form method="GET" action="{{ route('admin.auth.point.stats') }}" class="d-inline">
                        <select name="period" class="form-select" onchange="this.form.submit()">
                            <option value="1week" {{ $period == '1week' ? 'selected' : '' }}>최근 1주일</option>
                            <option value="1month" {{ $period == '1month' ? 'selected' : '' }}>최근 1개월</option>
                            <option value="3month" {{ $period == '3month' ? 'selected' : '' }}>최근 3개월</option>
                            <option value="6month" {{ $period == '6month' ? 'selected' : '' }}>최근 6개월</option>
                            <option value="1year" {{ $period == '1year' ? 'selected' : '' }}>최근 1년</option>
                        </select>
                    </form>
                </div>
            </div>

            <!-- 전체 통계 카드 -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="text-muted">전체 사용자</h6>
                            <h3 class="text-primary">{{ number_format($overall_stats['total_users']) }}</h3>
                            <small class="text-muted">포인트 계정 수</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="text-muted">총 잔액</h6>
                            <h3 class="text-warning">{{ number_format($overall_stats['total_balance'], 0) }}P</h3>
                            <small class="text-muted">시스템 전체 보유</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="text-muted">총 적립</h6>
                            <h3 class="text-success">{{ number_format($overall_stats['total_earned'], 0) }}P</h3>
                            <small class="text-muted">누적 발행량</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="text-muted">총 사용</h6>
                            <h3 class="text-danger">{{ number_format($overall_stats['total_used'], 0) }}P</h3>
                            <small class="text-muted">누적 소모량</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 추가 전체 통계 -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="text-muted">평균 보유량</h6>
                            <h4 class="text-info">{{ number_format($overall_stats['avg_balance'], 0) }}P</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="text-muted">보유 사용자</h6>
                            <h4 class="text-success">{{ number_format($overall_stats['users_with_balance']) }}명</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="text-muted">최대 보유량</h6>
                            <h4 class="text-warning">{{ number_format($overall_stats['max_balance'], 0) }}P</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="text-muted">만료 예정</h6>
                            <h4 class="text-danger">{{ number_format($overall_stats['pending_expiry_amount'], 0) }}P</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 거래 유형별 통계 -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">거래 유형별 통계 ({{ $period }})</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>유형</th>
                                            <th>건수</th>
                                            <th>총 금액</th>
                                            <th>평균 금액</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($transaction_type_stats as $stat)
                                            <tr>
                                                <td>
                                                    <span class="badge
                                                        @if($stat->transaction_type == 'earn') bg-success
                                                        @elseif($stat->transaction_type == 'use') bg-danger
                                                        @elseif($stat->transaction_type == 'refund') bg-info
                                                        @elseif($stat->transaction_type == 'expire') bg-warning
                                                        @elseif($stat->transaction_type == 'admin') bg-secondary
                                                        @else bg-light text-dark
                                                        @endif">
                                                        @switch($stat->transaction_type)
                                                            @case('earn') 적립 @break
                                                            @case('use') 사용 @break
                                                            @case('refund') 환불 @break
                                                            @case('expire') 만료 @break
                                                            @case('admin') 관리자 @break
                                                            @default {{ $stat->transaction_type }}
                                                        @endswitch
                                                    </span>
                                                </td>
                                                <td>{{ number_format($stat->count) }}건</td>
                                                <td>{{ number_format($stat->total_amount, 0) }}P</td>
                                                <td>{{ number_format($stat->avg_amount, 0) }}P</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">참조 유형별 통계 ({{ $period }})</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>참조 유형</th>
                                            <th>건수</th>
                                            <th>총 금액</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($reference_type_stats as $stat)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-info">{{ $stat->reference_type }}</span>
                                                </td>
                                                <td>{{ number_format($stat->count) }}건</td>
                                                <td>{{ number_format($stat->total_amount, 0) }}P</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">데이터가 없습니다.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 상위 포인트 보유자 -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">상위 포인트 보유자</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>순위</th>
                                            <th>사용자</th>
                                            <th>보유 포인트</th>
                                            <th>총 적립</th>
                                            <th>총 사용</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($top_holders as $index => $holder)
                                            <tr>
                                                <td>
                                                    @if($index == 0)
                                                        <span class="badge bg-warning">🥇</span>
                                                    @elseif($index == 1)
                                                        <span class="badge bg-secondary">🥈</span>
                                                    @elseif($index == 2)
                                                        <span class="badge bg-warning">🥉</span>
                                                    @else
                                                        {{ $index + 1 }}
                                                    @endif
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong>{{ $holder->user->name ?? 'N/A' }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $holder->user->email ?? 'N/A' }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary">{{ number_format($holder->balance, 0) }}P</span>
                                                </td>
                                                <td class="text-success">{{ number_format($holder->total_earned, 0) }}P</td>
                                                <td class="text-danger">{{ number_format($holder->total_used, 0) }}P</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">포인트 잔액 분포</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>잔액 구간</th>
                                            <th>사용자 수</th>
                                            <th>총 포인트</th>
                                            <th>비율</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $totalUsers = $balance_distribution->sum('count'); @endphp
                                        @foreach($balance_distribution as $dist)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-info">{{ $dist->range }}P</span>
                                                </td>
                                                <td>{{ number_format($dist->count) }}명</td>
                                                <td>{{ number_format($dist->total_amount, 0) }}P</td>
                                                <td>
                                                    @if($totalUsers > 0)
                                                        {{ number_format(($dist->count / $totalUsers) * 100, 1) }}%
                                                    @else
                                                        0%
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 기간별 거래 추이 -->
            @if($period_stats->count() > 0)
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">기간별 거래 추이 ({{ $period }})</h5>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>기간</th>
                                    <th>총 거래</th>
                                    <th>적립</th>
                                    <th>사용</th>
                                    <th>환불</th>
                                    <th>만료</th>
                                    <th>관리자</th>
                                    <th>순 증감</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($period_stats as $stat)
                                    <tr>
                                        <td>{{ $stat->period }}</td>
                                        <td>{{ number_format($stat->total_transactions) }}건</td>
                                        <td class="text-success">
                                            {{ number_format($stat->earn_count) }}건
                                            <br>
                                            <small>{{ number_format($stat->total_earned, 0) }}P</small>
                                        </td>
                                        <td class="text-danger">
                                            {{ number_format($stat->use_count) }}건
                                            <br>
                                            <small>{{ number_format($stat->total_used, 0) }}P</small>
                                        </td>
                                        <td class="text-info">{{ number_format($stat->refund_count) }}건</td>
                                        <td class="text-warning">{{ number_format($stat->expire_count) }}건</td>
                                        <td class="text-secondary">{{ number_format($stat->admin_count) }}건</td>
                                        <td class="{{ ($stat->total_earned - $stat->total_used) >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $stat->total_earned - $stat->total_used >= 0 ? '+' : '' }}{{ number_format($stat->total_earned - $stat->total_used, 0) }}P
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- 최근 대량 거래 -->
            @if($recent_large_transactions->count() > 0)
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">최근 대량 거래 (1000P 이상, 최근 7일)</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>시간</th>
                                    <th>사용자</th>
                                    <th>유형</th>
                                    <th>금액</th>
                                    <th>사유</th>
                                    <th>관리자</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recent_large_transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->created_at->format('m-d H:i') }}</td>
                                        <td>
                                            <div>
                                                <strong>{{ $transaction->user->name ?? 'N/A' }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $transaction->user->email ?? 'N/A' }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge
                                                @if($transaction->transaction_type == 'earn') bg-success
                                                @elseif($transaction->transaction_type == 'use') bg-danger
                                                @elseif($transaction->transaction_type == 'refund') bg-info
                                                @elseif($transaction->transaction_type == 'expire') bg-warning
                                                @elseif($transaction->transaction_type == 'admin') bg-secondary
                                                @else bg-light text-dark
                                                @endif">
                                                {{ $transaction->transaction_type_name }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="{{ $transaction->amount >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount, 0) }}P
                                            </strong>
                                        </td>
                                        <td>{{ Str::limit($transaction->reason, 30) }}</td>
                                        <td>
                                            @if($transaction->admin)
                                                <small>{{ $transaction->admin->name ?? 'N/A' }}</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- 만료 통계 -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">만료 통계</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-danger">{{ number_format($expiry_stats['expiring_today'], 0) }}P</h4>
                                <small class="text-muted">오늘 만료</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-warning">{{ number_format($expiry_stats['expiring_this_week'], 0) }}P</h4>
                                <small class="text-muted">이번 주 만료</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-info">{{ number_format($expiry_stats['expiring_this_month'], 0) }}P</h4>
                                <small class="text-muted">이번 달 만료</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-secondary">{{ number_format($expiry_stats['total_pending'], 0) }}P</h4>
                                <small class="text-muted">총 만료 예정</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
