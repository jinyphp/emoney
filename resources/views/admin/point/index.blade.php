@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '포인트 관리')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">포인트 관리</h2>
                    <p class="text-muted mb-0">사용자 포인트 잔액 및 관리</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#pointAllocateModal">
                        <i class="fe fe-plus me-1"></i>포인트적립
                    </button>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#pointDeductModal">
                        <i class="fe fe-minus me-1"></i>포인트차감
                    </button>
                </div>
            </div>

            <!-- 통계 카드 -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fe fe-users text-primary fs-3"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-0">{{ number_format($statistics['total_users']) }}</h5>
                                    <p class="text-muted mb-0">전체 사용자</p>
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
                                    <i class="fe fe-star text-warning fs-3"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-0">{{ number_format($statistics['total_balance'], 0) }}</h5>
                                    <p class="text-muted mb-0">총 잔액</p>
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
                                    <i class="fe fe-trending-up text-success fs-3"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-0">{{ number_format($statistics['total_earned'], 0) }}</h5>
                                    <p class="text-muted mb-0">총 적립</p>
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
                                    <i class="fe fe-trending-down text-danger fs-3"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-0">{{ number_format($statistics['total_used'], 0) }}</h5>
                                    <p class="text-muted mb-0">총 사용</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 추가 통계 카드 -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="mb-1">평균 보유량</h6>
                            <h4 class="text-info">{{ number_format($statistics['avg_balance'], 0) }}P</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="mb-1">보유 사용자</h6>
                            <h4 class="text-success">{{ number_format($statistics['users_with_balance']) }}명</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <h6 class="mb-1">만료 포인트</h6>
                            <h4 class="text-warning">{{ number_format($statistics['total_expired'], 0) }}P</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="mb-2">상위 보유자</h6>
                            @foreach($statistics['top_holders']->take(3) as $holder)
                                <div class="d-flex justify-content-between mb-1">
                                    <small>{{ $holder->user->name ?? 'N/A' }}</small>
                                    <small>{{ number_format($holder->balance, 0) }}P</small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- 검색 및 필터 -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.auth.point.index') }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">검색</label>
                                <input type="text" name="search" class="form-control" placeholder="이메일, 이름, 사용자ID 검색" value="{{ $request->search }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">최소 잔액</label>
                                <input type="number" name="balance_min" class="form-control" placeholder="0" value="{{ $request->balance_min }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">최대 잔액</label>
                                <input type="number" name="balance_max" class="form-control" placeholder="무제한" value="{{ $request->balance_max }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">정렬</label>
                                <select name="sort_by" class="form-select">
                                    <option value="balance" {{ $request->sort_by == 'balance' ? 'selected' : '' }}>잔액순</option>
                                    <option value="total_earned" {{ $request->sort_by == 'total_earned' ? 'selected' : '' }}>적립순</option>
                                    <option value="total_used" {{ $request->sort_by == 'total_used' ? 'selected' : '' }}>사용순</option>
                                    <option value="created_at" {{ $request->sort_by == 'created_at' ? 'selected' : '' }}>가입순</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">검색</button>
                                    <a href="{{ route('admin.auth.point.index') }}" class="btn btn-outline-secondary">초기화</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 최근 포인트 활동 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fe fe-activity me-2"></i>최근 포인트 활동
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">최근 관리자 조정 내역</h6>
                            <div id="adminRecentAdjustments">
                                @if(isset($recent_admin_adjustments) && count($recent_admin_adjustments) > 0)
                                    @foreach($recent_admin_adjustments as $adjustment)
                                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                        <div>
                                            <span class="badge {{ $adjustment->amount > 0 ? 'bg-success' : 'bg-danger' }} me-2">
                                                {{ $adjustment->amount > 0 ? '+' : '' }}{{ number_format($adjustment->amount) }}P
                                            </span>
                                            <small class="text-muted">{{ $adjustment->reason }}</small>
                                        </div>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($adjustment->created_at)->format('m/d H:i') }}</small>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="text-center text-muted py-3">
                                        <i class="fe fe-inbox fs-3 mb-2"></i>
                                        <p class="mb-0">최근 관리자 조정 내역이 없습니다.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">최근 회원 포인트 활동</h6>
                            <div id="userRecentActivity">
                                @if(isset($recent_user_activities) && count($recent_user_activities) > 0)
                                    @foreach($recent_user_activities as $activity)
                                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                        <div>
                                            <span class="fw-semibold">{{ $activity->user_name ?? '알 수 없음' }}</span>
                                            <span class="badge {{ $activity->amount > 0 ? 'bg-success' : 'bg-danger' }} ms-2">
                                                {{ $activity->amount > 0 ? '+' : '' }}{{ number_format($activity->amount) }}P
                                            </span>
                                            <br>
                                            <small class="text-muted">{{ $activity->transaction_type }}</small>
                                        </div>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($activity->created_at)->format('m/d H:i') }}</small>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="text-center text-muted py-3">
                                        <i class="fe fe-users fs-3 mb-2"></i>
                                        <p class="mb-0">최근 회원 활동이 없습니다.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 포인트 목록 -->
            <div class="card">
                <div class="card-body">
                    @if(isset($points) && $points->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>사용자</th>
                                        <th>잔액</th>
                                        <th>총 적립</th>
                                        <th>총 사용</th>
                                        <th>총 만료</th>
                                        <th>가입일</th>
                                        <th>액션</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($points as $point)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $point->user->name ?? 'N/A' }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $point->user->email ?? 'N/A' }}</small>
                                                    <br>
                                                    <small class="text-muted">ID: {{ $point->user_id }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary fs-6">
                                                    {{ number_format($point->balance, 0) }}P
                                                </span>
                                            </td>
                                            <td class="text-success">
                                                <strong>+{{ number_format($point->total_earned, 0) }}</strong>
                                            </td>
                                            <td class="text-danger">
                                                <strong>-{{ number_format($point->total_used, 0) }}</strong>
                                            </td>
                                            <td class="text-warning">
                                                <strong>-{{ number_format($point->total_expired, 0) }}</strong>
                                            </td>
                                            <td>{{ $point->created_at->format('Y-m-d') }}</td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        액션
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#" onclick="viewDetails({{ $point->user_id }})">
                                                            <i class="fe fe-eye me-2"></i>상세보기
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="adjustPoints({{ $point->user_id }})">
                                                            <i class="fe fe-edit-2 me-2"></i>포인트 조정
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="viewHistory({{ $point->user_id }})">
                                                            <i class="fe fe-list me-2"></i>거래내역
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="viewExpiry({{ $point->user_id }})">
                                                            <i class="fe fe-clock me-2"></i>만료예정
                                                        </a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if(method_exists($points, 'links'))
                            <div class="mt-3">
                                {{ $points->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <i class="fe fe-users text-primary fs-1 mb-3"></i>
                                            <h5>포인트 계정 생성</h5>
                                            <p class="text-muted mb-3">
                                                회원들이 거래를 시작하면<br>
                                                자동으로 포인트 계정이 생성됩니다.
                                            </p>
                                            <small class="text-muted">
                                                또는 헤더 버튼으로 직접 포인트를 지급할 수 있습니다.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <i class="fe fe-trending-up text-info fs-1 mb-3"></i>
                                            <h5>포인트 활동 대기 중</h5>
                                            <p class="text-muted mb-3">
                                                포인트 적립/차감 버튼을 사용하여<br>
                                                첫 번째 포인트 활동을 시작하세요.
                                            </p>
                                            <div class="d-flex gap-2 justify-content-center">
                                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#pointAllocateModal">
                                                    <i class="fe fe-plus me-1"></i>포인트적립
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#pointDeductModal">
                                                    <i class="fe fe-minus me-1"></i>포인트차감
                                                </button>
                                            </div>
                                        </div>
                                    </div>
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
// CSRF 토큰 설정
const csrfToken = '{{ csrf_token() }}';

// 회원 검색 및 포인트 조정 기능은 헤더 모달로 이동됨
// 이전 인라인 폼 기능은 제거되었음

// 회원 검색 함수
async function searchMember() {
    const email = document.getElementById('memberEmail').value.trim();
    if (!email) {
        alert('이메일을 입력해주세요.');
        return;
    }

    try {
        const response = await fetch('/admin/auth/point/search-member', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ email: email })
        });

        const result = await response.json();

        if (result.success) {
            displayMemberInfo(result.member, result.point_info);
            loadRecentAdjustments(result.member.id);
        } else {
            alert(result.message || '회원을 찾을 수 없습니다.');
            document.getElementById('memberInfo').style.display = 'none';
        }
    } catch (error) {
        console.error('회원 검색 오류:', error);
        alert('회원 검색 중 오류가 발생했습니다.');
    }
}

// 회원 정보 표시 함수
function displayMemberInfo(member, pointInfo) {
    const memberDetails = document.getElementById('memberDetails');
    const memberInfo = document.getElementById('memberInfo');

    memberDetails.innerHTML = `
        <div class="row">
            <div class="col-md-3">
                <strong>이름:</strong> ${member.name || 'N/A'}
            </div>
            <div class="col-md-4">
                <strong>이메일:</strong> ${member.email}
            </div>
            <div class="col-md-2">
                <strong>사용자 ID:</strong> ${member.id}
            </div>
            <div class="col-md-3">
                <strong>현재 포인트:</strong>
                <span class="badge bg-primary fs-6">${Number(pointInfo.balance || 0).toLocaleString()}P</span>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-3">
                <strong>총 적립:</strong> <span class="text-success">+${Number(pointInfo.total_earned || 0).toLocaleString()}P</span>
            </div>
            <div class="col-md-3">
                <strong>총 사용:</strong> <span class="text-danger">-${Number(pointInfo.total_used || 0).toLocaleString()}P</span>
            </div>
            <div class="col-md-3">
                <strong>총 만료:</strong> <span class="text-warning">-${Number(pointInfo.total_expired || 0).toLocaleString()}P</span>
            </div>
            <div class="col-md-3">
                <strong>가입일:</strong> ${new Date(member.created_at).toLocaleDateString()}
            </div>
        </div>
    `;

    memberInfo.style.display = 'block';

    // 선택된 회원 ID 저장
    memberInfo.dataset.memberId = member.id;
    memberInfo.dataset.memberUuid = member.uuid;
}

// 포인트 조정 함수
async function adjustMemberPoints() {
    const memberInfo = document.getElementById('memberInfo');
    const memberId = memberInfo.dataset.memberId;
    const memberUuid = memberInfo.dataset.memberUuid;
    const amount = document.getElementById('adjustAmount').value;
    const reason = document.getElementById('adjustReason').value.trim();
    const referenceType = document.getElementById('adjustReferenceType').value;

    if (!memberId) {
        alert('먼저 회원을 검색해주세요.');
        return;
    }

    if (!amount || amount == 0) {
        alert('조정할 포인트 금액을 입력해주세요.');
        return;
    }

    if (!reason) {
        alert('조정 사유를 입력해주세요.');
        return;
    }

    const confirmMessage = `${Number(amount) > 0 ? '지급' : '차감'}: ${Math.abs(amount)}P\n사유: ${reason}\n\n포인트를 조정하시겠습니까?`;
    if (!confirm(confirmMessage)) {
        return;
    }

    try {
        const response = await fetch('/admin/auth/point/adjust', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                member_id: memberId,
                member_uuid: memberUuid,
                amount: Number(amount),
                reason: reason,
                reference_type: referenceType
            })
        });

        const result = await response.json();

        if (result.success) {
            alert('포인트 조정이 완료되었습니다.');

            // 폼 초기화
            document.getElementById('adjustAmount').value = '';
            document.getElementById('adjustReason').value = '';

            // 회원 정보 다시 검색하여 업데이트
            document.getElementById('memberEmail').value = result.member.email;
            searchMember();
        } else {
            alert(result.message || '포인트 조정 중 오류가 발생했습니다.');
        }
    } catch (error) {
        console.error('포인트 조정 오류:', error);
        alert('포인트 조정 중 오류가 발생했습니다.');
    }
}

// 최근 관리자 조정 내역 로드
async function loadRecentAdjustments(memberId) {
    try {
        const response = await fetch(`/admin/auth/point/recent-adjustments/${memberId}`);
        const result = await response.json();

        const container = document.getElementById('recentAdjustments');

        if (result.success && result.adjustments.length > 0) {
            container.innerHTML = result.adjustments.map(adj => `
                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                    <div>
                        <span class="badge ${adj.amount > 0 ? 'bg-success' : 'bg-danger'} me-2">
                            ${adj.amount > 0 ? '+' : ''}${Number(adj.amount).toLocaleString()}P
                        </span>
                        <small>${adj.reason}</small>
                    </div>
                    <div class="text-end">
                        <small class="text-muted">${new Date(adj.created_at).toLocaleString()}</small>
                        <br>
                        <small class="text-muted">관리자: ${adj.admin_name || 'N/A'}</small>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<div class="text-center text-muted py-3"><small>최근 조정 내역이 없습니다.</small></div>';
        }
    } catch (error) {
        console.error('조정 내역 로드 오류:', error);
    }
}

// 기존 함수들
function viewDetails(userId) {
    alert('포인트 상세보기 기능 준비중입니다.');
}

function adjustPoints(userId) {
    const amount = prompt('조정할 포인트를 입력하세요 (음수는 차감):');
    if (amount && !isNaN(amount)) {
        const reason = prompt('조정 사유를 입력하세요:');
        if (reason) {
            alert('포인트 조정 기능 준비중입니다.');
        }
    }
}

function viewHistory(userId) {
    alert('거래내역 보기 기능 준비중입니다.');
}

function viewExpiry(userId) {
    alert('만료예정 포인트 보기 기능 준비중입니다.');
}

// 모달용 회원 검색 함수 (JwtAuth 파사드 사용)
async function searchMemberForModal(modalType) {
    const emailInput = document.getElementById(`${modalType}Email`);
    const email = emailInput.value.trim();

    if (!email) {
        alert('이메일을 입력해주세요.');
        return;
    }

    const loadingBtn = document.getElementById(`${modalType}SearchBtn`);
    const originalText = loadingBtn.innerHTML;
    loadingBtn.innerHTML = '<i class="spinner-border spinner-border-sm me-1"></i>검색중...';
    loadingBtn.disabled = true;

    try {
        const response = await fetch('/admin/auth/point/search-member-sharded', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ email: email })
        });

        const result = await response.json();

        if (result.success) {
            displayModalMemberInfo(result.member, result.point_info, modalType);
        } else {
            document.getElementById(`${modalType}MemberInfo`).style.display = 'none';
            alert(result.message || '회원을 찾을 수 없습니다.');
        }
    } catch (error) {
        console.error('회원 검색 오류:', error);
        alert('회원 검색 중 오류가 발생했습니다.');
    } finally {
        loadingBtn.innerHTML = originalText;
        loadingBtn.disabled = false;
    }
}

// 모달용 회원 정보 표시
function displayModalMemberInfo(member, pointInfo, modalType) {
    const memberInfoDiv = document.getElementById(`${modalType}MemberInfo`);

    memberInfoDiv.innerHTML = `
        <div class="alert alert-info">
            <h6 class="mb-2"><i class="fe fe-user me-1"></i>${member.name} (${member.email})</h6>
            <div class="row">
                <div class="col-md-6">
                    <strong>현재 잔액:</strong> <span class="text-primary">${Number(pointInfo.balance || 0).toLocaleString()}P</span>
                </div>
                <div class="col-md-6">
                    <strong>총 적립:</strong> <span class="text-success">${Number(pointInfo.total_earned || 0).toLocaleString()}P</span>
                </div>
            </div>
        </div>
    `;

    memberInfoDiv.style.display = 'block';

    // 선택된 회원 정보 저장
    memberInfoDiv.dataset.memberId = member.id;
    memberInfoDiv.dataset.memberUuid = member.uuid;

    // 포인트 입력 폼 활성화
    document.getElementById(`${modalType}Form`).style.display = 'block';
}

// 포인트 적립 처리
async function processPointAllocation() {
    const memberInfo = document.getElementById('allocateMemberInfo');
    const memberId = memberInfo.dataset.memberId;
    const memberUuid = memberInfo.dataset.memberUuid;
    const amount = document.getElementById('allocateAmount').value;
    const reason = document.getElementById('allocateReason').value.trim();

    if (!memberId) {
        alert('먼저 회원을 검색해주세요.');
        return;
    }

    if (!amount || Number(amount) <= 0) {
        alert('올바른 적립 포인트를 입력해주세요.');
        return;
    }

    if (!reason) {
        alert('적립 사유를 입력해주세요.');
        return;
    }

    if (!confirm(`${Number(amount).toLocaleString()}P를 적립하시겠습니까?`)) {
        return;
    }

    await processPointAdjustment(memberId, memberUuid, Number(amount), reason, 'admin_grant', 'allocate');
}

// 포인트 차감 처리
async function processPointDeduction() {
    const memberInfo = document.getElementById('deductMemberInfo');
    const memberId = memberInfo.dataset.memberId;
    const memberUuid = memberInfo.dataset.memberUuid;
    const amount = document.getElementById('deductAmount').value;
    const reason = document.getElementById('deductReason').value.trim();

    if (!memberId) {
        alert('먼저 회원을 검색해주세요.');
        return;
    }

    if (!amount || Number(amount) <= 0) {
        alert('올바른 차감 포인트를 입력해주세요.');
        return;
    }

    if (!reason) {
        alert('차감 사유를 입력해주세요.');
        return;
    }

    if (!confirm(`${Number(amount).toLocaleString()}P를 차감하시겠습니까?`)) {
        return;
    }

    await processPointAdjustment(memberId, memberUuid, -Number(amount), reason, 'admin_deduct', 'deduct');
}

// 포인트 조정 공통 처리
async function processPointAdjustment(memberId, memberUuid, amount, reason, referenceType, modalType) {
    try {
        const response = await fetch('/admin/auth/point/adjust', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                member_id: memberId,
                member_uuid: memberUuid,
                amount: amount,
                reason: reason,
                reference_type: referenceType
            })
        });

        const result = await response.json();

        if (result.success) {
            alert('포인트 조정이 완료되었습니다.');

            // 모달 닫기
            const modal = bootstrap.Modal.getInstance(document.getElementById(`point${modalType.charAt(0).toUpperCase() + modalType.slice(1)}Modal`));
            modal.hide();

            // 폼 초기화
            resetModalForm(modalType);

            // 페이지 새로고침 (통계 업데이트)
            location.reload();
        } else {
            alert(result.message || '포인트 조정 중 오류가 발생했습니다.');
        }
    } catch (error) {
        console.error('포인트 조정 오류:', error);
        alert('포인트 조정 중 오류가 발생했습니다.');
    }
}

// 모달 폼 초기화
function resetModalForm(modalType) {
    document.getElementById(`${modalType}Email`).value = '';
    document.getElementById(`${modalType}Amount`).value = '';
    document.getElementById(`${modalType}Reason`).value = '';
    document.getElementById(`${modalType}MemberInfo`).style.display = 'none';
    document.getElementById(`${modalType}Form`).style.display = 'none';
}

// 모달 닫힐 때 폼 초기화
document.addEventListener('DOMContentLoaded', function() {
    ['allocate', 'deduct'].forEach(type => {
        const modalEl = document.getElementById(`point${type.charAt(0).toUpperCase() + type.slice(1)}Modal`);
        modalEl.addEventListener('hidden.bs.modal', function() {
            resetModalForm(type);
        });
    });
});
</script>

<!-- 포인트 적립 모달 -->
<div class="modal fade" id="pointAllocateModal" tabindex="-1" aria-labelledby="pointAllocateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pointAllocateModalLabel">
                    <i class="fe fe-plus text-success me-2"></i>포인트 적립
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- 회원 검색 -->
                <div class="mb-4">
                    <label for="allocateEmail" class="form-label">회원 이메일</label>
                    <div class="input-group">
                        <input type="email" class="form-control" id="allocateEmail" placeholder="회원 이메일을 입력하세요" required>
                        <button class="btn btn-primary" type="button" id="allocateSearchBtn" onclick="searchMemberForModal('allocate')">
                            <i class="fe fe-search me-1"></i>검색
                        </button>
                    </div>
                </div>

                <!-- 회원 정보 표시 -->
                <div id="allocateMemberInfo" style="display: none;"></div>

                <!-- 포인트 적립 폼 -->
                <div id="allocateForm" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="allocateAmount" class="form-label">적립 포인트</label>
                            <input type="number" class="form-control" id="allocateAmount" placeholder="적립할 포인트" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label for="allocateReason" class="form-label">적립 사유</label>
                            <input type="text" class="form-control" id="allocateReason" placeholder="적립 사유를 입력하세요" required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-success" onclick="processPointAllocation()">
                    <i class="fe fe-plus me-1"></i>포인트 적립
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 포인트 차감 모달 -->
<div class="modal fade" id="pointDeductModal" tabindex="-1" aria-labelledby="pointDeductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pointDeductModalLabel">
                    <i class="fe fe-minus text-danger me-2"></i>포인트 차감
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- 회원 검색 -->
                <div class="mb-4">
                    <label for="deductEmail" class="form-label">회원 이메일</label>
                    <div class="input-group">
                        <input type="email" class="form-control" id="deductEmail" placeholder="회원 이메일을 입력하세요" required>
                        <button class="btn btn-primary" type="button" id="deductSearchBtn" onclick="searchMemberForModal('deduct')">
                            <i class="fe fe-search me-1"></i>검색
                        </button>
                    </div>
                </div>

                <!-- 회원 정보 표시 -->
                <div id="deductMemberInfo" style="display: none;"></div>

                <!-- 포인트 차감 폼 -->
                <div id="deductForm" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="deductAmount" class="form-label">차감 포인트</label>
                            <input type="number" class="form-control" id="deductAmount" placeholder="차감할 포인트" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label for="deductReason" class="form-label">차감 사유</label>
                            <input type="text" class="form-control" id="deductReason" placeholder="차감 사유를 입력하세요" required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-danger" onclick="processPointDeduction()">
                    <i class="fe fe-minus me-1"></i>포인트 차감
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
