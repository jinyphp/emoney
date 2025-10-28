@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '포인트 만료 상세보기')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.auth.point.expiry') }}">포인트 만료 관리</a></li>
                            <li class="breadcrumb-item active" aria-current="page">상세보기</li>
                        </ol>
                    </nav>
                    <h2 class="mb-0">포인트 만료 상세보기</h2>
                    <p class="text-muted mb-0">만료 스케줄 #{{ $expiry->id }}</p>
                </div>
                <div>
                    <a href="{{ route('admin.auth.point.expiry') }}" class="btn btn-outline-secondary">
                        <i class="fe fe-arrow-left me-2"></i>목록으로
                    </a>
                    @if(!$expiry->expired)
                    <button class="btn btn-warning" onclick="processExpiry({{ $expiry->id }})">
                        <i class="fe fe-clock me-2"></i>만료 처리
                    </button>
                    @endif
                    @if(!$expiry->notified && !$expiry->expired)
                    <button class="btn btn-info" onclick="sendNotification({{ $expiry->id }})">
                        <i class="fe fe-bell me-2"></i>알림 발송
                    </button>
                    @endif
                </div>
            </div>

            <div class="row">
                <!-- 기본 정보 -->
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">기본 정보</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">만료 스케줄 ID</label>
                                        <div class="fw-bold">#{{ $expiry->id }}</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-muted">포인트 금액</label>
                                        <div class="fw-bold text-primary fs-5">{{ number_format($expiry->amount, 0) }}P</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-muted">만료 예정일</label>
                                        <div class="fw-bold">
                                            {{ \Carbon\Carbon::parse($expiry->expires_at)->format('Y년 m월 d일 H:i') }}
                                            @php
                                                $isExpired = $expiry->expired || \Carbon\Carbon::parse($expiry->expires_at)->isPast();
                                                $isExpiringSoon = !$expiry->expired && \Carbon\Carbon::parse($expiry->expires_at)->diffInDays(now()) <= 3;
                                            @endphp
                                            @if($isExpired)
                                                <span class="badge bg-secondary ms-2">만료됨</span>
                                            @elseif($isExpiringSoon)
                                                <span class="badge bg-warning ms-2">임박</span>
                                            @else
                                                <span class="badge bg-success ms-2">정상</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-muted">남은 시간</label>
                                        <div class="fw-bold">
                                            @if($expiry->expired)
                                                <span class="text-secondary">만료 완료</span>
                                            @elseif(\Carbon\Carbon::parse($expiry->expires_at)->isPast())
                                                <span class="text-danger">{{ \Carbon\Carbon::parse($expiry->expires_at)->diffForHumans() }}</span>
                                            @else
                                                <span class="text-info">{{ \Carbon\Carbon::parse($expiry->expires_at)->diffForHumans() }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">생성일시</label>
                                        <div class="fw-bold">{{ \Carbon\Carbon::parse($expiry->created_at)->format('Y-m-d H:i:s') }}</div>
                                    </div>
                                    @if($expiry->expired && $expiry->expired_at)
                                    <div class="mb-3">
                                        <label class="form-label text-muted">만료 처리일시</label>
                                        <div class="fw-bold text-secondary">{{ \Carbon\Carbon::parse($expiry->expired_at)->format('Y-m-d H:i:s') }}</div>
                                    </div>
                                    @endif
                                    <div class="mb-3">
                                        <label class="form-label text-muted">알림 상태</label>
                                        <div class="fw-bold">
                                            @if($expiry->notified)
                                                <span class="badge bg-success">발송완료</span>
                                                @if($expiry->notified_at)
                                                    <small class="text-muted d-block">{{ \Carbon\Carbon::parse($expiry->notified_at)->format('Y-m-d H:i') }}</small>
                                                @endif
                                            @else
                                                <span class="badge bg-warning">미발송</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($expiry->point_log_id)
                                    <div class="mb-3">
                                        <label class="form-label text-muted">연관 포인트 로그</label>
                                        <div class="fw-bold">
                                            <a href="#" onclick="viewPointLog({{ $expiry->point_log_id }})" class="text-decoration-none">
                                                #{{ $expiry->point_log_id }}
                                            </a>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 사용자 정보 -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">사용자 정보</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">사용자 ID</label>
                                        <div class="fw-bold">{{ $expiry->user_id }}</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-muted">이름</label>
                                        <div class="fw-bold">{{ $user_data->name ?? 'N/A' }}</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-muted">이메일</label>
                                        <div class="fw-bold">{{ $user_data->email ?? 'N/A' }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    @if($expiry->user_uuid)
                                    <div class="mb-3">
                                        <label class="form-label text-muted">사용자 UUID</label>
                                        <div class="fw-bold font-monospace small">{{ $expiry->user_uuid }}</div>
                                    </div>
                                    @endif
                                    @if($expiry->shard_id)
                                    <div class="mb-3">
                                        <label class="form-label text-muted">샤드 ID</label>
                                        <div class="fw-bold">{{ $expiry->shard_id }}</div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 사이드바 정보 -->
                <div class="col-md-4">
                    <!-- 상태 카드 -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">현재 상태</h6>
                        </div>
                        <div class="card-body text-center">
                            @if($expiry->expired)
                                <div class="text-secondary">
                                    <i class="fe fe-check-circle fs-1"></i>
                                    <h5 class="mt-2">만료 완료</h5>
                                    <p class="text-muted">포인트가 만료 처리되었습니다</p>
                                </div>
                            @elseif(\Carbon\Carbon::parse($expiry->expires_at)->isPast())
                                <div class="text-danger">
                                    <i class="fe fe-alert-circle fs-1"></i>
                                    <h5 class="mt-2">만료됨</h5>
                                    <p class="text-muted">만료 처리가 필요합니다</p>
                                </div>
                            @elseif(\Carbon\Carbon::parse($expiry->expires_at)->diffInDays(now()) <= 3)
                                <div class="text-warning">
                                    <i class="fe fe-clock fs-1"></i>
                                    <h5 class="mt-2">만료 임박</h5>
                                    <p class="text-muted">곧 만료됩니다</p>
                                </div>
                            @else
                                <div class="text-success">
                                    <i class="fe fe-shield fs-1"></i>
                                    <h5 class="mt-2">정상</h5>
                                    <p class="text-muted">정상 상태입니다</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 통계 정보 -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">관련 통계</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">해당 사용자 총 만료 예정</span>
                                    <span class="fw-bold">{{ $user_expiry_count ?? 0 }}건</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">해당 사용자 만료 예정 금액</span>
                                    <span class="fw-bold">{{ number_format($user_expiry_amount ?? 0, 0) }}P</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">오늘 전체 만료 예정</span>
                                    <span class="fw-bold">{{ $today_expiry_count ?? 0 }}건</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 액션 버튼 -->
                    @if(!$expiry->expired)
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">관리 작업</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button class="btn btn-warning" onclick="processExpiry({{ $expiry->id }})">
                                    <i class="fe fe-clock me-2"></i>만료 처리 실행
                                </button>
                                @if(!$expiry->notified)
                                <button class="btn btn-info" onclick="sendNotification({{ $expiry->id }})">
                                    <i class="fe fe-bell me-2"></i>만료 알림 발송
                                </button>
                                @endif
                                <button class="btn btn-outline-danger" onclick="deleteExpiry({{ $expiry->id }})">
                                    <i class="fe fe-trash-2 me-2"></i>스케줄 삭제
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
function processExpiry(id) {
    if(confirm('이 포인트를 만료 처리하시겠습니까?\n\n처리 후에는 되돌릴 수 없습니다.')) {
        // TODO: AJAX 호출로 만료 처리 API 구현
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
        // TODO: AJAX 호출로 알림 발송 API 구현
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
        // TODO: AJAX 호출로 삭제 API 구현
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
                window.location.href = '/admin/auth/point/expiry';
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
    // TODO: 포인트 로그 상세보기 페이지로 이동
    window.location.href = '/admin/auth/point/log/' + logId;
}
</script>
@endsection