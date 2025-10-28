@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '은행 추가')

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
                                <i class="fe fe-plus me-2"></i>
                                은행 추가
                            </h1>
                            <p class="page-header-subtitle">새로운 은행 정보를 등록합니다</p>
                        </div>
                        <div>
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
            <form method="POST" action="{{ route('admin.auth.bank.store') }}">
                @csrf

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
                                        <option value="{{ $code }}" {{ old('country') == $code ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="bank_select" class="form-label">은행 선택 <span class="text-danger">*</span></label>
                                <select class="form-select @error('name') is-invalid @enderror" id="bank_select" disabled>
                                    <option value="">먼저 국가를 선택하세요</option>
                                </select>
                                <input type="hidden" id="name" name="name" value="{{ old('name') }}">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="code" class="form-label">은행 코드</label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror"
                                       id="code" name="code" value="{{ old('code') }}" maxlength="10"
                                       placeholder="은행을 식별하는 고유 코드" readonly>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">은행 선택 시 자동으로 설정됩니다</div>
                            </div>

                            <div class="col-md-6">
                                <label for="swift_code" class="form-label">SWIFT 코드</label>
                                <input type="text" class="form-control @error('swift_code') is-invalid @enderror"
                                       id="swift_code" name="swift_code" value="{{ old('swift_code') }}" maxlength="11"
                                       placeholder="SWIFT 코드" readonly>
                                @error('swift_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">은행 선택 시 자동으로 설정됩니다</div>
                            </div>

                            <div class="col-md-6">
                                <label for="website" class="form-label">웹사이트</label>
                                <input type="url" class="form-control @error('website') is-invalid @enderror"
                                       id="website" name="website" value="{{ old('website') }}" placeholder="https://example.com" readonly>
                                @error('website')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">은행 선택 시 자동으로 설정됩니다</div>
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label">전화번호</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                       id="phone" name="phone" value="{{ old('phone') }}" maxlength="50"
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
                                       id="account_number" name="account_number" value="{{ old('account_number') }}" maxlength="50"
                                       placeholder="계좌번호를 입력하세요">
                                @error('account_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="account_holder" class="form-label">예금주</label>
                                <input type="text" class="form-control @error('account_holder') is-invalid @enderror"
                                       id="account_holder" name="account_holder" value="{{ old('account_holder') }}" maxlength="100"
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
                                          placeholder="은행에 대한 추가 설명 (선택사항)">{{ old('description') }}</textarea>
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
                                       id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" max="9999"
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
                                           {{ old('enable', true) ? 'checked' : '' }}>
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
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.auth.bank.index') }}" class="btn btn-outline-secondary">
                                <i class="fe fe-x me-2"></i>취소
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fe fe-save me-2"></i>은행 등록
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <!-- 은행 등록 도움말 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fe fe-help-circle me-2"></i>은행 등록 도움말
                    </h5>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="mb-3">
                            <h6 class="text-primary mb-2">
                                <i class="fe fe-check-circle me-1"></i>필수 항목:
                            </h6>
                            <ul class="text-muted list-unstyled">
                                <li class="mb-1">• 은행명: 사용자에게 표시될 은행 이름</li>
                                <li class="mb-1">• 국가: 은행이 소속된 국가</li>
                            </ul>
                        </div>

                        <div class="mb-3">
                            <h6 class="text-info mb-2">
                                <i class="fe fe-info me-1"></i>기본 정보 (선택):
                            </h6>
                            <ul class="text-muted list-unstyled">
                                <li class="mb-1">• 은행 코드: 시스템 내부에서 사용할 고유 코드</li>
                                <li class="mb-1">• SWIFT 코드: 국제 송금용 식별 코드</li>
                                <li class="mb-1">• 웹사이트: 은행 공식 홈페이지</li>
                                <li class="mb-1">• 전화번호: 고객 서비스 번호</li>
                            </ul>
                        </div>

                        <div class="mb-3">
                            <h6 class="text-success mb-2">
                                <i class="fe fe-credit-card me-1"></i>계좌 정보 (선택):
                            </h6>
                            <ul class="text-muted list-unstyled">
                                <li class="mb-1">• 계좌번호: 은행 계좌번호 정보</li>
                                <li class="mb-1">• 예금주: 계좌 소유자 이름</li>
                            </ul>
                        </div>

                        <div class="mb-3">
                            <h6 class="text-warning mb-2">
                                <i class="fe fe-sort-asc me-1"></i>정렬 순서:
                            </h6>
                            <p class="text-muted mb-0">낮은 숫자일수록 목록에서 위쪽에 표시됩니다. 같은 순서인 경우 은행명 순으로 정렬됩니다.</p>
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
                                    <p class="mb-1"><strong>중복 확인:</strong></p>
                                    <p class="mb-0">같은 은행명이나 은행 코드가 이미 등록되어 있는지 확인해주세요.</p>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info mb-0">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fe fe-info"></i>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <p class="mb-1"><strong>활성 상태:</strong></p>
                                    <p class="mb-0">비활성 상태로 등록하면 사용자에게 표시되지 않습니다.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
    const nameInput = document.getElementById('name');
    const codeInput = document.getElementById('code');
    const swiftCodeInput = document.getElementById('swift_code');
    const websiteInput = document.getElementById('website');
    const phoneInput = document.getElementById('phone');

    // 국가 선택 시 은행 목록 로드
    countrySelect.addEventListener('change', function() {
        const countryCode = this.value;
        console.log('Country selected:', countryCode);

        // 모든 필드 초기화
        clearBankFields();

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
            banks.forEach(bank => {
                const option = document.createElement('option');
                option.value = JSON.stringify(bank);
                option.textContent = bank.name;
                bankSelect.appendChild(option);
            });
            bankSelect.disabled = false;
        } else {
            bankSelect.innerHTML = '<option value="">해당 국가의 은행이 없습니다</option>';
            bankSelect.disabled = true;
        }
    });

    // 은행 선택 시 정보 자동 채우기
    bankSelect.addEventListener('change', function() {
        console.log('Bank selected:', this.value);

        if (!this.value) {
            clearBankFields();
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
            clearBankFields();
        }
    });

    // 은행 관련 필드 초기화
    function clearBankFields() {
        nameInput.value = '';
        codeInput.value = '';
        swiftCodeInput.value = '';
        websiteInput.value = '';
        phoneInput.value = '';
    }

    // 페이지 로드 시 기존 값 복원
    const oldCountry = '{{ old("country") }}';
    const oldName = '{{ old("name") }}';

    if (oldCountry) {
        countrySelect.value = oldCountry;
        countrySelect.dispatchEvent(new Event('change'));

        // 기존 은행 선택 복원
        if (oldName) {
            setTimeout(() => {
                const options = bankSelect.querySelectorAll('option');
                for (let option of options) {
                    if (option.value) {
                        try {
                            const bank = JSON.parse(option.value);
                            if (bank.name === oldName) {
                                bankSelect.value = option.value;
                                bankSelect.dispatchEvent(new Event('change'));
                                break;
                            }
                        } catch (e) {
                            // Skip invalid JSON
                        }
                    }
                }
            }, 100);
        }
    }
});
</script>
@endsection
