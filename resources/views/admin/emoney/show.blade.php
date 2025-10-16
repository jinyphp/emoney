@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '지갑 상세')

@section('content')
<div class="container-fluid p-6">
    <!-- 페이지 헤더 -->
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="border-bottom pb-3 mb-3 d-flex align-items-center justify-content-between">
                <div class="mb-2 mb-lg-0">
                    <h1 class="mb-0 h2 fw-bold">지갑 상세</h1>
                    <p class="mb-0">지갑 정보 및 거래 내역</p>
                </div>
                <div>
                    <a href="{{ route('admin.auth.emoney.edit', $wallet->id) }}" class="btn btn-primary btn-sm">수정</a>
                    <a href="{{ route('admin.auth.emoney.index') }}" class="btn btn-outline-secondary btn-sm">목록으로</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 지갑 정보 -->
    <div class="row">
        <div class="col-lg-8 col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">지갑 정보</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>사용자:</strong>
                        </div>
                        <div class="col-md-9">
                            {{ $user->name ?? 'N/A' }} ({{ $user->email ?? 'N/A' }})
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>잔액:</strong>
                        </div>
                        <div class="col-md-9">
                            <span class="h4 text-success">{{ number_format($wallet->balance) }} {{ $wallet->currency }}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>포인트:</strong>
                        </div>
                        <div class="col-md-9">
                            <span class="h4 text-primary">{{ number_format($wallet->points) }} P</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>상태:</strong>
                        </div>
                        <div class="col-md-9">
                            @if($wallet->status === 'active')
                                <span class="badge bg-success">활성</span>
                            @elseif($wallet->status === 'inactive')
                                <span class="badge bg-secondary">비활성</span>
                            @else
                                <span class="badge bg-danger">정지</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>생성일:</strong>
                        </div>
                        <div class="col-md-9">
                            {{ $wallet->created_at }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <strong>수정일:</strong>
                        </div>
                        <div class="col-md-9">
                            {{ $wallet->updated_at }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- 거래 내역 -->
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">최근 거래 내역</h4>
                </div>
                <div class="card-body">
                    @if($transactions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>일시</th>
                                        <th>유형</th>
                                        <th>금액</th>
                                        <th>잔액</th>
                                        <th>메모</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->created_at }}</td>
                                        <td>
                                            @if($transaction->type === 'deposit')
                                                <span class="badge bg-success">입금</span>
                                            @else
                                                <span class="badge bg-danger">출금</span>
                                            @endif
                                        </td>
                                        <td class="{{ $transaction->type === 'deposit' ? 'text-success' : 'text-danger' }}">
                                            {{ $transaction->type === 'deposit' ? '+' : '-' }}{{ number_format($transaction->amount) }}
                                        </td>
                                        <td>{{ number_format($transaction->balance_after) }}</td>
                                        <td>{{ $transaction->memo ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">거래 내역이 없습니다.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-12">
            <!-- 삭제 액션 -->
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">위험 구역</h5>
                </div>
                <div class="card-body">
                    <p class="mb-3">지갑을 삭제하면 모든 거래 내역이 삭제됩니다. 이 작업은 되돌릴 수 없습니다.</p>
                    <form action="{{ route('admin.auth.emoney.destroy', $wallet->id) }}" method="POST"
                          onsubmit="return confirm('정말로 이 지갑을 삭제하시겠습니까?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">지갑 삭제</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
