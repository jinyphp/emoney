@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '출금 신청 관리')

@section('content')
<div class="container-fluid">
    <!-- 헤더 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2">출금 신청 관리</h1>
                    <p class="text-muted mb-0">사용자 이머니 출금 신청을 관리하고 승인/거부할 수 있습니다.</p>
                </div>
                <div>
                    <a href="{{ route('admin.auth.emoney.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> 이머니 관리로
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 통계 카드 -->
    <div class="row mb-4">
        <div class="col-sm-6 col-xl-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">총 신청</div>
                            <div class="h4 mb-0">{{ number_format($statistics['total_withdrawals']) }}</div>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-file-earmark-text text-primary" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">대기중</div>
                            <div class="h4 mb-0 text-warning">{{ number_format($statistics['pending_withdrawals']) }}</div>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-clock text-warning" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">승인됨</div>
                            <div class="h4 mb-0 text-success">{{ number_format($statistics['approved_withdrawals']) }}</div>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="text-muted small">총 출금 금액</div>
                            <div class="h4 mb-0 text-danger">₩{{ number_format($statistics['total_amount']) }}</div>
                        </div>
                        <div class="align-self-center">
                            <i class="bi bi-arrow-down-circle text-danger" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 필터 -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">검색 및 필터</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.auth.emoney.withdrawals.index') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">검색</label>
                        <input type="text" class="form-control" name="search" value="{{ $request->search }}"
                               placeholder="이메일, 이름, 예금주명 검색">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">상태</label>
                        <select class="form-select" name="status">
                            <option value="">전체</option>
                            <option value="pending" {{ $request->status === 'pending' ? 'selected' : '' }}>대기중</option>
                            <option value="approved" {{ $request->status === 'approved' ? 'selected' : '' }}>승인됨</option>
                            <option value="rejected" {{ $request->status === 'rejected' ? 'selected' : '' }}>거부됨</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">시작일</label>
                        <input type="date" class="form-control" name="date_from" value="{{ $request->date_from }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">종료일</label>
                        <input type="date" class="form-control" name="date_to" value="{{ $request->date_to }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> 검색
                            </button>
                            <a href="{{ route('admin.auth.emoney.withdrawals.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise"></i> 초기화
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 목록 -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">출금 신청 목록 ({{ $withdrawals->total() }}건)</h6>
            <div class="text-muted small">
                {{ $withdrawals->firstItem() ?? 0 }}-{{ $withdrawals->lastItem() ?? 0 }} of {{ $withdrawals->total() }}
            </div>
        </div>
        <div class="card-body p-0">
            @if($withdrawals->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>사용자</th>
                                <th>출금 정보</th>
                                <th>출금 계좌</th>
                                <th>신청 정보</th>
                                <th>처리일시</th>
                                <th>작업</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($withdrawals as $withdrawal)
                            <tr>
                                <td>{{ $withdrawal->id }}</td>
                                <td>
                                    <div>
                                        <strong>{{ $withdrawal->user_name ?? 'N/A' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $withdrawal->user_email }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong class="text-danger">₩{{ number_format($withdrawal->amount) }}</strong>
                                        <small class="text-muted">(수수료: ₩{{ number_format($withdrawal->fee ?? 0) }})</small>
                                        <br>
                                        <small class="text-info">총 차감: <strong>₩{{ number_format($withdrawal->amount + ($withdrawal->fee ?? 0)) }}</strong></small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $withdrawal->bank_name ?? 'N/A' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $withdrawal->account_number ?? 'N/A' }}</small>
                                        <br>
                                        <small class="text-muted">{{ $withdrawal->account_holder ?? 'N/A' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ \Carbon\Carbon::parse($withdrawal->created_at)->format('Y-m-d H:i') }}</strong>
                                        <br>
                                        @if($withdrawal->status === 'pending')
                                            <span class="badge bg-warning">대기중</span>
                                        @elseif($withdrawal->status === 'approved')
                                            <span class="badge bg-success">승인됨</span>
                                        @elseif($withdrawal->status === 'rejected')
                                            <span class="badge bg-danger">거부됨</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($withdrawal->checked_at)
                                        {{ \Carbon\Carbon::parse($withdrawal->checked_at)->format('Y-m-d H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($withdrawal->status === 'pending')
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-success"
                                                    onclick="approveWithdrawal({{ $withdrawal->id }})">
                                                <i class="bi bi-check"></i> 승인
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="rejectWithdrawal({{ $withdrawal->id }})">
                                                <i class="bi bi-x"></i> 거부
                                            </button>
                                        </div>
                                    @else
                                        <small class="text-muted">처리완료</small>
                                        @if($withdrawal->admin_memo)
                                            <br><small class="text-muted">{{ $withdrawal->admin_memo }}</small>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- 페이지네이션 -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $withdrawals->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox text-muted mb-3" style="font-size: 3rem;"></i>
                    <p class="text-muted">출금 신청 내역이 없습니다.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- 승인 모달 -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">출금 신청 승인</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="approveForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>주의:</strong> 출금을 승인하면 사용자의 이머니 잔액에서 해당 금액이 차감됩니다.
                    </div>
                    <p>이 출금 신청을 승인하시겠습니까?</p>
                    <div class="mb-3">
                        <label class="form-label">관리자 메모 (선택사항)</label>
                        <textarea class="form-control" name="admin_memo" rows="3" placeholder="승인 사유나 추가 메모를 입력하세요"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="submit" class="btn btn-success">승인</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 거부 모달 -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">출금 신청 거부</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>이 출금 신청을 거부하시겠습니까?</p>
                    <div class="mb-3">
                        <label class="form-label">거부 사유 <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="admin_memo" rows="3" placeholder="거부 사유를 입력하세요" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="submit" class="btn btn-danger">거부</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function approveWithdrawal(id) {
    const form = document.getElementById('approveForm');
    form.action = '{{ route("admin.auth.emoney.withdrawals.approve", "PLACEHOLDER") }}'.replace('PLACEHOLDER', id);
    new bootstrap.Modal(document.getElementById('approveModal')).show();
}

function rejectWithdrawal(id) {
    const form = document.getElementById('rejectForm');
    form.action = '{{ route("admin.auth.emoney.withdrawals.reject", "PLACEHOLDER") }}'.replace('PLACEHOLDER', id);
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}
</script>
@endsection
