@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '은행 계좌 상세보기')

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

    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">은행 계좌 상세보기</h2>
                    <p class="text-muted mb-0">{{ $bank->bank }} - {{ $bank->account }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.auth.emoney.bank.edit', $bank->id) }}" class="btn btn-warning">
                        <i class="fe fe-edit me-2"></i>수정
                    </a>
                    <button type="button" class="btn btn-danger" onclick="deleteBank()">
                        <i class="fe fe-trash me-2"></i>삭제
                    </button>
                    <a href="{{ route('admin.auth.emoney.bank.index') }}" class="btn btn-outline-secondary">
                        <i class="fe fe-arrow-left me-2"></i>목록으로
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- 기본 정보 -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">계좌 정보</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">사용자 ID</label>
                                    <p class="mb-0">{{ $bank->user_id }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">이메일</label>
                                    <p class="mb-0">{{ $bank->email }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">은행명</label>
                                    <p class="mb-0">{{ $bank->bank }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">계좌번호</label>
                                    <p class="mb-0"><code>{{ $bank->account }}</code></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">예금주</label>
                                    <p class="mb-0">{{ $bank->owner }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">계좌 유형</label>
                                    <p class="mb-0">{{ $bank->type ?: '-' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">통화</label>
                                    <p class="mb-0">{{ $bank->currency }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">SWIFT 코드</label>
                                    <p class="mb-0">{{ $bank->swift ?: '-' }}</p>
                                </div>
                                @if($bank->description)
                                <div class="col-12">
                                    <label class="form-label fw-bold">메모</label>
                                    <p class="mb-0">{{ $bank->description }}</p>
                                </div>
                                @endif
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">등록일</label>
                                    <p class="mb-0">{{ $bank->created_at->format('Y-m-d H:i:s') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">수정일</label>
                                    <p class="mb-0">{{ $bank->updated_at->format('Y-m-d H:i:s') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 상태 정보 -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">상태 정보</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">활성화 상태</label>
                                <div>
                                    @if($bank->enable)
                                        <span class="badge bg-success fs-6">활성화</span>
                                    @else
                                        <span class="badge bg-secondary fs-6">비활성화</span>
                                    @endif
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">승인 상태</label>
                                <div>
                                    @if($bank->status == 'active')
                                        <span class="badge bg-success fs-6">승인됨</span>
                                    @elseif($bank->status == 'pending')
                                        <span class="badge bg-warning fs-6">승인대기</span>
                                    @elseif($bank->status == 'rejected')
                                        <span class="badge bg-danger fs-6">거부됨</span>
                                    @else
                                        <span class="badge bg-light text-dark fs-6">{{ $bank->status }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">기본 계좌</label>
                                <div>
                                    @if($bank->default)
                                        <span class="badge bg-warning fs-6">
                                            <i class="fe fe-star"></i> 기본 계좌
                                        </span>
                                    @else
                                        <span class="text-muted">일반 계좌</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 사용자 정보 -->
                    @if($bank->user)
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0">사용자 정보</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <label class="form-label fw-bold">이름</label>
                                <p class="mb-1">{{ $bank->user->name ?: '-' }}</p>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold">UUID</label>
                                <p class="mb-1"><code>{{ $bank->user->uuid ?? '-' }}</code></p>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold">샤드</label>
                                <p class="mb-1">{{ $bank->user->shard_id ?? '-' }}</p>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold">가입일</label>
                                <p class="mb-0">{{ $bank->user->created_at ? $bank->user->created_at->format('Y-m-d') : '-' }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- 같은 사용자의 다른 계좌들 -->
            @if($otherBanks->count() > 0)
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">같은 사용자의 다른 계좌 ({{ $otherBanks->count() }}개)</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>은행</th>
                                            <th>계좌번호</th>
                                            <th>예금주</th>
                                            <th>상태</th>
                                            <th>기본</th>
                                            <th>등록일</th>
                                            <th>액션</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($otherBanks as $otherBank)
                                            <tr>
                                                <td>{{ $otherBank->bank }}</td>
                                                <td><code>{{ $otherBank->account }}</code></td>
                                                <td>{{ $otherBank->owner }}</td>
                                                <td>
                                                    @if($otherBank->enable)
                                                        <span class="badge bg-success">활성</span>
                                                    @else
                                                        <span class="badge bg-secondary">비활성</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($otherBank->default)
                                                        <span class="badge bg-warning">기본</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>{{ $otherBank->created_at->format('Y-m-d') }}</td>
                                                <td>
                                                    <a href="{{ route('admin.auth.emoney.bank.show', $otherBank->id) }}" class="btn btn-sm btn-outline-primary">
                                                        보기
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- 활동 로그 (향후 구현) -->
            {{-- @if(count($activityLogs) > 0)
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">활동 로그</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>일시</th>
                                            <th>활동</th>
                                            <th>상세</th>
                                            <th>관리자</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($activityLogs as $log)
                                            <tr>
                                                <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                                                <td>{{ $log->action }}</td>
                                                <td>{{ $log->description }}</td>
                                                <td>{{ $log->admin_name ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif --}}
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
