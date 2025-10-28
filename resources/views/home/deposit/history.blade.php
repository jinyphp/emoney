@extends($layout ?? 'jiny-auth::layouts.home')

@section('title', '충전 내역')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    .emoney-card {
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border-radius: 12px;
        overflow: hidden;
    }
    .status-badge {
        font-size: 0.85rem;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
    }
    .filter-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .deposit-item {
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }
    .deposit-item:hover {
        background-color: #f8f9fa;
        border-left-color: #007bff;
    }
    .deposit-pending { border-left-color: #ffc107; }
    .deposit-approved { border-left-color: #28a745; }
    .deposit-rejected { border-left-color: #dc3545; }
    .deposit-cancelled { border-left-color: #6c757d; }
    .deposit-refunded { border-left-color: #17a2b8; }
</style>
@endpush

@section('content')
<div class="container my-5">
    <!-- 헤더 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h1 class="h3 mb-2">충전 내역</h1>
                    <p class="text-muted">이머니 충전 신청 내역을 확인할 수 있습니다.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('home.emoney.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> 이머니 관리로
                    </a>
                    <a href="{{ route('home.emoney.deposit') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> 새 충전 신청
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 통계 카드 -->
    <div class="row mb-4">
        <div class="col-sm-6 col-xl-3 mb-3">
            <div class="card emoney-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">총 신청</div>
                            <div class="h4 mb-0">{{ number_format($statistics['total_count']) }}건</div>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-file-earmark-text text-primary" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3 mb-3">
            <div class="card emoney-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">승인 대기</div>
                            <div class="h4 mb-0 text-warning">{{ number_format($statistics['pending_count']) }}건</div>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-clock text-warning" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3 mb-3">
            <div class="card emoney-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">승인 완료</div>
                            <div class="h4 mb-0 text-success">{{ number_format($statistics['approved_count']) }}건</div>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3 mb-3">
            <div class="card emoney-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">총 충전 금액</div>
                            <div class="h4 mb-0 text-primary">₩{{ number_format($statistics['total_approved_amount']) }}</div>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-currency-dollar text-primary" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 필터 -->
    <div class="card emoney-card filter-card mb-4">
        <div class="card-body">
            <h6 class="mb-3">검색 및 필터</h6>
            <form method="GET" action="{{ route('home.emoney.deposit.history') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">상태</label>
                        <select class="form-select" name="status">
                            <option value="all">전체</option>
                            <option value="pending" {{ $request->status === 'pending' ? 'selected' : '' }}>승인 대기</option>
                            <option value="approved" {{ $request->status === 'approved' ? 'selected' : '' }}>승인 완료</option>
                            <option value="rejected" {{ $request->status === 'rejected' ? 'selected' : '' }}>승인 거부</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">시작일</label>
                        <input type="date" class="form-control" name="date_from" value="{{ $request->date_from }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">종료일</label>
                        <input type="date" class="form-control" name="date_to" value="{{ $request->date_to }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-light">
                                <i class="bi bi-search"></i> 검색
                            </button>
                            <a href="{{ route('home.emoney.deposit.history') }}" class="btn btn-outline-light">
                                <i class="bi bi-arrow-clockwise"></i> 초기화
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 충전 내역 목록 -->
    <div class="card emoney-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">충전 내역 ({{ $deposits->total() }}건)</h6>
            <div class="text-muted small">
                {{ $deposits->firstItem() ?? 0 }}-{{ $deposits->lastItem() ?? 0 }} of {{ $deposits->total() }}
            </div>
        </div>
        <div class="card-body p-0">
            @if($deposits->count() > 0)
                @foreach($deposits as $deposit)
                <div class="deposit-item deposit-{{ $deposit->status }} p-4 border-bottom">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <div class="fw-bold text-primary" style="font-size: 1.2rem;">
                                ₩{{ number_format($deposit->amount) }}
                            </div>
                            <div class="text-muted small">
                                {{ $deposit->currency ?? 'KRW' }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="fw-bold">{{ $deposit->depositor_name ?? 'N/A' }}</div>
                            <div class="text-muted small">{{ $deposit->bank_name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-2">
                            <span class="badge status-badge bg-{{ $statusColors[$deposit->status] ?? 'secondary' }}">
                                {{ $statusTexts[$deposit->status] ?? $deposit->status }}
                            </span>
                        </div>
                        <div class="col-md-2">
                            <div class="text-muted small">신청일시</div>
                            <div>{{ \Carbon\Carbon::parse($deposit->created_at)->format('Y-m-d H:i') }}</div>
                        </div>
                        <div class="col-md-2 text-end">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-info"
                                        onclick="showDepositDetail({{ $deposit->id }})" title="상세 보기">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @if($deposit->status === 'pending')
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="cancelDeposit({{ $deposit->id }})" title="취소 요청">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($deposit->admin_memo)
                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="alert alert-{{ $statusColors[$deposit->status] ?? 'secondary' }} alert-sm">
                                <strong>관리자 메모:</strong> {{ $deposit->admin_memo }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach

                <!-- 페이지네이션 -->
                <div class="p-3">
                    <div class="d-flex justify-content-center">
                        {{ $deposits->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox text-muted mb-3" style="font-size: 3rem;"></i>
                    <p class="text-muted">충전 내역이 없습니다.</p>
                    <a href="{{ route('home.emoney.deposit') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> 첫 충전 신청하기
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- 상세보기 모달 -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">충전 신청 상세</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailModalBody">
                <div class="text-center py-3">
                    <i class="bi bi-hourglass-split"></i> 로딩 중...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
            </div>
        </div>
    </div>
</div>

<!-- 취소 확인 모달 -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">충전 신청 취소</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>정말로 이 충전 신청을 취소하시겠습니까?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    취소된 신청은 복구할 수 없습니다.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">아니오</button>
                <button type="button" class="btn btn-danger" id="confirmCancelBtn">네, 취소합니다</button>
            </div>
        </div>
    </div>
</div>

<script>
function showDepositDetail(id) {
    const url = `{{ route('home.emoney.deposit.status', 'PLACEHOLDER') }}`.replace('PLACEHOLDER', id);

    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const deposit = data.data;
            document.getElementById('detailModalLabel').textContent = `충전 신청 상세 (ID: ${deposit.id})`;
            document.getElementById('detailModalBody').innerHTML = generateDetailHTML(deposit);
            new bootstrap.Modal(document.getElementById('detailModal')).show();
        } else {
            alert('상세 정보를 불러오는데 실패했습니다: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('상세 정보를 불러오는 중 오류가 발생했습니다.');
    });
}

function generateDetailHTML(deposit) {
    const statusColors = {
        'pending': 'warning',
        'approved': 'success',
        'rejected': 'danger',
        'cancelled': 'secondary',
        'refunded': 'info'
    };

    const statusTexts = {
        'pending': '승인 대기',
        'approved': '승인 완료',
        'rejected': '승인 거부',
        'cancelled': '취소 요청',
        'refunded': '환불 완료'
    };

    return `
        <div class="row">
            <div class="col-md-6">
                <h6 class="fw-bold mb-3">충전 정보</h6>
                <table class="table table-sm">
                    <tr>
                        <td class="fw-bold">금액:</td>
                        <td class="text-primary fw-bold">₩${new Intl.NumberFormat().format(deposit.amount)}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">통화:</td>
                        <td>${deposit.currency || 'KRW'}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">상태:</td>
                        <td><span class="badge bg-${statusColors[deposit.status] || 'secondary'}">${statusTexts[deposit.status] || deposit.status}</span></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">참조번호:</td>
                        <td>${deposit.reference_number || 'N/A'}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold mb-3">입금 정보</h6>
                <table class="table table-sm">
                    <tr>
                        <td class="fw-bold">입금자명:</td>
                        <td>${deposit.depositor_name || 'N/A'}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">은행명:</td>
                        <td>${deposit.bank_name || 'N/A'}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">은행코드:</td>
                        <td>${deposit.bank_code || 'N/A'}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">입금날짜:</td>
                        <td>${deposit.deposit_date || 'N/A'}</td>
                    </tr>
                </table>
            </div>
        </div>

        ${deposit.user_memo || deposit.admin_memo ? `
        <div class="row mt-4">
            <div class="col-12">
                <h6 class="fw-bold mb-3">메모</h6>
                ${deposit.user_memo ? `
                <div class="mb-2">
                    <strong>사용자 메모:</strong>
                    <div class="p-2 bg-light rounded">${deposit.user_memo}</div>
                </div>
                ` : ''}
                ${deposit.admin_memo ? `
                <div class="mb-2">
                    <strong>관리자 메모:</strong>
                    <div class="p-2 bg-light rounded">${deposit.admin_memo}</div>
                </div>
                ` : ''}
            </div>
        </div>
        ` : ''}

        <div class="row mt-4">
            <div class="col-12">
                <h6 class="fw-bold mb-3">기타 정보</h6>
                <table class="table table-sm">
                    <tr>
                        <td class="fw-bold">신청일시:</td>
                        <td>${new Date(deposit.created_at).toLocaleString('ko-KR')}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">최종 수정:</td>
                        <td>${new Date(deposit.updated_at).toLocaleString('ko-KR')}</td>
                    </tr>
                    ${deposit.checked_at ? `
                    <tr>
                        <td class="fw-bold">처리일시:</td>
                        <td>${new Date(deposit.checked_at).toLocaleString('ko-KR')}</td>
                    </tr>
                    ` : ''}
                </table>
            </div>
        </div>
    `;
}

let cancelDepositId = null;

function cancelDeposit(id) {
    cancelDepositId = id;
    new bootstrap.Modal(document.getElementById('cancelModal')).show();
}

document.getElementById('confirmCancelBtn').addEventListener('click', function() {
    if (!cancelDepositId) return;

    const url = `{{ route('home.emoney.deposit.cancel', 'PLACEHOLDER') }}`.replace('PLACEHOLDER', cancelDepositId);

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('충전 신청이 취소되었습니다.');
            location.reload();
        } else {
            alert('오류가 발생했습니다: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('취소 처리 중 오류가 발생했습니다.');
    })
    .finally(() => {
        bootstrap.Modal.getInstance(document.getElementById('cancelModal')).hide();
        cancelDepositId = null;
    });
});
</script>
@endsection
