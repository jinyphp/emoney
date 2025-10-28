@extends($layout ?? 'jiny-auth::layouts.home')

@section('title', '포인트 거래 내역')

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
                        <i class="bi bi-list-ul text-primary"></i>
                        포인트 거래 내역
                    </h2>
                    <p class="text-muted mb-0">포인트 적립, 사용, 만료 등 모든 거래 내역을 확인하세요</p>
                </div>
                <div>
                    <a href="{{ route('home.emoney.point.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> 포인트 관리로
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 현재 잔액 및 요약 정보 -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-2" style="font-size: 0.75rem;">현재 잔액</h6>
                            <h4 class="mb-0">{{ number_format($userPoint->balance ?? 0) }}P</h4>
                        </div>
                        <i class="bi bi-star fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-white-50 mb-2" style="font-size: 0.75rem;">총 적립</h6>
                            <h4 class="mb-0">{{ number_format($statistics['total_earned']) }}P</h4>
                        </div>
                        <i class="bi bi-plus-circle fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-white-50 mb-2" style="font-size: 0.75rem;">총 사용</h6>
                            <h4 class="mb-0">{{ number_format(abs($statistics['total_used'])) }}P</h4>
                        </div>
                        <i class="bi bi-credit-card fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-white-50 mb-2" style="font-size: 0.75rem;">거래 건수</h6>
                            <h4 class="mb-0">{{ number_format($statistics['total_logs']) }}</h4>
                        </div>
                        <i class="bi bi-list-ol fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 필터 섹션 -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0"><i class="bi bi-funnel"></i> 검색 및 필터</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('home.emoney.point.log') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="type" class="form-label">거래 유형</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">전체 유형</option>
                            <option value="earn" {{ request('type') == 'earn' ? 'selected' : '' }}>적립</option>
                            <option value="use" {{ request('type') == 'use' ? 'selected' : '' }}>사용</option>
                            <option value="refund" {{ request('type') == 'refund' ? 'selected' : '' }}>환불</option>
                            <option value="expire" {{ request('type') == 'expire' ? 'selected' : '' }}>만료</option>
                            <option value="admin" {{ request('type') == 'admin' ? 'selected' : '' }}>관리자</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">시작일</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">종료일</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="per_page" class="form-label">표시 개수</label>
                        <select class="form-select" id="per_page" name="per_page">
                            <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20개</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50개</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100개</option>
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> 검색
                        </button>
                        <a href="{{ route('home.emoney.point.log') }}" class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-arrow-clockwise"></i> 초기화
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 거래 내역 테이블 -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">포인트 거래 내역</h6>
                @if(method_exists($pointLogs, 'total'))
                    <span class="text-muted">총 {{ number_format($pointLogs->total()) }}건</span>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            @if($pointLogs->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>거래 정보</th>
                            <th>포인트</th>
                            <th>잔액 변화</th>
                            <th>거래 내용</th>
                            <th>거래일시</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pointLogs as $log)
                        <tr>
                            <td>
                                <strong>#{{ $log->id }}</strong>
                                @if($log->reference_id)
                                    <br><small class="text-muted">Ref: {{ $log->reference_id }}</small>
                                @endif
                            </td>
                            <td>
                                <div>
                                    @if($log->transaction_type == 'earn')
                                        <span class="badge bg-success">적립</span>
                                    @elseif($log->transaction_type == 'use')
                                        <span class="badge bg-info">사용</span>
                                    @elseif($log->transaction_type == 'refund')
                                        <span class="badge bg-primary">환불</span>
                                    @elseif($log->transaction_type == 'expire')
                                        <span class="badge bg-danger">만료</span>
                                    @elseif($log->transaction_type == 'admin')
                                        <span class="badge bg-warning">관리자</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $log->transaction_type }}</span>
                                    @endif
                                    <br>
                                    <small class="text-muted">{{ $log->reference_type ?? 'N/A' }}</small>
                                </div>
                            </td>
                            <td>
                                @if($log->amount > 0)
                                    <strong class="text-success">+{{ number_format($log->amount) }}P</strong>
                                @else
                                    <strong class="text-danger">{{ number_format($log->amount) }}P</strong>
                                @endif
                            </td>
                            <td>
                                <div>
                                    <small class="text-muted">이전: {{ number_format($log->balance_before) }}P</small>
                                    <br>
                                    <small class="text-muted">이후: {{ number_format($log->balance_after) }}P</small>
                                </div>
                            </td>
                            <td>
                                <div style="max-width: 200px;">
                                    {{ $log->reason ?? 'N/A' }}
                                </div>
                                @if($log->expires_at)
                                    <br><small class="text-warning">
                                        <i class="bi bi-clock"></i>
                                        {{ \Carbon\Carbon::parse($log->expires_at)->format('Y-m-d') }} 만료
                                    </small>
                                @endif
                            </td>
                            <td>
                                <div>
                                    <strong>{{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d') }}</strong>
                                    <br>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s') }}</small>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <h5 class="mt-3 mb-3">포인트 거래 내역이 없습니다</h5>
                <p class="text-muted mb-4">아직 포인트 거래 내역이 없습니다.</p>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ route('home.emoney.point.index') }}" class="btn btn-warning">
                        <i class="bi bi-star"></i> 포인트 관리로
                    </a>
                    <a href="{{ route('home.emoney.index') }}" class="btn btn-success">
                        <i class="bi bi-wallet2"></i> 이머니 관리로
                    </a>
                </div>
            </div>
            @endif
        </div>

        <!-- 페이지네이션 -->
        @if(method_exists($pointLogs, 'links') && $pointLogs->hasPages())
        <div class="card-footer">
            <nav aria-label="포인트 거래 내역 페이지네이션">
                {{ $pointLogs->appends(request()->query())->links() }}
            </nav>
        </div>
        @endif
    </div>
</div>

<script>
// 페이지 로드 시 현재 날짜를 기본값으로 설정
document.addEventListener('DOMContentLoaded', function() {
    const dateToInput = document.getElementById('date_to');
    if (dateToInput && !dateToInput.value) {
        dateToInput.value = new Date().toISOString().split('T')[0];
    }
});
</script>
@endsection