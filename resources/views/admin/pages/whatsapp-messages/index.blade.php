@extends('admin.layouts.master')

@section('page-title')
    رسائل WhatsApp
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">رسائل WhatsApp</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">رسائل WhatsApp</li>
                    </ol>
                </nav>
            </div>
            <div class="my-auto">
                <a href="{{ route('admin.whatsapp-messages.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> إرسال رسالة
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.whatsapp-messages.index') }}" class="row g-3" id="messagesFilterForm">
                    <input type="hidden" name="per_page" id="perPageHidden" value="{{ request('per_page', '50') }}">
                    <input type="hidden" name="per_page_custom" id="perPageCustomHidden" value="{{ request('per_page_custom', 50) }}">
                    <div class="col-md-3">
                        <label class="form-label">بحث</label>
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="البحث...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">الاتجاه</label>
                        <select class="form-select" name="direction">
                            <option value="">الكل</option>
                            <option value="inbound" {{ request('direction') == 'inbound' ? 'selected' : '' }}>واردة</option>
                            <option value="outbound" {{ request('direction') == 'outbound' ? 'selected' : '' }}>صادرة</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">الحالة</label>
                        <select class="form-select" name="status">
                            <option value="">الكل</option>
                            <option value="queued" {{ request('status') == 'queued' ? 'selected' : '' }}>في الانتظار</option>
                            <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>مرسل</option>
                            <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>مستلم</option>
                            <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>مقروء</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>فشل</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">من تاريخ</label>
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">إلى تاريخ</label>
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">الصف</label>
                        <select class="form-select" name="class_id" id="classFilter">
                            <option value="">كل الصفوف</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" @selected((string) request('class_id') === (string) $class->id)>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">المادة</label>
                        <select class="form-select" name="subject_id" id="subjectFilter">
                            <option value="">كل المواد</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected((string) request('subject_id') === (string) $subject->id)>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label d-block">&nbsp;</label>
                        <a href="{{ route('admin.whatsapp-messages.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header">
                <h5 class="card-title mb-0">قائمة الرسائل</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <form id="bulkDeleteSelectedForm" method="POST" action="{{ route('admin.whatsapp-messages.destroy-multiple', request()->query()) }}">
                        @csrf
                        @method('DELETE')
                        <div id="bulkActionsBar" class="d-none d-flex align-items-center gap-2">
                            <span class="fw-semibold"><span id="selectedCount">0</span> رسالة محددة</span>
                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#confirmBulkDeleteModal">
                                حذف المحدد
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="clearSelectionBtn">إلغاء التحديد</button>
                        </div>
                    </form>

                    <div id="perPageToolbarMount">
                        @php
                            $messagesPaginator = $messages;
                            $perPageToolbarOptions = [50, 100];
                        @endphp
                        @include('admin.partials.per-page-toolbar', [
                            'paginator' => $messagesPaginator,
                            'presetPerPages' => $perPageToolbarOptions,
                            'customPerPageMax' => 100,
                        ])
                    </div>
                </div>

                <form id="bulkDeleteByFilterForm" method="POST" action="{{ route('admin.whatsapp-messages.destroy-by-filter', request()->query()) }}">
                    @csrf
                    @method('DELETE')
                </form>

                <div class="table-responsive">
                    <table class="table table-hover table-striped text-nowrap">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAllMessages" class="form-check-input" aria-label="تحديد الكل">
                                </th>
                                <th>#</th>
                                <th>الاتجاه</th>
                                <th>المستقبل</th>
                                <th>الرسالة</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="messagesTableBody">
                            @include('admin.pages.whatsapp-messages.partials.table', ['messages' => $messages])
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmDeleteByFilterModal">
                        حذف كل الرسائل المرسلة حسب الفلتر الحالي
                    </button>
                </div>

                <div class="mt-3" id="messagesPaginationContainer">
                    @include('admin.pages.whatsapp-messages.partials.pagination', ['messages' => $messages])
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('selectAllMessages');
    const getCheckboxes = () => Array.from(document.querySelectorAll('.message-checkbox'));
    const selectedCount = document.getElementById('selectedCount');
    const bulkBar = document.getElementById('bulkActionsBar');
    const clearSelectionBtn = document.getElementById('clearSelectionBtn');
    const bulkDeleteSelectedForm = document.getElementById('bulkDeleteSelectedForm');
    const confirmBulkDeleteBtn = document.getElementById('confirmBulkDeleteBtn');
    const bulkDeleteByFilterForm = document.getElementById('bulkDeleteByFilterForm');
    const confirmDeleteByFilterBtn = document.getElementById('confirmDeleteByFilterBtn');
    const classFilter = document.getElementById('classFilter');
    const subjectFilter = document.getElementById('subjectFilter');
    const initialSubjectOptionsHtml = subjectFilter ? subjectFilter.innerHTML : '';
    const perPageToolbarMount = document.getElementById('perPageToolbarMount');
    const messagesFilterForm = document.getElementById('messagesFilterForm');
    const perPageHidden = document.getElementById('perPageHidden');
    const perPageCustomHidden = document.getElementById('perPageCustomHidden');
    const messagesTableBody = document.getElementById('messagesTableBody');
    const messagesPaginationContainer = document.getElementById('messagesPaginationContainer');
    const subjectsByClassUrl = '{{ route("admin.whatsapp-messages.subjects-by-class") }}';
    const messagesIndexUrl = '{{ route("admin.whatsapp-messages.index") }}';

    function updateBulkBar() {
        const selected = getCheckboxes().filter((cb) => cb.checked);
        if (selectedCount) selectedCount.textContent = selected.length;
        if (bulkBar) bulkBar.classList.toggle('d-none', selected.length === 0);
        if (selectAll) {
            const boxes = getCheckboxes();
            selectAll.checked = boxes.length > 0 && selected.length === boxes.length;
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            getCheckboxes().forEach((cb) => {
                cb.checked = selectAll.checked;
            });
            updateBulkBar();
        });
    }

    function attachRowCheckboxListeners() {
        getCheckboxes().forEach((cb) => cb.addEventListener('change', updateBulkBar));
    }

    function resetSelectionUi() {
        if (selectAll) {
            selectAll.checked = false;
        }
        updateBulkBar();
    }

    attachRowCheckboxListeners();

    if (clearSelectionBtn) {
        clearSelectionBtn.addEventListener('click', function () {
            if (selectAll) selectAll.checked = false;
            getCheckboxes().forEach((cb) => {
                cb.checked = false;
            });
            updateBulkBar();
        });
    }

    if (confirmBulkDeleteBtn && bulkDeleteSelectedForm) {
        confirmBulkDeleteBtn.addEventListener('click', function () {
            const ids = getCheckboxes().filter((cb) => cb.checked).map((cb) => cb.value);
            if (ids.length === 0) {
                return;
            }
            bulkDeleteSelectedForm.querySelectorAll('input[name="message_ids[]"]').forEach((el) => el.remove());
            ids.forEach((id) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'message_ids[]';
                input.value = id;
                bulkDeleteSelectedForm.appendChild(input);
            });
            bulkDeleteSelectedForm.submit();
        });
    }

    if (confirmDeleteByFilterBtn && bulkDeleteByFilterForm) {
        confirmDeleteByFilterBtn.addEventListener('click', function () {
            bulkDeleteByFilterForm.submit();
        });
    }

    async function loadSubjectsByClass(classId) {
        const selectedSubject = '{{ request("subject_id") }}';
        if (!classId) {
            subjectFilter.innerHTML = initialSubjectOptionsHtml;
            return;
        }
        subjectFilter.innerHTML = '<option value="">كل المواد</option>';
        try {
            const response = await fetch(`${subjectsByClassUrl}?class_id=${encodeURIComponent(classId)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!response.ok) {
                throw new Error('Failed to fetch subjects');
            }
            const data = await response.json();
            const rows = (data && data.data) ? data.data : [];
            rows.forEach((subject) => {
                const option = document.createElement('option');
                option.value = subject.id;
                option.textContent = subject.name;
                if (String(subject.id) === String(selectedSubject)) {
                    option.selected = true;
                }
                subjectFilter.appendChild(option);
            });
        } catch (error) {
            console.error(error);
        }
    }

    async function fetchMessages(page) {
        if (!messagesFilterForm) {
            return;
        }

        syncPerPageToFilterForm();
        const formData = new FormData(messagesFilterForm);
        if (page) {
            formData.set('page', String(page));
        } else {
            formData.delete('page');
        }

        const params = new URLSearchParams();
        formData.forEach((value, key) => {
            if (value !== null && String(value).trim() !== '') {
                params.set(key, String(value));
            }
        });

        const fetchUrl = `${messagesIndexUrl}?${params.toString()}`;
        try {
            const response = await fetch(fetchUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error('Failed to load messages');
            }

            const data = await response.json();
            if (!data || data.success !== true) {
                throw new Error('Invalid response');
            }

            if (messagesTableBody && data.table_html !== undefined) {
                messagesTableBody.innerHTML = data.table_html;
            }
            if (messagesPaginationContainer && data.pagination_html !== undefined) {
                messagesPaginationContainer.innerHTML = data.pagination_html;
            }
            if (perPageToolbarMount && data.per_page_toolbar_html !== undefined) {
                perPageToolbarMount.innerHTML = data.per_page_toolbar_html;
                bindPerPageToolbarEvents();
            }
            if (bulkDeleteSelectedForm && data.delete_multiple_url) {
                bulkDeleteSelectedForm.setAttribute('action', data.delete_multiple_url);
            }
            if (bulkDeleteByFilterForm && data.delete_by_filter_url) {
                bulkDeleteByFilterForm.setAttribute('action', data.delete_by_filter_url);
            }

            resetSelectionUi();
            attachRowCheckboxListeners();
            syncCustomPerPageUi();
            window.history.replaceState({}, '', fetchUrl);
        } catch (error) {
            console.error(error);
        }
    }

    if (classFilter && subjectFilter) {
        classFilter.addEventListener('change', async function () {
            await loadSubjectsByClass(classFilter.value);
            fetchMessages(1);
        });
    }

    if (subjectFilter) {
        subjectFilter.addEventListener('change', function () {
            fetchMessages(1);
        });
    }

    if (messagesFilterForm) {
        messagesFilterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            fetchMessages(1);
        });

        messagesFilterForm.querySelectorAll('select[name="direction"], select[name="status"], input[name="date_from"], input[name="date_to"]').forEach((el) => {
            el.addEventListener('change', function () {
                fetchMessages(1);
            });
        });
    }

    if (messagesPaginationContainer) {
        messagesPaginationContainer.addEventListener('click', function (e) {
            const link = e.target && e.target.closest ? e.target.closest('a') : null;
            if (!link) {
                return;
            }
            const href = link.getAttribute('href');
            if (!href) {
                return;
            }
            e.preventDefault();
            const url = new URL(href, window.location.origin);
            const page = url.searchParams.get('page') || '1';
            fetchMessages(page);
        });
    }

    function getPerPageSelect() {
        return document.getElementById('perPageSelect');
    }

    function getPerPageCustomInput() {
        return document.getElementById('perPageCustom');
    }

    function syncPerPageToFilterForm() {
        const select = getPerPageSelect();
        const customInput = getPerPageCustomInput();
        if (!select || !perPageHidden || !perPageCustomHidden) {
            return;
        }

        perPageHidden.value = select.value;
        if (customInput) {
            perPageCustomHidden.value = customInput.value || '50';
        }
    }

    function syncCustomPerPageUi() {
        const sel = getPerPageSelect();
        const wrap = document.getElementById('perPageCustomWrap');
        if (!sel || !wrap) {
            return;
        }
        if (sel.value === 'custom') {
            wrap.classList.remove('d-none');
            wrap.classList.add('d-flex');
        } else {
            wrap.classList.add('d-none');
            wrap.classList.remove('d-flex');
        }
    }

    function bindPerPageToolbarEvents() {
        const perPageToolbarContainer = document.getElementById('perPageToolbarContainer');
        if (!perPageToolbarContainer) {
            return;
        }

        perPageToolbarContainer.addEventListener('change', function (e) {
            if (!e.target || e.target.id !== 'perPageSelect') {
                return;
            }
            syncCustomPerPageUi();
            syncPerPageToFilterForm();
            if (e.target.value !== 'custom') {
                fetchMessages(1);
            }
        });

        perPageToolbarContainer.addEventListener('click', function (e) {
            const btn = e.target && e.target.closest ? e.target.closest('#applyCustomPerPage') : null;
            if (!btn) {
                return;
            }
            e.preventDefault();
            const customInput = getPerPageCustomInput();
            if (customInput) {
                const raw = parseInt(customInput.value, 10);
                if (!Number.isFinite(raw) || raw < 1 || raw > 100) {
                    alert('أدخل عدداً بين 1 و 100');
                    return;
                }
            }
            syncPerPageToFilterForm();
            fetchMessages(1);
        });
    }

    bindPerPageToolbarEvents();
    updateBulkBar();
    syncCustomPerPageUi();
});
</script>

<div class="modal fade" id="confirmBulkDeleteModal" tabindex="-1" aria-labelledby="confirmBulkDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmBulkDeleteModalLabel">تأكيد حذف الرسائل المحددة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body">
                سيتم حذف الرسائل الصادرة المحددة فقط. لا يمكن التراجع عن هذه العملية.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-danger" id="confirmBulkDeleteBtn">تأكيد الحذف</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmDeleteByFilterModal" tabindex="-1" aria-labelledby="confirmDeleteByFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteByFilterModalLabel">تأكيد حذف الرسائل حسب الفلتر</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body">
                سيتم حذف جميع الرسائل الصادرة المطابقة للفلاتر الحالية. لا يمكن التراجع عن هذه العملية.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteByFilterBtn">تأكيد الحذف</button>
            </div>
        </div>
    </div>
</div>
@stop
