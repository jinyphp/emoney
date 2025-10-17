@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '은행 계좌 추가')

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
                    <h2 class="mb-0">은행 계좌 추가</h2>
                    <p class="text-muted mb-0">새로운 사용자 은행 계좌 정보를 등록합니다</p>
                </div>
                <div>
                    <a href="{{ route('admin.auth.emoney.bank.index') }}" class="btn btn-outline-secondary">
                        <i class="fe fe-arrow-left me-2"></i>목록으로
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- 사용자 검색 -->
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">1. 사용자 검색 (샤딩 정보 필요)</h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.auth.emoney.bank.create') }}">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">UUID</label>
                                        <input type="text" name="uuid" class="form-control" placeholder="사용자 UUID" value="{{ $filters['uuid'] }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">샤드 번호</label>
                                        <input type="number" name="shard" class="form-control" placeholder="0~9" min="0" max="9" value="{{ $filters['shard'] }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">이메일</label>
                                        <input type="email" name="email" class="form-control" placeholder="이메일 주소" value="{{ $filters['email'] }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">사용자 ID</label>
                                        <input type="number" name="user_id" class="form-control" placeholder="사용자 ID" value="{{ $filters['user_id'] }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">&nbsp;</label>
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">검색</button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            @if(count($users) > 0)
                                <hr>
                                <h6>검색 결과</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>UUID</th>
                                                <th>샤드</th>
                                                <th>테이블</th>
                                                <th>이메일</th>
                                                <th>이름</th>
                                                <th>선택</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($users as $user)
                                                <tr>
                                                    <td>{{ $user->id }}</td>
                                                    <td><code>{{ $user->uuid }}</code></td>
                                                    <td>
                                                        @if(isset($user->shard_id) && $user->shard_id !== null)
                                                            <span class="badge bg-primary">{{ $user->shard_id }}</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td><span class="badge bg-info">{{ $user->table_name }}</span></td>
                                                    <td>{{ $user->email }}</td>
                                                    <td>{{ $user->name ?? '-' }}</td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-success"
                                                                onclick="selectUser({{ $user->id }}, '{{ $user->email }}', '{{ $user->uuid ?? '' }}', {{ $user->shard_id ?? 0 }})">
                                                            선택
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @elseif(request()->hasAny(['uuid', 'shard', 'email', 'user_id']))
                                <div class="alert alert-warning mt-3 mb-0">
                                    검색 조건에 맞는 사용자를 찾을 수 없습니다.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- 계좌 정보 입력 폼 -->
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">2. 은행 계좌 정보 입력</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.auth.emoney.bank.store') }}">
                                @csrf

                                <!-- 선택된 사용자 정보 -->
                                <div class="row mb-4" id="selected-user" style="display: none;">
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <strong>선택된 사용자:</strong>
                                            <span id="selected-user-info"></span>
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" id="user_id" name="user_id" value="{{ old('user_id') }}">
                                <input type="hidden" id="email" name="email" value="{{ old('email') }}">

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">은행명 <span class="text-danger">*</span></label>
                                        <select name="bank" class="form-select" required>
                                            <option value="">은행을 선택하세요</option>
                                            @foreach($banks as $bank)
                                                <option value="{{ $bank }}" {{ old('bank') == $bank ? 'selected' : '' }}>{{ $bank }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">계좌번호 <span class="text-danger">*</span></label>
                                        <input type="text" name="account" class="form-control" placeholder="계좌번호 입력" value="{{ old('account') }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">예금주 <span class="text-danger">*</span></label>
                                        <input type="text" name="owner" class="form-control" placeholder="예금주명 입력" value="{{ old('owner') }}" required>
                                    </div>
                                </div>

                                <div class="row g-3 mt-3">
                                    <div class="col-md-3">
                                        <label class="form-label">계좌 유형</label>
                                        <select name="type" class="form-select">
                                            <option value="">선택하세요</option>
                                            <option value="savings" {{ old('type') == 'savings' ? 'selected' : '' }}>예금</option>
                                            <option value="checking" {{ old('type') == 'checking' ? 'selected' : '' }}>당좌</option>
                                            <option value="business" {{ old('type') == 'business' ? 'selected' : '' }}>사업자</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">통화 <span class="text-danger">*</span></label>
                                        <select name="currency" class="form-select" required>
                                            @foreach($currencies as $code => $name)
                                                <option value="{{ $code }}" {{ old('currency', 'KRW') == $code ? 'selected' : '' }}>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">SWIFT 코드</label>
                                        <input type="text" name="swift" class="form-control" placeholder="SWIFT 코드" value="{{ old('swift') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">상태 <span class="text-danger">*</span></label>
                                        <select name="status" class="form-select" required>
                                            <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>승인대기</option>
                                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>활성</option>
                                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>비활성</option>
                                            <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>거부</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row g-3 mt-3">
                                    <div class="col-md-12">
                                        <label class="form-label">메모</label>
                                        <textarea name="description" class="form-control" rows="3" placeholder="계좌에 대한 메모나 설명을 입력하세요">{{ old('description') }}</textarea>
                                    </div>
                                </div>

                                <div class="row g-3 mt-3">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="checkbox" name="enable" class="form-check-input" id="enable" value="1" {{ old('enable', '1') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="enable">
                                                활성화
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input type="checkbox" name="default" class="form-check-input" id="default" value="1" {{ old('default') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="default">
                                                기본 계좌로 설정
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary" id="submit-btn" disabled>
                                                <i class="fe fe-save me-2"></i>저장
                                            </button>
                                            <a href="{{ route('admin.auth.emoney.bank.index') }}" class="btn btn-outline-secondary">
                                                <i class="fe fe-x me-2"></i>취소
                                            </a>
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
function selectUser(userId, email, uuid, shard) {
    // 폼에 사용자 정보 설정
    document.getElementById('user_id').value = userId;
    document.getElementById('email').value = email;

    // 선택된 사용자 정보 표시
    document.getElementById('selected-user-info').innerHTML =
        `ID: ${userId}, 이메일: ${email}, UUID: ${uuid}, 샤드: ${shard}`;
    document.getElementById('selected-user').style.display = 'block';

    // 저장 버튼 활성화
    document.getElementById('submit-btn').disabled = false;

    // 성공 메시지
    alert('사용자가 선택되었습니다. 계좌 정보를 입력해주세요.');
}

// 페이지 로드 시 기존 선택된 사용자가 있으면 버튼 활성화
document.addEventListener('DOMContentLoaded', function() {
    const userId = document.getElementById('user_id').value;
    if (userId) {
        document.getElementById('submit-btn').disabled = false;
        document.getElementById('selected-user').style.display = 'block';
        const email = document.getElementById('email').value;
        document.getElementById('selected-user-info').innerHTML =
            `ID: ${userId}, 이메일: ${email}`;
    }
});
</script>
@endsection
