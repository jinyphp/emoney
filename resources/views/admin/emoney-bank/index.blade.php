@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '은행 계좌 관리')

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
                    <h2 class="mb-0">은행 계좌 관리</h2>
                    <p class="text-muted mb-0">사용자 등록 은행 계좌 정보 관리</p>
                </div>
                <div>
                    <a href="{{ route('admin.auth.emoney.bank.create') }}" class="btn btn-primary">
                        <i class="fe fe-plus me-2"></i>계좌 추가
                    </a>
                </div>
            </div>

            <!-- 통계 카드 -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fe fe-home text-primary fs-3"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-0">{{ number_format($statistics['total_accounts']) }}</h5>
                                    <p class="text-muted mb-0">전체 계좌</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fe fe-check-circle text-success fs-3"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-0">{{ number_format($statistics['active_accounts']) }}</h5>
                                    <p class="text-muted mb-0">활성 계좌</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fe fe-star text-warning fs-3"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-0">{{ number_format($statistics['default_accounts']) }}</h5>
                                    <p class="text-muted mb-0">기본 계좌</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="flex-shrink-0">
                                <h6 class="mb-2">은행별 통계</h6>
                                @foreach($statistics['banks']->take(3) as $bank)
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>{{ $bank->bank }}</small>
                                        <small>{{ $bank->count }}개</small>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 검색 및 필터 -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.auth.emoney.bank.index') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">검색</label>
                                <input type="text" name="search" class="form-control" placeholder="이메일, 은행명, 계좌번호, 예금주 검색" value="{{ $request->search }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">상태</label>
                                <select name="status" class="form-select">
                                    <option value="">전체</option>
                                    <option value="active" {{ $request->status == 'active' ? 'selected' : '' }}>활성</option>
                                    <option value="inactive" {{ $request->status == 'inactive' ? 'selected' : '' }}>비활성</option>
                                    <option value="pending" {{ $request->status == 'pending' ? 'selected' : '' }}>대기</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">은행</label>
                                <select name="bank" class="form-select">
                                    <option value="">전체</option>
                                    @foreach($statistics['banks'] as $bank)
                                        <option value="{{ $bank->bank }}" {{ $request->bank == $bank->bank ? 'selected' : '' }}>
                                            {{ $bank->bank }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">활성화</label>
                                <select name="enable" class="form-select">
                                    <option value="">전체</option>
                                    <option value="1" {{ $request->enable == '1' ? 'selected' : '' }}>활성화</option>
                                    <option value="0" {{ $request->enable == '0' ? 'selected' : '' }}>비활성화</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">기본</label>
                                <select name="default" class="form-select">
                                    <option value="">전체</option>
                                    <option value="1" {{ $request->default == '1' ? 'selected' : '' }}>기본</option>
                                    <option value="0" {{ $request->default == '0' ? 'selected' : '' }}>일반</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">검색</button>
                                    <a href="{{ route('admin.auth.emoney.bank.index') }}" class="btn btn-outline-secondary">초기화</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 은행 계좌 목록 -->
            <div class="card">
                <div class="card-body">
                    @if(isset($banks) && $banks->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>사용자</th>
                                        <th>은행정보</th>
                                        <th>계좌정보</th>
                                        <th>통화</th>
                                        <th>상태</th>
                                        <th>기본계좌</th>
                                        <th>등록일</th>
                                        <th>액션</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($banks as $bank)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $bank->user_id }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $bank->email }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $bank->bank }}</strong>
                                                    @if($bank->swift)
                                                        <br>
                                                        <small class="text-muted">SWIFT: {{ $bank->swift }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <code>{{ $bank->account }}</code>
                                                    <br>
                                                    <small>{{ $bank->owner }}</small>
                                                </div>
                                            </td>
                                            <td>{{ $bank->currency ?? '-' }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    @if($bank->enable)
                                                        <span class="badge bg-success">활성화</span>
                                                    @else
                                                        <span class="badge bg-secondary">비활성화</span>
                                                    @endif

                                                    @if($bank->status == 'active')
                                                        <span class="badge bg-success">승인</span>
                                                    @elseif($bank->status == 'pending')
                                                        <span class="badge bg-warning">대기</span>
                                                    @elseif($bank->status == 'rejected')
                                                        <span class="badge bg-danger">거부</span>
                                                    @else
                                                        <span class="badge bg-light text-dark">{{ $bank->status }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($bank->default)
                                                    <span class="badge bg-warning">
                                                        <i class="fe fe-star"></i> 기본
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $bank->created_at->format('Y-m-d') }}</td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        액션
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="{{ route('admin.auth.emoney.bank.show', $bank->id) }}">
                                                            <i class="fe fe-eye me-2"></i>상세보기
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="{{ route('admin.auth.emoney.bank.edit', $bank->id) }}">
                                                            <i class="fe fe-edit me-2"></i>수정
                                                        </a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteBank({{ $bank->id }}, '{{ $bank->bank }}', '{{ $bank->account }}')">
                                                            <i class="fe fe-trash me-2"></i>삭제
                                                        </a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if(method_exists($banks, 'links'))
                            <div class="mt-3">
                                {{ $banks->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fe fe-home text-muted fs-1"></i>
                            <p class="text-muted mt-3 mb-0">등록된 은행 계좌가 없습니다.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
function deleteBank(id, bankName, account) {
    if(confirm(`정말로 이 계좌를 삭제하시겠습니까?\n\n은행: ${bankName}\n계좌: ${account}`)) {
        // CSRF 토큰을 포함한 삭제 폼 생성
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/auth/emoney/bank/${id}`;

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
