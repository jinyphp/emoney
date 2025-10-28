@extends($layout ?? 'jiny-auth::layouts.home')

@section('title', '출금 내역')

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
                        <i class="bi bi-clock-history text-info"></i>
                        출금 내역
                    </h2>
                    <p class="text-muted mb-0">이머니 출금 신청 및 처리 내역을 확인하세요</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('home.emoney.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> 이머니 관리로
                    </a>
                    <a href="{{ route('home.emoney.withdraw') }}" class="btn btn-danger">
                        <i class="bi bi-plus-circle"></i> 새 출금 신청
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 통계 요약 -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-white-50 mb-2" style="font-size: 0.75rem;">총 신청 건수</h6>
                            <h4 class="mb-0">{{ number_format($statistics['total_requests']) }}건</h4>
                        </div>
                        <i class="bi bi-list-check fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-2" style="font-size: 0.75rem;">승인 대기</h6>
                            <h4 class="mb-0">{{ number_format($statistics['pending_requests']) }}건</h4>
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
                            <h6 class="text-uppercase text-white-50 mb-2" style="font-size: 0.75rem;">승인 완료</h6>
                            <h4 class="mb-0">{{ number_format($statistics['approved_requests']) }}건</h4>
                        </div>
                        <i class="bi bi-check-circle fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-white-50 mb-2" style="font-size: 0.75rem;">총 출금액</h6>
                            <h4 class="mb-0">₩{{ number_format($statistics['total_withdrawn']) }}</h4>
                        </div>
                        <i class="bi bi-wallet2 fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 현재 잔액 표시 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">현재 이머니 잔액:</span>
                        <strong class="h5 mb-0 text-primary">₩{{ number_format($emoney->balance ?? 0) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 필터 및 검색 -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0"><i class="bi bi-funnel"></i> 검색 및 필터</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('home.emoney.withdraw.history') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="status" class="form-label">상태</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">전체 상태</option>
                            <option value="pending" {{ $filters['status'] == 'pending' ? 'selected' : '' }}>승인대기</option>
                            <option value="approved" {{ $filters['status'] == 'approved' ? 'selected' : '' }}>승인완료</option>
                            <option value="rejected" {{ $filters['status'] == 'rejected' ? 'selected' : '' }}>거절됨</option>
                            <option value="processing" {{ $filters['status'] == 'processing' ? 'selected' : '' }}>처리중</option>
                            <option value="completed" {{ $filters['status'] == 'completed' ? 'selected' : '' }}>완료</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">시작일</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" value="{{ $filters['date_from'] }}">
                    </div>
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">종료일</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" value="{{ $filters['date_to'] }}">
                    </div>
                    <div class="col-md-3">
                        <label for="per_page" class="form-label">표시 개수</label>
                        <select class="form-select" id="per_page" name="per_page">
                            <option value="10" {{ $filters['per_page'] == 10 ? 'selected' : '' }}>10개</option>
                            <option value="20" {{ $filters['per_page'] == 20 ? 'selected' : '' }}>20개</option>
                            <option value="50" {{ $filters['per_page'] == 50 ? 'selected' : '' }}>50개</option>
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> 검색
                        </button>
                        <a href="{{ route('home.emoney.withdraw.history') }}" class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-arrow-clockwise"></i> 초기화
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 출금 내역 테이블 -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">출금 내역 목록</h6>
                <span class="text-muted">총 {{ number_format($pagination['total_count']) }}건</span>
            </div>
        </div>
        <div class="card-body p-0">
            @if($withdrawals->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>출금 정보</th>
                            <th>계좌 정보</th>
                            <th>상태</th>
                            <th>신청일</th>
                            <th>처리일</th>
                            <th>액션</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($withdrawals as $withdrawal)
                        <tr>
                            <td>
                                <strong>#{{ $withdrawal->id }}</strong>
                            </td>
                            <td>
                                <div>
                                    <strong class="text-danger">-₩{{ number_format($withdrawal->amount) }}</strong>
                                    <span class="badge bg-secondary ms-1">{{ $withdrawal->currency ?? 'KRW' }}</span>
                                </div>
                                @if($withdrawal->fee > 0)
                                <small class="text-muted">수수료: ₩{{ number_format($withdrawal->fee) }}</small><br>
                                <small class="text-success">실입금: ₩{{ number_format($withdrawal->amount - $withdrawal->fee) }}</small>
                                @endif
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $withdrawal->bank_name }}</strong><br>
                                    <code class="small">{{ $withdrawal->account_number }}</code><br>
                                    <small class="text-muted">{{ $withdrawal->account_holder }}</small>
                                </div>
                            </td>
                            <td>
                                @if($withdrawal->status == 'pending')
                                    <span class="badge bg-warning">승인대기</span>
                                @elseif($withdrawal->status == 'approved')
                                    <span class="badge bg-success">승인완료</span>
                                @elseif($withdrawal->status == 'rejected')
                                    <span class="badge bg-danger">거절됨</span>
                                @elseif($withdrawal->status == 'processing')
                                    <span class="badge bg-info">처리중</span>
                                @elseif($withdrawal->status == 'completed')
                                    <span class="badge bg-primary">완료</span>
                                @else
                                    <span class="badge bg-secondary">{{ $withdrawal->status }}</span>
                                @endif
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($withdrawal->created_at)->format('Y-m-d H:i') }}
                            </td>
                            <td>
                                @if($withdrawal->checked_at)
                                    {{ \Carbon\Carbon::parse($withdrawal->checked_at)->format('Y-m-d H:i') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        액션
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="viewWithdrawDetail({{ $withdrawal->id }})">
                                                <i class="bi bi-eye"></i> 상세보기
                                            </a>
                                        </li>
                                        @if($withdrawal->status == 'pending')
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" onclick="cancelWithdraw({{ $withdrawal->id }})">
                                                <i class="bi bi-x-circle"></i> 취소 요청
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
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
                <h5 class="mt-3 mb-3">출금 내역이 없습니다</h5>
                <p class="text-muted mb-4">아직 출금 신청 내역이 없습니다.</p>
                <a href="{{ route('home.emoney.withdraw') }}" class="btn btn-danger">
                    <i class="bi bi-plus-circle"></i> 첫 출금 신청하기
                </a>
            </div>
            @endif
        </div>

        <!-- 페이지네이션 -->
        @if($pagination['total_pages'] > 1)
        <div class="card-footer">
            <nav aria-label="출금 내역 페이지네이션">
                <ul class="pagination justify-content-center mb-0">
                    @if($pagination['has_prev'])
                    <li class="page-item">
                        <a class="page-link" href="?page={{ $pagination['prev_page'] }}&{{ http_build_query($filters) }}">이전</a>
                    </li>
                    @endif

                    @for($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++)
                    <li class="page-item {{ $i == $pagination['current_page'] ? 'active' : '' }}">
                        <a class="page-link" href="?page={{ $i }}&{{ http_build_query($filters) }}">{{ $i }}</a>
                    </li>
                    @endfor

                    @if($pagination['has_next'])
                    <li class="page-item">
                        <a class="page-link" href="?page={{ $pagination['next_page'] }}&{{ http_build_query($filters) }}">다음</a>
                    </li>
                    @endif
                </ul>
            </nav>
        </div>
        @endif
    </div>
</div>

<!-- 상세보기 모달 -->
<div class="modal fade" id="withdrawDetailModal" tabindex="-1" aria-labelledby="withdrawDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="withdrawDetailModalLabel">출금 상세 정보</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="withdrawDetailBody">
                <!-- 상세 정보가 여기에 로드됩니다 -->
            </div>
        </div>
    </div>
</div>

<script>
function viewWithdrawDetail(withdrawId) {
    // 출금 상세 정보 모달 표시 (임시로 alert 사용)
    alert('출금 상세 정보 (ID: ' + withdrawId + ')\n이 기능은 추후 구현될 예정입니다.');
}

function cancelWithdraw(withdrawId) {
    if (confirm('정말로 이 출금 신청을 취소하시겠습니까?')) {
        // 출금 취소 요청 (임시로 alert 사용)
        alert('출금 취소 요청 (ID: ' + withdrawId + ')\n이 기능은 추후 구현될 예정입니다.');
    }
}
</script>
@endsection