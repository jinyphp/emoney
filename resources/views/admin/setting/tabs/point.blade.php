<div class="row">
    <div class="col-lg-8">
        <!-- 포인트 기본 설정 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fe fe-dollar-sign me-2 text-success"></i>포인트 기본 설정
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="point_enable" id="point_enable"
                                {{ ($settings['point']['enable'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="point_enable">
                                포인트 시스템 사용
                            </label>
                        </div>
                        <div class="form-text">포인트 적립 및 사용 기능을 활성화합니다.</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="point_currency" class="form-label">포인트 단위</label>
                        <input type="text" class="form-control" name="point_currency" id="point_currency"
                            value="{{ $settings['point']['currency'] ?? 'P' }}" maxlength="5"
                            placeholder="P">
                        <div class="form-text">포인트 표시 단위 (예: P, 원, 포인트)</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="point_decimal_places" class="form-label">소수점 자릿수</label>
                        <select class="form-select" name="point_decimal_places" id="point_decimal_places">
                            <option value="0" {{ ($settings['point']['decimal_places'] ?? 0) == 0 ? 'selected' : '' }}>정수만 (1000P)</option>
                            <option value="1" {{ ($settings['point']['decimal_places'] ?? 0) == 1 ? 'selected' : '' }}>소수점 1자리 (1000.0P)</option>
                            <option value="2" {{ ($settings['point']['decimal_places'] ?? 0) == 2 ? 'selected' : '' }}>소수점 2자리 (1000.00P)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- 포인트 적립 설정 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fe fe-plus-circle me-2 text-primary"></i>포인트 적립 설정
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="point_earn_signup_bonus" class="form-label">회원가입 보너스</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="point_earn.signup_bonus" id="point_earn_signup_bonus"
                                value="{{ $settings['point']['earn']['signup_bonus'] ?? 1000 }}" min="0">
                            <span class="input-group-text" id="currency_suffix_1">{{ $settings['point']['currency'] ?? 'P' }}</span>
                        </div>
                        <div class="form-text">신규 회원가입 시 지급할 포인트</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="point_earn_daily_login" class="form-label">일일 로그인</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="point_earn.daily_login" id="point_earn_daily_login"
                                value="{{ $settings['point']['earn']['daily_login'] ?? 10 }}" min="0">
                            <span class="input-group-text">{{ $settings['point']['currency'] ?? 'P' }}</span>
                        </div>
                        <div class="form-text">매일 첫 로그인 시 지급할 포인트</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="point_earn_review_write" class="form-label">리뷰 작성</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="point_earn.review_write" id="point_earn_review_write"
                                value="{{ $settings['point']['earn']['review_write'] ?? 100 }}" min="0">
                            <span class="input-group-text">{{ $settings['point']['currency'] ?? 'P' }}</span>
                        </div>
                        <div class="form-text">리뷰 작성 시 지급할 포인트</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="point_earn_referral" class="form-label">추천인 가입</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="point_earn.referral" id="point_earn_referral"
                                value="{{ $settings['point']['earn']['referral'] ?? 500 }}" min="0">
                            <span class="input-group-text">{{ $settings['point']['currency'] ?? 'P' }}</span>
                        </div>
                        <div class="form-text">추천한 회원이 가입했을 때 지급할 포인트</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="point_earn_purchase_rate" class="form-label">구매 적립률</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="point_earn.purchase_rate" id="point_earn_purchase_rate"
                                value="{{ $settings['point']['earn']['purchase_rate'] ?? 1 }}" min="0" max="100" step="0.1">
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text">구매 금액의 몇 퍼센트를 적립할지 설정</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 포인트 사용 설정 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fe fe-minus-circle me-2 text-danger"></i>포인트 사용 설정
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="point_use_min_amount" class="form-label">최소 사용 포인트</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="point_use.min_amount" id="point_use_min_amount"
                                value="{{ $settings['point']['use']['min_amount'] ?? 100 }}" min="0">
                            <span class="input-group-text">{{ $settings['point']['currency'] ?? 'P' }}</span>
                        </div>
                        <div class="form-text">한 번에 사용할 수 있는 최소 포인트</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="point_use_max_amount_per_order" class="form-label">주문당 최대 사용</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="point_use.max_amount_per_order" id="point_use_max_amount_per_order"
                                value="{{ $settings['point']['use']['max_amount_per_order'] ?? 50000 }}" min="0">
                            <span class="input-group-text">{{ $settings['point']['currency'] ?? 'P' }}</span>
                        </div>
                        <div class="form-text">한 주문에서 사용할 수 있는 최대 포인트</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="point_use_max_rate_per_order" class="form-label">주문 금액 대비 최대 사용률</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="point_use.max_rate_per_order" id="point_use_max_rate_per_order"
                                value="{{ $settings['point']['use']['max_rate_per_order'] ?? 50 }}" min="0" max="100">
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text">주문 금액의 몇 퍼센트까지 포인트로 결제 가능</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 포인트 만료 설정 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fe fe-clock me-2 text-warning"></i>포인트 만료 설정
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="point_expiry.enable" id="point_expiry_enable"
                                {{ ($settings['point']['expiry']['enable'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="point_expiry_enable">
                                포인트 만료 기능 사용
                            </label>
                        </div>
                        <div class="form-text">일정 기간 후 포인트 자동 만료</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="point_expiry_days" class="form-label">포인트 유효 기간</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="point_expiry.days" id="point_expiry_days"
                                value="{{ $settings['point']['expiry']['days'] ?? 365 }}" min="1" max="3650">
                            <span class="input-group-text">일</span>
                        </div>
                        <div class="form-text">포인트 적립 후 만료까지의 기간</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="point_expiry_notice_days" class="form-label">만료 알림 기간</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="point_expiry.notice_days" id="point_expiry_notice_days"
                                value="{{ $settings['point']['expiry']['notice_days'] ?? 30 }}" min="1" max="365">
                            <span class="input-group-text">일 전</span>
                        </div>
                        <div class="form-text">만료 며칠 전에 알림을 보낼지 설정</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- 포인트 통계 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fe fe-pie-chart me-2 text-success"></i>포인트 통계
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">총 발행 포인트:</span>
                    <span class="fw-medium text-primary">2,450,000P</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">사용된 포인트:</span>
                    <span class="fw-medium text-danger">1,230,000P</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">잔여 포인트:</span>
                    <span class="fw-medium text-success">1,220,000P</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">만료 예정:</span>
                    <span class="fw-medium text-warning">85,000P</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">오늘 적립:</span>
                    <span class="fw-medium text-info">12,500P</span>
                </div>
            </div>
        </div>

        <!-- 포인트 시뮬레이터 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fe fe-calculator me-2 text-info"></i>포인트 계산기
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="calc_purchase_amount" class="form-label">구매 금액</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="calc_purchase_amount" placeholder="10000" onchange="calculatePoints()">
                        <span class="input-group-text">원</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">적립 포인트</label>
                    <div class="alert alert-info mb-0">
                        <div class="d-flex justify-content-between">
                            <span>적립률:</span>
                            <span id="calc_rate">{{ $settings['point']['earn']['purchase_rate'] ?? 1 }}%</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>적립 포인트:</span>
                            <span id="calc_earned_points">0{{ $settings['point']['currency'] ?? 'P' }}</span>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="calc_use_points" class="form-label">사용할 포인트</label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="calc_use_points" placeholder="1000" onchange="calculateUsage()">
                        <span class="input-group-text">{{ $settings['point']['currency'] ?? 'P' }}</span>
                    </div>
                </div>

                <div class="alert alert-success">
                    <div class="d-flex justify-content-between">
                        <span>할인 금액:</span>
                        <span id="calc_discount">0원</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>최종 결제:</span>
                        <span id="calc_final_amount">0원</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 포인트 운영 가이드 -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">
                    <i class="fe fe-book-open me-2"></i>운영 가이드
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-dark">적립률 설정</h6>
                    <p class="small text-muted mb-0">
                        일반적으로 1-5% 범위에서 설정하며, 업종과 마진을 고려하여 결정하세요.
                    </p>
                </div>
                <div class="mb-3">
                    <h6 class="text-dark">사용 제한</h6>
                    <p class="small text-muted mb-0">
                        과도한 포인트 사용을 방지하기 위해 적절한 제한을 설정하는 것이 중요합니다.
                    </p>
                </div>
                <div>
                    <h6 class="text-dark">만료 정책</h6>
                    <p class="small text-muted mb-0">
                        포인트 만료는 사용자의 재방문을 유도하고 부채를 관리하는 데 효과적입니다.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// 포인트 계산기
function calculatePoints() {
    const purchaseAmount = parseInt(document.getElementById('calc_purchase_amount').value) || 0;
    const rate = parseFloat(document.getElementById('point_earn_purchase_rate').value) || 1;
    const currency = document.querySelector('input[name="point_currency"]').value || 'P';

    const earnedPoints = Math.floor(purchaseAmount * rate / 100);
    document.getElementById('calc_earned_points').textContent = earnedPoints + currency;
    document.getElementById('calc_rate').textContent = rate + '%';

    // 사용 포인트 계산도 다시 실행
    calculateUsage();
}

function calculateUsage() {
    const purchaseAmount = parseInt(document.getElementById('calc_purchase_amount').value) || 0;
    const usePoints = parseInt(document.getElementById('calc_use_points').value) || 0;
    const maxRate = parseInt(document.getElementById('point_use_max_rate_per_order').value) || 50;
    const maxAmount = parseInt(document.getElementById('point_use_max_amount_per_order').value) || 50000;

    // 사용 가능한 최대 포인트 계산
    const maxByRate = Math.floor(purchaseAmount * maxRate / 100);
    const maxUsable = Math.min(maxByRate, maxAmount);
    const actualUse = Math.min(usePoints, maxUsable);

    const discount = actualUse;
    const finalAmount = Math.max(0, purchaseAmount - discount);

    document.getElementById('calc_discount').textContent = discount.toLocaleString() + '원';
    document.getElementById('calc_final_amount').textContent = finalAmount.toLocaleString() + '원';

    // 제한 초과 시 경고
    if (usePoints > maxUsable && usePoints > 0) {
        document.getElementById('calc_use_points').style.borderColor = '#dc3545';
        document.getElementById('calc_use_points').title = `최대 사용 가능: ${maxUsable.toLocaleString()}P`;
    } else {
        document.getElementById('calc_use_points').style.borderColor = '';
        document.getElementById('calc_use_points').title = '';
    }
}

// 통화 단위 업데이트
document.addEventListener('DOMContentLoaded', function() {
    const currencyInput = document.getElementById('point_currency');

    function updateCurrencySuffix() {
        const currency = currencyInput.value || 'P';
        document.querySelectorAll('.input-group-text').forEach(element => {
            if (element.textContent.match(/^[A-Za-z가-힣]+$/)) {
                element.textContent = currency;
            }
        });
    }

    currencyInput.addEventListener('input', updateCurrencySuffix);

    // 설정값 변경 시 계산기 업데이트
    document.querySelectorAll('input[name^="point_"]').forEach(input => {
        input.addEventListener('input', function() {
            calculatePoints();
        });
    });

    // 초기 계산
    updateCurrencySuffix();
});
</script>
@endpush