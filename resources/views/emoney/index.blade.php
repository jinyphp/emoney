@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '이머니 관리')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">이머니 관리</h2>
                    <p class="text-muted mb-0">사용자 전자지갑 및 잔액 관리</p>
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
                                    <i class="fe fe-user-check text-success fs-3"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-0">{{ number_format($statistics['active_users']) }}</h5>
                                    <p class="text-muted mb-0">활성 사용자</p>
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
                                    <i class="fe fe-dollar-sign text-warning fs-3"></i>
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
                                    <i class="fe fe-star text-info fs-3"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-0">{{ number_format($statistics['total_points']) }}</h5>
                                    <p class="text-muted mb-0">총 포인트</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 검색 및 필터 -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.auth.emoney.index') }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">검색</label>
                                <input type="text" name="search" class="form-control" placeholder="이메일, 이름, 사용자ID 검색" value="{{ $request->search }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">상태</label>
                                <select name="status" class="form-select">
                                    <option value="">전체</option>
                                    <option value="active" {{ $request->status == 'active' ? 'selected' : '' }}>활성</option>
                                    <option value="inactive" {{ $request->status == 'inactive' ? 'selected' : '' }}>비활성</option>
                                    <option value="suspended" {{ $request->status == 'suspended' ? 'selected' : '' }}>정지</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">통화</label>
                                <select name="currency" class="form-select">
                                    <option value="">전체</option>
                                    <option value="KRW" {{ $request->currency == 'KRW' ? 'selected' : '' }}>KRW</option>
                                    <option value="USD" {{ $request->currency == 'USD' ? 'selected' : '' }}>USD</option>
                                    <option value="EUR" {{ $request->currency == 'EUR' ? 'selected' : '' }}>EUR</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">페이지당</label>
                                <select name="per_page" class="form-select">
                                    <option value="20" {{ $request->per_page == '20' ? 'selected' : '' }}>20개</option>
                                    <option value="50" {{ $request->per_page == '50' ? 'selected' : '' }}>50개</option>
                                    <option value="100" {{ $request->per_page == '100' ? 'selected' : '' }}>100개</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">검색</button>
                                    <a href="{{ route('admin.auth.emoney.index') }}" class="btn btn-outline-secondary">초기화</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 이머니 목록 -->
            <div class="card">
                <div class="card-body">
                    @if(isset($emoneys) && $emoneys->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>사용자 ID</th>
                                        <th>이메일</th>
                                        <th>이름</th>
                                        <th>통화</th>
                                        <th>잔액</th>
                                        <th>포인트</th>
                                        <th>상태</th>
                                        <th>생성일</th>
                                        <th>액션</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($emoneys as $emoney)
                                        <tr>
                                            <td>{{ $emoney->user_id }}</td>
                                            <td>{{ $emoney->email }}</td>
                                            <td>{{ $emoney->name }}</td>
                                            <td>{{ $emoney->currency }}</td>
                                            <td class="text-end">
                                                <strong>{{ number_format($emoney->balance, 2) }}</strong>
                                            </td>
                                            <td class="text-end">
                                                <span class="badge bg-info">{{ number_format($emoney->point) }}</span>
                                            </td>
                                            <td>
                                                @if($emoney->status == 'active')
                                                    <span class="badge bg-success">활성</span>
                                                @elseif($emoney->status == 'inactive')
                                                    <span class="badge bg-secondary">비활성</span>
                                                @elseif($emoney->status == 'suspended')
                                                    <span class="badge bg-danger">정지</span>
                                                @else
                                                    <span class="badge bg-light text-dark">{{ $emoney->status }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $emoney->created_at->format('Y-m-d') }}</td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        액션
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#" onclick="viewDetails({{ $emoney->id }})">
                                                            <i class="fe fe-eye me-2"></i>상세보기
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="editBalance({{ $emoney->id }})">
                                                            <i class="fe fe-edit-2 me-2"></i>잔액수정
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="editPoints({{ $emoney->id }})">
                                                            <i class="fe fe-star me-2"></i>포인트수정
                                                        </a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if(method_exists($emoneys, 'links'))
                            <div class="mt-3">
                                {{ $emoneys->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fe fe-credit-card text-muted fs-1"></i>
                            <p class="text-muted mt-3 mb-0">이머니 계정이 없습니다.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
function viewDetails(id) {
    // 상세보기 구현
    alert('상세보기 기능 준비중입니다.');
}

function editBalance(id) {
    // 잔액수정 구현
    alert('잔액수정 기능 준비중입니다.');
}

function editPoints(id) {
    // 포인트수정 구현
    alert('포인트수정 기능 준비중입니다.');
}
</script>
@endsection
