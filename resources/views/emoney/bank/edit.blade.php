@extends('jiny-admin::layouts.admin.sidebar')

@section('title', '은행 계좌 수정')

@section('content')
<div class="container-fluid">
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

    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">은행 계좌 수정</h2>
                    <p class="text-muted mb-0">{{ $bank->bank }} - {{ $bank->account }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.auth.emoney.bank.show', $bank->id) }}" class="btn btn-outline-info">
                        <i class="fe fe-eye me-2"></i>상세보기
                    </a>
                    <a href="{{ route('admin.auth.emoney.bank.index') }}" class="btn btn-outline-secondary">
                        <i class="fe fe-arrow-left me-2"></i>목록으로
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- 사용자 정보 (읽기 전용) -->
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">사용자 정보 (변경 불가)</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">사용자 ID</label>
                                    <p class="mb-0">{{ $bank->user_id }}</p>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">이메일</label>
                                    <p class="mb-0">{{ $bank->email }}</p>
                                </div>
                                @if($bank->user)
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">이름</label>
                                    <p class="mb-0">{{ $bank->user->name ?? '-' }}</p>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">UUID/샤드</label>
                                    <p class="mb-0">{{ $bank->user->uuid ?? '-' }} / {{ $bank->user->shard_id ?? '-' }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 계좌 정보 수정 폼 -->
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">은행 계좌 정보 수정</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.auth.emoney.bank.update', $bank->id) }}">
                                @csrf
                                @method('PUT')

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">은행명 <span class="text-danger">*</span></label>
                                        <select name="bank" class="form-select" required>
                                            <option value="">은행을 선택하세요</option>
                                            @foreach($banks as $bankOption)
                                                <option value="{{ $bankOption }}" {{ old('bank', $bank->bank) == $bankOption ? 'selected' : '' }}>{{ $bankOption }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">계좌번호 <span class="text-danger">*</span></label>
                                        <input type="text" name="account" class="form-control" placeholder="계좌번호 입력" value="{{ old('account', $bank->account) }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">예금주 <span class="text-danger">*</span></label>
                                        <input type="text" name="owner" class="form-control" placeholder="예금주명 입력" value="{{ old('owner', $bank->owner) }}" required>
                                    </div>
                                </div>

                                <div class="row g-3 mt-3">
                                    <div class="col-md-3">
                                        <label class="form-label">계좌 유형</label>
                                        <select name="type" class="form-select">
                                            <option value="">선택하세요</option>
                                            <option value="savings" {{ old('type', $bank->type) == 'savings' ? 'selected' : '' }}>예금</option>
                                            <option value="checking" {{ old('type', $bank->type) == 'checking' ? 'selected' : '' }}>당좌</option>
                                            <option value="business" {{ old('type', $bank->type) == 'business' ? 'selected' : '' }}>사업자</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">통화 <span class="text-danger">*</span></label>
                                        <select name="currency" class="form-select" required>
                                            @foreach($currencies as $code => $name)
                                                <option value="{{ $code }}" {{ old('currency', $bank->currency) == $code ? 'selected' : '' }}>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">SWIFT 코드</label>
                                        <input type="text" name="swift" class="form-control" placeholder="SWIFT 코드" value="{{ old('swift', $bank->swift) }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">상태 <span class="text-danger">*</span></label>
                                        <select name="status" class="form-select" required>
                                            @foreach($statuses as $statusKey => $statusLabel)
                                                <option value="{{ $statusKey }}" {{ old('status', $bank->status) == $statusKey ? 'selected' : '' }}>{{ $statusLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row g-3 mt-3">
                                    <div class="col-md-12">
                                        <label class="form-label">메모</label>
                                        <textarea name="description" class="form-control" rows="3" placeholder="계좌에 대한 메모나 설명을 입력하세요">{{ old('description', $bank->description) }}</textarea>
                                    </div>
                                </div>

                                <div class="row g-3 mt-3">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="checkbox" name="enable" class="form-check-input" id="enable" value="1" {{ old('enable', $bank->enable) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="enable">
                                                활성화
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="checkbox" name="default" class="form-check-input" id="default" value="1" {{ old('default', $bank->default) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="default">
                                                기본 계좌로 설정
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- 수정 이력 정보 -->
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="card border-light bg-light">
                                            <div class="card-body py-2">
                                                <div class="row text-sm">
                                                    <div class="col-md-6">
                                                        <small class="text-muted">등록일: {{ $bank->created_at->format('Y-m-d H:i:s') }}</small>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <small class="text-muted">최종 수정일: {{ $bank->updated_at->format('Y-m-d H:i:s') }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fe fe-save me-2"></i>저장
                                            </button>
                                            <a href="{{ route('admin.auth.emoney.bank.show', $bank->id) }}" class="btn btn-outline-secondary">
                                                <i class="fe fe-x me-2"></i>취소
                                            </a>
                                            <button type="button" class="btn btn-danger ms-auto" onclick="deleteBank()">
                                                <i class="fe fe-trash me-2"></i>삭제
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deleteBank() {
    if(confirm('정말로 이 계좌를 삭제하시겠습니까?\n\n은행: {{ $bank->bank }}\n계좌: {{ $bank->account }}')) {
        // CSRF 토큰을 포함한 삭제 폼 생성
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.auth.emoney.bank.destroy", $bank->id) }}';

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