@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '포인트 계정 상세')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">포인트 계정 상세</h2>
                    <p class="text-muted mb-0">{{ $userPoint->user->name ?? '알 수 없는 사용자' }}의 포인트 정보</p>
                </div>
                <div>
                    <a href="{{ route('admin.auth.point.edit', $userPoint->id) }}" class="btn btn-warning me-2">
                        <i class="fe fe-edit me-2"></i>편집
                    </a>
                    <a href="{{ route('admin.auth.point.index') }}" class="btn btn-outline-primary">
                        <i class="fe fe-arrow-left me-2"></i>목록으로
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <!-- 기본 정보 -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">기본 정보</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <th>사용자 ID:</th>
                                    <td>{{ $userPoint->user_id }}</td>
                                </tr>
                                <tr>
                                    <th>이름:</th>
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
                                <tr>
                                    <th>생성일:</th>
                                    <td>{{ $userPoint->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>수정일:</th>
                                    <td>{{ $userPoint->updated_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- 만료 예정 포인트 -->
                    @if($expiringPoints->count() > 0)
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0">만료 예정 포인트 (30일 이내)</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>금액</th>
                                            <th>만료일</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($expiringPoints as $expiry)
                                        <tr>
                                            <td>{{ number_format($expiry->amount, 2) }} P</td>
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

                <!-- 통계 및 거래 내역 -->
                <div class="col-lg-8">
                    <!-- 통계 카드 -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body py-3">
                                    <h5 class="mb-0">{{ number_format($statistics['total_transactions']) }}</h5>
                                    <small class="text-muted">총 거래수</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body py-3">
                                    <h5 class="mb-0">{{ number_format($statistics['transactions_this_month']) }}</h5>
                                    <small class="text-muted">이번 달 거래</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body py-3">
                                    <h5 class="mb-0 text-success">+{{ number_format($statistics['earned_this_month'], 0) }}</h5>
                                    <small class="text-muted">이번 달 적립</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body py-3">
                                    <h5 class="mb-0 text-danger">{{ number_format($statistics['used_this_month'], 0) }}</h5>
                                    <small class="text-muted">이번 달 사용</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 거래 내역 -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">거래 내역</h5>
                            <div>
                                <a href="{{ route('admin.auth.point.edit', $userPoint->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fe fe-plus me-1"></i>포인트 조정
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- 필터 -->
                            <form method="GET" class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <select name="transaction_type" class="form-select form-select-sm">
                                        <option value="">전체 거래유형</option>
                                        @foreach($transactionTypes as $type => $label)
                                            <option value="{{ $type }}" {{ $request->transaction_type == $type ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="date" name="date_from" class="form-control form-control-sm"
                                           value="{{ $request->date_from }}" placeholder="시작일">
                                </div>
                                <div class="col-md-3">
                                    <input type="date" name="date_to" class="form-control form-control-sm"
                                           value="{{ $request->date_to }}" placeholder="종료일">
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        <i class="fe fe-search me-1"></i>검색
                                    </button>
                                    <a href="{{ route('admin.auth.point.show', $userPoint->id) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fe fe-refresh-cw me-1"></i>초기화
                                    </a>
                                </div>
                            </form>

                            <!-- 거래 내역 테이블 -->
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>일시</th>
                                            <th>유형</th>
                                            <th>금액</th>
                                            <th>이전 잔액</th>
                                            <th>이후 잔액</th>
                                            <th>사유</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($logs as $log)
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
                                                <span class="badge {{ $badgeClass }}">
                                                    {{ $transactionTypes[$log->transaction_type] ?? $log->transaction_type }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="{{ $log->amount >= 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ $log->amount >= 0 ? '+' : '' }}{{ number_format($log->amount, 2) }}
                                                </span>
                                            </td>
                                            <td>{{ number_format($log->balance_before, 2) }}</td>
                                            <td>{{ number_format($log->balance_after, 2) }}</td>
                                            <td>{{ $log->reason ?: '-' }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                거래 내역이 없습니다.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- 페이지네이션 -->
                            @if($logs->hasPages())
                                <div class="d-flex justify-content-center">
                                    {{ $logs->withQueryString()->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- 위험 구역 -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card border-danger">
                        <div class="card-header bg-danger text-white">
                            <h6 class="card-title mb-0"><i class="fe fe-alert-triangle me-2"></i>위험 구역</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">아래 작업들은 되돌릴 수 없습니다. 신중하게 진행하세요.</p>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="fe fe-trash-2 me-2"></i>포인트 계정 삭제
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 삭제 확인 모달 -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">포인트 계정 삭제</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.auth.point.destroy', $userPoint->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>경고!</strong> 이 작업은 되돌릴 수 없습니다.
                    </div>

                    <div class="mb-3">
                        <label for="deletion_type" class="form-label">삭제 유형</label>
                        <select class="form-select" name="deletion_type" required>
                            <option value="soft">소프트 삭제 (잔액만 0으로 초기화)</option>
                            <option value="hard">완전 삭제 (모든 데이터 삭제)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">관리자 비밀번호 확인</label>
                        <input type="password" class="form-control" name="confirm_password" required>
                    </div>

                    <div class="mb-3">
                        <label for="deletion_reason" class="form-label">삭제 사유</label>
                        <textarea class="form-control" name="deletion_reason" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="submit" class="btn btn-danger">삭제 확인</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
