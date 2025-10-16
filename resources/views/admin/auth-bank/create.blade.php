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
                                <label for="name" class="form-label">은행명 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}" required placeholder="은행명을 입력하세요">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="code" class="form-label">은행 코드</label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror"
                                       id="code" name="code" value="{{ old('code') }}" maxlength="10"
                                       placeholder="은행을 식별하는 고유 코드 (선택사항)">
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">시스템 내부에서 사용할 고유 코드</div>
                            </div>

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
                                <label for="swift_code" class="form-label">SWIFT 코드</label>
                                <input type="text" class="form-control @error('swift_code') is-invalid @enderror"
                                       id="swift_code" name="swift_code" value="{{ old('swift_code') }}" maxlength="11"
                                       placeholder="국제 송금을 위한 SWIFT 코드 (선택사항)">
                                @error('swift_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">국제 송금용 식별 코드</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 연락처 정보 섹션 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="card-title mb-0">연락처 정보</h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="website" class="form-label">웹사이트</label>
                                <input type="url" class="form-control @error('website') is-invalid @enderror"
                                       id="website" name="website" value="{{ old('website') }}" placeholder="https://example.com">
                                @error('website')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label">전화번호</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                       id="phone" name="phone" value="{{ old('phone') }}" maxlength="50"
                                       placeholder="고객 서비스 전화번호">
                                @error('phone')
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
                                <i class="fe fe-info me-1"></i>선택 항목:
                            </h6>
                            <ul class="text-muted list-unstyled">
                                <li class="mb-1">• 은행 코드: 시스템 내부에서 사용할 고유 코드</li>
                                <li class="mb-1">• SWIFT 코드: 국제 송금용 식별 코드</li>
                                <li class="mb-1">• 웹사이트: 은행 공식 홈페이지</li>
                                <li class="mb-1">• 전화번호: 고객 서비스 번호</li>
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
@endsection
