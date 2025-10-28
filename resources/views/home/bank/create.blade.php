@extends($layout ?? 'jiny-auth::layouts.home')

@section('title', '계좌 등록')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    .country-flag {
        width: 20px;
        height: 15px;
        margin-right: 8px;
    }
    .bank-option {
        padding: 8px 12px;
        border-bottom: 1px solid #eee;
    }
    .bank-option:hover {
        background-color: #f8f9fa;
    }
    .bank-info {
        font-size: 0.9rem;
        color: #6c757d;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- 헤더 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-2">
                        <i class="bi bi-plus-circle text-primary"></i>
                        계좌 등록
                    </h2>
                    <p class="text-muted mb-0">새로운 은행 계좌를 등록하세요</p>
                </div>
                <div>
                    <a href="{{ route('home.emoney.bank.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> 계좌 목록으로
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 계좌 등록 폼 -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">은행 계좌 정보</h5>
                </div>
                <div class="card-body">
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

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>입력 오류:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('home.emoney.bank.store') }}" method="POST">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="country" class="form-label">국가 <span class="text-danger">*</span></label>
                                <select class="form-select" id="country" name="country" required>
                                    <option value="">국가를 선택하세요</option>
                                    <option value="KR">🇰🇷 대한민국</option>
                                    <option value="US">🇺🇸 미국</option>
                                    <option value="JP">🇯🇵 일본</option>
                                    <option value="CN">🇨🇳 중국</option>
                                    <option value="GB">🇬🇧 영국</option>
                                    <option value="DE">🇩🇪 독일</option>
                                    <option value="FR">🇫🇷 프랑스</option>
                                    <option value="CA">🇨🇦 캐나다</option>
                                    <option value="AU">🇦🇺 호주</option>
                                    <option value="SG">🇸🇬 싱가포르</option>
                                    <option value="HK">🇭🇰 홍콩</option>
                                    <option value="TH">🇹🇭 태국</option>
                                    <option value="VN">🇻🇳 베트남</option>
                                    <option value="ID">🇮🇩 인도네시아</option>
                                    <option value="MY">🇲🇾 말레이시아</option>
                                    <option value="PH">🇵🇭 필리핀</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="bank_name" class="form-label">은행명 <span class="text-danger">*</span></label>
                                <select class="form-select" id="bank_name" name="bank_name" required disabled>
                                    <option value="">먼저 국가를 선택하세요</option>
                                </select>
                                <input type="hidden" id="bank_code" name="bank_code">
                                <input type="hidden" id="swift_code" name="swift_code">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="account_number" class="form-label">계좌번호 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="account_number" name="account_number"
                                       placeholder="계좌번호를 입력하세요" required>
                            </div>
                            <div class="col-md-6">
                                <label for="account_holder" class="form-label">예금주명 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="account_holder" name="account_holder"
                                       placeholder="예금주명을 입력하세요" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_default" name="is_default">
                                <label class="form-check-label" for="is_default">
                                    기본 계좌로 설정
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('home.emoney.bank.index') }}" class="btn btn-secondary">
                                취소
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> 계좌 등록
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// 은행 목록 데이터 (banklist.json과 동일)
const bankList = {
    "KR": [
        {"name": "KB국민은행", "code": "KB", "swift_code": "CZNBKRSE", "website": "https://www.kbstar.com", "phone": "1588-9999"},
        {"name": "신한은행", "code": "SH", "swift_code": "SHBKKRSE", "website": "https://www.shinhan.com", "phone": "1599-8000"},
        {"name": "우리은행", "code": "WR", "swift_code": "HVBKKRSE", "website": "https://www.wooribank.com", "phone": "1599-5000"},
        {"name": "하나은행", "code": "HN", "swift_code": "HNBNKRSE", "website": "https://www.kebhana.com", "phone": "1599-1111"},
        {"name": "기업은행", "code": "IBK", "swift_code": "IBKOKRSE", "website": "https://www.ibk.co.kr", "phone": "1566-2566"},
        {"name": "NH농협은행", "code": "NH", "swift_code": "NACFKRSE", "website": "https://banking.nonghyup.com", "phone": "1588-2100"},
        {"name": "수협은행", "code": "SUHYUP", "swift_code": "SHFCKRSE", "website": "https://www.suhyup-bank.com", "phone": "1588-1515"},
        {"name": "SC제일은행", "code": "SC", "swift_code": "SCBLKRSE", "website": "https://www.scfirstbank.com", "phone": "1588-1599"},
        {"name": "시티은행", "code": "CITI", "swift_code": "CITIKRSE", "website": "https://www.citibank.co.kr", "phone": "1588-2588"},
        {"name": "경남은행", "code": "KNB", "swift_code": "KYNBKRSE", "website": "https://www.knbank.co.kr", "phone": "1588-0505"},
        {"name": "광주은행", "code": "GJB", "swift_code": "GJBKKRSE", "website": "https://www.kjbank.com", "phone": "1588-3388"},
        {"name": "대구은행", "code": "DGB", "swift_code": "DAEBKRSE", "website": "https://www.dgb.co.kr", "phone": "1566-3737"},
        {"name": "부산은행", "code": "BNK", "swift_code": "PSBKKRSE", "website": "https://www.busanbank.co.kr", "phone": "1588-6200"},
        {"name": "전북은행", "code": "JBB", "swift_code": "JBVLKRSE", "website": "https://www.jbbank.co.kr", "phone": "1588-7000"},
        {"name": "제주은행", "code": "JJB", "swift_code": "JEJUKRSE", "website": "https://www.e-jejubank.com", "phone": "1588-0079"},
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
    ],
    "CN": [
        {"name": "Bank of China", "code": "BOC", "swift_code": "BKCHCNBJ", "website": "https://www.boc.cn", "phone": "+86-95566"},
        {"name": "Industrial and Commercial Bank of China", "code": "ICBC", "swift_code": "ICBKCNBJ", "website": "https://www.icbc.com.cn", "phone": "+86-95588"},
        {"name": "China Construction Bank", "code": "CCB", "swift_code": "PCBCCNBJ", "website": "https://www.ccb.com", "phone": "+86-95533"}
    ],
    "GB": [
        {"name": "HSBC", "code": "HSBC", "swift_code": "HBUKGB4B", "website": "https://www.hsbc.com", "phone": "+44-345-740-4404"},
        {"name": "Standard Chartered", "code": "SCB", "swift_code": "SCBLGB2L", "website": "https://www.sc.com", "phone": "+44-345-600-6161"}
    ],
    "DE": [
        {"name": "Deutsche Bank", "code": "DB", "swift_code": "DEUTDEFF", "website": "https://www.deutsche-bank.de", "phone": "+49-69-910-00"},
        {"name": "Commerzbank", "code": "CBK", "swift_code": "COBADEFF", "website": "https://www.commerzbank.de", "phone": "+49-69-136-20"}
    ],
    "FR": [
        {"name": "BNP Paribas", "code": "BNP", "swift_code": "BNPAFRPP", "website": "https://www.bnpparibas.com", "phone": "+33-1-40-14-45-46"},
        {"name": "Société Générale", "code": "SG", "swift_code": "SOGEFRPP", "website": "https://www.societegenerale.com", "phone": "+33-1-42-14-20-00"}
    ],
    "CA": [
        {"name": "Royal Bank of Canada", "code": "RBC", "swift_code": "ROYCCAT2", "website": "https://www.rbc.com", "phone": "+1-800-769-2511"},
        {"name": "Toronto-Dominion Bank", "code": "TD", "swift_code": "TDOMCATT", "website": "https://www.td.com", "phone": "+1-866-567-8888"}
    ],
    "AU": [
        {"name": "Commonwealth Bank of Australia", "code": "CBA", "swift_code": "CTBAAU2S", "website": "https://www.commbank.com.au", "phone": "+61-13-22-21"},
        {"name": "Australia and New Zealand Banking Group", "code": "ANZ", "swift_code": "ANZBAU3M", "website": "https://www.anz.com.au", "phone": "+61-13-13-14"}
    ],
    "SG": [
        {"name": "DBS Bank", "code": "DBS", "swift_code": "DBSSSGSG", "website": "https://www.dbs.com.sg", "phone": "+65-6327-2265"},
        {"name": "Oversea-Chinese Banking Corporation", "code": "OCBC", "swift_code": "OCBCSGSG", "website": "https://www.ocbc.com", "phone": "+65-6363-3333"}
    ],
    "HK": [
        {"name": "Hong Kong and Shanghai Banking Corporation", "code": "HSBC_HK", "swift_code": "HSBCHKHH", "website": "https://www.hsbc.com.hk", "phone": "+852-2233-3000"},
        {"name": "Standard Chartered Hong Kong", "code": "SCB_HK", "swift_code": "SCBLHKHH", "website": "https://www.sc.com/hk", "phone": "+852-2886-8868"}
    ],
    "TH": [
        {"name": "Bangkok Bank", "code": "BBL", "swift_code": "BKKBTHBK", "website": "https://www.bangkokbank.com", "phone": "+66-2-626-4000"},
        {"name": "Kasikornbank", "code": "KBANK", "swift_code": "KASITHBK", "website": "https://www.kasikornbank.com", "phone": "+66-2-888-8888"}
    ],
    "VN": [
        {"name": "Vietcombank", "code": "VCB", "swift_code": "BFTVVNVX", "website": "https://www.vietcombank.com.vn", "phone": "+84-24-3936-1600"},
        {"name": "BIDV", "code": "BIDV", "swift_code": "BIDVVNVX", "website": "https://www.bidv.com.vn", "phone": "+84-24-3974-3979"}
    ],
    "ID": [
        {"name": "Bank Central Asia", "code": "BCA", "swift_code": "CENAIDJA", "website": "https://www.bca.co.id", "phone": "+62-21-2358-8000"},
        {"name": "Bank Mandiri", "code": "MANDIRI", "swift_code": "BMRIIDJA", "website": "https://www.bankmandiri.co.id", "phone": "+62-21-5299-7777"}
    ],
    "MY": [
        {"name": "Malayan Banking Berhad", "code": "MAYBANK", "swift_code": "MBBEMYKL", "website": "https://www.maybank.com.my", "phone": "+60-3-2070-8833"},
        {"name": "CIMB Bank", "code": "CIMB", "swift_code": "CIBBMYKL", "website": "https://www.cimb.com.my", "phone": "+60-3-6204-7788"}
    ],
    "PH": [
        {"name": "Banco de Oro", "code": "BDO", "swift_code": "BNORPHMM", "website": "https://www.bdo.com.ph", "phone": "+63-2-8840-7000"},
        {"name": "Metropolitan Bank & Trust Company", "code": "METROBANK", "swift_code": "MBTCPHMM", "website": "https://www.metrobank.com.ph", "phone": "+63-2-8700-7777"}
    ]
};

document.addEventListener('DOMContentLoaded', function() {
    const countrySelect = document.getElementById('country');
    const bankSelect = document.getElementById('bank_name');
    const bankCodeInput = document.getElementById('bank_code');
    const swiftCodeInput = document.getElementById('swift_code');

    // 국가 선택 시 은행 목록 업데이트
    countrySelect.addEventListener('change', function() {
        const selectedCountry = this.value;

        // 은행 선택 초기화
        bankSelect.innerHTML = '<option value="">은행을 선택하세요</option>';
        bankCodeInput.value = '';
        swiftCodeInput.value = '';

        if (selectedCountry && bankList[selectedCountry]) {
            // 선택된 국가의 은행 목록 로드
            bankSelect.disabled = false;

            bankList[selectedCountry].forEach(bank => {
                const option = document.createElement('option');
                option.value = bank.name;
                option.textContent = bank.name;
                option.dataset.code = bank.code;
                option.dataset.swiftCode = bank.swift_code || '';
                bankSelect.appendChild(option);
            });
        } else {
            bankSelect.disabled = true;
            bankSelect.innerHTML = '<option value="">먼저 국가를 선택하세요</option>';
        }
    });

    // 은행 선택 시 추가 정보 설정
    bankSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];

        if (selectedOption.dataset.code) {
            bankCodeInput.value = selectedOption.dataset.code;
            swiftCodeInput.value = selectedOption.dataset.swiftCode || '';
        } else {
            bankCodeInput.value = '';
            swiftCodeInput.value = '';
        }
    });

    // 계좌번호 포맷팅 (한국 은행의 경우)
    const accountNumberInput = document.getElementById('account_number');
    accountNumberInput.addEventListener('input', function() {
        const country = countrySelect.value;
        let value = this.value.replace(/[^0-9]/g, ''); // 숫자만 허용

        if (country === 'KR') {
            // 한국 계좌번호 형식 (예: 123-45-678901)
            if (value.length > 3 && value.length <= 5) {
                value = value.slice(0, 3) + '-' + value.slice(3);
            } else if (value.length > 5) {
                value = value.slice(0, 3) + '-' + value.slice(3, 5) + '-' + value.slice(5, 11);
            }
        }

        this.value = value;
    });

    // 폼 제출 시 유효성 검사
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const country = countrySelect.value;
        const bankName = bankSelect.value;
        const accountNumber = accountNumberInput.value.trim();
        const accountHolder = document.getElementById('account_holder').value.trim();

        if (!country) {
            e.preventDefault();
            alert('국가를 선택해주세요.');
            countrySelect.focus();
            return;
        }

        if (!bankName) {
            e.preventDefault();
            alert('은행을 선택해주세요.');
            bankSelect.focus();
            return;
        }

        if (!accountNumber) {
            e.preventDefault();
            alert('계좌번호를 입력해주세요.');
            accountNumberInput.focus();
            return;
        }

        if (!accountHolder) {
            e.preventDefault();
            alert('예금주명을 입력해주세요.');
            document.getElementById('account_holder').focus();
            return;
        }

        // 한국 계좌번호 유효성 검사 (간단한 형식 체크)
        if (country === 'KR') {
            const koreanAccountPattern = /^\d{3}-\d{2}-\d{6}$/;
            if (!koreanAccountPattern.test(accountNumber)) {
                e.preventDefault();
                alert('올바른 계좌번호 형식을 입력해주세요. (예: 123-45-678901)');
                accountNumberInput.focus();
                return;
            }
        }
    });
});
</script>
@endsection