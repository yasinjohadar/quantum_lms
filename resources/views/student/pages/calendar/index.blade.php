@extends('student.layouts.master')

@section('page-title')
    التقويم
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-3 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">التقويم</h5>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addEventModal">
                    <i class="fas fa-plus me-1"></i> إضافة حدث
                </button>
                <a href="{{ route('student.calendar.export') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-download me-1"></i> تصدير التقويم
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-9">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-3">
                        <div id="calendar" 
                             data-api-url="{{ route('student.calendar.events-api') }}" 
                             data-notes-api-url="{{ route('student.calendar.notes-api') }}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-sticky-note me-2"></i> المفكرة
                        </h6>
                    </div>
                    <div class="card-body p-2">
                        <button type="button" class="btn btn-primary btn-sm w-100 mb-3" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                            <i class="fas fa-plus me-1"></i> إضافة ملاحظة
                        </button>
                        <div class="input-group input-group-sm mb-3">
                            <input type="date" class="form-control" id="notesDateFilter" value="{{ date('Y-m-d') }}">
                            <button class="btn btn-outline-secondary" type="button" id="filterNotesBtn">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <div id="notesList" style="max-height: 600px; overflow-y: auto;">
                            <p class="text-muted text-center small">جاري التحميل...</p>
                        </div>
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

<!-- Modal لإضافة/تعديل ملاحظة -->
<div class="modal fade" id="addNoteModal" tabindex="-1" aria-labelledby="addNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="addNoteModalLabel">
                    <i class="fas fa-sticky-note me-2"></i> إضافة ملاحظة جديدة
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form id="noteForm">
                <div class="modal-body">
                    <input type="hidden" id="noteId" name="note_id">
                    
                    <div class="mb-3">
                        <label for="noteDate" class="form-label">التاريخ <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="noteDate" name="note_date" required value="{{ date('Y-m-d') }}">
                    </div>
                    
                    <div class="mb-3">
                        <label for="noteTitle" class="form-label">العنوان (اختياري)</label>
                        <input type="text" class="form-control" id="noteTitle" name="title" placeholder="عنوان الملاحظة">
                    </div>
                    
                    <div class="mb-3">
                        <label for="noteContent" class="form-label">المحتوى <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="noteContent" name="content" rows="5" required placeholder="اكتب ملاحظتك هنا..."></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="noteColor" class="form-label">اللون</label>
                            <input type="color" class="form-control form-control-color" id="noteColor" name="color" value="#fbbf24">
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="noteIsPinned" name="is_pinned">
                                <label class="form-check-label" for="noteIsPinned">
                                    <i class="fas fa-thumbtack me-1"></i> تثبيت الملاحظة
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-1"></i> حفظ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal لحذف الملاحظة -->
<div class="modal fade" id="deleteNoteModal" tabindex="-1" aria-labelledby="deleteNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title d-flex align-items-center" id="deleteNoteModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    تأكيد الحذف
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-trash-alt text-danger" style="font-size: 3rem;"></i>
                </div>
                <h6 class="mb-2">هل أنت متأكد من حذف هذه الملاحظة؟</h6>
                <p class="text-muted small mb-0">لا يمكن التراجع عن هذه العملية بعد الحذف.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> إلغاء
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteNoteBtn">
                    <i class="fas fa-trash me-1"></i> حذف
                </button>
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
<style>
    #calendar {
        min-height: 500px;
        width: 100%;
    }
    .fc-event {
        cursor: pointer;
        border-radius: 4px;
        padding: 2px 4px;
        font-weight: 500;
    }
    .fc {
        font-size: 1rem;
    }
    .fc-toolbar {
        flex-wrap: wrap;
    }
    .fc-toolbar-title {
        font-size: 1.5rem;
        font-weight: 600;
    }
    .fc-button {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }
    .fc-daygrid-day {
        min-height: 100px;
    }
    .fc-event-title {
        font-weight: 600;
        padding: 2px 4px;
    }
    .note-item {
        border-left: 4px solid;
        padding: 0.5rem;
        margin-bottom: 0.5rem;
        border-radius: 4px;
        background: #f8f9fa;
        cursor: pointer;
        transition: all 0.2s;
    }
    .note-item:hover {
        transform: translateX(-5px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .note-item.pinned {
        border-left-width: 6px;
        background: #fff3cd;
    }
    .note-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-left: 4px;
    }
    .event-details {
        font-size: 0.95rem;
    }
    .event-details .badge {
        font-size: 0.85rem;
        padding: 0.5rem 0.75rem;
    }
    @media (max-width: 768px) {
        #calendar {
            min-height: 400px;
        }
        .fc-toolbar {
            flex-direction: column;
            gap: 0.5rem;
        }
        .fc-toolbar-chunk {
            width: 100%;
            text-align: center;
        }
    }
</style>
@endpush

@push('scripts')
<!-- FullCalendar CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

<script>
(function() {
    'use strict';
    
    // متغيرات عامة
    var calendarInstance = null;
    var notesData = {};
    
    // دالة لتنسيق التاريخ والوقت للـ input datetime-local
    function formatDateTimeLocal(date) {
        var d = new Date(date);
        var year = d.getFullYear();
        var month = String(d.getMonth() + 1).padStart(2, '0');
        var day = String(d.getDate()).padStart(2, '0');
        var hours = String(d.getHours()).padStart(2, '0');
        var minutes = String(d.getMinutes()).padStart(2, '0');
        return year + '-' + month + '-' + day + 'T' + hours + ':' + minutes;
    }
    
    // دالة لتحويل datetime-local إلى ISO string
    function datetimeLocalToISO(datetimeLocal) {
        if (!datetimeLocal) return null;
        return new Date(datetimeLocal).toISOString();
    }
    
    // دالة لتنسيق التاريخ للعرض
    function formatDate(date) {
        return new Date(date).toLocaleDateString('ar-SA', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }
    
    // دالة لجلب الملاحظات
    function loadNotes(startDate, endDate) {
        var calendarEl = document.getElementById('calendar');
        if (!calendarEl) return;
        
        var notesApiUrl = calendarEl.dataset.notesApiUrl;
        if (!notesApiUrl) {
            console.log('Notes API URL not found');
            return;
        }
        
        fetch(notesApiUrl + '?start=' + startDate + '&end=' + endDate, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success && data.notes) {
                notesData = {};
                data.notes.forEach(function(note) {
                    var dateKey = note.note_date;
                    if (!notesData[dateKey]) {
                        notesData[dateKey] = [];
                    }
                    notesData[dateKey].push(note);
                });
                renderNotesList();
            }
        })
        .catch(function(error) {
            console.error('Error fetching notes:', error);
        });
    }
    
    // دالة لعرض قائمة الملاحظات
    function renderNotesList(filterDate) {
        var notesList = document.getElementById('notesList');
        if (!notesList) return;
        
        var notesToShow = [];
        if (filterDate) {
            var dateKey = filterDate.split('T')[0];
            notesToShow = notesData[dateKey] || [];
        } else {
            Object.values(notesData).forEach(function(notes) {
                notesToShow = notesToShow.concat(notes);
            });
            notesToShow.sort(function(a, b) {
                if (a.is_pinned !== b.is_pinned) {
                    return b.is_pinned - a.is_pinned;
                }
                return new Date(b.note_date) - new Date(a.note_date);
            });
        }
        
        if (notesToShow.length === 0) {
            notesList.innerHTML = '<p class="text-muted text-center small">لا توجد ملاحظات</p>';
            return;
        }
        
        var html = '';
        notesToShow.forEach(function(note) {
            html += '<div class="note-item ' + (note.is_pinned ? 'pinned' : '') + '" style="border-left-color: ' + note.color + ';" data-note-id="' + note.id + '">';
            if (note.is_pinned) {
                html += '<i class="fas fa-thumbtack text-warning me-1"></i>';
            }
            html += '<div class="d-flex justify-content-between align-items-start">';
            html += '<div class="flex-grow-1">';
            if (note.title) {
                html += '<strong>' + note.title + '</strong><br>';
            }
            html += '<small class="text-muted">' + (note.content ? note.content.substring(0, 50) : '') + (note.content && note.content.length > 50 ? '...' : '') + '</small>';
            html += '<br><small class="text-muted"><i class="fas fa-calendar me-1"></i>' + formatDate(note.note_date) + '</small>';
            html += '</div>';
            html += '<div class="btn-group btn-group-sm">';
            html += '<button class="btn btn-sm btn-outline-primary edit-note-btn" data-note-id="' + note.id + '" title="تعديل"><i class="fas fa-edit"></i></button>';
            html += '<button class="btn btn-sm btn-outline-danger delete-note-btn" data-note-id="' + note.id + '" title="حذف"><i class="fas fa-trash"></i></button>';
            html += '</div></div></div>';
        });
        notesList.innerHTML = html;
        
        // إضافة event listeners
        document.querySelectorAll('.edit-note-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                var noteId = this.getAttribute('data-note-id');
                editNote(noteId);
            });
        });
        
        document.querySelectorAll('.delete-note-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                var noteId = this.getAttribute('data-note-id');
                deleteNote(noteId);
            });
        });
    }
    
    // دالة لإضافة ملاحظة
    function addNote() {
        var form = document.getElementById('noteForm');
        if (form) form.reset();
        
        var noteId = document.getElementById('noteId');
        if (noteId) noteId.value = '';
        
        var noteDate = document.getElementById('noteDate');
        if (noteDate) noteDate.value = new Date().toISOString().split('T')[0];
        
        var noteColor = document.getElementById('noteColor');
        if (noteColor) noteColor.value = '#fbbf24';
        
        var noteIsPinned = document.getElementById('noteIsPinned');
        if (noteIsPinned) noteIsPinned.checked = false;
        
        var label = document.getElementById('addNoteModalLabel');
        if (label) label.innerHTML = '<i class="fas fa-sticky-note me-2"></i> إضافة ملاحظة جديدة';
    }
    
    // دالة لتعديل ملاحظة
    function editNote(noteId) {
        var note = null;
        Object.values(notesData).forEach(function(notes) {
            notes.forEach(function(n) {
                if (n.id == noteId) note = n;
            });
        });
        if (!note) {
            console.error('Note not found:', noteId);
            return;
        }
        
        console.log('Editing note:', note);
        
        // تعبئة البيانات قبل فتح الـ modal
        document.getElementById('noteId').value = note.id;
        document.getElementById('addNoteModalLabel').innerHTML = '<i class="fas fa-sticky-note me-2"></i> تعديل ملاحظة';
        document.getElementById('noteDate').value = note.note_date;
        document.getElementById('noteTitle').value = note.title || '';
        document.getElementById('noteContent').value = note.content || '';
        document.getElementById('noteColor').value = note.color || '#fbbf24';
        document.getElementById('noteIsPinned').checked = note.is_pinned || false;
        
        // فتح الـ modal
        var modal = new bootstrap.Modal(document.getElementById('addNoteModal'));
        modal.show();
    }
    
    // متغير لتخزين noteId المراد حذفه
    var noteIdToDelete = null;
    
    // دالة لحذف ملاحظة
    function deleteNote(noteId) {
        noteIdToDelete = noteId;
        var modal = new bootstrap.Modal(document.getElementById('deleteNoteModal'));
        modal.show();
    }
    
    // دالة لتأكيد حذف الملاحظة
    function confirmDeleteNote() {
        if (!noteIdToDelete) return;
        
        fetch('/student/calendar/notes/' + noteIdToDelete, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(function(response) { return response.json(); })
        .then(function(result) {
            var modal = bootstrap.Modal.getInstance(document.getElementById('deleteNoteModal'));
            if (modal) modal.hide();
            
            if (result.success) {
                if (calendarInstance) {
                    var view = calendarInstance.view;
                    loadNotes(view.activeStart.toISOString().split('T')[0], view.activeEnd.toISOString().split('T')[0]);
                }
                noteIdToDelete = null;
            } else {
                alert(result.message || 'حدث خطأ أثناء حذف الملاحظة');
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
            alert('حدث خطأ أثناء حذف الملاحظة');
            noteIdToDelete = null;
        });
    }
    
    // تهيئة التقويم
    function initCalendar() {
        var calendarEl = document.getElementById('calendar');
        if (!calendarEl) {
            console.error('Calendar element not found!');
            return;
        }
        
        if (typeof FullCalendar === 'undefined') {
            console.error('FullCalendar is not loaded!');
            calendarEl.innerHTML = '<div class="alert alert-danger text-center"><i class="fas fa-exclamation-triangle me-2"></i>خطأ في تحميل التقويم. يرجى تحديث الصفحة.</div>';
            return;
        }
        
        console.log('Initializing FullCalendar...');
        
        calendarInstance = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'ar',
            direction: 'rtl',
            height: 'auto',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            firstDay: 6,
            navLinks: true,
            dayMaxEvents: true,
            events: function(fetchInfo, successCallback, failureCallback) {
                var apiUrl = calendarEl.dataset.apiUrl;
                if (!apiUrl) {
                    successCallback([]);
                    return;
                }
                fetch(apiUrl + '?start=' + fetchInfo.startStr + '&end=' + fetchInfo.endStr, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    successCallback(data);
                })
                .catch(function(error) {
                    console.error('Error fetching events:', error);
                    failureCallback(error);
                });
            },
            selectable: true,
            selectMirror: true,
            select: function(info) {
                document.getElementById('eventForm').reset();
                document.getElementById('eventId').value = '';
                document.getElementById('addEventModalLabel').textContent = 'إضافة حدث جديد';
                
                var startDate = new Date(info.start);
                var endDate = info.end ? new Date(info.end) : new Date(startDate.getTime() + 60 * 60 * 1000);
                
                document.getElementById('eventStartDate').value = formatDateTimeLocal(startDate);
                document.getElementById('eventEndDate').value = formatDateTimeLocal(endDate);
                document.getElementById('eventAllDay').checked = info.allDay;
                
                var modal = new bootstrap.Modal(document.getElementById('addEventModal'));
                modal.show();
                
                calendarInstance.unselect();
            },
            eventClick: function(info) {
                var event = info.event;
                var extendedProps = event.extendedProps;
                
                var eventIcon = 'fa-calendar';
                var eventTypeLabel = 'حدث تقويم';
                var eventTypeClass = 'text-primary';
                
                if (extendedProps.type === 'quiz') {
                    eventIcon = 'fa-clipboard-check';
                    eventTypeLabel = 'اختبار';
                    eventTypeClass = 'text-warning';
                } else if (extendedProps.type === 'assignment') {
                    eventIcon = 'fa-tasks';
                    eventTypeLabel = 'واجب';
                    eventTypeClass = 'text-danger';
                } else if (extendedProps.event_type === 'meeting') {
                    eventIcon = 'fa-users';
                    eventTypeLabel = 'اجتماع';
                    eventTypeClass = 'text-info';
                } else if (extendedProps.event_type === 'holiday') {
                    eventIcon = 'fa-calendar-times';
                    eventTypeLabel = 'عطلة';
                    eventTypeClass = 'text-success';
                } else if (extendedProps.event_type === 'exam') {
                    eventIcon = 'fa-file-alt';
                    eventTypeLabel = 'امتحان';
                    eventTypeClass = 'text-danger';
                }
                
                document.getElementById('eventModalLabel').innerHTML = '<i class="fas ' + eventIcon + ' me-2"></i>' + event.title;
                
                var bodyContent = '<div class="event-details">';
                bodyContent += '<div class="mb-3"><span class="badge ' + eventTypeClass + ' bg-light"><i class="fas ' + eventIcon + ' me-1"></i>' + eventTypeLabel + '</span></div>';
                
                if (extendedProps.description) {
                    bodyContent += '<div class="mb-3"><strong><i class="fas fa-align-right me-2 text-muted"></i>الوصف:</strong><p class="mt-1 mb-0">' + extendedProps.description + '</p></div>';
                }
                
                if (extendedProps.location) {
                    bodyContent += '<div class="mb-3"><strong><i class="fas fa-map-marker-alt me-2 text-muted"></i>الموقع:</strong><span class="ms-2">' + extendedProps.location + '</span></div>';
                }
                
                if (event.start) {
                    bodyContent += '<div class="mb-3"><strong><i class="fas fa-clock me-2 text-muted"></i>البدء:</strong><span class="ms-2">' + event.start.toLocaleString('ar-SA') + '</span></div>';
                }
                
                if (event.end) {
                    bodyContent += '<div class="mb-3"><strong><i class="fas fa-flag-checkered me-2 text-muted"></i>الانتهاء:</strong><span class="ms-2">' + event.end.toLocaleString('ar-SA') + '</span></div>';
                }
                
                bodyContent += '</div>';
                document.getElementById('eventModalBody').innerHTML = bodyContent;
                
                if (extendedProps.type === 'calendar_event' && extendedProps.event_id) {
                    document.getElementById('editEventBtn').style.display = 'inline-block';
                    document.getElementById('editEventBtn').setAttribute('data-event-id', extendedProps.event_id);
                    document.getElementById('deleteEventBtn').style.display = 'inline-block';
                    document.getElementById('deleteEventBtn').setAttribute('data-event-id', extendedProps.event_id);
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
                
                var modal = new bootstrap.Modal(document.getElementById('eventModal'));
                modal.show();
            },
            datesSet: function(dateInfo) {
                loadNotes(dateInfo.startStr, dateInfo.endStr);
            }
        });
        
        calendarInstance.render();
        console.log('FullCalendar rendered successfully!');
        
        // جلب الملاحظات الأولية
        var view = calendarInstance.view;
        loadNotes(view.activeStart.toISOString().split('T')[0], view.activeEnd.toISOString().split('T')[0]);
    }
    
    // Event Listeners
    function setupEventListeners() {
        // معالجة form الأحداث
        var eventForm = document.getElementById('eventForm');
        if (eventForm) {
            eventForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                var submitBtn = this.querySelector('button[type="submit"]');
                var originalBtnText = submitBtn ? submitBtn.innerHTML : '';
                
                // إضافة loading state
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> جاري الحفظ...';
                }
                
                var formData = new FormData(this);
                var eventId = document.getElementById('eventId').value;
                var url = eventId ? '/student/calendar/events/' + eventId : '/student/calendar/events';
                var method = eventId ? 'PUT' : 'POST';
                
                var data = {
                    title: formData.get('title'),
                    description: formData.get('description'),
                    event_type: formData.get('event_type'),
                    start_date: datetimeLocalToISO(formData.get('start_date')),
                    end_date: datetimeLocalToISO(formData.get('end_date')),
                    is_all_day: document.getElementById('eventAllDay').checked,
                    location: formData.get('location'),
                    color: formData.get('color'),
                    _method: method
                };
                
                console.log('Sending event data:', data);
                
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(function(response) {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        return response.json().then(function(err) {
                            throw new Error(JSON.stringify(err));
                        });
                    }
                    return response.json();
                })
                .then(function(result) {
                    console.log('Response result:', result);
                    if (result.success) {
                        bootstrap.Modal.getInstance(document.getElementById('addEventModal')).hide();
                        if (calendarInstance) calendarInstance.refetchEvents();
                        eventForm.reset();
                    } else {
                        alert(result.message || 'حدث خطأ أثناء حفظ الحدث');
                    }
                })
                .catch(function(error) {
                    console.error('Error:', error);
                    try {
                        var errObj = JSON.parse(error.message);
                        if (errObj.errors) {
                            // عرض validation errors
                            var errorMessages = Object.values(errObj.errors).flat().join('\n');
                            alert('أخطاء في البيانات:\n' + errorMessages);
                        } else {
                            alert(errObj.message || 'حدث خطأ أثناء حفظ الحدث');
                        }
                    } catch (e) {
                        alert('حدث خطأ أثناء حفظ الحدث. يرجى التحقق من البيانات والمحاولة مرة أخرى.');
                    }
                })
                .finally(function() {
                    // إعادة تعيين loading state
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }
                });
            });
        }
        
        // معالجة form الملاحظات
        var noteForm = document.getElementById('noteForm');
        if (noteForm) {
            noteForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                var submitBtn = this.querySelector('button[type="submit"]');
                var originalBtnText = submitBtn ? submitBtn.innerHTML : '';
                
                // إضافة loading state
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> جاري الحفظ...';
                }
                
                var formData = new FormData(this);
                var noteId = document.getElementById('noteId').value;
                var url = noteId ? '/student/calendar/notes/' + noteId : '/student/calendar/notes';
                var method = noteId ? 'PUT' : 'POST';
                
                var data = {
                    note_date: formData.get('note_date'),
                    title: formData.get('title'),
                    content: formData.get('content'),
                    color: formData.get('color'),
                    is_pinned: document.getElementById('noteIsPinned').checked,
                    _method: method
                };
                
                console.log('Sending note data:', data);
                
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(function(response) {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        return response.json().then(function(err) {
                            throw new Error(JSON.stringify(err));
                        });
                    }
                    return response.json();
                })
                .then(function(result) {
                    console.log('Response result:', result);
                    if (result.success) {
                        bootstrap.Modal.getInstance(document.getElementById('addNoteModal')).hide();
                        if (calendarInstance) {
                            var view = calendarInstance.view;
                            loadNotes(view.activeStart.toISOString().split('T')[0], view.activeEnd.toISOString().split('T')[0]);
                        }
                        noteForm.reset();
                    } else {
                        alert(result.message || 'حدث خطأ أثناء حفظ الملاحظة');
                    }
                })
                .catch(function(error) {
                    console.error('Error:', error);
                    try {
                        var errObj = JSON.parse(error.message);
                        if (errObj.errors) {
                            // عرض validation errors
                            var errorMessages = Object.values(errObj.errors).flat().join('\n');
                            alert('أخطاء في البيانات:\n' + errorMessages);
                        } else {
                            alert(errObj.message || 'حدث خطأ أثناء حفظ الملاحظة');
                        }
                    } catch (e) {
                        alert('حدث خطأ أثناء حفظ الملاحظة. يرجى التحقق من البيانات والمحاولة مرة أخرى.');
                    }
                })
                .finally(function() {
                    // إعادة تعيين loading state
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }
                });
            });
        }
        
        // فلترة الملاحظات
        var filterNotesBtn = document.getElementById('filterNotesBtn');
        if (filterNotesBtn) {
            filterNotesBtn.addEventListener('click', function() {
                var filterDate = document.getElementById('notesDateFilter').value;
                renderNotesList(filterDate || null);
            });
        }
        
        var notesDateFilter = document.getElementById('notesDateFilter');
        if (notesDateFilter) {
            notesDateFilter.addEventListener('change', function() {
                if (this.value) renderNotesList(this.value);
            });
        }
        
        // تعديل الحدث
        var editEventBtn = document.getElementById('editEventBtn');
        if (editEventBtn) {
            editEventBtn.addEventListener('click', function() {
                var eventId = this.getAttribute('data-event-id');
                bootstrap.Modal.getInstance(document.getElementById('eventModal')).hide();
                
                fetch('/student/calendar/events/' + eventId)
                .then(function(response) { return response.json(); })
                .then(function(event) {
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
                    
                    var modal = new bootstrap.Modal(document.getElementById('addEventModal'));
                    modal.show();
                })
                .catch(function(error) {
                    console.error('Error:', error);
                    alert('حدث خطأ أثناء جلب بيانات الحدث');
                });
            });
        }
        
        // حذف الحدث
        var deleteEventBtn = document.getElementById('deleteEventBtn');
        if (deleteEventBtn) {
            deleteEventBtn.addEventListener('click', function() {
                if (!confirm('هل أنت متأكد من حذف هذا الحدث؟')) return;
                
                var eventId = this.getAttribute('data-event-id');
                
                fetch('/student/calendar/events/' + eventId, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(function(response) { return response.json(); })
                .then(function(result) {
                    if (result.success) {
                        bootstrap.Modal.getInstance(document.getElementById('eventModal')).hide();
                        if (calendarInstance) calendarInstance.refetchEvents();
                    } else {
                        alert(result.message || 'حدث خطأ أثناء حذف الحدث');
                    }
                })
                .catch(function(error) {
                    console.error('Error:', error);
                    alert('حدث خطأ أثناء حذف الحدث');
                });
            });
        }
        
        // إعادة تعيين Modal عند الإغلاق
        var addEventModal = document.getElementById('addEventModal');
        if (addEventModal) {
            addEventModal.addEventListener('hidden.bs.modal', function() {
                document.getElementById('eventForm').reset();
                document.getElementById('eventId').value = '';
                document.getElementById('addEventModalLabel').textContent = 'إضافة حدث جديد';
                document.getElementById('eventColor').value = '#10b981';
            });
        }
        
        // عند النقر على زر إضافة ملاحظة (من الزر الأزرق)
        var addNoteBtn = document.querySelector('[data-bs-target="#addNoteModal"]');
        if (addNoteBtn) {
            addNoteBtn.addEventListener('click', function() {
                // إعادة تعيين النموذج قبل فتح الـ modal
                addNote();
            });
        }
        
        // عند فتح الـ modal، التحقق من حالة التعديل
        var addNoteModal = document.getElementById('addNoteModal');
        if (addNoteModal) {
            addNoteModal.addEventListener('show.bs.modal', function() {
                // إذا كان noteId فارغاً، فهذا يعني إضافة جديدة
                var noteId = document.getElementById('noteId').value;
                if (!noteId) {
                    addNote();
                }
                // إذا كان noteId موجوداً، لا تفعل شيئاً (التعديل)
            });
        }
        
        // تأكيد حذف الملاحظة
        var confirmDeleteNoteBtn = document.getElementById('confirmDeleteNoteBtn');
        if (confirmDeleteNoteBtn) {
            confirmDeleteNoteBtn.addEventListener('click', function() {
                confirmDeleteNote();
            });
        }
        
        // إعادة تعيين noteIdToDelete عند إغلاق modal
        var deleteNoteModal = document.getElementById('deleteNoteModal');
        if (deleteNoteModal) {
            deleteNoteModal.addEventListener('hidden.bs.modal', function() {
                noteIdToDelete = null;
            });
        }
    }
    
    // بدء التهيئة عند تحميل الصفحة
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                initCalendar();
                setupEventListeners();
            }, 200);
        });
    } else {
        setTimeout(function() {
            initCalendar();
            setupEventListeners();
        }, 200);
    }
})();
</script>
@endpush
