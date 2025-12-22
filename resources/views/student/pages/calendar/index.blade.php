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
    
    calendar.render();
});
</script>
@endpush
@stop

