@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', 'Emoney 시스템 설정')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">Emoney 시스템 설정</h2>
                    <p class="text-muted mb-0">Emoney 및 포인트 시스템 설정 관리</p>
                </div>
                <div>
                    <a href="{{ route('admin.auth.emoney.setting.backup') }}" class="btn btn-outline-info">
                        <i class="fe fe-download me-2"></i>설정 백업
                    </a>
                    <button type="button" class="btn btn-outline-warning" onclick="resetSettings()">
                        <i class="fe fe-refresh-cw me-2"></i>초기화
                    </button>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.auth.emoney.setting.store') }}">
                @csrf

                <div class="row">
                    <!-- Emoney 설정 -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fe fe-dollar-sign me-2"></i>Emoney 설정
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- Emoney 활성화 -->
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="emoney_enable"
                                               name="emoney[enable]" value="1"
                                               {{ old('emoney.enable', $settings['emoney']['enable'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="emoney_enable">
                                            <strong>Emoney 시스템 활성화</strong>
                                        </label>
                                    </div>
                                    <small class="text-muted">Emoney 시스템 전체 활성화/비활성화</small>
                                </div>

                                <!-- 가입 지급 포인트 -->
                                <div class="mb-3">
                                    <label for="emoney_register" class="form-label">가입 시 지급 Emoney</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="emoney_register"
                                               name="emoney[register]" min="0" step="1"
                                               value="{{ old('emoney.register', $settings['emoney']['register'] ?? 1000) }}">
                                        <span class="input-group-text">원</span>
                                    </div>
                                    <small class="text-muted">신규 회원 가입시 자동 지급되는 Emoney 금액</small>
                                </div>

                                <!-- 기본 잔액 -->
                                <div class="mb-3">
                                    <label for="emoney_default_balance" class="form-label">기본 잔액</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="emoney_default_balance"
                                               name="emoney[default_balance]" min="0" step="1"
                                               value="{{ old('emoney.default_balance', $settings['emoney']['default_balance'] ?? 0) }}">
                                        <span class="input-group-text">원</span>
                                    </div>
                                    <small class="text-muted">계정 생성시 기본 Emoney 잔액</small>
                                </div>

                                <!-- 최대 잔액 -->
                                <div class="mb-3">
                                    <label for="emoney_max_balance" class="form-label">최대 보유 한도</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="emoney_max_balance"
                                               name="emoney[max_balance]" min="0" step="1"
                                               value="{{ old('emoney.max_balance', $settings['emoney']['max_balance'] ?? 1000000) }}">
                                        <span class="input-group-text">원</span>
                                    </div>
                                    <small class="text-muted">개인이 보유할 수 있는 최대 Emoney 금액 (0은 제한없음)</small>
                                </div>

                                <!-- 송금 기능 -->
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="emoney_transfer_enabled"
                                               name="emoney[transfer_enabled]" value="1"
                                               {{ old('emoney.transfer_enabled', $settings['emoney']['transfer_enabled'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="emoney_transfer_enabled">
                                            사용자간 송금 허용
                                        </label>
                                    </div>
                                </div>

                                <!-- 송금 수수료 -->
                                <div class="mb-3">
                                    <label for="emoney_transfer_fee" class="form-label">송금 수수료율</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="emoney_transfer_fee"
                                               name="emoney[transfer_fee]" min="0" max="100" step="0.1"
                                               value="{{ old('emoney.transfer_fee', $settings['emoney']['transfer_fee'] ?? 0) }}">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <small class="text-muted">송금시 차감되는 수수료율 (0-100%)</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 포인트 설정 -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fe fe-gift me-2"></i>포인트 설정
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- 포인트 활성화 -->
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="point_enable"
                                               name="point[enable]" value="1"
                                               {{ old('point.enable', $settings['point']['enable'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="point_enable">
                                            <strong>포인트 시스템 활성화</strong>
                                        </label>
                                    </div>
                                    <small class="text-muted">포인트 시스템 전체 활성화/비활성화</small>
                                </div>

                                <!-- 가입 지급 포인트 -->
                                <div class="mb-3">
                                    <label for="point_register" class="form-label">가입 시 지급 포인트</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="point_register"
                                               name="point[register]" min="0" step="1"
                                               value="{{ old('point.register', $settings['point']['register'] ?? 1000) }}">
                                        <span class="input-group-text">P</span>
                                    </div>
                                    <small class="text-muted">신규 회원 가입시 자동 지급되는 포인트</small>
                                </div>

                                <!-- 포인트 만료 -->
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="point_expiry_enabled"
                                               name="point[expiry_enabled]" value="1"
                                               {{ old('point.expiry_enabled', $settings['point']['expiry_enabled'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="point_expiry_enabled">
                                            포인트 만료 기능 활성화
                                        </label>
                                    </div>
                                    <small class="text-muted">포인트 자동 만료 기능 사용</small>
                                </div>

                                <!-- 만료 기간 -->
                                <div class="mb-3">
                                    <label for="point_expiry_days" class="form-label">포인트 만료 기간</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="point_expiry_days"
                                               name="point[expiry_days]" min="1" step="1"
                                               value="{{ old('point.expiry_days', $settings['point']['expiry_days'] ?? 365) }}">
                                        <span class="input-group-text">일</span>
                                    </div>
                                    <small class="text-muted">포인트 적립 후 만료되는 기간</small>
                                </div>

                                <!-- 만료 알림 -->
                                <div class="mb-3">
                                    <label for="point_notification_days" class="form-label">만료 알림 기간</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="point_notification_days"
                                               name="point[notification_days]" min="1" step="1"
                                               value="{{ old('point.notification_days', $settings['point']['notification_days'] ?? 7) }}">
                                        <span class="input-group-text">일 전</span>
                                    </div>
                                    <small class="text-muted">만료 며칠 전에 알림을 발송할지 설정</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 시스템 정보 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fe fe-info me-2"></i>시스템 정보
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h6 class="text-muted">설정 파일 위치</h6>
                                    <p class="mb-0"><code>vendor/jiny/emoney/config/setting.json</code></p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h6 class="text-muted">마지막 수정일</h6>
                                    <p class="mb-0">
                                        @if(file_exists(base_path('vendor/jiny/emoney/config/setting.json')))
                                            {{ \Carbon\Carbon::createFromTimestamp(filemtime(base_path('vendor/jiny/emoney/config/setting.json')))->format('Y-m-d H:i:s') }}
                                        @else
                                            파일 없음
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h6 class="text-muted">현재 버전</h6>
                                    <p class="mb-0">v1.0.0</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h6 class="text-muted">설정 상태</h6>
                                    <p class="mb-0">
                                        @if(($settings['emoney']['enable'] ?? false) || ($settings['point']['enable'] ?? false))
                                            <span class="badge bg-success">활성</span>
                                        @else
                                            <span class="badge bg-secondary">비활성</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 저장 버튼 -->
                <div class="text-end">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fe fe-save me-2"></i>설정 저장
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
function resetSettings() {
    if (confirm('모든 설정을 초기값으로 되돌리시겠습니까?\n\n이 작업은 되돌릴 수 없습니다.')) {
        // 초기화 폼 생성 및 제출
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.auth.emoney.setting.reset") }}';

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);

        document.body.appendChild(form);
        form.submit();
    }
}

// 폼 변경 감지
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const inputs = form.querySelectorAll('input, select, textarea');
    let originalData = new FormData(form);

    let hasChanges = false;

    inputs.forEach(input => {
        input.addEventListener('change', function() {
            hasChanges = true;
        });
    });

    // 페이지 떠날 때 경고
    window.addEventListener('beforeunload', function(e) {
        if (hasChanges) {
            e.preventDefault();
            e.returnValue = '저장하지 않은 변경사항이 있습니다. 페이지를 떠나시겠습니까?';
        }
    });

    // 폼 제출시 경고 해제
    form.addEventListener('submit', function() {
        hasChanges = false;
    });
});

// 포인트 만료 기능과 관련 옵션 연동
document.getElementById('point_expiry_enabled').addEventListener('change', function() {
    const expiryDays = document.getElementById('point_expiry_days');
    const notificationDays = document.getElementById('point_notification_days');

    if (this.checked) {
        expiryDays.removeAttribute('disabled');
        notificationDays.removeAttribute('disabled');
    } else {
        expiryDays.setAttribute('disabled', 'disabled');
        notificationDays.setAttribute('disabled', 'disabled');
    }
});

// 송금 기능과 수수료 연동
document.getElementById('emoney_transfer_enabled').addEventListener('change', function() {
    const transferFee = document.getElementById('emoney_transfer_fee');

    if (this.checked) {
        transferFee.removeAttribute('disabled');
    } else {
        transferFee.setAttribute('disabled', 'disabled');
    }
});

// 페이지 로드시 초기 상태 설정
document.addEventListener('DOMContentLoaded', function() {
    // 포인트 만료 관련
    const pointExpiryEnabled = document.getElementById('point_expiry_enabled');
    if (pointExpiryEnabled) {
        pointExpiryEnabled.dispatchEvent(new Event('change'));
    }

    // 송금 관련
    const transferEnabled = document.getElementById('emoney_transfer_enabled');
    if (transferEnabled) {
        transferEnabled.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection