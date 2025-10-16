@extends($layout ?? 'jiny-auth::layouts.admin.sidebar')

@section('title', '지갑 수정')

@section('content')
<div class="container-fluid p-6">
    <!-- 페이지 헤더 -->
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="border-bottom pb-3 mb-3">
                <div class="mb-2 mb-lg-0">
                    <h1 class="mb-0 h2 fw-bold">지갑 수정</h1>
                    <p class="mb-0">지갑 정보를 수정합니다</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 지갑 수정 폼 -->
    <div class="row">
        <div class="col-lg-8 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">지갑 정보</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.auth.emoney.update', $wallet->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- 사용자 정보 (읽기 전용) -->
                        <div class="mb-3">
                            <label class="form-label">사용자</label>
                            <input type="text" class="form-control" value="{{ $user->name ?? 'N/A' }} ({{ $user->email ?? 'N/A' }})" readonly>
                        </div>

                        <!-- 잔액 -->
                        <div class="mb-3">
                            <label for="balance" class="form-label">잔액 <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('balance') is-invalid @enderror"
                                   id="balance" name="balance" value="{{ old('balance', $wallet->balance) }}"
                                   min="0" step="0.01" required>
                            @error('balance')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 포인트 -->
                        <div class="mb-3">
                            <label for="points" class="form-label">포인트 <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('points') is-invalid @enderror"
                                   id="points" name="points" value="{{ old('points', $wallet->points) }}"
                                   min="0" step="0.01" required>
                            @error('points')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 통화 -->
                        <div class="mb-3">
                            <label for="currency" class="form-label">통화 <span class="text-danger">*</span></label>
                            <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency" required>
                                <option value="KRW" {{ old('currency', $wallet->currency) === 'KRW' ? 'selected' : '' }}>KRW (원)</option>
                                <option value="USD" {{ old('currency', $wallet->currency) === 'USD' ? 'selected' : '' }}>USD (달러)</option>
                                <option value="EUR" {{ old('currency', $wallet->currency) === 'EUR' ? 'selected' : '' }}>EUR (유로)</option>
                            </select>
                            @error('currency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- 상태 -->
                        <div class="mb-3">
                            <label for="status" class="form-label">상태 <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="active" {{ old('status', $wallet->status) === 'active' ? 'selected' : '' }}>활성</option>
                                <option value="inactive" {{ old('status', $wallet->status) === 'inactive' ? 'selected' : '' }}>비활성</option>
                                <option value="suspended" {{ old('status', $wallet->status) === 'suspended' ? 'selected' : '' }}>정지</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.auth.emoney.show', $wallet->id) }}" class="btn btn-outline-secondary">취소</a>
                            <button type="submit" class="btn btn-primary">수정</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
