<li class="nav-item">
                <div class="navbar-heading">금융</div>
            </li>

            {{-- 금융 관리 --}}
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.auth.bank.index') }}">
                    <i class="fe fe-credit-card me-2"></i>은행목록
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#navFinance"
                    aria-expanded="false" aria-controls="navFinance">
                    <i class="nav-icon fe fe-dollar-sign me-2"></i>
                    이머니
                </a>
                <div id="navFinance" class="collapse" data-bs-parent="#sideNavbar">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.emoney.index') }}">
                                <i class="fe fe-credit-card me-2"></i>이머니 관리
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.emoney.bank.index') }}">
                                <i class="fe fe-home me-2"></i>은행 계좌
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.emoney.deposits') }}">
                                <i class="fe fe-arrow-down-circle me-2"></i>입금 내역
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.emoney.withdrawals') }}">
                                <i class="fe fe-arrow-up-circle me-2"></i>출금 내역
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.emoney.index') }}">
                                <i class="fe fe-list me-2"></i>거래 로그
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
                    포인트
                </a>
                <div id="navPoint" class="collapse" data-bs-parent="#sideNavbar">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.point.index') }}">
                                <i class="fe fe-star me-2"></i>포인트 관리
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.point.log') }}">
                                <i class="fe fe-list me-2"></i>거래 로그
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.point.expiry') }}">
                                <i class="fe fe-clock me-2"></i>만료 관리
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.point.stats') }}">
                                <i class="fe fe-bar-chart-2 me-2"></i>통계
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
