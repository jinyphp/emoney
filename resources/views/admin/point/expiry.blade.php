@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '포인트 만료 관리')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">포인트 만료 관리</h2>
                    <p class="text-muted mb-0">포인트 만료 스케줄 및 관리</p>
                </div>
                <div>
                    <button class="btn btn-warning" onclick="processExpiredPoints()">
                        <i class="fe fe-clock me-2"></i>만료 처리 실행
                    </button>
                    <button class="btn btn-info" onclick="sendExpiryNotifications()">
                        <i class="fe fe-bell me-2"></i>만료 알림 발송
                    </button>
                </div>
            </div>

            <!-- 통계 카드 -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="mb-1">전체 스케줄</h6>
                            <h4 class="text-primary">{{ number_format($statistics['total_schedules']) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="mb-1">만료 대기</h6>
                            <h4 class="text-warning">{{ number_format($statistics['pending_expiries']) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="mb-1">만료 완료</h6>
                            <h4 class="text-secondary">{{ number_format($statistics['expired_count']) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="mb-1">오늘 만료</h6>
                            <h4 class="text-danger">{{ number_format($statistics['expiring_today']) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="mb-1">이번 주</h6>
                            <h4 class="text-warning">{{ number_format($statistics['expiring_this_week']) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="mb-1">이번 달</h6>
                            <h4 class="text-info">{{ number_format($statistics['expiring_this_month']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 금액 통계 카드 -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="mb-1">만료 대기 금액</h6>
                            <h4 class="text-warning">{{ number_format($statistics['total_pending_amount'], 0) }}P</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="mb-1">만료된 금액</h6>
                            <h4 class="text-secondary">{{ number_format($statistics['total_expired_amount'], 0) }}P</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="mb-1">알림 대기</h6>
                            <h4 class="text-info">{{ number_format($statistics['notification_pending']) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="mb-2">월별 만료 예정</h6>
                            @foreach($statistics['monthly_expiry_schedule']->take(3) as $schedule)
                                <div class="d-flex justify-content-between mb-1">
                                    <small>{{ $schedule->month }}</small>
                                    <small>{{ number_format($schedule->total_amount, 0) }}P</small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- 검색 및 필터 -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.auth.point.expiry') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">검색</label>
                                <input type="text" name="search" class="form-control" placeholder="이메일, 이름, 사용자ID 검색" value="{{ $request->search }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">만료상태</label>
                                <select name="expired" class="form-select">
                                    <option value="">전체</option>
                                    <option value="0" {{ $request->expired == '0' ? 'selected' : '' }}>만료 대기</option>
                                    <option value="1" {{ $request->expired == '1' ? 'selected' : '' }}>만료 완료</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">알림상태</label>
                                <select name="notified" class="form-select">
                                    <option value="">전체</option>
                                    <option value="0" {{ $request->notified == '0' ? 'selected' : '' }}>미발송</option>
                                    <option value="1" {{ $request->notified == '1' ? 'selected' : '' }}>발송완료</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">만료 임박</label>
                                <select name="expiring_days" class="form-select">
                                    <option value="">전체</option>
                                    <option value="1" {{ $request->expiring_days == '1' ? 'selected' : '' }}>1일 이내</option>
                                    <option value="3" {{ $request->expiring_days == '3' ? 'selected' : '' }}>3일 이내</option>
                                    <option value="7" {{ $request->expiring_days == '7' ? 'selected' : '' }}>7일 이내</option>
                                    <option value="30" {{ $request->expiring_days == '30' ? 'selected' : '' }}>30일 이내</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">만료 시작일</label>
                                <input type="date" name="expires_from" class="form-control" value="{{ $request->expires_from }}">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">만료 종료일</label>
                                <input type="date" name="expires_to" class="form-control" value="{{ $request->expires_to }}">
                            </div>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">검색</button>
                                <a href="{{ route('admin.auth.point.expiry') }}" class="btn btn-outline-secondary">초기화</a>
                                <button type="button" class="btn btn-success" onclick="exportExpiry()">
                                    <i class="fe fe-download me-2"></i>엑셀 다운로드
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 만료 스케줄 목록 -->
            <div class="card">
                <div class="card-body">
                    @if(isset($expiries) && $expiries->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>사용자</th>
                                        <th>포인트</th>
                                        <th>만료일</th>
                                        <th>남은 기간</th>
                                        <th>상태</th>
                                        <th>알림</th>
                                        <th>등록일</th>
                                        <th>액션</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expiries as $expiry)
                                        <tr class="{{ $expiry->isExpired() ? 'table-secondary' : ($expiry->isExpiringSoon(3) ? 'table-warning' : '') }}">
                                            <td><strong>#{{ $expiry->id }}</strong></td>
                                            <td>
                                                <div>
                                                    <strong>{{ $expiry->user->name ?? 'N/A' }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $expiry->user->email ?? 'N/A' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    {{ number_format($expiry->amount, 0) }}P
                                                </span>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $expiry->expires_at->format('Y-m-d') }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $expiry->expires_at->format('H:i') }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                @if($expiry->expired)
                                                    <span class="badge bg-secondary">만료됨</span>
                                                @elseif($expiry->expires_at->isPast())
                                                    <span class="badge bg-danger">만료 예정</span>
                                                @else
                                                    @php
                                                        $days = $expiry->expires_at->diffInDays(now());
                                                        $hours = $expiry->expires_at->diffInHours(now()) % 24;
                                                    @endphp
                                                    @if($days > 0)
                                                        <span class="badge {{ $days <= 3 ? 'bg-warning' : 'bg-info' }}">
                                                            {{ $days }}일 {{ $hours }}시간
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger">
                                                            {{ $hours }}시간
                                                        </span>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                @if($expiry->expired)
                                                    <div>
                                                        <span class="badge bg-secondary">만료 완료</span>
                                                        @if($expiry->expired_at)
                                                            <br>
                                                            <small class="text-muted">{{ $expiry->expired_at->format('m-d H:i') }}</small>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="badge bg-warning">대기 중</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($expiry->notified)
                                                    <div>
                                                        <span class="badge bg-success">
                                                            <i class="fe fe-check"></i> 발송
                                                        </span>
                                                        @if($expiry->notified_at)
                                                            <br>
                                                            <small class="text-muted">{{ $expiry->notified_at->format('m-d H:i') }}</small>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="badge bg-secondary">미발송</span>
                                                @endif
                                            </td>
                                            <td>{{ $expiry->created_at->format('m-d H:i') }}</td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        액션
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#" onclick="viewExpiryDetails({{ $expiry->id }})">
                                                            <i class="fe fe-eye me-2"></i>상세보기
                                                        </a></li>
                                                        @if(!$expiry->expired && $expiry->expires_at->isPast())
                                                        <li><a class="dropdown-item text-warning" href="#" onclick="processExpiry({{ $expiry->id }})">
                                                            <i class="fe fe-clock me-2"></i>만료 처리
                                                        </a></li>
                                                        @endif
                                                        @if(!$expiry->notified && !$expiry->expired)
                                                        <li><a class="dropdown-item text-info" href="#" onclick="sendNotification({{ $expiry->id }})">
                                                            <i class="fe fe-bell me-2"></i>알림 발송
                                                        </a></li>
                                                        @endif
                                                        <li><a class="dropdown-item" href="#" onclick="viewPointLog({{ $expiry->point_log_id }})">
                                                            <i class="fe fe-list me-2"></i>원본 로그
                                                        </a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if(method_exists($expiries, 'links'))
                            <div class="mt-3">
                                {{ $expiries->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fe fe-clock text-muted fs-1"></i>
                            <p class="text-muted mt-3 mb-0">만료 스케줄이 없습니다.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
function viewExpiryDetails(id) {
    alert('만료 상세보기 기능 준비중입니다.');
}

function processExpiry(id) {
    if(confirm('이 포인트를 만료 처리하시겠습니까?')) {
        alert('만료 처리 기능 준비중입니다.');
    }
}

function sendNotification(id) {
    if(confirm('만료 알림을 발송하시겠습니까?')) {
        alert('알림 발송 기능 준비중입니다.');
    }
}

function viewPointLog(logId) {
    alert('포인트 로그 보기 기능 준비중입니다.');
}

function processExpiredPoints() {
    if(confirm('만료된 모든 포인트를 처리하시겠습니까?')) {
        alert('일괄 만료 처리 기능 준비중입니다.');
    }
}

function sendExpiryNotifications() {
    if(confirm('만료 예정 알림을 일괄 발송하시겠습니까?')) {
        alert('일괄 알림 발송 기능 준비중입니다.');
    }
}

function exportExpiry() {
    alert('엑셀 다운로드 기능 준비중입니다.');
}
</script>
@endsection
