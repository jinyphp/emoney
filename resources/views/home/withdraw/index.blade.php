@extends($layout ?? 'jiny-auth::layouts.home')

@section('title', '이머니 출금')

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

    @if (session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle"></i>
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- 헤더 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-2">
                        <i class="bi bi-arrow-down-circle text-danger"></i>
                        이머니 출금
                    </h2>
                    <p class="text-muted mb-0">등록된 계좌로 이머니를 출금하세요</p>
                </div>
                <div>
                    <a href="{{ route('home.emoney.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> 이머니 관리로
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 섹션 1: 현재 잔액과 출금 안내 -->
    <div class="row mb-5">
        <!-- 왼쪽: 현재 잔액 -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.875rem; font-weight: 600;">출금 가능 잔액</h6>
                            <h2 class="mb-1" style="font-size: 2rem; font-weight: 700; color: #1a1a1a;">
                                ₩{{ number_format($emoney->balance ?? 0) }}
                            </h2>
                            <p class="text-muted mb-0" style="font-size: 0.875rem;">Available balance</p>
                        </div>
                        <div>
                            <i class="bi bi-wallet2 text-primary" style="font-size: 2.5rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 오른쪽: 출금 안내 -->
        <div class="col-lg-6 mb-4">
            <div class="alert alert-warning border-0 h-100 d-flex flex-column justify-content-center">
                <h5 class="alert-heading">
                    <i class="bi bi-exclamation-triangle"></i> 이머니 출금 안내
                </h5>
                <hr>
                <p class="mb-2"><strong>출금 방법:</strong></p>
                <ul class="mb-3">
                    <li>등록된 은행 계좌 중 하나를 선택해주세요</li>
                    <li>출금할 금액을 입력하고 신청해주세요</li>
                    <li>출금 수수료가 차감됩니다 (5% 또는 최소 1,000원)</li>
                    <li>관리자 승인 후 계좌로 송금됩니다 (1-3일 소요)</li>
                </ul>
                <p class="mb-0 text-muted">최소 출금 금액: 5,000원 | 승인 전 취소 요청이 가능합니다.</p>
            </div>
        </div>
    </div>

    <!-- 섹션 2: 출금 신청 폼과 계좌 정보 -->
    <div class="row">
        <!-- 왼쪽: 출금 신청 폼 -->
        <div class="col-lg-8 mb-4">
            @if($bankAccounts->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-arrow-down-circle"></i> 이머니 출금 신청
                    </h5>
                </div>
                <div class="card-body">
                    <form id="withdrawForm" method="POST" action="{{ route('home.emoney.withdraw.store') }}">
                        @csrf

                        <!-- 출금 계좌 선택 -->
                        <div class="mb-4">
                            <label for="bank_account_id" class="form-label">출금 계좌 선택 <span class="text-danger">*</span></label>
                            @foreach($bankAccounts as $account)
                            <div class="form-check border rounded p-3 mb-2 {{ $account->is_default ? 'border-success bg-light' : '' }}">
                                <input class="form-check-input" type="radio" name="bank_account_id"
                                       id="account_{{ $account->id }}" value="{{ $account->id }}"
                                       {{ $account->is_default ? 'checked' : '' }} required>
                                <label class="form-check-label w-100" for="account_{{ $account->id }}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $account->bank_name ?? $account->bank }}</strong>
                                            <span class="badge bg-info ms-2">{{ $account->currency ?? 'KRW' }}</span>
                                            @if($account->is_default)
                                                <span class="badge bg-success ms-1">기본 계좌</span>
                                            @endif
                                            <br>
                                            <span class="text-muted">{{ $account->account_number ?? $account->account }}</span>
                                            <br>
                                            <span class="text-muted small">예금주: {{ $account->account_holder ?? $account->owner }}</span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            @endforeach
                            @error('bank_account_id')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 출금 금액 -->
                        <div class="mb-4">
                            <label for="amount" class="form-label">출금 금액 <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₩</span>
                                <input type="number" class="form-control" id="amount" name="amount"
                                       value="{{ old('amount') }}"
                                       min="5000" max="{{ $emoney->balance ?? 0 }}"
                                       placeholder="출금할 금액을 입력하세요" required>
                            </div>
                            <div class="form-text">
                                최소 출금 금액: 5,000원 | 최대 출금 가능 금액: {{ number_format($emoney->balance ?? 0) }}원
                            </div>
                            @error('amount')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 출금 수수료 표시 -->
                        <div class="card bg-light mb-4">
                            <div class="card-body py-3">
                                <div class="row">
                                    <div class="col-4">
                                        <span class="text-muted">출금 신청 금액:</span><br>
                                        <strong id="request-amount">₩0</strong>
                                    </div>
                                    <div class="col-4">
                                        <span class="text-muted">출금 수수료 (5%):</span><br>
                                        <strong id="withdraw-fee" class="text-danger">₩0</strong>
                                    </div>
                                    <div class="col-4">
                                        <span class="text-muted">실제 입금 금액:</span><br>
                                        <strong id="actual-amount" class="text-success">₩0</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 출금 사유 (선택사항) -->
                        <div class="mb-4">
                            <label for="withdraw_reason" class="form-label">출금 사유 (선택사항)</label>
                            <textarea class="form-control" id="withdraw_reason" name="withdraw_reason"
                                      rows="3" placeholder="출금 사유를 입력하세요 (선택사항)">{{ old('withdraw_reason') }}</textarea>
                            @error('withdraw_reason')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 제출 버튼 -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger btn-lg" id="withdrawSubmitBtn">
                                <i class="bi bi-arrow-down-circle"></i> 출금 신청하기
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @else
            <!-- 등록된 계좌가 없는 경우 -->
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-bank display-1 text-muted"></i>
                    <h5 class="mt-3 mb-3">등록된 계좌가 없습니다</h5>
                    <p class="text-muted mb-4">이머니 출금을 위해 먼저 은행 계좌를 등록해주세요.</p>
                    <a href="{{ route('home.emoney.bank.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> 계좌 등록하기
                    </a>
                </div>
            </div>
            @endif
        </div>

        <!-- 오른쪽: 최근 출금 기록 -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-clock-history"></i> 최근 출금 기록
                    </h6>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @if($recentWithdrawals->count() > 0)
                        @foreach($recentWithdrawals as $withdrawal)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <div class="fw-bold">₩{{ number_format($withdrawal->amount) }}</div>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($withdrawal->created_at)->format('m-d H:i') }}</small>
                            </div>
                            <div>
                                @if($withdrawal->status == 'pending')
                                    <span class="badge bg-warning">승인대기</span>
                                @elseif($withdrawal->status == 'approved')
                                    <span class="badge bg-success">승인완료</span>
                                @elseif($withdrawal->status == 'rejected')
                                    <span class="badge bg-danger">거절됨</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-inbox display-6"></i>
                            <p class="mt-2 mb-0">출금 기록이 없습니다</p>
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('home.emoney.withdraw.history') }}" class="btn btn-outline-primary btn-sm w-100">
                        <i class="bi bi-list"></i> 전체 출금 기록 보기
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for form handling -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('amount');
    const requestAmountDisplay = document.getElementById('request-amount');
    const feeDisplay = document.getElementById('withdraw-fee');
    const actualAmountDisplay = document.getElementById('actual-amount');
    const withdrawForm = document.getElementById('withdrawForm');
    const submitBtn = document.getElementById('withdrawSubmitBtn');

    // 출금 금액 변경시 수수료 계산
    function calculateFee() {
        const amount = parseInt(amountInput.value) || 0;
        const feeRate = 0.05; // 5% 수수료
        const minFee = 1000; // 최소 수수료 1,000원

        const fee = Math.max(Math.floor(amount * feeRate), minFee);
        const actualAmount = amount - fee;

        requestAmountDisplay.textContent = '₩' + amount.toLocaleString();
        feeDisplay.textContent = '₩' + fee.toLocaleString();
        actualAmountDisplay.textContent = '₩' + actualAmount.toLocaleString();

        // 잔액 확인
        const currentBalance = {{ $emoney->balance ?? 0 }};
        if (amount > currentBalance) {
            amountInput.classList.add('is-invalid');
            submitBtn.disabled = true;
        } else if (amount < 5000) {
            amountInput.classList.add('is-invalid');
            submitBtn.disabled = true;
        } else {
            amountInput.classList.remove('is-invalid');
            submitBtn.disabled = false;
        }
    }

    // 입력 이벤트 리스너
    if (amountInput) {
        amountInput.addEventListener('input', calculateFee);
        amountInput.addEventListener('change', calculateFee);
    }

    // 폼 제출 확인
    if (withdrawForm) {
        withdrawForm.addEventListener('submit', function(e) {
            const amount = parseInt(amountInput.value) || 0;
            const currentBalance = {{ $emoney->balance ?? 0 }};

            if (amount < 5000) {
                e.preventDefault();
                alert('최소 출금 금액은 5,000원입니다.');
                return;
            }

            if (amount > currentBalance) {
                e.preventDefault();
                alert('출금 가능 잔액을 초과했습니다.');
                return;
            }

            const confirmed = confirm(`₩${amount.toLocaleString()}원을 출금 신청하시겠습니까?\n\n수수료가 차감되어 실제 입금되는 금액은 다를 수 있습니다.`);
            if (!confirmed) {
                e.preventDefault();
            }
        });
    }

    // 초기 계산
    calculateFee();
});
</script>
@endsection