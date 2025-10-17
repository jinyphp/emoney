@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '포인트 관리')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">포인트 관리</h2>
                    <p class="text-muted mb-0">사용자 포인트 잔액 및 관리</p>
                </div>
            </div>

            <!-- 통계 카드 -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fe fe-users text-primary fs-3"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-0">{{ number_format($statistics['total_users']) }}</h5>
                                    <p class="text-muted mb-0">전체 사용자</p>
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
                                    <h5 class="mb-0">{{ number_format($statistics['total_balance'], 0) }}</h5>
                                    <p class="text-muted mb-0">총 잔액</p>
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
                                    <i class="fe fe-trending-up text-success fs-3"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-0">{{ number_format($statistics['total_earned'], 0) }}</h5>
                                    <p class="text-muted mb-0">총 적립</p>
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
                                    <i class="fe fe-trending-down text-danger fs-3"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-0">{{ number_format($statistics['total_used'], 0) }}</h5>
                                    <p class="text-muted mb-0">총 사용</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 추가 통계 카드 -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="mb-1">평균 보유량</h6>
                            <h4 class="text-info">{{ number_format($statistics['avg_balance'], 0) }}P</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="mb-1">보유 사용자</h6>
                            <h4 class="text-success">{{ number_format($statistics['users_with_balance']) }}명</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="mb-1">만료 포인트</h6>
                            <h4 class="text-warning">{{ number_format($statistics['total_expired'], 0) }}P</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="mb-2">상위 보유자</h6>
                            @foreach($statistics['top_holders']->take(3) as $holder)
                                <div class="d-flex justify-content-between mb-1">
                                    <small>{{ $holder->user->name ?? 'N/A' }}</small>
                                    <small>{{ number_format($holder->balance, 0) }}P</small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- 검색 및 필터 -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.auth.point.index') }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">검색</label>
                                <input type="text" name="search" class="form-control" placeholder="이메일, 이름, 사용자ID 검색" value="{{ $request->search }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">최소 잔액</label>
                                <input type="number" name="balance_min" class="form-control" placeholder="0" value="{{ $request->balance_min }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">최대 잔액</label>
                                <input type="number" name="balance_max" class="form-control" placeholder="무제한" value="{{ $request->balance_max }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">정렬</label>
                                <select name="sort_by" class="form-select">
                                    <option value="balance" {{ $request->sort_by == 'balance' ? 'selected' : '' }}>잔액순</option>
                                    <option value="total_earned" {{ $request->sort_by == 'total_earned' ? 'selected' : '' }}>적립순</option>
                                    <option value="total_used" {{ $request->sort_by == 'total_used' ? 'selected' : '' }}>사용순</option>
                                    <option value="created_at" {{ $request->sort_by == 'created_at' ? 'selected' : '' }}>가입순</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">검색</button>
                                    <a href="{{ route('admin.auth.point.index') }}" class="btn btn-outline-secondary">초기화</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 포인트 목록 -->
            <div class="card">
                <div class="card-body">
                    @if(isset($points) && $points->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>사용자</th>
                                        <th>잔액</th>
                                        <th>총 적립</th>
                                        <th>총 사용</th>
                                        <th>총 만료</th>
                                        <th>가입일</th>
                                        <th>액션</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($points as $point)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $point->user->name ?? 'N/A' }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $point->user->email ?? 'N/A' }}</small>
                                                    <br>
                                                    <small class="text-muted">ID: {{ $point->user_id }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary fs-6">
                                                    {{ number_format($point->balance, 0) }}P
                                                </span>
                                            </td>
                                            <td class="text-success">
                                                <strong>+{{ number_format($point->total_earned, 0) }}</strong>
                                            </td>
                                            <td class="text-danger">
                                                <strong>-{{ number_format($point->total_used, 0) }}</strong>
                                            </td>
                                            <td class="text-warning">
                                                <strong>-{{ number_format($point->total_expired, 0) }}</strong>
                                            </td>
                                            <td>{{ $point->created_at->format('Y-m-d') }}</td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        액션
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#" onclick="viewDetails({{ $point->user_id }})">
                                                            <i class="fe fe-eye me-2"></i>상세보기
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="adjustPoints({{ $point->user_id }})">
                                                            <i class="fe fe-edit-2 me-2"></i>포인트 조정
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="viewHistory({{ $point->user_id }})">
                                                            <i class="fe fe-list me-2"></i>거래내역
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="viewExpiry({{ $point->user_id }})">
                                                            <i class="fe fe-clock me-2"></i>만료예정
                                                        </a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if(method_exists($points, 'links'))
                            <div class="mt-3">
                                {{ $points->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fe fe-star text-muted fs-1"></i>
                            <p class="text-muted mt-3 mb-0">포인트 계정이 없습니다.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
function viewDetails(userId) {
    alert('포인트 상세보기 기능 준비중입니다.');
}

function adjustPoints(userId) {
    const amount = prompt('조정할 포인트를 입력하세요 (음수는 차감):');
    if (amount && !isNaN(amount)) {
        const reason = prompt('조정 사유를 입력하세요:');
        if (reason) {
            alert('포인트 조정 기능 준비중입니다.');
        }
    }
}

function viewHistory(userId) {
    alert('거래내역 보기 기능 준비중입니다.');
}

function viewExpiry(userId) {
    alert('만료예정 포인트 보기 기능 준비중입니다.');
}
</script>
@endsection
