@extends($layout ?? 'jiny-auth::layouts.home')

@section('title', '거래 내역')

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
                        거래 내역
                    </h2>
                    <p class="text-muted mb-0">이머니 충전, 사용, 출금 등 모든 거래 내역을 확인하세요</p>
                </div>
                <div>
                    <a href="{{ route('home.emoney.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> 이머니 관리로
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 현재 잔액 및 요약 정보 -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-white-50 mb-2" style="font-size: 0.75rem;">현재 잔액</h6>
                            <h4 class="mb-0">₩{{ number_format($userEmoney->balance ?? 0) }}</h4>
                        </div>
                        <i class="bi bi-wallet2 fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-white-50 mb-2" style="font-size: 0.75rem;">총 충전액</h6>
                            <h4 class="mb-0">₩{{ number_format($userEmoney->total_deposit ?? 0) }}</h4>
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
                            <h6 class="text-uppercase text-white-50 mb-2" style="font-size: 0.75rem;">총 사용액</h6>
                            <h4 class="mb-0">₩{{ number_format($userEmoney->total_used ?? 0) }}</h4>
                        </div>
                        <i class="bi bi-credit-card fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-2" style="font-size: 0.75rem;">총 출금액</h6>
                            <h4 class="mb-0">₩{{ number_format($userEmoney->total_withdrawn ?? 0) }}</h4>
                        </div>
                        <i class="bi bi-arrow-down-circle fs-2"></i>
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
            <form method="GET" action="{{ route('home.emoney.log') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="type" class="form-label">거래 유형</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">전체 유형</option>
                            <option value="deposit" {{ request('type') == 'deposit' ? 'selected' : '' }}>충전</option>
                            <option value="purchase" {{ request('type') == 'purchase' ? 'selected' : '' }}>구매/사용</option>
                            <option value="withdrawal" {{ request('type') == 'withdrawal' ? 'selected' : '' }}>출금</option>
                            <option value="refund" {{ request('type') == 'refund' ? 'selected' : '' }}>환불</option>
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
                        <a href="{{ route('home.emoney.log') }}" class="btn btn-outline-secondary ms-2">
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
                <h6 class="mb-0">거래 내역 목록</h6>
                @if(method_exists($emoneyLogs, 'total'))
                    <span class="text-muted">총 {{ number_format($emoneyLogs->total()) }}건</span>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            @if($emoneyLogs->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>거래 정보</th>
                            <th>금액</th>
                            <th>잔액 변화</th>
                            <th>거래 내용</th>
                            <th>거래일시</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($emoneyLogs as $log)
                        <tr>
                            <td>
                                <strong>#{{ $log->id }}</strong>
                                @if($log->reference_id)
                                    <br><small class="text-muted">Ref: {{ $log->reference_id }}</small>
                                @endif
                            </td>
                            <td>
                                <div>
                                    @if($log->type == 'deposit')
                                        <span class="badge bg-success">충전</span>
                                    @elseif($log->type == 'purchase')
                                        <span class="badge bg-info">구매/사용</span>
                                    @elseif($log->type == 'withdrawal')
                                        <span class="badge bg-warning">출금</span>
                                    @elseif($log->type == 'refund')
                                        <span class="badge bg-primary">환불</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $log->type }}</span>
                                    @endif
                                    <br>
                                    <small class="text-muted">{{ $log->reference_type ?? 'N/A' }}</small>
                                </div>
                            </td>
                            <td>
                                @if($log->amount > 0)
                                    <strong class="text-success">+₩{{ number_format($log->amount) }}</strong>
                                @else
                                    <strong class="text-danger">₩{{ number_format($log->amount) }}</strong>
                                @endif
                            </td>
                            <td>
                                <div>
                                    <small class="text-muted">이전: ₩{{ number_format($log->balance_before) }}</small>
                                    <br>
                                    <small class="text-muted">이후: ₩{{ number_format($log->balance_after) }}</small>
                                </div>
                            </td>
                            <td>
                                <div style="max-width: 200px;">
                                    {{ $log->description ?? 'N/A' }}
                                </div>
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
                <h5 class="mt-3 mb-3">거래 내역이 없습니다</h5>
                <p class="text-muted mb-4">아직 이머니 거래 내역이 없습니다.</p>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ route('home.emoney.deposit') }}" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> 이머니 충전하기
                    </a>
                    <a href="{{ route('home.emoney.withdraw') }}" class="btn btn-danger">
                        <i class="bi bi-arrow-down-circle"></i> 이머니 출금하기
                    </a>
                </div>
            </div>
            @endif
        </div>

        <!-- 페이지네이션 -->
        @if(method_exists($emoneyLogs, 'links') && $emoneyLogs->hasPages())
        <div class="card-footer">
            <nav aria-label="거래 내역 페이지네이션">
                {{ $emoneyLogs->appends(request()->query())->links() }}
            </nav>
        </div>
        @endif
    </div>
</div>

<!-- 거래 상세보기 모달 -->
<div class="modal fade" id="transactionDetailModal" tabindex="-1" aria-labelledby="transactionDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transactionDetailModalLabel">거래 상세 정보</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="transactionDetailBody">
                <!-- 상세 정보가 여기에 로드됩니다 -->
            </div>
        </div>
    </div>
</div>

<script>
function viewTransactionDetail(logId) {
    // 거래 상세 정보 모달 표시 (임시로 alert 사용)
    alert('거래 상세 정보 (ID: ' + logId + ')\n이 기능은 추후 구현될 예정입니다.');
}

// 페이지 로드 시 현재 날짜를 기본값으로 설정
document.addEventListener('DOMContentLoaded', function() {
    const dateToInput = document.getElementById('date_to');
    if (dateToInput && !dateToInput.value) {
        dateToInput.value = new Date().toISOString().split('T')[0];
    }
});
</script>
@endsection