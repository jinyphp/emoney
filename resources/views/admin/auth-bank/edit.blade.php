@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '은행 수정')

@section('content')
<div class="container-fluid p-6">
    <!-- Page Header -->
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="page-header">
                <div class="page-header-content">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="page-header-title">
                                <i class="fe fe-edit me-2"></i>
                                은행 수정
                            </h1>
                            <p class="page-header-subtitle">{{ $bank->name }} 정보를 수정합니다</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.auth.bank.show', $bank->id) }}" class="btn btn-outline-secondary me-2">
                                <i class="fe fe-eye me-2"></i>상세보기
                            </a>
                            <a href="{{ route('admin.auth.bank.index') }}" class="btn btn-outline-secondary">
                                <i class="fe fe-arrow-left me-2"></i>목록으로
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 성공/오류 메시지 -->
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
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row mt-4">
        <div class="col-lg-8">

            <form method="POST" action="{{ route('admin.auth.bank.update', $bank->id) }}">
                @csrf
                @method('PUT')

                <!-- 기본 정보 섹션 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="card-title mb-0">기본 정보</h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="country" class="form-label">국가 <span class="text-danger">*</span></label>
                                <select class="form-select @error('country') is-invalid @enderror" id="country" name="country" required>
                                    <option value="">국가를 선택하세요</option>
                                    @foreach($countries as $code => $name)
                                        <option value="{{ $code }}" {{ old('country', $bank->country) == $code ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="bank_select" class="form-label">은행 선택 <span class="text-danger">*</span></label>
                                <select class="form-select @error('name') is-invalid @enderror" id="bank_select">
                                    <option value="">로딩 중...</option>
                                </select>
                                <input type="hidden" id="name" name="name" value="{{ old('name', $bank->name) }}">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="custom_bank">
                                    <label class="form-check-label" for="custom_bank">
                                        사용자 정의 은행 정보 사용
                                    </label>
                                </div>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="code" class="form-label">은행 코드</label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror"
                                       id="code" name="code" value="{{ old('code', $bank->code) }}" maxlength="10"
                                       placeholder="은행을 식별하는 고유 코드" readonly>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">은행 선택 시 자동으로 설정됩니다</div>
                            </div>

                            <div class="col-md-6">
                                <label for="swift_code" class="form-label">SWIFT 코드</label>
                                <input type="text" class="form-control @error('swift_code') is-invalid @enderror"
                                       id="swift_code" name="swift_code" value="{{ old('swift_code', $bank->swift_code) }}" maxlength="11"
                                       placeholder="SWIFT 코드" readonly>
                                @error('swift_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">은행 선택 시 자동으로 설정됩니다</div>
                            </div>

                            <div class="col-md-6">
                                <label for="website" class="form-label">웹사이트</label>
                                <input type="url" class="form-control @error('website') is-invalid @enderror"
                                       id="website" name="website" value="{{ old('website', $bank->website) }}" placeholder="https://example.com" readonly>
                                @error('website')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">은행 선택 시 자동으로 설정됩니다</div>
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label">전화번호</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                       id="phone" name="phone" value="{{ old('phone', $bank->phone) }}" maxlength="50"
                                       placeholder="전화번호" readonly>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">은행 선택 시 자동으로 설정됩니다</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 계좌 정보 섹션 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="card-title mb-0">계좌 정보</h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="account_number" class="form-label">계좌번호</label>
                                <input type="text" class="form-control @error('account_number') is-invalid @enderror"
                                       id="account_number" name="account_number" value="{{ old('account_number', $bank->account_number) }}" maxlength="50"
                                       placeholder="계좌번호를 입력하세요">
                                @error('account_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="account_holder" class="form-label">예금주</label>
                                <input type="text" class="form-control @error('account_holder') is-invalid @enderror"
                                       id="account_holder" name="account_holder" value="{{ old('account_holder', $bank->account_holder) }}" maxlength="100"
                                       placeholder="예금주명을 입력하세요">
                                @error('account_holder')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 설명 섹션 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="card-title mb-0">설명</h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="description" class="form-label">은행에 대한 추가 설명</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="4" maxlength="1000"
                                          placeholder="은행에 대한 추가 설명 (선택사항)">{{ old('description', $bank->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">은행에 대한 추가 설명을 입력하세요</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 설정 섹션 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="card-title mb-0">설정</h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="sort_order" class="form-label">정렬 순서</label>
                                <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                       id="sort_order" name="sort_order" value="{{ old('sort_order', $bank->sort_order) }}" min="0" max="9999"
                                       placeholder="0">
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">낮은 숫자일수록 목록에서 위쪽에 표시됩니다</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">상태</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="enable" name="enable" value="1"
                                           {{ old('enable', $bank->enable) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enable">
                                        활성 상태
                                    </label>
                                </div>
                                <div class="form-text">활성화된 은행만 사용자에게 표시됩니다</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 버튼 섹션 -->
                <div class="d-flex justify-content-between align-items-center mt-4 pt-4 border-top">
                    <div>
                        <a href="{{ route('admin.auth.bank.show', $bank->id) }}" class="btn btn-outline-secondary me-2">
                            <i class="fe fe-x me-2"></i>취소
                        </a>
                        <button type="button" class="btn btn-outline-danger" onclick="deleteBank({{ $bank->id }}, '{{ $bank->name }}')">
                            <i class="fe fe-trash me-2"></i>삭제
                        </button>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fe fe-save me-2"></i>변경사항 저장
                    </button>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <!-- 현재 은행 정보 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fe fe-info me-2"></i>현재 정보
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        @if($bank->logo_url)
                            <img src="{{ $bank->logo_url }}" alt="{{ $bank->name }}" class="rounded me-3" style="width: 48px; height: 48px;">
                        @else
                            <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fe fe-credit-card text-muted"></i>
                            </div>
                        @endif
                        <div>
                            <h6 class="mb-1">{{ $bank->name }}</h6>
                            <div class="d-flex align-items-center gap-2">
                                @if($bank->enable)
                                    <span class="badge bg-success">활성</span>
                                @else
                                    <span class="badge bg-secondary">비활성</span>
                                @endif
                                <span class="badge bg-info">{{ $bank->country_name }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 small">
                        <div class="col-12">
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">은행 코드</span>
                                <span>{{ $bank->code ?: '-' }}</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">SWIFT 코드</span>
                                <span>{{ $bank->swift_code ?: '-' }}</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">계좌번호</span>
                                <span>{{ $bank->account_number ?: '-' }}</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">예금주</span>
                                <span>{{ $bank->account_holder ?: '-' }}</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">정렬 순서</span>
                                <span class="badge bg-light text-dark">{{ $bank->sort_order }}</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between py-2">
                                <span class="text-muted">등록일</span>
                                <span>{{ $bank->created_at->format('Y-m-d') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 수정 도움말 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fe fe-help-circle me-2"></i>수정 도움말
                    </h5>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="mb-3">
                            <h6 class="text-primary mb-2">
                                <i class="fe fe-check-circle me-1"></i>수정 가능 항목:
                            </h6>
                            <ul class="text-muted list-unstyled">
                                <li class="mb-1">• 은행명: 사용자에게 표시될 은행 이름</li>
                                <li class="mb-1">• 국가: 은행이 소속된 국가</li>
                                <li class="mb-1">• 연락처: 웹사이트, 전화번호</li>
                                <li class="mb-1">• 계좌 정보: 계좌번호, 예금주</li>
                                <li class="mb-1">• 상태: 활성/비활성 상태 변경</li>
                            </ul>
                        </div>

                        <div class="mb-3">
                            <h6 class="text-warning mb-2">
                                <i class="fe fe-sort-asc me-1"></i>정렬 순서:
                            </h6>
                            <p class="text-muted mb-0">0부터 9999까지 설정 가능하며, 낮은 숫자일수록 목록에서 위쪽에 표시됩니다.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 주의사항 -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fe fe-alert-triangle me-2"></i>주의 사항
                    </h5>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="alert alert-warning mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fe fe-alert-triangle"></i>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <p class="mb-1"><strong>데이터 영향:</strong></p>
                                    <p class="mb-0">은행명이나 코드 변경 시 기존 연결된 데이터에 영향을 줄 수 있습니다.</p>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fe fe-eye-off"></i>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <p class="mb-1"><strong>비활성 상태:</strong></p>
                                    <p class="mb-0">비활성화하면 사용자에게 표시되지 않습니다.</p>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-danger mb-0">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fe fe-trash"></i>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <p class="mb-1"><strong>삭제 제한:</strong></p>
                                    <p class="mb-0">관련된 데이터가 있으면 삭제가 제한될 수 있습니다.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
function deleteBank(id, bankName) {
    if(confirm(`정말로 '${bankName}' 은행을 삭제하시겠습니까?\n\n이 작업은 되돌릴 수 없습니다.`)) {
        // CSRF 토큰을 포함한 삭제 폼 생성
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/auth/bank/${id}`;

        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';

        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = '{{ csrf_token() }}';

        form.appendChild(methodInput);
        form.appendChild(tokenInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<script>
// 은행 데이터 (하드코딩으로 임시 해결)
const bankData = {
    "KR": [
        {"name": "KB국민은행", "code": "KB", "swift_code": "CZNBKRSE", "website": "https://www.kbstar.com", "phone": "1588-9999"},
        {"name": "신한은행", "code": "SH", "swift_code": "SHBKKRSE", "website": "https://www.shinhan.com", "phone": "1599-8000"},
        {"name": "우리은행", "code": "WR", "swift_code": "HVBKKRSE", "website": "https://www.wooribank.com", "phone": "1599-5000"},
        {"name": "하나은행", "code": "HN", "swift_code": "HNBNKRSE", "website": "https://www.kebhana.com", "phone": "1599-1111"},
        {"name": "기업은행", "code": "IBK", "swift_code": "IBKOKRSE", "website": "https://www.ibk.co.kr", "phone": "1566-2566"},
        {"name": "NH농협은행", "code": "NH", "swift_code": "NACFKRSE", "website": "https://banking.nonghyup.com", "phone": "1588-2100"},
        {"name": "수협은행", "code": "SUHYUP", "swift_code": "SHFCKRSE", "website": "https://www.suhyup-bank.com", "phone": "1588-1515"},
        {"name": "카카오뱅크", "code": "KAKAO", "swift_code": null, "website": "https://www.kakaobank.com", "phone": "1599-3333"},
        {"name": "케이뱅크", "code": "KBANK", "swift_code": null, "website": "https://www.kbanknow.com", "phone": "1522-1000"},
        {"name": "토스뱅크", "code": "TOSS", "swift_code": null, "website": "https://www.tossbank.com", "phone": "1661-7654"}
    ],
    "US": [
        {"name": "Bank of America", "code": "BOA", "swift_code": "BOFAUS3N", "website": "https://www.bankofamerica.com", "phone": "+1-800-432-1000"},
        {"name": "JPMorgan Chase", "code": "CHASE", "swift_code": "CHASUS33", "website": "https://www.chase.com", "phone": "+1-800-935-9935"},
        {"name": "Wells Fargo", "code": "WF", "swift_code": "WFBIUS6S", "website": "https://www.wellsfargo.com", "phone": "+1-800-869-3557"},
        {"name": "Citibank", "code": "CITI_US", "swift_code": "CITIUS33", "website": "https://www.citibank.com", "phone": "+1-800-374-9700"}
    ],
    "JP": [
        {"name": "MUFG Bank", "code": "MUFG", "swift_code": "BOTKJPJT", "website": "https://www.bk.mufg.jp", "phone": "+81-3-3240-1111"},
        {"name": "Mizuho Bank", "code": "MIZUHO", "swift_code": "MHBKJPJT", "website": "https://www.mizuhobank.com", "phone": "+81-3-3596-1111"},
        {"name": "Sumitomo Mitsui Banking", "code": "SMBC", "swift_code": "SMBCJPJT", "website": "https://www.smbc.co.jp", "phone": "+81-3-3282-8111"}
    ]
};

document.addEventListener('DOMContentLoaded', function() {
    const countrySelect = document.getElementById('country');
    const bankSelect = document.getElementById('bank_select');
    const customBankCheck = document.getElementById('custom_bank');
    const nameInput = document.getElementById('name');
    const codeInput = document.getElementById('code');
    const swiftCodeInput = document.getElementById('swift_code');
    const websiteInput = document.getElementById('website');
    const phoneInput = document.getElementById('phone');

    // 현재 은행 정보
    const currentBank = {
        name: '{{ $bank->name }}',
        code: '{{ $bank->code }}',
        swift_code: '{{ $bank->swift_code }}',
        website: '{{ $bank->website }}',
        phone: '{{ $bank->phone }}'
    };

    console.log('Current bank:', currentBank);

    // 사용자 정의 은행 체크박스 변경 시
    customBankCheck.addEventListener('change', function() {
        const isCustom = this.checked;
        console.log('Custom bank mode:', isCustom);

        codeInput.readOnly = !isCustom;
        swiftCodeInput.readOnly = !isCustom;
        websiteInput.readOnly = !isCustom;
        phoneInput.readOnly = !isCustom;
        bankSelect.disabled = isCustom;

        if (isCustom) {
            // 사용자 정의 모드: 현재 값들을 복원하고 편집 가능하게
            nameInput.value = currentBank.name;
            codeInput.value = currentBank.code;
            swiftCodeInput.value = currentBank.swift_code;
            websiteInput.value = currentBank.website;
            phoneInput.value = currentBank.phone;

            // 은행 선택을 비활성화
            bankSelect.style.opacity = '0.5';
        } else {
            // 목록 선택 모드
            bankSelect.style.opacity = '1';
        }
    });

    // 국가 선택 시 은행 목록 로드
    countrySelect.addEventListener('change', function() {
        const countryCode = this.value;
        console.log('Country selected:', countryCode);

        if (!countryCode) {
            bankSelect.disabled = true;
            bankSelect.innerHTML = '<option value="">먼저 국가를 선택하세요</option>';
            return;
        }

        // 해당 국가의 은행 목록 가져오기
        const banks = bankData[countryCode] || [];
        console.log('Banks found:', banks);

        // 은행 선택 옵션 업데이트
        bankSelect.innerHTML = '<option value="">은행을 선택하세요</option>';

        if (banks.length > 0) {
            let foundCurrentBank = false;

            banks.forEach(bank => {
                const option = document.createElement('option');
                option.value = JSON.stringify(bank);
                option.textContent = bank.name;

                // 현재 은행과 일치하는지 확인
                if (bank.name === currentBank.name) {
                    option.selected = true;
                    foundCurrentBank = true;
                }

                bankSelect.appendChild(option);
            });

            // 현재 은행이 목록에 없으면 사용자 정의 모드로 전환
            if (!foundCurrentBank) {
                customBankCheck.checked = true;
                customBankCheck.dispatchEvent(new Event('change'));
            }

            bankSelect.disabled = customBankCheck.checked;
        } else {
            bankSelect.innerHTML = '<option value="">해당 국가의 은행이 없습니다</option>';
            // 은행 목록이 없으면 사용자 정의 모드로 전환
            customBankCheck.checked = true;
            customBankCheck.dispatchEvent(new Event('change'));
        }
    });

    // 은행 선택 시 정보 자동 채우기
    bankSelect.addEventListener('change', function() {
        console.log('Bank selected:', this.value);

        if (!this.value || customBankCheck.checked) {
            return;
        }

        try {
            const bank = JSON.parse(this.value);
            console.log('Parsed bank data:', bank);

            nameInput.value = bank.name || '';
            codeInput.value = bank.code || '';
            swiftCodeInput.value = bank.swift_code || '';
            websiteInput.value = bank.website || '';
            phoneInput.value = bank.phone || '';

        } catch (error) {
            console.error('Error parsing bank data:', error);
        }
    });

    // 페이지 로드 시 초기화
    const currentCountry = countrySelect.value;
    if (currentCountry) {
        console.log('Initializing with country:', currentCountry);
        countrySelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection
