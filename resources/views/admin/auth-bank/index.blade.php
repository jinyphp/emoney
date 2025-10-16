@extends('jiny-admin::layouts.admin.sidebar')

@section('title', '은행 목록 관리')

@section('content')
<div class="container-fluid p-6">
    <!-- Page Header -->
    <section class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="page-header">
                <div class="page-header-content">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="page-header-title">
                                <i class="fe fe-credit-card me-2"></i>
                                은행 목록 관리
                            </h1>
                            <p class="page-header-subtitle">시스템에서 사용할 은행 정보를 관리합니다</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.auth.bank.create') }}" class="btn btn-primary">
                                <i class="fe fe-plus me-2"></i>은행 추가
                            </a>
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fe fe-download me-2"></i>내보내기
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="exportData('csv')">
                                            <i class="fe fe-file-text me-2"></i>CSV 파일
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="exportData('excel')">
                                            <i class="fe fe-grid me-2"></i>Excel 파일
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="exportData('json')">
                                            <i class="fe fe-code me-2"></i>JSON 파일
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 성공/오류 메시지 -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- 통계 요약 -->
    <section class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-2">
                                <i class="fe fe-activity me-2"></i>
                                은행 현황 요약
                            </h5>
                            <p class="text-muted mb-0">시스템에 등록된 은행들의 전체 현황입니다.</p>
                        </div>
                        <div class="d-flex gap-4">
                            <div class="text-center">
                                <div class="h4 text-primary mb-0">{{ number_format($statistics['total_banks']) }}</div>
                                <small class="text-muted">전체</small>
                            </div>
                            <div class="text-center">
                                <div class="h4 text-success mb-0">{{ number_format($statistics['active_banks']) }}</div>
                                <small class="text-muted">활성</small>
                            </div>
                            <div class="text-center">
                                <div class="h4 text-danger mb-0">{{ number_format($statistics['inactive_banks']) }}</div>
                                <small class="text-muted">비활성</small>
                            </div>
                            <div class="text-center border-start ps-4">
                                <div class="h4 text-info mb-0">{{ number_format($statistics['countries']) }}</div>
                                <small class="text-muted">지원국가</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 검색 및 필터 -->
    <section class="row mt-4">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">검색 및 필터</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.auth.bank.index') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">검색</label>
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control"
                                           placeholder="은행명, 은행코드 검색..." value="{{ $request->search }}">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fe fe-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">국가</label>
                                <select name="country" class="form-select">
                                    <option value="">모든 국가</option>
                                    @foreach($countries as $code => $name)
                                        <option value="{{ $code }}" {{ $request->country == $code ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">상태</label>
                                <select name="enable" class="form-select">
                                    <option value="">모든 상태</option>
                                    <option value="1" {{ $request->enable == '1' ? 'selected' : '' }}>활성</option>
                                    <option value="0" {{ $request->enable == '0' ? 'selected' : '' }}>비활성</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">정렬</label>
                                <select name="sort_by" class="form-select">
                                    <option value="sort_order" {{ $request->sort_by == 'sort_order' ? 'selected' : '' }}>정렬순서</option>
                                    <option value="name" {{ $request->sort_by == 'name' ? 'selected' : '' }}>은행명</option>
                                    <option value="country" {{ $request->sort_by == 'country' ? 'selected' : '' }}>국가</option>
                                    <option value="created_at" {{ $request->sort_by == 'created_at' ? 'selected' : '' }}>등록일</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fe fe-filter me-2"></i>필터 적용
                                    </button>
                                    <a href="{{ route('admin.auth.bank.index') }}" class="btn btn-outline-secondary">
                                        <i class="fe fe-x me-2"></i>초기화
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>



        </div>
    </section>

    <!-- 은행 목록 -->
    <section class="row mt-4">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">은행 목록</h4>
                    <div>
                        <button type="button" class="btn btn-outline-danger btn-sm" id="bulkDeleteBtn" disabled>
                            <i class="fe fe-trash-2 me-2"></i>선택 삭제
                        </button>
                    </div>
                </div>
                @if(isset($banks) && $banks->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width='50px'>
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                    <th width='100px'>순서</th>
                                    <th>은행명</th>
                                    <th width='100px'>코드</th>
                                    <th width='100px'>국가</th>
                                    <th width='200px'>SWIFT</th>
                                    <th width='100px'>상태</th>
                                    <th width='200px'>등록일</th>
                                    <th width='200px'>관리</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($banks as $bank)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="selected_ids[]" value="{{ $bank->id }}" class="form-check-input item-checkbox">
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $bank->sort_order }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($bank->logo_url)
                                                    <img src="{{ $bank->logo_url }}" alt="{{ $bank->name }}" class="rounded me-3" style="width: 32px; height: 32px;">
                                                @else
                                                    <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                        <i class="fe fe-credit-card text-muted"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <a href="{{ route('admin.auth.bank.show', $bank->id) }}" class="text-decoration-none">
                                                        <strong>{{ $bank->name }}</strong>
                                                    </a>
                                                    @if($bank->website)
                                                        <br>
                                                        <a href="{{ $bank->website }}" target="_blank" class="text-muted small">
                                                            <i class="fe fe-external-link"></i> 웹사이트
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($bank->code)
                                                <code>{{ $bank->code }}</code>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $bank->country_name }}</span>
                                        </td>
                                        <td>
                                            @if($bank->swift_code)
                                                <code>{{ $bank->swift_code }}</code>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($bank->enable)
                                                <span class="badge bg-success">활성</span>
                                            @else
                                                <span class="badge bg-secondary">비활성</span>
                                            @endif
                                        </td>
                                        <td>{{ $bank->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.auth.bank.show', $bank->id) }}"
                                                   class="btn btn-outline-primary btn-sm" title="상세보기">
                                                    <i class="fe fe-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.auth.bank.edit', $bank->id) }}"
                                                   class="btn btn-outline-secondary btn-sm" title="수정">
                                                    <i class="fe fe-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger btn-sm"
                                                        onclick="deleteBank({{ $bank->id }}, '{{ $bank->name }}')" title="삭제">
                                                    <i class="fe fe-trash-2"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- 페이지네이션 --}}
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                @if($banks->total() > 0)
                                    Showing banks {{ number_format($banks->firstItem()) }} to {{ number_format($banks->lastItem()) }} of {{ number_format($banks->total()) }}
                                @else
                                    No results found
                                @endif
                            </div>
                            <div>
                                @if(method_exists($banks, 'links') && $banks->hasPages())
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination pagination-sm mb-0">
                                            {{-- Previous Page Link --}}
                                            @if ($banks->onFirstPage())
                                                <li class="page-item disabled">
                                                    <span class="page-link"><i class="fe fe-chevron-left"></i></span>
                                                </li>
                                            @else
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $banks->previousPageUrl() }}" rel="prev">
                                                        <i class="fe fe-chevron-left"></i>
                                                    </a>
                                                </li>
                                            @endif

                                            {{-- Pagination Elements --}}
                                            @foreach ($banks->getUrlRange(1, $banks->lastPage()) as $page => $url)
                                                @if ($page == $banks->currentPage())
                                                    <li class="page-item active">
                                                        <span class="page-link bg-primary border-primary">{{ $page }}</span>
                                                    </li>
                                                @else
                                                    <li class="page-item">
                                                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                                    </li>
                                                @endif
                                            @endforeach

                                            {{-- Next Page Link --}}
                                            @if ($banks->hasMorePages())
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $banks->nextPageUrl() }}" rel="next">
                                                        <i class="fe fe-chevron-right"></i>
                                                    </a>
                                                </li>
                                            @else
                                                <li class="page-item disabled">
                                                    <span class="page-link"><i class="fe fe-chevron-right"></i></span>
                                                </li>
                                            @endif
                                        </ul>
                                    </nav>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card-body">
                        <div class="text-center text-muted py-5">
                            <i class="fe fe-credit-card fs-1 mb-3"></i>
                            <h5>등록된 은행이 없습니다</h5>
                            <p>현재 등록된 은행이 없습니다.</p>
                        </div>
                    </div>
                @endif
            </div>



        </div>
    </section>

</div>

<!-- 삭제 확인 모달 -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">삭제 확인</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                선택한 은행을 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">삭제</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 벌크 작업 폼 -->
<form id="bulkActionForm" method="POST" action="{{ route('admin.auth.bank.index') }}/bulk-delete" style="display: none;">
    @csrf
    <input type="hidden" name="selected_ids" id="bulkSelectedIds">
</form>

@endsection

@push('scripts')
<script>
// 삭제 함수
function deleteBank(id, bankName) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const deleteForm = document.getElementById('deleteForm');
    deleteForm.action = `{{ route('admin.auth.bank.index') }}/${id}`;

    // 모달 내용 업데이트
    document.querySelector('#deleteModal .modal-body').innerHTML =
        `정말로 '${bankName}' 은행을 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.`;

    modal.show();
}

// 내보내기 함수
function exportData(format) {
    // 현재 페이지의 필터링 파라미터들을 가져옵니다
    const urlParams = new URLSearchParams(window.location.search);

    // 내보내기 형식 추가
    urlParams.set('format', format);

    // 내보내기 URL 생성
    const exportUrl = "{{ route('admin.auth.bank.export') }}" + '?' + urlParams.toString();

    // 새 창에서 다운로드 시작
    const link = document.createElement('a');
    link.href = exportUrl;
    link.download = '';
    link.target = '_blank';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    // 사용자에게 다운로드 시작을 알림
    if (format === 'json') {
        // JSON의 경우 알림 표시
        alert('JSON 데이터가 새 탭에서 열립니다.');
    } else {
        // 기타 형식의 경우 다운로드 시작 알림
        const formatNames = {
            'csv': 'CSV',
            'excel': 'Excel'
        };

        // 토스트 알림 표시 (Bootstrap 토스트가 있다면)
        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            const toastHtml = `
                <div class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fe fe-download me-2"></i>${formatNames[format]} 파일 다운로드가 시작되었습니다.
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>
            `;

            // 토스트 컨테이너가 없으면 생성
            let toastContainer = document.querySelector('.toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
                document.body.appendChild(toastContainer);
            }

            // 토스트 추가 및 표시
            toastContainer.insertAdjacentHTML('beforeend', toastHtml);
            const toastElement = toastContainer.lastElementChild;
            const toast = new bootstrap.Toast(toastElement);
            toast.show();

            // 토스트 숨겨진 후 DOM에서 제거
            toastElement.addEventListener('hidden.bs.toast', () => {
                toastElement.remove();
            });
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // 전체 선택 체크박스
    const selectAllCheckbox = document.getElementById('selectAll');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkButtons();
        });
    }

    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkButtons);
    });

    function updateBulkButtons() {
        const checkedItems = document.querySelectorAll('.item-checkbox:checked');
        const hasChecked = checkedItems.length > 0;

        if (bulkDeleteBtn) {
            bulkDeleteBtn.disabled = !hasChecked;
        }

        // 전체 선택 체크박스 상태 업데이트
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = checkedItems.length === itemCheckboxes.length && itemCheckboxes.length > 0;
            selectAllCheckbox.indeterminate = checkedItems.length > 0 && checkedItems.length < itemCheckboxes.length;
        }
    }

    // 벌크 삭제
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function() {
            const checkedItems = document.querySelectorAll('.item-checkbox:checked');
            if (checkedItems.length === 0) return;

            if (confirm(`선택한 ${checkedItems.length}개 은행을 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.`)) {
                const selectedIds = Array.from(checkedItems).map(cb => cb.value);
                document.getElementById('bulkSelectedIds').value = selectedIds.join(',');
                document.getElementById('bulkActionForm').submit();
            }
        });
    }
});
</script>
@endpush
