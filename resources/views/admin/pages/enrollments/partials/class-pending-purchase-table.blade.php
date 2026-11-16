{{-- عرض طلبات الصفوف المدفوعة المعلقة كجدول --}}
<div class="enrollments-index-table-wrap">
    <div class="table-responsive">
        <table class="table enrollments-index-table align-middle mb-0">
            <thead>
            <tr>
                <th scope="col" style="width: 48px;">#</th>
                <th scope="col">الطالب</th>
                <th scope="col" class="d-none d-md-table-cell">الهاتف</th>
                <th scope="col" class="enrollments-col-class">الصف</th>
                <th scope="col">المبلغ</th>
                <th scope="col" class="enrollments-col-date">تاريخ الطلب</th>
                <th scope="col" class="d-none d-lg-table-cell">انتهاء مقترح</th>
                <th scope="col" style="min-width: 150px;">الإجراءات</th>
            </tr>
            </thead>
            <tbody>
            @foreach($purchases as $purchase)
                @php
                    $student = $purchase->user;
                    $schoolClass = $purchase->purchasable;
                    $rowNum = $loop->iteration + ($purchases->currentPage() - 1) * $purchases->perPage();
                    $initial = $student ? mb_strtoupper(mb_substr(trim($student->name), 0, 1)) : '—';
                    $approvalDefaults = \App\Support\PurchaseApprovalExpiryDefaults::resolve($purchase);
                    $classEndsAt = $approvalDefaults['class_subscription_ends_at']?->format('Y-m-d');
                @endphp
                <tr>
                    <th scope="row" class="text-muted small">{{ $rowNum }}</th>
                    <td>
                        <div class="ui-user-cell">
                            <span class="ui-user-avatar">{{ $initial }}</span>
                            <div class="min-width-0">
                                <div class="ui-user-name text-truncate">{{ $student->name ?? 'غير محدد' }}</div>
                                @if ($student?->email)
                                    <small class="text-muted text-truncate d-block">{{ $student->email }}</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="d-none d-md-table-cell">
                        @if ($student?->phone)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $student->phone) }}"
                               class="text-success text-nowrap" target="_blank" rel="noopener" dir="ltr">
                                <i class="fab fa-whatsapp me-1"></i>{{ $student->phone }}
                            </a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="enrollments-col-class">
                        <span class="ui-class-pill">
                            <i class="bi bi-building"></i>
                            {{ $schoolClass->name ?? 'صف غير محدد' }}
                        </span>
                        @if ($schoolClass?->stage)
                            <div class="ui-enrollment-subject-meta mt-1">{{ $schoolClass->stage->name }}</div>
                        @endif
                    </td>
                    <td class="text-nowrap fw-semibold">
                        {{ number_format((float) $purchase->price, 2) }} <small class="text-muted">ر.س</small>
                    </td>
                    <td class="enrollments-col-date">
                        <div class="ui-date-cell">
                            {{ $purchase->created_at->format('Y-m-d') }}
                            <small>{{ $purchase->created_at->format('H:i') }}</small>
                        </div>
                    </td>
                    <td class="d-none d-lg-table-cell">
                        <div class="ui-date-cell" dir="ltr">
                            {{ $approvalDefaults['default_expires_at']->format('Y-m-d') }}
                            @if ($classEndsAt)
                                <small dir="rtl">حد الصف: {{ $classEndsAt }}</small>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            @include('admin.pages.enrollments.partials.approve-purchase-btn', [
                                'purchase' => $purchase,
                                'studentName' => $student->name ?? 'غير محدد',
                                'itemName' => $schoolClass->name ?? 'صف غير محدد',
                                'typeLabel' => 'الصف',
                            ])
                            <form action="{{ route('admin.payments.pending-purchases.reject', $purchase) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('هل تريد رفض طلب الصف المدفوع هذا؟');">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-x-lg me-1"></i> رفض
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
