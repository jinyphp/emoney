@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '포인트 계정 생성')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">포인트 계정 생성</h2>
                    <p class="text-muted mb-0">사용자 포인트 계정 생성 또는 포인트 조정</p>
                </div>
                <div>
                    <a href="{{ route('admin.auth.point.index') }}" class="btn btn-outline-primary">
                        <i class="fe fe-arrow-left me-2"></i>목록으로
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">포인트 계정 생성</h5>
                        </div>
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                            @endif

                            <form action="{{ route('admin.auth.point.store') }}" method="POST">
                                @csrf

                                <!-- 사용자 선택 -->
                                <div class="mb-3">
                                    <label for="user_search" class="form-label">사용자 검색</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="user_search" name="user_search"
                                               placeholder="이메일, 이름, ID로 검색" value="{{ $request->user_search }}">
                                        <button type="button" class="btn btn-outline-secondary" onclick="searchUsers()">
                                            <i class="fe fe-search"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="user_id" class="form-label">사용자 선택 <span class="text-danger">*</span></label>
                                    <select class="form-select" id="user_id" name="user_id" required>
                                        <option value="">사용자를 선택하세요</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} ({{ $user->email }}) - ID: {{ $user->id }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- 작업 유형 -->
                                <div class="mb-3">
                                    <label for="action_type" class="form-label">작업 유형 <span class="text-danger">*</span></label>
                                    <select class="form-select" id="action_type" name="action_type" required onchange="toggleAdjustmentFields()">
                                        <option value="">작업을 선택하세요</option>
                                        <option value="create" {{ old('action_type') == 'create' ? 'selected' : '' }}>포인트 계정 생성만</option>
                                        <option value="adjust" {{ old('action_type') == 'adjust' ? 'selected' : '' }}>포인트 계정 생성 + 포인트 조정</option>
                                    </select>
                                </div>

                                <!-- 포인트 조정 필드들 (adjust 선택시에만 표시) -->
                                <div id="adjustment_fields" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="adjustment_type" class="form-label">조정 유형</label>
                                                <select class="form-select" id="adjustment_type" name="adjustment_type">
                                                    <option value="">선택하세요</option>
                                                    <option value="earn" {{ old('adjustment_type') == 'earn' ? 'selected' : '' }}>포인트 적립</option>
                                                    <option value="admin_add" {{ old('adjustment_type') == 'admin_add' ? 'selected' : '' }}>관리자 지급</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="amount" class="form-label">포인트 금액</label>
                                                <input type="number" class="form-control" id="amount" name="amount"
                                                       value="{{ old('amount') }}" min="0" step="0.01" placeholder="0.00">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="reason" class="form-label">조정 사유</label>
                                        <textarea class="form-control" id="reason" name="reason" rows="3"
                                                  placeholder="포인트 조정 사유를 입력하세요">{{ old('reason') }}</textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="expires_at" class="form-label">만료일 (선택사항)</label>
                                        <input type="datetime-local" class="form-control" id="expires_at" name="expires_at"
                                               value="{{ old('expires_at') }}">
                                        <small class="form-text text-muted">포인트 적립시에만 적용됩니다.</small>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-outline-secondary me-2" onclick="history.back()">취소</button>
                                    <button type="submit" class="btn btn-primary">생성</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">도움말</h6>
                        </div>
                        <div class="card-body">
                            <h6>작업 유형 설명</h6>
                            <ul class="list-unstyled">
                                <li><strong>포인트 계정 생성만:</strong> 사용자에게 포인트 계정만 생성하고 초기 잔액은 0입니다.</li>
                                <li><strong>포인트 계정 생성 + 포인트 조정:</strong> 계정 생성과 동시에 포인트를 지급합니다.</li>
                            </ul>

                            <hr>

                            <h6>조정 유형 설명</h6>
                            <ul class="list-unstyled">
                                <li><strong>포인트 적립:</strong> 일반적인 포인트 적립 (만료일 설정 가능)</li>
                                <li><strong>관리자 지급:</strong> 관리자 권한으로 직접 지급하는 포인트</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleAdjustmentFields() {
    const actionType = document.getElementById('action_type').value;
    const adjustmentFields = document.getElementById('adjustment_fields');

    if (actionType === 'adjust') {
        adjustmentFields.style.display = 'block';
        document.getElementById('adjustment_type').required = true;
        document.getElementById('amount').required = true;
    } else {
        adjustmentFields.style.display = 'none';
        document.getElementById('adjustment_type').required = false;
        document.getElementById('amount').required = false;
    }
}

function searchUsers() {
    const searchTerm = document.getElementById('user_search').value;
    if (searchTerm) {
        window.location.href = `{{ route('admin.auth.point.create') }}?user_search=${encodeURIComponent(searchTerm)}`;
    }
}

// 페이지 로드 시 초기 상태 설정
document.addEventListener('DOMContentLoaded', function() {
    toggleAdjustmentFields();
});
</script>
@endsection