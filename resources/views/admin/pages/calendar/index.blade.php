@extends('admin.layouts.master')

@section('page-title')
    التقويم والجدولة
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">التقويم والجدولة</h5>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.calendar.events.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> إضافة حدث جديد
                </a>
                <a href="{{ route('admin.calendar.reminders.index') }}" class="btn btn-outline-info btn-sm">
                    <i class="fas fa-bell me-1"></i> إدارة التذكيرات
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div id="calendar" data-events="{{ json_encode($formattedEvents ?? [], JSON_UNESCAPED_UNICODE) }}"></div>
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
                <a href="#" id="eventModalUrl" class="btn btn-primary" style="display: none;">عرض التفاصيل</a>
            </div>
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
<script src="{{ asset('assets/js/calendar.js') }}"></script>
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
            // استخدام API endpoint لجلب الأحداث
            const url = '{{ route("admin.calendar.events-api") }}?start=' + encodeURIComponent(fetchInfo.startStr) + '&end=' + encodeURIComponent(fetchInfo.endStr);
            
            console.log('Fetching events from:', url);
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin'
            })
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Events loaded from API:', data);
                    if (Array.isArray(data)) {
                        successCallback(data);
                    } else {
                        console.error('Invalid data format:', data);
                        // Fallback to initial events
                        const initialEvents = JSON.parse(document.getElementById('calendar').dataset.events || '[]');
                        console.log('Using initial events:', initialEvents);
                        successCallback(initialEvents);
                    }
                })
                .catch(error => {
                    console.error('Error fetching events:', error);
                    // Fallback to initial events if API fails
                    const initialEvents = JSON.parse(document.getElementById('calendar').dataset.events || '[]');
                    console.log('Using initial events as fallback:', initialEvents);
                    successCallback(initialEvents);
                });
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
    
    // تحميل البيانات الأولية أولاً
    const initialEvents = JSON.parse(document.getElementById('calendar').dataset.events || '[]');
    console.log('Initial events loaded:', initialEvents);
    if (initialEvents.length > 0) {
        calendar.addEventSource(initialEvents);
    }
    
    calendar.render();
    
    // تحديث التقويم بعد إعادة تحميل الصفحة (مثلاً بعد إضافة حدث جديد)
    @if (session('success'))
        // إعادة تحميل الأحداث بعد ثانية واحدة
        setTimeout(function() {
            console.log('Refetching events after success message');
            calendar.refetchEvents();
        }, 500);
    @endif
    
    // إضافة event listener لتحديث التقويم عند تغيير التاريخ
    calendar.on('datesSet', function(dateInfo) {
        console.log('Calendar dates changed:', dateInfo);
    });
    
    // Log when events are loaded
    calendar.on('eventsSet', function(info) {
        console.log('Events set in calendar:', info.events.length, 'events');
    });
});
</script>
@endpush

