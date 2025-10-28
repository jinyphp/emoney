@extends($layout ?? 'jiny-auth::layouts.home')

@section('title', '계좌 관리')

@section('content')
<div class="container-fluid">
    <!-- 헤더 -->
    <div class="row mb-4">
        <div class="col-12">
            <!-- 성공/에러 메시지 표시 -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="bi bi-info-circle"></i>
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-2">
                        <i class="bi bi-bank text-info"></i>
                        계좌 관리
                    </h2>
                    <p class="text-muted mb-0">등록된 은행 계좌를 관리하세요</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('home.emoney.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> 이머니 대시보드로
                    </a>
                    <a href="{{ route('home.emoney.bank.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> 새 계좌 추가
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 등록된 은행 계좌 -->
    @if($bankAccounts->count() > 0)
        <div class="row">
            @foreach($bankAccounts as $account)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card {{ $account->is_default ? 'border-success' : '' }}">
                    <div class="card-header {{ $account->is_default ? 'bg-success text-white' : 'bg-light' }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">{{ $account->bank_name ?? '은행' }}</h6>
                            @if($account->is_default)
                                <span class="badge bg-warning text-dark">기본 계좌</span>
                            @else
                                <span class="badge bg-secondary">{{ $account->currency ?? 'KRW' }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <span class="text-muted small">계좌번호</span><br>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold h6">{{ $account->account_number ?? '' }}</span>
                                <span class="badge bg-info">{{ $account->currency ?? 'KRW' }}</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <span class="text-muted small">예금주</span><br>
                            <span class="fw-bold">{{ $account->account_holder ?? '' }}</span>
                        </div>
                        @if($account->swift)
                        <div class="mb-3">
                            <span class="text-muted small">SWIFT 코드</span><br>
                            <span class="text-muted">{{ $account->swift }}</span>
                        </div>
                        @endif

                        <div class="d-flex gap-2">
                            @if(!$account->is_default)
                                <form action="{{ route('home.emoney.bank.set-default', $account->id) }}" method="POST" class="flex-fill">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm w-100">
                                        <i class="bi bi-check-circle"></i> 기본
                                    </button>
                                </form>
                            @else
                                <div class="flex-fill">
                                    <button class="btn btn-success btn-sm w-100" disabled>
                                        <i class="bi bi-check-circle"></i> 기본
                                    </button>
                                </div>
                            @endif

                            <a href="{{ route('home.emoney.bank.edit', $account->id) }}" class="btn btn-outline-primary btn-sm flex-fill">
                                <i class="bi bi-pencil"></i> 수정
                            </a>

                            <form action="{{ route('home.emoney.bank.delete', $account->id) }}" method="POST"
                                  onsubmit="return confirm('정말 삭제하시겠습니까?')" class="flex-fill">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                    <i class="bi bi-trash"></i> 삭제
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="card-footer text-muted">
                        <small>등록일: {{ \Carbon\Carbon::parse($account->created_at)->format('Y-m-d') }}</small>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <!-- 계좌가 없는 경우 -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-bank display-1 text-muted"></i>
                        <h5 class="mt-3 mb-3">등록된 계좌가 없습니다</h5>
                        <p class="text-muted mb-4">이머니 출금을 위해 은행 계좌를 등록해주세요.</p>
                        <a href="{{ route('home.emoney.bank.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> 계좌 등록하기
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>

@endsection