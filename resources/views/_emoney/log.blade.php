@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '거래 로그')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">거래 로그</h2>
                    <p class="text-muted mb-0">이머니 모든 거래 내역 및 로그 관리</p>
                </div>
            </div>

            <!-- 통계 카드 -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fe fe-list text-primary fs-3"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-0">{{ number_format($statistics['total_logs']) }}</h5>
                                    <p class="text-muted mb-0">전체 로그</p>
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
                                    <i class="fe fe-calendar text-success fs-3"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-0">{{ number_format($statistics['today_logs']) }}</h5>
                                    <p class="text-muted mb-0">오늘 로그</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="mb-3">거래 유형별 통계</h6>
                            <div class="row">
                                @foreach($statistics['transaction_types'] as $type)
                                    <div class="col-6 mb-2">
                                        <div class="d-flex justify-content-between">
                                            <span class="badge bg-info">{{ $type->type ?: '미분류' }}</span>
                                            <small>{{ number_format($type->count) }}건</small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 일주일 통계 -->
            @if($statistics['daily_stats']->count() > 0)
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="mb-3">최근 7일 거래 통계</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>날짜</th>
                                    <th>거래 건수</th>
                                    <th>총 입금액</th>
                                    <th>총 출금액</th>
                                    <th>순 증감</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($statistics['daily_stats'] as $stat)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($stat->date)->format('m-d (D)') }}</td>
                                        <td>{{ number_format($stat->count) }}건</td>
                                        <td class="text-success">+{{ number_format($stat->total_deposit, 0) }}</td>
                                        <td class="text-danger">-{{ number_format($stat->total_withdraw, 0) }}</td>
                                        <td class="{{ ($stat->total_deposit - $stat->total_withdraw) >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($stat->total_deposit - $stat->total_withdraw, 0) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- 검색 및 필터 -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.auth.emoney.index') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">검색</label>
                                <input type="text" name="search" class="form-control" placeholder="이메일, 사용자ID, 거래ID, 설명 검색" value="{{ $request->search }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">거래유형</label>
                                <select name="type" class="form-select">
                                    <option value="">전체</option>
                                    @foreach($statistics['transaction_types'] as $type)
                                        <option value="{{ $type->type }}" {{ $request->type == $type->type ? 'selected' : '' }}>
                                            {{ $type->type ?: '미분류' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">상태</label>
                                <select name="status" class="form-select">
                                    <option value="">전체</option>
                                    <option value="completed" {{ $request->status == 'completed' ? 'selected' : '' }}>완료</option>
                                    <option value="pending" {{ $request->status == 'pending' ? 'selected' : '' }}>대기</option>
                                    <option value="failed" {{ $request->status == 'failed' ? 'selected' : '' }}>실패</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">거래테이블</label>
                                <select name="trans" class="form-select">
                                    <option value="">전체</option>
                                    <option value="deposit" {{ $request->trans == 'deposit' ? 'selected' : '' }}>입금</option>
                                    <option value="withdraw" {{ $request->trans == 'withdraw' ? 'selected' : '' }}>출금</option>
                                    <option value="transfer" {{ $request->trans == 'transfer' ? 'selected' : '' }}>이체</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">시작일</label>
                                <input type="date" name="date_from" class="form-control" value="{{ $request->date_from }}">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">종료일</label>
                                <input type="date" name="date_to" class="form-control" value="{{ $request->date_to }}">
                            </div>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">검색</button>
                                <a href="{{ route('admin.auth.emoney.index') }}" class="btn btn-outline-secondary">초기화</a>
                                <button type="button" class="btn btn-success" onclick="exportLogs()">
                                    <i class="fe fe-download me-2"></i>엑셀 다운로드
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 거래 로그 목록 -->
            <div class="card">
                <div class="card-body">
                    @if(isset($logs) && $logs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>로그 ID</th>
                                        <th>사용자</th>
                                        <th>거래유형</th>
                                        <th>금액정보</th>
                                        <th>잔액정보</th>
                                        <th>거래정보</th>
                                        <th>상태</th>
                                        <th>시간</th>
                                        <th>액션</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($logs as $log)
                                        <tr>
                                            <td>
                                                <strong>#{{ $log->id }}</strong>
                                                @if($log->trans_id)
                                                    <br>
                                                    <small class="text-muted">{{ $log->trans_id }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $log->user_id }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $log->email }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge
                                                    @if($log->type == 'deposit') bg-success
                                                    @elseif($log->type == 'withdraw') bg-danger
                                                    @elseif($log->type == 'transfer') bg-info
                                                    @elseif($log->type == 'point_add') bg-warning
                                                    @elseif($log->type == 'point_use') bg-secondary
                                                    @else bg-light text-dark
                                                    @endif">
                                                    {{ $log->type ?: '미분류' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($log->deposit)
                                                    <div class="text-success">
                                                        <strong>+{{ number_format($log->deposit, 2) }}</strong>
                                                        @if($log->deposit_currency)
                                                            <small>({{ $log->deposit_currency }})</small>
                                                        @endif
                                                    </div>
                                                @endif
                                                @if($log->withdraw)
                                                    <div class="text-danger">
                                                        <strong>-{{ number_format($log->withdraw, 2) }}</strong>
                                                        @if($log->withdraw_currency)
                                                            <small>({{ $log->withdraw_currency }})</small>
                                                        @endif
                                                    </div>
                                                @endif
                                                @if($log->point)
                                                    <div class="text-info">
                                                        <i class="fe fe-star"></i> {{ number_format($log->point) }}P
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                @if($log->balance)
                                                    <div>
                                                        <strong>{{ number_format($log->balance, 2) }}</strong>
                                                        @if($log->balance_currency)
                                                            <small>({{ $log->balance_currency }})</small>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                @if($log->trans)
                                                    <span class="badge bg-info">{{ $log->trans }}</span>
                                                @endif
                                                @if($log->currency)
                                                    <br>
                                                    <small class="text-muted">{{ $log->currency }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($log->status == 'completed')
                                                    <span class="badge bg-success">완료</span>
                                                @elseif($log->status == 'pending')
                                                    <span class="badge bg-warning">대기</span>
                                                @elseif($log->status == 'failed')
                                                    <span class="badge bg-danger">실패</span>
                                                @else
                                                    <span class="badge bg-light text-dark">{{ $log->status }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div>
                                                    {{ $log->created_at->format('m-d H:i:s') }}
                                                    <br>
                                                    <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        <i class="fe fe-more-horizontal"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#" onclick="viewLogDetails({{ $log->id }})">
                                                            <i class="fe fe-eye me-2"></i>상세보기
                                                        </a></li>
                                                        @if($log->trans && $log->trans_id)
                                                        <li><a class="dropdown-item" href="#" onclick="viewTransaction('{{ $log->trans }}', '{{ $log->trans_id }}')">
                                                            <i class="fe fe-external-link me-2"></i>관련 거래보기
                                                        </a></li>
                                                        @endif
                                                        @if($log->description)
                                                        <li><a class="dropdown-item" href="#" onclick="viewDescription('{{ $log->description }}')">
                                                            <i class="fe fe-file-text me-2"></i>설명보기
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

                        @if(method_exists($logs, 'links'))
                            <div class="mt-3">
                                {{ $logs->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fe fe-list text-muted fs-1"></i>
                            <p class="text-muted mt-3 mb-0">거래 로그가 없습니다.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
function viewLogDetails(id) {
    alert('로그 상세보기 기능 준비중입니다.');
}

function viewTransaction(table, transId) {
    alert(`${table} 테이블의 거래 ID ${transId} 보기 기능 준비중입니다.`);
}

function viewDescription(description) {
    alert('설명: ' + description);
}

function exportLogs() {
    alert('엑셀 다운로드 기능 준비중입니다.');
}
</script>
@endsection
