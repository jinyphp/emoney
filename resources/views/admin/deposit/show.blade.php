@extends('jiny-admin::layouts.admin')

@section('title', '충전 신청 상세보기')

@section('content')
<div class="container-fluid">
    <!-- 헤더 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-2">
                        <i class="bi bi-receipt text-info"></i>
                        충전 신청 상세보기
                    </h2>
                    <p class="text-muted mb-0">충전 신청 #{{ $deposit->id }} 상세 정보</p>
                </div>
                <div>
                    <button onclick="window.close()" class="btn btn-outline-secondary">
                        <i class="bi bi-x"></i> 닫기
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- 기본 정보 -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">충전 신청 정보</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">신청 ID</label>
                            <p class="form-control-plaintext">#{{ $deposit->id }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">참조번호</label>
                            <p class="form-control-plaintext">{{ $deposit->reference_number ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">충전 금액</label>
                            <p class="form-control-plaintext text-primary fw-bold">₩{{ number_format($deposit->amount) }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">상태</label>
                            <p class="form-control-plaintext">
                                @if($deposit->status === 'pending')
                                    <span class="badge bg-warning">대기중</span>
                                @elseif($deposit->status === 'approved')
                                    <span class="badge bg-success">승인됨</span>
                                @elseif($deposit->status === 'rejected')
                                    <span class="badge bg-danger">거절됨</span>
                                @else
                                    <span class="badge bg-secondary">{{ $deposit->status }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">입금자명</label>
                            <p class="form-control-plaintext">{{ $deposit->depositor_name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">입금 날짜</label>
                            <p class="form-control-plaintext">{{ $deposit->deposit_date ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">은행명</label>
                            <p class="form-control-plaintext">{{ $deposit->bank_name }} ({{ $deposit->bank_code ?? '-' }})</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">충전 방법</label>
                            <p class="form-control-plaintext">{{ $deposit->method === 'bank_transfer' ? '은행 계좌 이체' : $deposit->method }}</p>
                        </div>
                        @if($deposit->user_memo)
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">사용자 메모</label>
                            <p class="form-control-plaintext">{{ $deposit->user_memo }}</p>
                        </div>
                        @endif
                        @if($deposit->admin_memo)
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">관리자 메모</label>
                            <p class="form-control-plaintext">{{ $deposit->admin_memo }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 사용자 정보 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">사용자 정보</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">사용자명</label>
                            <p class="form-control-plaintext">{{ $deposit->user_name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">이메일</label>
                            <p class="form-control-plaintext">{{ $deposit->user_email }}</p>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">사용자 UUID</label>
                            <p class="form-control-plaintext"><code>{{ $deposit->user_uuid }}</code></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 처리 정보 및 로그 -->
        <div class="col-lg-4">
            <!-- 처리 정보 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">처리 정보</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">신청 일시</label>
                        <p class="form-control-plaintext">{{ \Carbon\Carbon::parse($deposit->created_at)->format('Y-m-d H:i:s') }}</p>
                    </div>
                    @if($deposit->checked_at)
                    <div class="mb-3">
                        <label class="form-label fw-bold">처리 일시</label>
                        <p class="form-control-plaintext">{{ \Carbon\Carbon::parse($deposit->checked_at)->format('Y-m-d H:i:s') }}</p>
                    </div>
                    @endif
                    @if($deposit->checked_by)
                    <div class="mb-3">
                        <label class="form-label fw-bold">처리자 ID</label>
                        <p class="form-control-plaintext">#{{ $deposit->checked_by }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- 관련 로그 -->
            @if($logs && $logs->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">관련 로그</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($logs as $log)
                        <div class="timeline-item mb-3">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">{{ $log->type }}</h6>
                                <p class="mb-1 small">{{ $log->description }}</p>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i:s') }}</small>
                                @if($log->amount)
                                <br><small class="text-success">금액: ₩{{ number_format($log->amount) }}</small>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- 관리자 로그 -->
            @if($admin_logs && $admin_logs->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">관리자 처리 로그</h5>
                </div>
                <div class="card-body">
                    @foreach($admin_logs as $adminLog)
                    <div class="mb-3 p-2 bg-light rounded">
                        <div class="d-flex justify-content-between">
                            <strong>{{ ucfirst($adminLog->action) }}</strong>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($adminLog->created_at)->format('m/d H:i') }}</small>
                        </div>
                        <small class="text-muted">관리자 ID: {{ $adminLog->admin_id }}</small>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 20px;
}

.timeline-item {
    position: relative;
}

.timeline-marker {
    position: absolute;
    left: -25px;
    top: 5px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.timeline:before {
    content: '';
    position: absolute;
    left: -21px;
    top: 10px;
    bottom: 0;
    width: 2px;
    background-color: #dee2e6;
}
</style>
@endsection
