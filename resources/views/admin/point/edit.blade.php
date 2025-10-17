@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '포인트 조정')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">포인트 조정</h2>
                    <p class="text-muted mb-0">{{ $userPoint->user->name ?? '알 수 없는 사용자' }}의 포인트 조정</p>
                </div>
                <div>
                    <a href="{{ route('admin.auth.point.show', $userPoint->id) }}" class="btn btn-outline-primary">
                        <i class="fe fe-arrow-left me-2"></i>상세보기
                    </a>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <div class="row">
                <!-- 포인트 조정 폼 -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">포인트 조정</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.auth.point.update', $userPoint->id) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="adjustment_type" class="form-label">조정 유형 <span class="text-danger">*</span></label>
                                            <select class="form-select" id="adjustment_type" name="adjustment_type" required>
                                                <option value="">조정 유형을 선택하세요</option>
                                                @foreach($adjustmentTypes as $type => $label)
                                                    <option value="{{ $type }}" {{ old('adjustment_type') == $type ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="amount" class="form-label">포인트 금액 <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="amount" name="amount"
                                                       value="{{ old('amount') }}" min="0.01" step="0.01" placeholder="0.00" required>
                                                <span class="input-group-text">P</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="reason" class="form-label">조정 사유</label>
                                    <textarea class="form-control" id="reason" name="reason" rows="3"
                                              placeholder="포인트 조정 사유를 입력하세요">{{ old('reason') }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="expires_at" class="form-label">만료일 (선택사항)</label>
                                    <input type="datetime-local" class="form-control" id="expires_at" name="expires_at"
                                           value="{{ old('expires_at') }}">
                                    <small class="form-text text-muted">포인트 적립 유형에서만 적용됩니다.</small>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-outline-secondary me-2" onclick="history.back()">취소</button>
                                    <button type="submit" class="btn btn-primary">포인트 조정</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- 사이드바 정보 -->
                <div class="col-lg-4">
                    <!-- 현재 포인트 정보 -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="card-title mb-0">현재 포인트 정보</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <th>사용자:</th>
                                    <td>{{ $userPoint->user->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>이메일:</th>
                                    <td>{{ $userPoint->user->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>현재 잔액:</th>
                                    <td><span class="badge bg-primary fs-6">{{ number_format($userPoint->balance, 2) }} P</span></td>
                                </tr>
                                <tr>
                                    <th>총 적립:</th>
                                    <td>{{ number_format($userPoint->total_earned, 2) }} P</td>
                                </tr>
                                <tr>
                                    <th>총 사용:</th>
                                    <td>{{ number_format($userPoint->total_used, 2) }} P</td>
                                </tr>
                                <tr>
                                    <th>총 만료:</th>
                                    <td>{{ number_format($userPoint->total_expired, 2) }} P</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- 조정 유형 설명 -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0">조정 유형 설명</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled small">
                                <li><strong>포인트 적립:</strong> 일반적인 포인트 적립 (만료일 설정 가능)</li>
                                <li><strong>포인트 사용:</strong> 사용자가 포인트를 사용한 것으로 처리</li>
                                <li><strong>관리자 추가 지급:</strong> 관리자 권한으로 추가 지급</li>
                                <li><strong>관리자 차감:</strong> 관리자 권한으로 차감</li>
                                <li><strong>포인트 환불:</strong> 이전 사용분을 환불 처리</li>
                                <li><strong>포인트 만료:</strong> 포인트를 강제로 만료 처리</li>
                            </ul>
                        </div>
                    </div>

                    <!-- 최근 거래 내역 -->
                    @if($recentLogs->count() > 0)
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0">최근 거래 내역</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>일시</th>
                                            <th>유형</th>
                                            <th>금액</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentLogs as $log)
                                        <tr>
                                            <td>{{ $log->created_at->format('m/d H:i') }}</td>
                                            <td>
                                                @php
                                                    $badgeClass = match($log->transaction_type) {
                                                        'earn' => 'bg-success',
                                                        'use' => 'bg-danger',
                                                        'refund' => 'bg-info',
                                                        'expire' => 'bg-warning',
                                                        'admin' => 'bg-primary',
                                                        default => 'bg-secondary'
                                                    };
                                                @endphp
                                                <span class="badge {{ $badgeClass }} small">
                                                    {{ $log->transaction_type }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="{{ $log->amount >= 0 ? 'text-success' : 'text-danger' }} small">
                                                    {{ $log->amount >= 0 ? '+' : '' }}{{ number_format($log->amount, 0) }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- 만료 예정 포인트 -->
                    @if($expiringPoints->count() > 0)
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0">만료 예정 포인트</h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning small">
                                <strong>주의:</strong> 30일 이내에 만료 예정인 포인트가 {{ number_format($expiringPoints->sum('amount'), 0) }}P 있습니다.
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>금액</th>
                                            <th>만료일</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($expiringPoints->take(5) as $expiry)
                                        <tr>
                                            <td>{{ number_format($expiry->amount, 0) }}P</td>
                                            <td>{{ \Carbon\Carbon::parse($expiry->expires_at)->format('m/d') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const adjustmentType = document.getElementById('adjustment_type');
    const expiresAtField = document.getElementById('expires_at');
    const expiresAtGroup = expiresAtField.closest('.mb-3');

    function toggleExpiresField() {
        if (adjustmentType.value === 'earn') {
            expiresAtGroup.style.display = 'block';
        } else {
            expiresAtGroup.style.display = 'none';
            expiresAtField.value = '';
        }
    }

    adjustmentType.addEventListener('change', toggleExpiresField);
    toggleExpiresField(); // 초기 상태 설정
});
</script>
@endsection