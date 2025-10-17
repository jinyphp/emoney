@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '포인트 거래 로그')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">포인트 거래 로그</h2>
                    <p class="text-muted mb-0">모든 포인트 거래 내역 및 로그 관리</p>
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
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="mb-1">이번 달 적립</h6>
                            <h4 class="text-success">{{ number_format($statistics['monthly_summary']['earned'], 0) }}P</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="mb-1">이번 달 사용</h6>
                            <h4 class="text-danger">{{ number_format($statistics['monthly_summary']['used'], 0) }}P</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 거래 유형별 통계 -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="mb-3">거래 유형별 통계</h6>
                            <div class="row">
                                @foreach($statistics['transaction_types'] as $type)
                                    <div class="col-6 mb-2">
                                        <div class="d-flex justify-content-between">
                                            <span class="badge
                                                @if($type->transaction_type == 'earn') bg-success
                                                @elseif($type->transaction_type == 'use') bg-danger
                                                @elseif($type->transaction_type == 'refund') bg-info
                                                @elseif($type->transaction_type == 'expire') bg-warning
                                                @elseif($type->transaction_type == 'admin') bg-secondary
                                                @else bg-light text-dark
                                                @endif">
                                                @switch($type->transaction_type)
                                                    @case('earn') 적립 @break
                                                    @case('use') 사용 @break
                                                    @case('refund') 환불 @break
                                                    @case('expire') 만료 @break
                                                    @case('admin') 관리자 @break
                                                    @default {{ $type->transaction_type }}
                                                @endswitch
                                            </span>
                                            <small>{{ number_format($type->count) }}건</small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="mb-3">참조 유형별 통계</h6>
                            <div class="row">
                                @foreach($statistics['reference_types']->take(6) as $ref)
                                    <div class="col-6 mb-2">
                                        <div class="d-flex justify-content-between">
                                            <span class="badge bg-info">{{ $ref->reference_type }}</span>
                                            <small>{{ number_format($ref->count) }}건</small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 최근 7일 통계 -->
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
                                    <th>적립 포인트</th>
                                    <th>사용 포인트</th>
                                    <th>순 증감</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($statistics['daily_stats'] as $stat)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($stat->date)->format('m-d (D)') }}</td>
                                        <td>{{ number_format($stat->count) }}건</td>
                                        <td class="text-success">+{{ number_format($stat->total_earned, 0) }}P</td>
                                        <td class="text-danger">-{{ number_format($stat->total_used, 0) }}P</td>
                                        <td class="{{ ($stat->total_earned - $stat->total_used) >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($stat->total_earned - $stat->total_used, 0) }}P
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
                    <form method="GET" action="{{ route('admin.auth.point.log') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">검색</label>
                                <input type="text" name="search" class="form-control" placeholder="이메일, 이름, 사용자ID, 사유 검색" value="{{ $request->search }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">거래유형</label>
                                <select name="transaction_type" class="form-select">
                                    <option value="">전체</option>
                                    <option value="earn" {{ $request->transaction_type == 'earn' ? 'selected' : '' }}>적립</option>
                                    <option value="use" {{ $request->transaction_type == 'use' ? 'selected' : '' }}>사용</option>
                                    <option value="refund" {{ $request->transaction_type == 'refund' ? 'selected' : '' }}>환불</option>
                                    <option value="expire" {{ $request->transaction_type == 'expire' ? 'selected' : '' }}>만료</option>
                                    <option value="admin" {{ $request->transaction_type == 'admin' ? 'selected' : '' }}>관리자</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">참조유형</label>
                                <select name="reference_type" class="form-select">
                                    <option value="">전체</option>
                                    @foreach($statistics['reference_types'] as $ref)
                                        <option value="{{ $ref->reference_type }}" {{ $request->reference_type == $ref->reference_type ? 'selected' : '' }}>
                                            {{ $ref->reference_type }}
                                        </option>
                                    @endforeach
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
                                <div class="d-flex flex-column gap-1">
                                    <button type="submit" class="btn btn-primary btn-sm">검색</button>
                                    <a href="{{ route('admin.auth.point.log') }}" class="btn btn-outline-secondary btn-sm">초기화</a>
                                </div>
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
                                        <th>금액</th>
                                        <th>잔액 변화</th>
                                        <th>참조정보</th>
                                        <th>사유</th>
                                        <th>시간</th>
                                        <th>액션</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($logs as $log)
                                        <tr>
                                            <td><strong>#{{ $log->id }}</strong></td>
                                            <td>
                                                <div>
                                                    <strong>{{ $log->user->name ?? 'N/A' }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $log->user->email ?? 'N/A' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge
                                                    @if($log->transaction_type == 'earn') bg-success
                                                    @elseif($log->transaction_type == 'use') bg-danger
                                                    @elseif($log->transaction_type == 'refund') bg-info
                                                    @elseif($log->transaction_type == 'expire') bg-warning
                                                    @elseif($log->transaction_type == 'admin') bg-secondary
                                                    @else bg-light text-dark
                                                    @endif">
                                                    {{ $log->transaction_type_name }}
                                                </span>
                                            </td>
                                            <td>
                                                <strong class="{{ $log->amount >= 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ $log->amount >= 0 ? '+' : '' }}{{ number_format($log->amount, 0) }}P
                                                </strong>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ number_format($log->balance_before, 0) }} →
                                                    <strong>{{ number_format($log->balance_after, 0) }}</strong>
                                                </small>
                                            </td>
                                            <td>
                                                @if($log->reference_type)
                                                    <div>
                                                        <span class="badge bg-info">{{ $log->reference_type }}</span>
                                                        @if($log->reference_id)
                                                            <br>
                                                            <small class="text-muted">#{{ $log->reference_id }}</small>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small>{{ Str::limit($log->reason, 30) }}</small>
                                                @if($log->expires_at)
                                                    <br>
                                                    <small class="text-warning">
                                                        <i class="fe fe-clock"></i> {{ $log->expires_at->format('Y-m-d') }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                <div>
                                                    {{ $log->created_at->format('m-d H:i') }}
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
                                                        @if($log->reference_type && $log->reference_id)
                                                        <li><a class="dropdown-item" href="#" onclick="viewReference('{{ $log->reference_type }}', '{{ $log->reference_id }}')">
                                                            <i class="fe fe-external-link me-2"></i>참조보기
                                                        </a></li>
                                                        @endif
                                                        @if($log->admin_id)
                                                        <li><a class="dropdown-item" href="#" onclick="viewAdmin({{ $log->admin_id }})">
                                                            <i class="fe fe-user me-2"></i>관리자정보
                                                        </a></li>
                                                        @endif
                                                        @if($log->metadata)
                                                        <li><a class="dropdown-item" href="#" onclick="viewMetadata('{{ json_encode($log->metadata) }}')">
                                                            <i class="fe fe-code me-2"></i>메타데이터
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

function viewReference(type, id) {
    alert(`${type} 참조 ID ${id} 보기 기능 준비중입니다.`);
}

function viewAdmin(adminId) {
    alert('관리자 정보 보기 기능 준비중입니다.');
}

function viewMetadata(metadata) {
    alert('메타데이터: ' + metadata);
}
</script>
@endsection
