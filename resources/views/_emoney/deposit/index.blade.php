@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '입금 관리')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">입금 관리</h2>
                    <p class="text-muted mb-0">사용자 입금 내역 및 승인 관리</p>
                </div>
            </div>

            <!-- 통계 카드 -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fe fe-arrow-down-circle text-primary fs-3"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-0">{{ number_format($statistics['total_deposits']) }}</h5>
                                    <p class="text-muted mb-0 small">전체 입금</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fe fe-clock text-warning fs-3"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-0">{{ number_format($statistics['pending_deposits']) }}</h5>
                                    <p class="text-muted mb-0 small">승인대기</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fe fe-check-circle text-success fs-3"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-0">{{ number_format($statistics['approved_deposits']) }}</h5>
                                    <p class="text-muted mb-0 small">승인완료</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center">
                                <h5 class="mb-0">{{ number_format($statistics['total_amount'], 0) }}</h5>
                                <p class="text-muted mb-0 small">총 입금액</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center">
                                <h5 class="mb-0">{{ number_format($statistics['today_deposits']) }}</h5>
                                <p class="text-muted mb-0 small">오늘 입금</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center">
                                <h5 class="mb-0">{{ number_format($statistics['today_amount'], 0) }}</h5>
                                <p class="text-muted mb-0 small">오늘 금액</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 검색 및 필터 -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.auth.emoney.deposits.index') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">검색</label>
                                <input type="text" name="search" class="form-control" placeholder="이메일, 사용자ID, 은행명, 계좌번호 검색" value="{{ $request->search }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">상태</label>
                                <select name="status" class="form-select">
                                    <option value="">전체</option>
                                    <option value="pending" {{ $request->status == 'pending' ? 'selected' : '' }}>대기</option>
                                    <option value="approved" {{ $request->status == 'approved' ? 'selected' : '' }}>승인</option>
                                    <option value="rejected" {{ $request->status == 'rejected' ? 'selected' : '' }}>거부</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">확인상태</label>
                                <select name="checked" class="form-select">
                                    <option value="">전체</option>
                                    <option value="1" {{ $request->checked == '1' ? 'selected' : '' }}>확인됨</option>
                                    <option value="0" {{ $request->checked == '0' ? 'selected' : '' }}>미확인</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">시작일</label>
                                <input type="date" name="date_from" class="form-control" value="{{ $request->date_from }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">종료일</label>
                                <input type="date" name="date_to" class="form-control" value="{{ $request->date_to }}">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-1">
                                    <button type="submit" class="btn btn-primary btn-sm">검색</button>
                                    <a href="{{ route('admin.auth.emoney.deposits.index') }}" class="btn btn-outline-secondary btn-sm">초기화</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 입금 목록 -->
            <div class="card">
                <div class="card-body">
                    @if(isset($deposits) && $deposits->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>입금정보</th>
                                        <th>사용자</th>
                                        <th>금액</th>
                                        <th>은행정보</th>
                                        <th>상태</th>
                                        <th>확인</th>
                                        <th>신청일</th>
                                        <th>액션</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($deposits as $deposit)
                                        <tr class="{{ !$deposit->checked ? 'table-warning' : '' }}">
                                            <td>
                                                <div>
                                                    <strong>#{{ $deposit->id }}</strong>
                                                    @if($deposit->log_id)
                                                        <br>
                                                        <small class="text-muted">Log: {{ $deposit->log_id }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $deposit->user_id }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $deposit->email }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong class="text-primary fs-5">{{ number_format($deposit->amount, 2) }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        {{ $deposit->currency }}
                                                        @if($deposit->currency_rate && $deposit->currency != 'KRW')
                                                            ({{ $deposit->currency_rate }})
                                                        @endif
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $deposit->bank }}</strong>
                                                    <br>
                                                    <code>{{ $deposit->account }}</code>
                                                    <br>
                                                    <small>{{ $deposit->owner }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                @if($deposit->status == 'pending')
                                                    <span class="badge bg-warning">대기</span>
                                                @elseif($deposit->status == 'approved')
                                                    <span class="badge bg-success">승인</span>
                                                @elseif($deposit->status == 'rejected')
                                                    <span class="badge bg-danger">거부</span>
                                                @elseif($deposit->status == 'processing')
                                                    <span class="badge bg-info">처리중</span>
                                                @else
                                                    <span class="badge bg-light text-dark">{{ $deposit->status }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($deposit->checked)
                                                    <div>
                                                        <span class="badge bg-success">
                                                            <i class="fe fe-check"></i> 확인
                                                        </span>
                                                        @if($deposit->checked_at)
                                                            <br>
                                                            <small class="text-muted">{{ \Carbon\Carbon::parse($deposit->checked_at)->format('m-d H:i') }}</small>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="badge bg-secondary">미확인</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div>
                                                    {{ $deposit->created_at->format('m-d H:i') }}
                                                    <br>
                                                    <small class="text-muted">{{ $deposit->created_at->diffForHumans() }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        액션
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#" onclick="viewDepositDetails({{ $deposit->id }})">
                                                            <i class="fe fe-eye me-2"></i>상세보기
                                                        </a></li>
                                                        @if(!$deposit->checked)
                                                        <li><a class="dropdown-item text-success" href="#" onclick="approveDeposit({{ $deposit->id }})">
                                                            <i class="fe fe-check me-2"></i>승인하기
                                                        </a></li>
                                                        <li><a class="dropdown-item text-danger" href="#" onclick="rejectDeposit({{ $deposit->id }})">
                                                            <i class="fe fe-x me-2"></i>거부하기
                                                        </a></li>
                                                        @endif
                                                        @if($deposit->description)
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item" href="#" onclick="viewNote('{{ $deposit->description }}')">
                                                            <i class="fe fe-file-text me-2"></i>메모보기
                                                        </a></li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if(method_exists($deposits, 'links'))
                            <div class="mt-3">
                                {{ $deposits->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fe fe-arrow-down-circle text-muted fs-1"></i>
                            <p class="text-muted mt-3 mb-0">입금 내역이 없습니다.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
function viewDepositDetails(id) {
    alert('입금 상세보기 기능 준비중입니다.');
}

function approveDeposit(id) {
    if(confirm('이 입금을 승인하시겠습니까?')) {
        alert('입금 승인 기능 준비중입니다.');
    }
}

function rejectDeposit(id) {
    const reason = prompt('거부 사유를 입력하세요:');
    if(reason) {
        alert('입금 거부 기능 준비중입니다.');
    }
}

function viewNote(note) {
    alert('메모: ' + note);
}
</script>
@endsection
