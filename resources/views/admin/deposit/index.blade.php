@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '충전 신청 관리')

@section('content')
<div class="container-fluid">
    <!-- 헤더 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2">충전 신청 관리</h1>
                    <p class="text-muted mb-0">사용자 이머니 충전 신청을 관리하고 승인/거부할 수 있습니다.</p>
                </div>
                <div>
                    <a href="{{ route('admin.auth.emoney.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> 이머니 관리로
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 통계 카드 -->
    <div class="row mb-4">
        <div class="col-sm-6 col-xl-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">총 신청</div>
                            <div class="h4 mb-0">{{ number_format($statistics['total_deposits']) }}</div>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-file-earmark-text text-primary" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">대기중</div>
                            <div class="h4 mb-0 text-warning">{{ number_format($statistics['pending_deposits']) }}</div>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-clock text-warning" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">승인됨</div>
                            <div class="h4 mb-0 text-success">{{ number_format($statistics['approved_deposits']) }}</div>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">총 승인 금액</div>
                            <div class="h4 mb-0 text-primary">₩{{ number_format($statistics['total_amount']) }}</div>
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
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">검색 및 필터</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.auth.emoney.deposits.index') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">검색</label>
                        <input type="text" class="form-control" name="search" value="{{ $request->search }}"
                               placeholder="이메일, 이름, 입금자명 검색">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">상태</label>
                        <select class="form-select" name="status">
                            <option value="">전체</option>
                            <option value="pending" {{ $request->status === 'pending' ? 'selected' : '' }}>대기중</option>
                            <option value="approved" {{ $request->status === 'approved' ? 'selected' : '' }}>승인됨</option>
                            <option value="rejected" {{ $request->status === 'rejected' ? 'selected' : '' }}>거부됨</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">시작일</label>
                        <input type="date" class="form-control" name="date_from" value="{{ $request->date_from }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">종료일</label>
                        <input type="date" class="form-control" name="date_to" value="{{ $request->date_to }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> 검색
                            </button>
                            <a href="{{ route('admin.auth.emoney.deposits.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise"></i> 초기화
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 목록 -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">충전 신청 목록 ({{ $deposits->total() }}건)</h6>
            <div class="text-muted small">
                {{ $deposits->firstItem() ?? 0 }}-{{ $deposits->lastItem() ?? 0 }} of {{ $deposits->total() }}
            </div>
        </div>
        <div class="card-body p-0">
            @if($deposits->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>사용자</th>
                                <th>금액/(입금자/은행)</th>
                                <th>상태</th>
                                <th>신청일시</th>
                                <th>처리일시/처리자</th>
                                <th>작업</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deposits as $deposit)
                            <tr>
                                <td>{{ $deposit->id }}</td>
                                <td>
                                    <div>
                                        <strong>{{ $deposit->user_name ?? 'N/A' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $deposit->user_email }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong class="text-primary">₩{{ number_format($deposit->amount) }}</strong>
                                    </div>
                                    <div class="text-muted small">
                                        ({{ $deposit->depositor_name ?? 'N/A' }}/{{ $deposit->bank_name ?? 'N/A' }})
                                    </div>
                                </td>
                                <td>
                                    @if($deposit->status === 'pending')
                                        <span class="badge bg-warning">대기중</span>
                                    @elseif($deposit->status === 'approved')
                                        <span class="badge bg-success">승인됨</span>
                                    @elseif($deposit->status === 'rejected')
                                        <span class="badge bg-danger">거부됨</span>
                                    @endif
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($deposit->created_at)->format('Y-m-d H:i') }}
                                </td>
                                <td>
                                    <div>
                                        @if($deposit->checked_at)
                                            {{ \Carbon\Carbon::parse($deposit->checked_at)->format('Y-m-d H:i') }}
                                        @else
                                            -
                                        @endif
                                    </div>
                                    <div class="text-muted small">
                                        @if($deposit->checked_by)
                                            관리자 #{{ $deposit->checked_by }}
                                        @else
                                            -
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <!-- 상세보기 버튼 -->
                                        <button type="button" class="btn btn-sm btn-outline-info"
                                                onclick="showDeposit({{ $deposit->id }})" title="상세 보기">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        @if($deposit->status === 'pending')
                                            <!-- 입금확인/거절 버튼 (대기 상태일 때만) -->
                                            <button type="button" class="btn btn-sm btn-success"
                                                    onclick="approveDeposit({{ $deposit->id }})" title="입금확인">
                                                <i class="bi bi-check-circle"></i> 입금확인
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="rejectDeposit({{ $deposit->id }})" title="거절">
                                                <i class="bi bi-x-circle"></i> 거절
                                            </button>
                                        @endif

                                        @if($deposit->status !== 'approved')
                                            <!-- 삭제 버튼 (승인되지 않은 경우만) -->
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="deleteDeposit({{ $deposit->id }})" title="삭제">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    </div>

                                    @if($deposit->status !== 'pending' && $deposit->admin_memo)
                                        <div class="mt-1">
                                            <small class="text-muted">{{ $deposit->admin_memo }}</small>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- 페이지네이션 -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $deposits->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox text-muted mb-3" style="font-size: 3rem;"></i>
                    <p class="text-muted">충전 신청 내역이 없습니다.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- 입금확인 모달 -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">입금확인 및 이머니 충전</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="approveForm" method="POST" onsubmit="return handleApproval(event)">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>입금확인</strong>을 하시면 해당 사용자의 이머니 잔액이 즉시 증가됩니다.
                    </div>
                    <p>이 충전 신청의 입금을 확인하고 이머니를 충전하시겠습니까?</p>
                    <div class="mb-3">
                        <label class="form-label">관리자 메모 (선택사항)</label>
                        <textarea class="form-control" name="admin_memo" rows="3" placeholder="입금확인 사유나 추가 메모를 입력하세요"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="submit" class="btn btn-success" id="approveSubmitBtn">
                        <i class="bi bi-check-circle"></i> 입금확인 및 충전
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 거부 모달 -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">충전 신청 거부</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST" onsubmit="return handleRejection(event)">
                @csrf
                <div class="modal-body">
                    <p>이 충전 신청을 거부하시겠습니까?</p>
                    <div class="mb-3">
                        <label class="form-label">거부 사유 <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="admin_memo" rows="3" placeholder="거부 사유를 입력하세요" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="submit" class="btn btn-danger" id="rejectSubmitBtn">거부</button>
                </div>
            </form>
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
                <!-- 상세 정보가 여기에 표시됩니다 -->
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

<script>
function approveDeposit(id) {
    const form = document.getElementById('approveForm');
    form.action = '{{ route("admin.auth.emoney.deposits.approve", "PLACEHOLDER") }}'.replace('PLACEHOLDER', id);
    new bootstrap.Modal(document.getElementById('approveModal')).show();
}

function rejectDeposit(id) {
    const form = document.getElementById('rejectForm');
    form.action = '{{ route("admin.auth.emoney.deposits.reject", "PLACEHOLDER") }}'.replace('PLACEHOLDER', id);
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function showDeposit(id) {
    const url = '{{ route("admin.auth.emoney.deposits.show", "PLACEHOLDER") }}'.replace('PLACEHOLDER', id);

    // AJAX로 상세 정보 로드
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
            const deposit = data.data.deposit;
            const user = data.data.user;
            const bank = data.data.bank;

            // 모달 내용 업데이트
            document.getElementById('detailModalLabel').textContent = `충전 신청 상세 (ID: ${deposit.id})`;
            document.getElementById('detailModalBody').innerHTML = generateDetailHTML(deposit, user, bank);

            // 모달 표시
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

function generateDetailHTML(deposit, user, bank) {
    const statusBadge = {
        'pending': '<span class="badge bg-warning">대기중</span>',
        'approved': '<span class="badge bg-success">승인됨</span>',
        'rejected': '<span class="badge bg-danger">거부됨</span>'
    };

    return `
        <div class="row">
            <div class="col-md-6">
                <h6 class="fw-bold mb-3">신청자 정보</h6>
                <table class="table table-sm">
                    <tr>
                        <td class="fw-bold">이름:</td>
                        <td>${user.name || 'N/A'}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">이메일:</td>
                        <td>${user.email || 'N/A'}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">사용자 UUID:</td>
                        <td><small class="text-muted">${deposit.user_uuid}</small></td>
                    </tr>
                </table>
            </div>
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
                        <td>${statusBadge[deposit.status] || deposit.status}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row mt-4">
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
            <div class="col-md-6">
                <h6 class="fw-bold mb-3">처리 정보</h6>
                <table class="table table-sm">
                    <tr>
                        <td class="fw-bold">처리 방법:</td>
                        <td>${deposit.method || 'bank_transfer'}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">참조번호:</td>
                        <td>${deposit.reference_number || 'N/A'}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">처리일시:</td>
                        <td>${deposit.checked_at ? new Date(deposit.checked_at).toLocaleString('ko-KR') : 'N/A'}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">처리자:</td>
                        <td>${deposit.checked_by || 'N/A'}</td>
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
                </table>
            </div>
        </div>
    `;
}

function deleteDeposit(id) {
    if (confirm('정말로 이 충전 신청을 삭제하시겠습니까?\n삭제된 데이터는 복구할 수 없습니다.')) {
        fetch('{{ route("admin.auth.emoney.deposits.delete", "PLACEHOLDER") }}'.replace('PLACEHOLDER', id), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('충전 신청이 성공적으로 삭제되었습니다.');
                location.reload();
            } else {
                alert('오류가 발생했습니다: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('삭제 중 오류가 발생했습니다.');
        });
    }
}

// 승인 처리 함수
function handleApproval(event) {
    event.preventDefault();

    const form = event.target;
    const submitBtn = document.getElementById('approveSubmitBtn');
    const originalText = submitBtn.innerHTML;

    // 버튼 비활성화
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> 처리중...';

    // FormData 생성
    const formData = new FormData(form);

    // AJAX 요청
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (response.ok) {
            // 성공시 모달 닫고 페이지 새로고침
            bootstrap.Modal.getInstance(document.getElementById('approveModal')).hide();
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            // 에러 발생시 페이지로 이동 (폼 에러 메시지 표시)
            window.location.href = form.action;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('처리 중 오류가 발생했습니다.');

        // 버튼 복원
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });

    return false;
}

// 거절 처리 함수
function handleRejection(event) {
    event.preventDefault();

    const form = event.target;
    const submitBtn = document.getElementById('rejectSubmitBtn');
    const originalText = submitBtn.innerHTML;

    // 거절 사유 확인
    const adminMemo = form.querySelector('[name="admin_memo"]').value.trim();
    if (!adminMemo) {
        alert('거절 사유를 입력해주세요.');
        return false;
    }

    // 버튼 비활성화
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> 처리중...';

    // FormData 생성
    const formData = new FormData(form);

    // AJAX 요청
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (response.ok) {
            // 성공시 모달 닫고 페이지 새로고침
            bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            // 에러 발생시 페이지로 이동 (폼 에러 메시지 표시)
            window.location.href = form.action;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('처리 중 오류가 발생했습니다.');

        // 버튼 복원
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });

    return false;
}
</script>
@endsection
