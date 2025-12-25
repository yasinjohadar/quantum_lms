@extends('student.layouts.master')

@section('page-title')
    التقويم
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">التقويم</h5>
            </div>
            <div>
                <button type="button" class="btn btn-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#addEventModal">
                    <i class="fas fa-plus me-1"></i> إضافة حدث
                </button>
                <a href="{{ route('student.calendar.export') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-download me-1"></i> تصدير التقويم
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div id="calendar" data-api-url="{{ route('student.calendar.events-api') }}"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal لعرض تفاصيل الحدث -->
<div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalLabel">تفاصيل الحدث</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body" id="eventModalBody">
                <!-- سيتم ملؤه عبر JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                <button type="button" class="btn btn-danger" id="deleteEventBtn" style="display: none;" data-event-id="">
                    <i class="fas fa-trash me-1"></i> حذف
                </button>
                <button type="button" class="btn btn-warning" id="editEventBtn" style="display: none;" data-event-id="">
                    <i class="fas fa-edit me-1"></i> تعديل
                </button>
                <a href="#" id="eventModalUrl" class="btn btn-primary" style="display: none;">عرض التفاصيل</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal لإضافة/تعديل حدث -->
<div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEventModalLabel">إضافة حدث جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form id="eventForm">
                <div class="modal-body">
                    <input type="hidden" id="eventId" name="event_id">
                    
                    <div class="mb-3">
                        <label for="eventTitle" class="form-label">عنوان الحدث <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="eventTitle" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="eventDescription" class="form-label">الوصف</label>
                        <textarea class="form-control" id="eventDescription" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="eventType" class="form-label">نوع الحدث</label>
                        <select class="form-select" id="eventType" name="event_type">
                            <option value="general">عام</option>
                            <option value="meeting">اجتماع</option>
                            <option value="holiday">عطلة</option>
                            <option value="exam">امتحان</option>
                            <option value="other">أخرى</option>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="eventStartDate" class="form-label">تاريخ البدء <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="eventStartDate" name="start_date" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="eventEndDate" class="form-label">تاريخ الانتهاء</label>
                            <input type="datetime-local" class="form-control" id="eventEndDate" name="end_date">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="eventAllDay" name="is_all_day">
                            <label class="form-check-label" for="eventAllDay">
                                طوال اليوم
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="eventLocation" class="form-label">الموقع</label>
                        <input type="text" class="form-control" id="eventLocation" name="location">
                    </div>
                    
                    <div class="mb-3">
                        <label for="eventColor" class="form-label">اللون</label>
                        <input type="color" class="form-control form-control-color" id="eventColor" name="color" value="#10b981">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> حفظ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css" rel="stylesheet">
<style>
    #calendar {
        min-height: 600px;
    }
    .fc-event {
        cursor: pointer;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales/ar.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'ar',
        direction: 'rtl',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        events: function(fetchInfo, successCallback, failureCallback) {
            const apiUrl = document.getElementById('calendar').dataset.apiUrl;
            fetch(apiUrl + '?start=' + fetchInfo.startStr + '&end=' + fetchInfo.endStr, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                successCallback(data);
            })
            .catch(error => {
                console.error('Error fetching events:', error);
                failureCallback(error);
            });
        },
        selectable: true,
        selectMirror: true,
        select: function(info) {
            // فتح modal الإضافة عند اختيار فترة زمنية
            document.getElementById('eventForm').reset();
            document.getElementById('eventId').value = '';
            document.getElementById('addEventModalLabel').textContent = 'إضافة حدث جديد';
            
            // تعيين التاريخ والوقت المحدد
            const startDate = new Date(info.start);
            const endDate = info.end ? new Date(info.end) : new Date(startDate.getTime() + 60 * 60 * 1000);
            
            document.getElementById('eventStartDate').value = formatDateTimeLocal(startDate);
            document.getElementById('eventEndDate').value = formatDateTimeLocal(endDate);
            document.getElementById('eventAllDay').checked = info.allDay;
            
            const modal = new bootstrap.Modal(document.getElementById('addEventModal'));
            modal.show();
            
            calendar.unselect();
        },
        eventClick: function(info) {
            const event = info.event;
            const extendedProps = event.extendedProps;
            
            document.getElementById('eventModalLabel').textContent = event.title;
            let bodyContent = '';
            
            if (extendedProps.description) {
                bodyContent += '<p><strong>الوصف:</strong> ' + extendedProps.description + '</p>';
            }
            
            if (extendedProps.location) {
                bodyContent += '<p><strong>الموقع:</strong> ' + extendedProps.location + '</p>';
            }
            
            bodyContent += '<p><strong>النوع:</strong> ' + (extendedProps.type === 'calendar_event' ? 'حدث تقويم' : 
                          extendedProps.type === 'quiz' ? 'اختبار' : 'واجب') + '</p>';
            
            bodyContent += '<p><strong>البدء:</strong> ' + event.start.toLocaleString('ar-SA') + '</p>';
            
            if (event.end) {
                bodyContent += '<p><strong>الانتهاء:</strong> ' + event.end.toLocaleString('ar-SA') + '</p>';
            }
            
            document.getElementById('eventModalBody').innerHTML = bodyContent;
            
            // إظهار/إخفاء أزرار التعديل والحذف (فقط للأحداث الشخصية)
            if (extendedProps.type === 'calendar_event' && extendedProps.event_id) {
                const eventId = extendedProps.event_id;
                document.getElementById('editEventBtn').style.display = 'inline-block';
                document.getElementById('editEventBtn').setAttribute('data-event-id', eventId);
                document.getElementById('deleteEventBtn').style.display = 'inline-block';
                document.getElementById('deleteEventBtn').setAttribute('data-event-id', eventId);
            } else {
                document.getElementById('editEventBtn').style.display = 'none';
                document.getElementById('deleteEventBtn').style.display = 'none';
            }
            
            if (extendedProps.url) {
                document.getElementById('eventModalUrl').href = extendedProps.url;
                document.getElementById('eventModalUrl').style.display = 'inline-block';
            } else {
                document.getElementById('eventModalUrl').style.display = 'none';
            }
            
            const modal = new bootstrap.Modal(document.getElementById('eventModal'));
            modal.show();
        },
        eventDisplay: 'block',
        height: 'auto',
    });
    
    calendar.render();
    
    // دالة لتنسيق التاريخ والوقت للـ input datetime-local
    function formatDateTimeLocal(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }
    
    // دالة لتحويل datetime-local إلى ISO string
    function datetimeLocalToISO(datetimeLocal) {
        if (!datetimeLocal) return null;
        return new Date(datetimeLocal).toISOString();
    }
    
    // معالجة form الإضافة/التعديل
    document.getElementById('eventForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const eventId = document.getElementById('eventId').value;
        const url = eventId 
            ? `/student/calendar/events/${eventId}`
            : '/student/calendar/events';
        const method = eventId ? 'PUT' : 'POST';
        
        const data = {
            title: formData.get('title'),
            description: formData.get('description'),
            event_type: formData.get('event_type'),
            start_date: datetimeLocalToISO(formData.get('start_date')),
            end_date: datetimeLocalToISO(formData.get('end_date')),
            is_all_day: document.getElementById('eventAllDay').checked,
            location: formData.get('location'),
            color: formData.get('color'),
            _method: method === 'PUT' ? 'PUT' : 'POST',
        };
        
        fetch(url, {
            method: method === 'PUT' ? 'POST' : 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                bootstrap.Modal.getInstance(document.getElementById('addEventModal')).hide();
                calendar.refetchEvents();
                this.reset();
            } else {
                alert(result.message || 'حدث خطأ أثناء حفظ الحدث');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء حفظ الحدث');
        });
    });
    
    // معالجة تعديل الحدث
    document.getElementById('editEventBtn').addEventListener('click', function() {
        const eventId = this.getAttribute('data-event-id');
        bootstrap.Modal.getInstance(document.getElementById('eventModal')).hide();
        
        fetch(`/student/calendar/events/${eventId}`)
        .then(response => response.json())
        .then(event => {
            document.getElementById('eventId').value = event.id;
            document.getElementById('eventTitle').value = event.title;
            document.getElementById('eventDescription').value = event.description || '';
            document.getElementById('eventType').value = event.event_type;
            document.getElementById('eventStartDate').value = formatDateTimeLocal(new Date(event.start_date));
            document.getElementById('eventEndDate').value = event.end_date ? formatDateTimeLocal(new Date(event.end_date)) : '';
            document.getElementById('eventAllDay').checked = event.is_all_day;
            document.getElementById('eventLocation').value = event.location || '';
            document.getElementById('eventColor').value = event.color || '#10b981';
            document.getElementById('addEventModalLabel').textContent = 'تعديل حدث';
            
            const modal = new bootstrap.Modal(document.getElementById('addEventModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء جلب بيانات الحدث');
        });
    });
    
    // معالجة حذف الحدث
    document.getElementById('deleteEventBtn').addEventListener('click', function() {
        if (!confirm('هل أنت متأكد من حذف هذا الحدث؟')) {
            return;
        }
        
        const eventId = this.getAttribute('data-event-id');
        
        fetch(`/student/calendar/events/${eventId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                bootstrap.Modal.getInstance(document.getElementById('eventModal')).hide();
                calendar.refetchEvents();
            } else {
                alert(result.message || 'حدث خطأ أثناء حذف الحدث');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء حذف الحدث');
        });
    });
    
    // إعادة تعيين Modal عند الإغلاق
    document.getElementById('addEventModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('eventForm').reset();
        document.getElementById('eventId').value = '';
        document.getElementById('addEventModalLabel').textContent = 'إضافة حدث جديد';
        document.getElementById('eventColor').value = '#10b981';
    });
});
</script>
@endpush
