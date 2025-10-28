<li class="nav-item">
    <div class="navbar-heading">금융</div>
</li>

{{-- 이머니 관리 --}}
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#navEmoney" aria-expanded="false"
        aria-controls="navEmoney">
        <i class="nav-icon fe fe-dollar-sign me-2"></i>
        이머니 관리
    </a>
    <div id="navEmoney" class="collapse" data-bs-parent="#sideNavbar">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.auth.emoney.index') }}">
                    <i class="fe fe-users me-2"></i>사용자 지갑
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.auth.emoney.deposits.index') }}">
                    <i class="fe fe-plus-circle me-2"></i>충전 신청 관리
                    @php
                        try {
                            $pendingDeposits = DB::table('user_emoney_deposits')->where('status', 'pending')->count();
                            if ($pendingDeposits > 0) {
                                echo '<span class="badge bg-warning ms-2">' . $pendingDeposits . '</span>';
                            }
                        } catch (\Exception $e) {
                            // 테이블이 존재하지 않는 경우 무시
                        }
                    @endphp
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.auth.emoney.withdrawals.index') }}">
                    <i class="fe fe-minus-circle me-2"></i>출금 신청 관리
                    @php
                        try {
                            $pendingWithdrawals = DB::table('user_emoney_withdrawals')
                                ->where('status', 'pending')
                                ->count();
                            if ($pendingWithdrawals > 0) {
                                echo '<span class="badge bg-warning ms-2">' . $pendingWithdrawals . '</span>';
                            }
                        } catch (\Exception $e) {
                            // 테이블이 존재하지 않는 경우 무시
                        }
                    @endphp
                </a>
            </li>
        </ul>
    </div>
</li>

{{-- 포인트 관리 --}}
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#navPoint"
        aria-expanded="false" aria-controls="navPoint">
        <i class="nav-icon fe fe-star me-2"></i>
        포인트 관리
    </a>
    <div id="navPoint" class="collapse" data-bs-parent="#sideNavbar">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.auth.point.index') }}">
                    <i class="fe fe-star me-2"></i>포인트 계정
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.auth.point.log') }}">
                    <i class="fe fe-list me-2"></i>포인트 로그
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.auth.point.expiry.index') }}">
                    <i class="fe fe-clock me-2"></i>만료 관리
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.auth.point.stats') }}">
                    <i class="fe fe-bar-chart-2 me-2"></i>포인트 통계
                </a>
            </li>
        </ul>
    </div>
</li>

{{-- 은행 관리 --}}
<li class="nav-item">
    <a class="nav-link" href="{{ route('admin.auth.bank.index') }}">
        <i class="fe fe-credit-card me-2"></i>은행 계좌 관리
    </a>
</li>

{{-- 은행 관리 --}}
<li class="nav-item">
    <a class="nav-link" href="{{ route('admin.auth.emoney.setting.index') }}">
        이머니 설정
    </a>
</li>
