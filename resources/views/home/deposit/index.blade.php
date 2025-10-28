@extends($layout ?? 'jiny-auth::layouts.home')

@section('title', '이머니 충전')

@section('content')
<div class="container-fluid">
    <!-- 헤더 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-2">
                        <i class="bi bi-plus-circle text-success"></i>
                        이머니 충전
                    </h2>
                    <p class="text-muted mb-0">이머니를 충전하여 다양한 서비스를 이용하세요</p>
                </div>
                <div>
                    <a href="{{ route('home.emoney.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> 이머니 관리로
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 섹션 1: 현재 잔액과 충전 안내 -->
    <div class="row mb-5">
        <!-- 왼쪽: 현재 잔액 -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.875rem; font-weight: 600;">현재 잔액</h6>
                            <h2 class="mb-1" style="font-size: 2rem; font-weight: 700; color: #1a1a1a;">
                                ₩{{ number_format($currentBalance) }}
                            </h2>
                            <p class="text-muted mb-0" style="font-size: 0.875rem;">Current balance</p>
                        </div>
                        <div>
                            <i class="bi bi-wallet2 text-success" style="font-size: 2.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 오른쪽: 충전 안내 -->
        <div class="col-lg-6 mb-4">
            <div class="alert alert-info border-0 h-100 d-flex flex-column justify-content-center">
                <h5 class="alert-heading">
                    <i class="bi bi-info-circle"></i> 이머니 충전 안내
                </h5>
                <hr>
                <p class="mb-2"><strong>충전 방법:</strong></p>
                <ul class="mb-3">
                    <li>충전할 금액과 입금자명을 입력해주세요</li>
                    <li>시스템 계좌로 입금 후 충전 신청을 해주세요</li>
                    <li>관리자 확인 후 이머니가 적립됩니다 (1-2시간 소요)</li>
                    <li>최소 충전 금액: 1,000원 | 최대: 1,000,000원</li>
                </ul>
                <p class="mb-0 text-muted">승인 전 취소 요청이 가능하며, 등록된 계좌로 환불됩니다.</p>
            </div>
        </div>
    </div>

    <!-- 섹션 2: 입력 설정과 충전 기록 -->
    <div class="row">
        <!-- 왼쪽: 충전 신청 폼 -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-plus-circle"></i> 이머니 충전 신청
                    </h5>
                </div>
                <div class="card-body">
                    <form id="depositForm" method="POST" action="{{ route('home.emoney.deposit.store') }}">
                        @csrf

                        <!-- 충전 금액 -->
                        <div class="mb-4">
                            <label for="amount" class="form-label">충전 금액 <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₩</span>
                                <input type="number" class="form-control" id="amount" name="amount"
                                       placeholder="충전할 금액을 입력하세요" min="1000" max="1000000" step="1000" required>
                            </div>
                            <div class="form-text">최소 1,000원부터 최대 1,000,000원까지 충전 가능합니다.</div>
                        </div>

                        <!-- 빠른 금액 선택 -->
                        <div class="mb-4">
                            <label class="form-label">빠른 금액 선택</label>
                            <div class="row">
                                <div class="col-6 mb-2">
                                    <button type="button" class="btn btn-outline-secondary w-100 quick-amount" data-amount="10000">1만원</button>
                                </div>
                                <div class="col-6 mb-2">
                                    <button type="button" class="btn btn-outline-secondary w-100 quick-amount" data-amount="30000">3만원</button>
                                </div>
                                <div class="col-6 mb-2">
                                    <button type="button" class="btn btn-outline-secondary w-100 quick-amount" data-amount="50000">5만원</button>
                                </div>
                                <div class="col-6 mb-2">
                                    <button type="button" class="btn btn-outline-secondary w-100 quick-amount" data-amount="100000">10만원</button>
                                </div>
                            </div>
                        </div>

                        <!-- 입금 은행 선택 -->
                        <div class="mb-4">
                            <label for="bank_id" class="form-label">입금할 은행 <span class="text-danger">*</span></label>
                            @if($depositBanks->count() > 0)
                                <select class="form-select" id="bank_id" name="bank_id" required>
                                    <option value="">입금할 은행을 선택하세요</option>
                                    @foreach($depositBanks as $bank)
                                        <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">선택하신 은행의 시스템 계좌로 입금해 주세요.</div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    현재 이용 가능한 입금 계좌가 없습니다. 관리자에게 문의해 주세요.
                                </div>
                            @endif
                        </div>

                        <!-- 입금자명 -->
                        <div class="mb-4">
                            <label for="depositor_name" class="form-label">입금자명 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="depositor_name" name="depositor_name"
                                   placeholder="실제 입금하신 이름을 정확히 입력하세요" maxlength="100" required>
                            <div class="form-text">입금자명은 관리자 확인을 위해 정확히 입력해 주세요.</div>
                        </div>

                        <!-- 입금 날짜 -->
                        <div class="mb-4">
                            <label for="deposit_date" class="form-label">입금 날짜 <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="deposit_date" name="deposit_date"
                                   value="{{ date('Y-m-d') }}" required>
                        </div>

                        <!-- 충전 사유 (선택사항) -->
                        <div class="mb-4">
                            <label for="user_memo" class="form-label">메모 <span class="text-muted">(선택사항)</span></label>
                            <textarea class="form-control" id="user_memo" name="user_memo" rows="3"
                                      placeholder="충전과 관련된 메모를 입력하세요 (선택사항)" maxlength="500"></textarea>
                        </div>

                        <!-- 버튼 그룹 -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('home.emoney.index') }}" class="btn btn-secondary">
                                취소
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-plus-circle"></i> 충전 신청
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 오른쪽: 충전 내역 -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-clock-history"></i> 최근 충전 내역
                    </h6>
                    <a href="{{ route('home.emoney.deposit.history') }}" class="btn btn-sm btn-outline-secondary">전체 내역</a>
                </div>
                <div class="card-body">
                    @if($recentDeposits && $recentDeposits->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentDeposits as $deposit)
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <h6 class="mb-0">₩{{ number_format($deposit->amount) }}</h6>
                                                @if($deposit->status === 'pending')
                                                    <span class="badge bg-warning">승인 대기</span>
                                                @elseif($deposit->status === 'approved')
                                                    <span class="badge bg-success">승인 완료</span>
                                                @elseif($deposit->status === 'rejected')
                                                    <span class="badge bg-danger">승인 거부</span>
                                                @elseif($deposit->status === 'cancelled')
                                                    <span class="badge bg-secondary">취소 요청</span>
                                                @elseif($deposit->status === 'refunded')
                                                    <span class="badge bg-info">환불 완료</span>
                                                @endif
                                            </div>
                                            <p class="mb-1 text-muted small">
                                                {{ $deposit->bank_name }} | {{ $deposit->depositor_name }}
                                            </p>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($deposit->created_at)->format('m/d H:i') }}
                                                @if($deposit->reference_number)
                                                    | {{ $deposit->reference_number }}
                                                @endif
                                            </small>
                                            @if($deposit->status === 'pending')
                                                <div class="mt-2">
                                                    <button class="btn btn-sm btn-outline-primary me-1"
                                                            onclick="checkDepositStatus({{ $deposit->id }})">
                                                        <i class="bi bi-search"></i> 상태 확인
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger"
                                                            onclick="cancelDeposit({{ $deposit->id }})">
                                                        <i class="bi bi-x"></i> 취소 요청
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-inbox text-muted mb-2" style="font-size: 2rem;"></i>
                            <p class="text-muted mb-0">충전 내역이 없습니다.</p>
                            <small class="text-muted">첫 충전을 신청해 보세요!</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 취소 요청 모달 -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">충전 취소 요청</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="cancelForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        충전을 취소하시면 등록하신 계좌로 환불 처리됩니다.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">취소 사유 <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="cancel_reason" rows="3" placeholder="취소 사유를 입력하세요" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">환불 계좌 <span class="text-danger">*</span></label>
                        @if($userBankAccounts && $userBankAccounts->count() > 0)
                            <select class="form-select" name="refund_account_id" required>
                                <option value="">환불 받을 계좌를 선택하세요</option>
                                @foreach($userBankAccounts as $account)
                                    <option value="{{ $account->id }}" {{ $account->is_default ? 'selected' : '' }}>
                                        {{ $account->bank_name }} - {{ $account->account_number }} ({{ $account->account_holder }})
                                        @if($account->is_default) - 기본계좌 @endif
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i>
                                등록된 은행 계좌가 없습니다. <a href="{{ route('home.emoney.bank.create') }}" class="alert-link" target="_blank">계좌를 먼저 등록</a>해 주세요.
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="submit" class="btn btn-danger">취소 요청</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 상태 확인 모달 -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">충전 상태 확인</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="statusModalBody">
                <!-- 상태 정보가 여기에 표시됩니다 -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 빠른 금액 선택 버튼
    const quickAmountButtons = document.querySelectorAll('.quick-amount');
    const amountInput = document.getElementById('amount');

    quickAmountButtons.forEach(button => {
        button.addEventListener('click', function() {
            const amount = this.getAttribute('data-amount');
            amountInput.value = amount;

            // 선택된 버튼 스타일 변경
            quickAmountButtons.forEach(btn => {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline-secondary');
            });
            this.classList.remove('btn-outline-secondary');
            this.classList.add('btn-primary');
        });
    });

    // 간단하고 안정적인 폼 제출
    const depositForm = document.getElementById('depositForm');
    if (depositForm) {
        depositForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = this.querySelector('button[type="submit"]');
            if (!submitBtn) return;

            const originalText = submitBtn.innerHTML;

            // 기본 검증
            const amount = document.getElementById('amount').value;
            const bankId = document.getElementById('bank_id').value;
            const depositorName = document.getElementById('depositor_name').value;
            const depositDate = document.getElementById('deposit_date').value;

            if (!amount || amount < 1000) {
                alert('충전 금액을 확인해주세요 (최소 1,000원)');
                return;
            }

            if (!bankId) {
                alert('입금할 은행을 선택해주세요');
                return;
            }

            if (!depositorName.trim()) {
                alert('입금자명을 입력해주세요');
                return;
            }

            if (!depositDate) {
                alert('입금 날짜를 선택해주세요');
                return;
            }

            // 버튼 비활성화
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> 처리중...';

            // 폼 데이터 수집
            const formData = new FormData(this);

            // CSRF 토큰 확인
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                             document.querySelector('input[name="_token"]')?.value;

            if (csrfToken) {
                formData.append('_token', csrfToken);
            }

            // AJAX 요청
            const xhr = new XMLHttpRequest();
            xhr.open('POST', this.action);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');

            xhr.onload = function() {
                try {
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            alert('충전 신청이 완료되었습니다!');
                            window.location.reload();
                        } else {
                            alert('오류: ' + (response.message || '알 수 없는 오류가 발생했습니다'));
                        }
                    } else {
                        alert('서버 오류가 발생했습니다 (상태: ' + xhr.status + ')');
                    }
                } catch (e) {
                    alert('응답 처리 중 오류가 발생했습니다');
                }

                // 버튼 복원
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            };

            xhr.onerror = function() {
                alert('네트워크 오류가 발생했습니다');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            };

            xhr.ontimeout = function() {
                alert('요청 시간이 초과되었습니다');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            };

            xhr.timeout = 30000; // 30초 타임아웃
            xhr.send(formData);
        });
    }
});

// 간단한 상태 확인 함수
function checkDepositStatus(depositId) {
    if (!depositId) {
        alert('잘못된 요청입니다');
        return;
    }

    const xhr = new XMLHttpRequest();
    const url = `{{ route('home.emoney.deposit.status', ':id') }}`.replace(':id', depositId);

    xhr.open('GET', url);
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
    }

    xhr.onload = function() {
        try {
            if (xhr.status === 200) {
                const data = JSON.parse(xhr.responseText);
                if (data.success && data.data) {
                    const deposit = data.data;
                    const modalBody = document.getElementById('statusModalBody');

                    modalBody.innerHTML = `
                        <div class="row mb-2">
                            <div class="col-4"><strong>충전 금액:</strong></div>
                            <div class="col-8">₩${deposit.formatted_amount || deposit.amount}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><strong>상태:</strong></div>
                            <div class="col-8"><span class="badge bg-primary">${deposit.status_text || deposit.status}</span></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><strong>은행:</strong></div>
                            <div class="col-8">${deposit.bank_name || 'N/A'}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><strong>입금자:</strong></div>
                            <div class="col-8">${deposit.depositor_name || 'N/A'}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><strong>참조번호:</strong></div>
                            <div class="col-8">${deposit.reference_number || 'N/A'}</div>
                        </div>
                    `;

                    if (typeof bootstrap !== 'undefined') {
                        new bootstrap.Modal(document.getElementById('statusModal')).show();
                    } else {
                        // Fallback for older Bootstrap
                        document.getElementById('statusModal').style.display = 'block';
                    }
                } else {
                    alert('상태 조회 실패: ' + (data.message || '알 수 없는 오류'));
                }
            } else {
                alert('서버 오류 (상태: ' + xhr.status + ')');
            }
        } catch (e) {
            alert('응답 처리 중 오류가 발생했습니다');
        }
    };

    xhr.onerror = function() {
        alert('네트워크 오류가 발생했습니다');
    };

    xhr.send();
}

// 간단한 취소 요청 함수
function cancelDeposit(depositId) {
    if (!depositId) {
        alert('잘못된 요청입니다');
        return;
    }

    const form = document.getElementById('cancelForm');
    if (!form) {
        alert('취소 폼을 찾을 수 없습니다');
        return;
    }

    form.action = `{{ route('home.emoney.deposit.cancel', ':id') }}`.replace(':id', depositId);

    if (typeof bootstrap !== 'undefined') {
        new bootstrap.Modal(document.getElementById('cancelModal')).show();
    } else {
        // Fallback for older Bootstrap
        document.getElementById('cancelModal').style.display = 'block';
    }

    // 취소 폼 제출 처리
    form.onsubmit = function(e) {
        e.preventDefault();

        const xhr = new XMLHttpRequest();
        const formData = new FormData(this);

        xhr.open('POST', this.action);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
        }

        xhr.onload = function() {
            try {
                if (xhr.status === 200) {
                    const data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        alert('취소 요청이 완료되었습니다');
                        if (typeof bootstrap !== 'undefined') {
                            bootstrap.Modal.getInstance(document.getElementById('cancelModal')).hide();
                        } else {
                            document.getElementById('cancelModal').style.display = 'none';
                        }
                        window.location.reload();
                    } else {
                        alert('취소 실패: ' + (data.message || '알 수 없는 오류'));
                    }
                } else {
                    alert('서버 오류 (상태: ' + xhr.status + ')');
                }
            } catch (e) {
                alert('응답 처리 중 오류가 발생했습니다');
            }
        };

        xhr.onerror = function() {
            alert('네트워크 오류가 발생했습니다');
        };

        xhr.send(formData);
    };
}

// 잔액 및 목록 실시간 업데이트 함수
function updateBalanceAndHistory() {
    fetch('{{ route("home.emoney.deposit") }}', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 잔액 업데이트
            const balanceElement = document.querySelector('h2[style*="font-size: 2rem"]');
            if (balanceElement && data.currentBalance !== undefined) {
                balanceElement.textContent = '₩' + new Intl.NumberFormat().format(data.currentBalance);
            }

            // 충전 내역 업데이트
            if (data.recentDeposits && data.recentDeposits.length > 0) {
                updateDepositHistory(data.recentDeposits);
            }
        }
    })
    .catch(error => {
        console.log('업데이트 확인 중 오류:', error);
    });
}

// 충전 내역 업데이트 함수
function updateDepositHistory(deposits) {
    const historyContainer = document.querySelector('.list-group.list-group-flush');
    if (!historyContainer) return;

    if (deposits.length === 0) {
        historyContainer.innerHTML = `
            <div class="text-center py-4">
                <i class="bi bi-inbox text-muted mb-2" style="font-size: 2rem;"></i>
                <p class="text-muted mb-0">충전 내역이 없습니다.</p>
                <small class="text-muted">첫 충전을 신청해 보세요!</small>
            </div>
        `;
        return;
    }

    let historyHTML = '';
    deposits.forEach(deposit => {
        const statusBadge = getStatusBadge(deposit.status);
        const createdAt = new Date(deposit.created_at).toLocaleDateString('ko-KR', {
            month: 'numeric',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        historyHTML += `
            <div class="list-group-item border-0 px-0">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="mb-0">₩${new Intl.NumberFormat().format(deposit.amount)}</h6>
                            ${statusBadge}
                        </div>
                        <p class="mb-1 text-muted small">
                            ${deposit.bank_name || 'N/A'} | ${deposit.depositor_name || 'N/A'}
                        </p>
                        <small class="text-muted">
                            ${createdAt}
                            ${deposit.reference_number ? ' | ' + deposit.reference_number : ''}
                        </small>
                        ${deposit.status === 'pending' ? `
                        <div class="mt-2">
                            <button class="btn btn-sm btn-outline-primary me-1"
                                    onclick="checkDepositStatus(${deposit.id})">
                                <i class="bi bi-search"></i> 상태 확인
                            </button>
                            <button class="btn btn-sm btn-outline-danger"
                                    onclick="cancelDeposit(${deposit.id})">
                                <i class="bi bi-x"></i> 취소 요청
                            </button>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
    });

    historyContainer.innerHTML = historyHTML;
}

// 상태 배지 생성 함수
function getStatusBadge(status) {
    switch (status) {
        case 'pending':
            return '<span class="badge bg-warning">승인 대기</span>';
        case 'approved':
            return '<span class="badge bg-success">승인 완료</span>';
        case 'rejected':
            return '<span class="badge bg-danger">승인 거부</span>';
        case 'cancelled':
            return '<span class="badge bg-secondary">취소 요청</span>';
        case 'refunded':
            return '<span class="badge bg-info">환불 완료</span>';
        default:
            return '<span class="badge bg-secondary">' + status + '</span>';
    }
}

// 실시간 업데이트 시작
document.addEventListener('DOMContentLoaded', function() {
    // 페이지 포커스시 업데이트
    window.addEventListener('focus', updateBalanceAndHistory);

    // 5분마다 자동 업데이트
    setInterval(updateBalanceAndHistory, 5 * 60 * 1000);

    // 충전 신청 성공 후 즉시 업데이트
    const originalFormSubmit = document.getElementById('depositForm');
    if (originalFormSubmit) {
        const originalAction = originalFormSubmit.action;
        originalFormSubmit.addEventListener('submit', function(e) {
            // 폼 제출 후 3초 뒤 업데이트 (서버 처리 시간 고려)
            setTimeout(updateBalanceAndHistory, 3000);
        });
    }
});
</script>
@endsection
