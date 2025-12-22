/**
 * Calendar JavaScript for Quantum LMS
 * Handles FullCalendar initialization and event management
 */

(function() {
    'use strict';

    // Initialize calendar if element exists
    if (document.getElementById('calendar')) {
        initializeCalendar();
    }

    function initializeCalendar() {
        const calendarEl = document.getElementById('calendar');
        if (!calendarEl) return;

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
                const apiUrl = calendarEl.dataset.apiUrl || window.calendarApiUrl;
                if (!apiUrl) {
                    console.error('Calendar API URL not found');
                    failureCallback('API URL not configured');
                    return;
                }

                fetch(apiUrl + '?start=' + fetchInfo.startStr + '&end=' + fetchInfo.endStr, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    successCallback(data);
                })
                .catch(error => {
                    console.error('Error fetching calendar events:', error);
                    failureCallback(error);
                });
            },
            eventClick: function(info) {
                handleEventClick(info);
            },
            eventDisplay: 'block',
            height: 'auto',
            editable: false,
            selectable: false,
        });

        calendar.render();
        window.calendarInstance = calendar; // Store for potential future use
    }

    function handleEventClick(info) {
        const event = info.event;
        const extendedProps = event.extendedProps;
        const modal = document.getElementById('eventModal');
        
        if (!modal) return;

        // Set modal title
        const modalTitle = modal.querySelector('#eventModalLabel');
        if (modalTitle) {
            modalTitle.textContent = event.title;
        }

        // Build modal body content
        let bodyContent = '';
        
        if (extendedProps.description) {
            bodyContent += '<p><strong>الوصف:</strong> ' + escapeHtml(extendedProps.description) + '</p>';
        }
        
        if (extendedProps.location) {
            bodyContent += '<p><strong>الموقع:</strong> ' + escapeHtml(extendedProps.location) + '</p>';
        }
        
        const eventTypeLabels = {
            'calendar_event': 'حدث تقويم',
            'quiz': 'اختبار',
            'assignment': 'واجب'
        };
        
        bodyContent += '<p><strong>النوع:</strong> ' + (eventTypeLabels[extendedProps.type] || extendedProps.type) + '</p>';
        
        bodyContent += '<p><strong>البدء:</strong> ' + formatDate(event.start) + '</p>';
        
        if (event.end) {
            bodyContent += '<p><strong>الانتهاء:</strong> ' + formatDate(event.end) + '</p>';
        }
        
        const modalBody = modal.querySelector('#eventModalBody');
        if (modalBody) {
            modalBody.innerHTML = bodyContent;
        }
        
        // Set URL if available
        const modalUrl = modal.querySelector('#eventModalUrl');
        if (modalUrl) {
            if (extendedProps.url) {
                modalUrl.href = extendedProps.url;
                modalUrl.style.display = 'inline-block';
            } else {
                modalUrl.style.display = 'none';
            }
        }
        
        // Show modal
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
    }

    function formatDate(date) {
        if (!date) return '';
        return new Date(date).toLocaleString('ar-SA', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
})();

