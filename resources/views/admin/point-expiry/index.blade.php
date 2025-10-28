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
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">총 신청 목록 ({{ $expiries->total() ?? 0 }}건)</h6>
                        <div class="text-muted small">
                            {{ $expiries->firstItem() ?? 0 }}-{{ $expiries->lastItem() ?? 0 }} of {{ $expiries->total() ?? 0 }}
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if(isset($expiries) && $expiries->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">ID</th>
                                        <th style="width: 180px;">사용자</th>
                                        <th style="width: 160px;">금액/유형</th>
                                        <th style="width: 80px;">상태</th>
                                        <th style="width: 120px;">신청일시</th>
                                        <th style="width: 120px;">처리일시</th>
                                        <th style="width: 80px;">작업</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expiries as $expiry)
                                        @php
                                            $isExpired = $expiry->expired || \Carbon\Carbon::parse($expiry->expires_at)->isPast();
                                            $isExpiringSoon = !$expiry->expired && \Carbon\Carbon::parse($expiry->expires_at)->diffInDays(now()) <= 3;
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $expiry->id }}</strong>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $expiry->user_data->name ?? 'N/A' }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $expiry->user_data->email ?? 'N/A' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong class="text-primary">₩{{ number_format($expiry->amount, 0) }}</strong>
                                                    <br>
                                                    <small class="text-muted">(포인트만료)</small>
                                                </div>
                                            </td>
                                            <td>
                                                @if($expiry->expired)
                                                    <span class="badge bg-secondary">처리됨</span>
                                                @elseif($isExpiringSoon)
                                                    <span class="badge bg-warning">거부됨</span>
                                                @else
                                                    <span class="badge bg-success">승인됨</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div>{{ \Carbon\Carbon::parse($expiry->created_at)->format('Y-m-d H:i') }}</div>
                                            </td>
                                            <td>
                                                @if($expiry->expired && $expiry->expired_at)
                                                    <div>{{ \Carbon\Carbon::parse($expiry->expired_at)->format('Y-m-d H:i') }}</div>
                                                    <small class="text-muted">#1</small>
                                                @else
                                                    <div>{{ \Carbon\Carbon::parse($expiry->expires_at)->format('Y-m-d H:i') }}</div>
                                                    <small class="text-muted">#1</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="viewExpiryDetails({{ $expiry->id }})" title="상세보기">
                                                        <i class="fe fe-eye"></i>
                                                    </button>
                                                    @if(!$expiry->expired)
                                                    <button class="btn btn-sm btn-outline-danger" onclick="processExpiry({{ $expiry->id }})" title="삭제">
                                                        <i class="fe fe-trash-2"></i>
                                                    </button>
                                                    @endif
                                                </div>
                                                @if(!$expiry->expired && $isExpiringSoon)
                                                <div class="mt-1">
                                                    <small class="text-danger">임금 확인이 되지 않습니다. 정확한 계좌번호와 입금자명을 확인해 주세요.</small>
                                                </div>
                                                @endif
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
    window.location.href = '/admin/auth/point/expiry/' + id;
}

function processExpiry(id) {
    if(confirm('이 포인트를 만료 처리하시겠습니까?\n\n처리 후에는 되돌릴 수 없습니다.')) {
        fetch('/admin/auth/point/expiry/' + id + '/process', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert('만료 처리가 완료되었습니다.');
                location.reload();
            } else {
                alert('만료 처리 중 오류가 발생했습니다: ' + (data.message || '알 수 없는 오류'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('만료 처리 중 오류가 발생했습니다.');
        });
    }
}

function sendNotification(id) {
    if(confirm('만료 알림을 발송하시겠습니까?')) {
        fetch('/admin/auth/point/expiry/' + id + '/notify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert('알림이 발송되었습니다.');
                location.reload();
            } else {
                alert('알림 발송 중 오류가 발생했습니다: ' + (data.message || '알 수 없는 오류'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('알림 발송 중 오류가 발생했습니다.');
        });
    }
}

function deleteExpiry(id) {
    if(confirm('이 만료 스케줄을 삭제하시겠습니까?\n\n삭제 후에는 되돌릴 수 없습니다.')) {
        fetch('/admin/auth/point/expiry/' + id, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert('만료 스케줄이 삭제되었습니다.');
                location.reload();
            } else {
                alert('삭제 중 오류가 발생했습니다: ' + (data.message || '알 수 없는 오류'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('삭제 중 오류가 발생했습니다.');
        });
    }
}

function viewPointLog(logId) {
    window.location.href = '/admin/auth/point/log?log_id=' + logId;
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
    // 현재 검색 조건을 포함해서 엑셀 다운로드
    const form = document.querySelector('form[method="GET"]');
    const formData = new FormData(form);

    // URL 파라미터 구성
    const urlParams = new URLSearchParams();
    for (let [key, value] of formData.entries()) {
        if (value) {
            urlParams.append(key, value);
        }
    }

    // 엑셀 다운로드 URL로 이동 (현재 필터 조건 포함)
    const exportUrl = '/admin/auth/point/expiry/export?' + urlParams.toString();
    window.location.href = exportUrl;
}
</script>
@endsection
