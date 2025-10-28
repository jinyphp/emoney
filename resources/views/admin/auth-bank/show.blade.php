@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '은행 상세보기')

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
                                <i class="fe fe-eye me-2"></i>
                                은행 상세보기
                            </h1>
                            <p class="page-header-subtitle">{{ $bank->name }} 정보</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.auth.bank.index') }}" class="btn btn-outline-secondary me-2">
                                <i class="fe fe-arrow-left me-2"></i>목록으로
                            </a>
                            <a href="{{ route('admin.auth.bank.edit', $bank->id) }}" class="btn btn-primary">
                                <i class="fe fe-edit me-2"></i>수정
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

    <div class="row mt-4">
        <div class="col-lg-8">
            <!-- 기본 정보 섹션 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fe fe-info me-2"></i>기본 정보
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="d-flex align-items-center mb-4">
                                @if($bank->logo_url)
                                    <img src="{{ $bank->logo_url }}" alt="{{ $bank->name }}" class="rounded me-3" style="width: 48px; height: 48px;">
                                @else
                                    <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                        <i class="fe fe-credit-card text-muted"></i>
                                    </div>
                                @endif
                                <div>
                                    <h5 class="mb-1">{{ $bank->name }}</h5>
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
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fe fe-hash me-2 text-muted"></i>
                                    <strong>은행 코드</strong>
                                </div>
                                @if($bank->code)
                                    <code class="fs-6">{{ $bank->code }}</code>
                                @else
                                    <span class="text-muted">설정되지 않음</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fe fe-globe me-2 text-muted"></i>
                                    <strong>SWIFT 코드</strong>
                                </div>
                                @if($bank->swift_code)
                                    <code class="fs-6">{{ $bank->swift_code }}</code>
                                @else
                                    <span class="text-muted">설정되지 않음</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fe fe-sort-asc me-2 text-muted"></i>
                                    <strong>정렬 순서</strong>
                                </div>
                                <span class="badge bg-light text-dark fs-6">{{ $bank->sort_order }}</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fe fe-calendar me-2 text-muted"></i>
                                    <strong>등록일</strong>
                                </div>
                                <div class="text-muted">{{ $bank->created_at->format('Y-m-d H:i:s') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 연락처 정보 섹션 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fe fe-phone me-2"></i>연락처 정보
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fe fe-globe me-2 text-muted"></i>
                                    <strong>웹사이트</strong>
                                </div>
                                @if($bank->website)
                                    <a href="{{ $bank->website }}" target="_blank" class="text-decoration-none">
                                        <i class="fe fe-external-link me-1"></i>{{ $bank->website }}
                                    </a>
                                @else
                                    <span class="text-muted">설정되지 않음</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fe fe-phone me-2 text-muted"></i>
                                    <strong>전화번호</strong>
                                </div>
                                @if($bank->phone)
                                    <a href="tel:{{ $bank->phone }}" class="text-decoration-none">
                                        <i class="fe fe-phone me-1"></i>{{ $bank->phone }}
                                    </a>
                                @else
                                    <span class="text-muted">설정되지 않음</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 계좌 정보 섹션 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fe fe-credit-card me-2"></i>계좌 정보
                    </h4>
                </div>
                <div class="card-body">
                    @if($bank->account_number || $bank->account_holder)
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fe fe-hash me-2 text-muted"></i>
                                        <strong>계좌번호</strong>
                                    </div>
                                    @if($bank->account_number)
                                        <code class="fs-6">{{ $bank->account_number }}</code>
                                    @else
                                        <span class="text-muted">설정되지 않음</span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fe fe-user me-2 text-muted"></i>
                                        <strong>예금주</strong>
                                    </div>
                                    @if($bank->account_holder)
                                        <span class="fs-6">{{ $bank->account_holder }}</span>
                                    @else
                                        <span class="text-muted">설정되지 않음</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fe fe-credit-card text-muted mb-3" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-0">계좌 정보가 등록되지 않았습니다.</p>
                            <small class="text-muted">은행 수정에서 계좌번호와 예금주 정보를 추가할 수 있습니다.</small>
                        </div>
                    @endif
                </div>
            </div>

            @if($bank->description)
                <!-- 설명 섹션 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <i class="fe fe-file-text me-2"></i>설명
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="bg-light p-4 rounded">
                            <p class="mb-0">{{ $bank->description }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- 액션 버튼 섹션 -->
            {{-- <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fe fe-settings me-2"></i>관리 액션
                    </h4>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.auth.bank.edit', $bank->id) }}" class="btn btn-primary">
                            <i class="fe fe-edit me-2"></i>수정
                        </a>
                        <button type="button" class="btn btn-danger" onclick="deleteBank({{ $bank->id }}, '{{ $bank->name }}')">
                            <i class="fe fe-trash me-2"></i>삭제
                        </button>
                        <a href="{{ route('admin.auth.bank.index') }}" class="btn btn-outline-secondary">
                            <i class="fe fe-list me-2"></i>목록으로
                        </a>
                    </div>
                </div>
            </div> --}}
        </div>

        <div class="col-lg-4">
            <!-- 같은 국가의 다른 은행들 -->
            @if($relatedBanks->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fe fe-globe me-2"></i>{{ $bank->country_name }} 다른 은행들
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach($relatedBanks as $relatedBank)
                                <a href="{{ route('admin.auth.bank.show', $relatedBank->id) }}"
                                   class="list-group-item list-group-item-action d-flex align-items-center border-0 px-3 py-3">
                                    @if($relatedBank->logo_url)
                                        <img src="{{ $relatedBank->logo_url }}" alt="{{ $relatedBank->name }}"
                                             class="rounded me-3" style="width: 32px; height: 32px;">
                                    @else
                                        <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center"
                                             style="width: 32px; height: 32px;">
                                            <i class="fe fe-credit-card text-muted"></i>
                                        </div>
                                    @endif
                                    <div class="flex-grow-1">
                                        <div class="fw-medium text-dark">{{ $relatedBank->name }}</div>
                                        <div class="d-flex align-items-center gap-2 mt-1">
                                            @if($relatedBank->code)
                                                <small class="text-muted">{{ $relatedBank->code }}</small>
                                            @endif
                                            @if($relatedBank->enable)
                                                <span class="badge bg-success badge-sm">활성</span>
                                            @else
                                                <span class="badge bg-secondary badge-sm">비활성</span>
                                            @endif
                                        </div>
                                    </div>
                                    <i class="fe fe-chevron-right text-muted"></i>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- 빠른 액션 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fe fe-zap me-2"></i>빠른 액션
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.auth.bank.edit', $bank->id) }}" class="btn btn-primary btn-sm">
                            <i class="fe fe-edit me-2"></i>은행 정보 수정
                        </a>
                        <a href="{{ route('admin.auth.bank.create') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fe fe-plus me-2"></i>새 은행 추가
                        </a>
                        <a href="{{ route('admin.auth.bank.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fe fe-list me-2"></i>은행 목록으로
                        </a>
                        <hr class="my-2">
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteBank({{ $bank->id }}, '{{ $bank->name }}')">
                            <i class="fe fe-trash me-2"></i>은행 삭제
                        </button>
                    </div>
                </div>
            </div>

            <!-- 도움말 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fe fe-help-circle me-2"></i>은행 정보 도움말
                    </h5>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="mb-3">
                            <h6 class="text-primary mb-2">
                                <i class="fe fe-info me-1"></i>기본 정보:
                            </h6>
                            <ul class="text-muted list-unstyled">
                                <li class="mb-1">• <strong>은행 코드</strong>: 시스템 내부 식별용</li>
                                <li class="mb-1">• <strong>SWIFT 코드</strong>: 국제 송금 시 사용</li>
                                <li class="mb-1">• <strong>정렬 순서</strong>: 목록 표시 순서</li>
                            </ul>
                        </div>

                        <div class="mb-3">
                            <h6 class="text-success mb-2">
                                <i class="fe fe-credit-card me-1"></i>계좌 정보:
                            </h6>
                            <ul class="text-muted list-unstyled">
                                <li class="mb-1">• <strong>계좌번호</strong>: 실제 계좌 번호</li>
                                <li class="mb-1">• <strong>예금주</strong>: 계좌 소유자 명</li>
                                <li class="mb-1">• 결제 시 표시되는 정보입니다</li>
                            </ul>
                        </div>

                        <div class="mb-0">
                            <h6 class="text-warning mb-2">
                                <i class="fe fe-alert-triangle me-1"></i>주의사항:
                            </h6>
                            <ul class="text-muted list-unstyled">
                                <li class="mb-1">• 계좌 정보는 민감한 정보입니다</li>
                                <li class="mb-1">• 비활성화하면 사용자에게 표시되지 않습니다</li>
                                <li class="mb-1">• 삭제 시 관련 데이터가 모두 제거됩니다</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 시스템 정보 -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fe fe-database me-2"></i>시스템 정보
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="text-muted">ID</span>
                                <code class="small">{{ $bank->id }}</code>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="text-muted">상태</span>
                                @if($bank->enable)
                                    <span class="badge bg-success">활성</span>
                                @else
                                    <span class="badge bg-secondary">비활성</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="text-muted">등록일</span>
                                <div class="text-end">
                                    <div class="small">{{ $bank->created_at->format('Y-m-d H:i') }}</div>
                                    <div class="text-muted small">{{ $bank->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center py-2">
                                <span class="text-muted">최종 수정</span>
                                <div class="text-end">
                                    <div class="small">{{ $bank->updated_at->format('Y-m-d H:i') }}</div>
                                    <div class="text-muted small">{{ $bank->updated_at->diffForHumans() }}</div>
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
@endsection
