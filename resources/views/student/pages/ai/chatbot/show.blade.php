@extends('student.layouts.master')

@section('page-title')
    {{ $conversation->title ?? 'المحادثة' }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1" id="conversation-title">{{ $conversation->title ?? 'المحادثة' }}</h5>
                <!-- Context Indicator -->
                <div class="mt-2">
                    @if($conversation->lesson)
                        <span class="badge bg-info me-1"><i class="fas fa-book me-1"></i> درس: {{ $conversation->lesson->title }}</span>
                    @elseif($conversation->subject)
                        <span class="badge bg-primary me-1"><i class="fas fa-book-open me-1"></i> مادة: {{ $conversation->subject->name }}</span>
                    @else
                        <span class="badge bg-secondary me-1"><i class="fas fa-comments me-1"></i> محادثة عامة</span>
                    @endif
                </div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm" id="change-context-btn" data-bs-toggle="modal" data-bs-target="#changeContextModal">
                    <i class="fas fa-exchange-alt me-1"></i> تغيير السياق
                </button>
                <button class="btn btn-outline-secondary btn-sm" id="rename-conversation-btn" data-bs-toggle="modal" data-bs-target="#renameModal">
                    <i class="fas fa-edit me-1"></i> إعادة تسمية
                </button>
                <a href="{{ route('student.ai.chatbot.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i> رجوع
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div id="chat-messages" class="chat-messages" style="min-height: 400px; max-height: 600px; overflow-y: auto; padding: 20px;">
                            @foreach($messages as $message)
                                <div class="message mb-3 {{ $message->role === 'user' ? 'text-end' : 'text-start' }}" data-message-id="{{ $message->id }}">
                                    <div class="d-inline-block p-3 rounded {{ $message->role === 'user' ? 'bg-primary text-white' : 'bg-light' }}" style="max-width: 70%;">
                                        @if($message->quick_action)
                                            <span class="badge bg-info mb-2">
                                                @php
                                                    $actions = [
                                                        'simple_explanation' => 'شرح بسيط',
                                                        'example' => 'مثال',
                                                        'summary' => 'ملخص',
                                                        'review_questions' => 'أسئلة للمراجعة',
                                                        'important_terms' => 'مصطلحات مهمة',
                                                    ];
                                                @endphp
                                                {{ $actions[$message->quick_action] ?? $message->quick_action }}
                                            </span>
                                        @endif
                                        <div class="message-content">{!! nl2br(e($message->content)) !!}</div>
                                        
                                        @if($message->hasAttachments())
                                            <div class="mt-2">
                                                @foreach($message->attachments as $attachment)
                                                    @if($attachment->isImage())
                                                        <img src="{{ $attachment->url }}" class="img-thumbnail me-2 mb-2" style="max-width: 200px;" alt="{{ $attachment->file_name }}">
                                                    @else
                                                        <a href="{{ $attachment->url }}" class="btn btn-sm btn-outline-{{ $message->role === 'user' ? 'light' : 'secondary' }} me-2 mb-2" target="_blank">
                                                            <i class="fas fa-file me-1"></i>{{ $attachment->file_name }}
                                                        </a>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                        
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <small class="{{ $message->role === 'user' ? 'text-white-50' : 'text-muted' }}">
                                                {{ $message->created_at->format('H:i') }}
                                            </small>
                                            @if($message->role === 'assistant')
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-sm btn-link p-0 text-muted copy-message-btn" data-message-id="{{ $message->id }}" title="نسخ الرسالة">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-link p-0 text-muted bookmark-btn {{ $message->is_bookmarked ? 'text-warning' : '' }}" data-message-id="{{ $message->id }}" data-bookmarked="{{ $message->is_bookmarked ? 'true' : 'false' }}" title="{{ $message->is_bookmarked ? 'إزالة الإشارة المرجعية' : 'إضافة إشارة مرجعية' }}">
                                                        <i class="{{ $message->is_bookmarked ? 'fas' : 'far' }} fa-bookmark"></i>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Quick Actions -->
                        <div class="quick-actions mb-3 border-top pt-3">
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary quick-action-btn" data-action="simple_explanation">
                                    <i class="fas fa-lightbulb me-1"></i> شرح بسيط
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary quick-action-btn" data-action="example">
                                    <i class="fas fa-list-ul me-1"></i> مثال
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary quick-action-btn" data-action="summary">
                                    <i class="fas fa-file-alt me-1"></i> ملخص
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary quick-action-btn" data-action="review_questions">
                                    <i class="fas fa-question-circle me-1"></i> أسئلة للمراجعة
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary quick-action-btn" data-action="important_terms">
                                    <i class="fas fa-tags me-1"></i> مصطلحات مهمة
                                </button>
                            </div>
                        </div>

                        <div class="chat-input mt-3 border-top pt-3">
                            <form id="message-form" action="{{ route('student.ai.chatbot.send-message', $conversation->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="quick_action" id="quick_action_input" value="">
                                <div id="attachments-preview" class="mb-2"></div>
                                <div class="input-group">
                                    <button type="button" class="btn btn-outline-secondary" id="attach-file-btn" title="إرفاق ملف">
                                        <i class="fas fa-paperclip"></i>
                                    </button>
                                    <input type="file" id="file-input" name="attachments[]" multiple accept="image/*,.txt,.pdf,.doc,.docx" style="display: none;">
                                    <input type="text" class="form-control" id="message-input" name="message" placeholder="اكتب رسالتك هنا..." required>
                                    <button type="submit" class="btn btn-primary" id="send-btn">
                                        <i class="fas fa-paper-plane"></i> إرسال
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Context Modal -->
<div class="modal fade" id="changeContextModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تغيير السياق</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form id="change-context-form">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="context_subject_id" class="form-label">المادة (اختياري)</label>
                        <select class="form-select" id="context_subject_id" name="subject_id">
                            <option value="">محادثة عامة</option>
                            @foreach(Auth::user()->subjects()->active()->get() as $subject)
                                <option value="{{ $subject->id }}" {{ $conversation->subject_id == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="context_lesson_id" class="form-label">الدرس (اختياري)</label>
                        <select class="form-select" id="context_lesson_id" name="lesson_id" {{ !$conversation->subject_id ? 'disabled' : '' }}>
                            <option value="">اختر الدرس</option>
                            @if($conversation->subject_id)
                                @foreach(\App\Models\Lesson::whereHas('unit.section', function($q) use ($conversation) {
                                    $q->where('subject_id', $conversation->subject_id);
                                })->active()->get() as $lesson)
                                    <option value="{{ $lesson->id }}" {{ $conversation->lesson_id == $lesson->id ? 'selected' : '' }}>{{ $lesson->title }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">تغيير السياق</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Rename Modal -->
<div class="modal fade" id="renameModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إعادة تسمية المحادثة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form id="rename-form">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="new_title" class="form-label">اسم المحادثة</label>
                        <input type="text" class="form-control" id="new_title" name="title" value="{{ $conversation->title }}" required maxlength="255">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@push('styles')
<style>
.chat-messages {
    background: #f8f9fa;
    border-radius: 8px;
}
.message-content {
    word-wrap: break-word;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('message-form');
    const input = document.getElementById('message-input');
    const sendBtn = document.getElementById('send-btn');
    const messagesContainer = document.getElementById('chat-messages');

    // التمرير للأسفل
    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    scrollToBottom();

    // Quick Actions
    let selectedQuickAction = null;
    const quickActionButtons = document.querySelectorAll('.quick-action-btn');
    quickActionButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.dataset.action;
            selectedQuickAction = action;
            document.getElementById('quick_action_input').value = action;
            
            // Update button styles
            quickActionButtons.forEach(b => b.classList.remove('active', 'btn-primary'));
            quickActionButtons.forEach(b => b.classList.add('btn-outline-primary'));
            this.classList.remove('btn-outline-primary');
            this.classList.add('active', 'btn-primary');
            
            // Focus on input
            input.focus();
        });
    });

    // File Attachments
    const attachFileBtn = document.getElementById('attach-file-btn');
    const fileInput = document.getElementById('file-input');
    const attachmentsPreview = document.getElementById('attachments-preview');
    let selectedFiles = [];

    attachFileBtn.addEventListener('click', function() {
        fileInput.click();
    });

    fileInput.addEventListener('change', function() {
        const files = Array.from(this.files);
        if (files.length > 5) {
            alert('يمكن إرفاق 5 ملفات كحد أقصى');
            return;
        }

        selectedFiles = files;
        displayAttachmentsPreview();
    });

    function displayAttachmentsPreview() {
        attachmentsPreview.innerHTML = '';
        selectedFiles.forEach((file, index) => {
            const fileDiv = document.createElement('div');
            fileDiv.className = 'd-inline-block me-2 mb-2 p-2 bg-light rounded border';
            fileDiv.innerHTML = `
                <span>${file.name}</span>
                <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-1" onclick="removeAttachment(${index})">
                    <i class="fas fa-times"></i>
                </button>
            `;
            attachmentsPreview.appendChild(fileDiv);
        });
    }

    window.removeAttachment = function(index) {
        selectedFiles.splice(index, 1);
        displayAttachmentsPreview();
        
        // Update file input
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        fileInput.files = dt.files;
    };

    // Form Submit
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const message = input.value.trim();
        if (!message && selectedFiles.length === 0) return;

        // إضافة رسالة المستخدم
        addMessageToChat('user', message || (selectedQuickAction ? 'استخدم ' + getQuickActionText(selectedQuickAction) : ''));
        
        const formData = new FormData(form);
        formData.set('message', message || '');
        
        input.value = '';
        selectedFiles = [];
        fileInput.value = '';
        attachmentsPreview.innerHTML = '';
        selectedQuickAction = null;
        document.getElementById('quick_action_input').value = '';
        quickActionButtons.forEach(b => {
            b.classList.remove('active', 'btn-primary');
            b.classList.add('btn-outline-primary');
        });
        
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الإرسال...';

        // إرسال الطلب
        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> إرسال';

            if (data.success) {
                addMessageToChat('assistant', data.message.content, data.message.attachments || [], data.message.id);
            } else {
                addMessageToChat('assistant', 'عذراً، حدث خطأ: ' + (data.error || 'خطأ غير معروف'));
            }
        })
        .catch(error => {
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> إرسال';
            addMessageToChat('assistant', 'عذراً، حدث خطأ في الاتصال.');
            console.error('Error:', error);
        });
    });

    function getQuickActionText(action) {
        const texts = {
            'simple_explanation': 'شرح بسيط',
            'example': 'مثال',
            'summary': 'ملخص',
            'review_questions': 'أسئلة للمراجعة',
            'important_terms': 'مصطلحات مهمة',
        };
        return texts[action] || action;
    }

    function addMessageToChat(role, content, attachments = [], messageId = null) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message mb-3 ${role === 'user' ? 'text-end' : 'text-start'}`;
        if (messageId) {
            messageDiv.setAttribute('data-message-id', messageId);
        }
        
        const bgClass = role === 'user' ? 'bg-primary text-white' : 'bg-light';
        const timeClass = role === 'user' ? 'text-white-50' : 'text-muted';
        const time = new Date().toLocaleTimeString('ar-SA', { hour: '2-digit', minute: '2-digit' });

        let attachmentsHtml = '';
        if (attachments && attachments.length > 0) {
            attachmentsHtml = '<div class="mt-2">';
            attachments.forEach(att => {
                if (att.file_type === 'image') {
                    attachmentsHtml += `<img src="${att.url}" class="img-thumbnail me-2 mb-2" style="max-width: 200px;" alt="${att.file_name}">`;
                } else {
                    attachmentsHtml += `<a href="${att.url}" class="btn btn-sm btn-outline-secondary me-2 mb-2" target="_blank"><i class="fas fa-file me-1"></i>${att.file_name}</a>`;
                }
            });
            attachmentsHtml += '</div>';
        }

        let actionButtonsHtml = '';
        if (role === 'assistant') {
            actionButtonsHtml = `
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-link p-0 text-muted copy-message-btn" data-message-id="${messageId || ''}" title="نسخ الرسالة">
                        <i class="fas fa-copy"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-link p-0 text-muted bookmark-btn" data-message-id="${messageId || ''}" data-bookmarked="false" title="إضافة إشارة مرجعية">
                        <i class="far fa-bookmark"></i>
                    </button>
                </div>
            `;
        }

        messageDiv.innerHTML = `
            <div class="d-inline-block p-3 rounded ${bgClass}" style="max-width: 70%;">
                <div class="message-content">${content.replace(/\n/g, '<br>')}</div>
                ${attachmentsHtml}
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <small class="${timeClass}">${time}</small>
                    ${actionButtonsHtml}
                </div>
            </div>
        `;

        messagesContainer.appendChild(messageDiv);
        scrollToBottom();
    }

    // Change Context
    const changeContextForm = document.getElementById('change-context-form');
    const contextSubjectSelect = document.getElementById('context_subject_id');
    const contextLessonSelect = document.getElementById('context_lesson_id');

    contextSubjectSelect.addEventListener('change', function() {
        const subjectId = this.value;
        if (subjectId) {
            contextLessonSelect.disabled = false;
            fetch(`{{ url('student/subjects') }}/${subjectId}/lessons`)
                .then(response => response.json())
                .then(data => {
                    contextLessonSelect.innerHTML = '<option value="">اختر الدرس</option>';
                    data.forEach(lesson => {
                        contextLessonSelect.innerHTML += `<option value="${lesson.id}">${lesson.title}</option>`;
                    });
                })
                .catch(error => console.error('Error fetching lessons:', error));
        } else {
            contextLessonSelect.disabled = true;
            contextLessonSelect.innerHTML = '<option value="">اختر المادة أولاً</option>';
        }
    });

    changeContextForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch(`{{ url('student/ai/chatbot') }}/{{ $conversation->id }}/update-context`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Reload to show new context
            } else {
                alert('حدث خطأ: ' + (data.error || 'خطأ غير معروف'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في الاتصال.');
        });
    });

    // Rename Conversation
    const renameForm = document.getElementById('rename-form');
    renameForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch(`{{ url('student/ai/chatbot') }}/{{ $conversation->id }}/rename`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('conversation-title').textContent = data.title;
                const modal = bootstrap.Modal.getInstance(document.getElementById('renameModal'));
                modal.hide();
            } else {
                alert('حدث خطأ: ' + (data.error || 'خطأ غير معروف'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في الاتصال.');
        });
    });

    // Copy Message functionality
    document.addEventListener('click', function(e) {
        if (e.target.closest('.copy-message-btn')) {
            e.preventDefault();
            e.stopPropagation();
            
            const btn = e.target.closest('.copy-message-btn');
            const messageElement = btn.closest('.message');
            const messageContentElement = messageElement.querySelector('.message-content');
            
            // استخراج النص من الـ HTML
            let textToCopy = messageContentElement.innerText || messageContentElement.textContent;
            
            // نسخ النص إلى clipboard
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(textToCopy).then(function() {
                    // تغيير أيقونة الزر مؤقتاً للإشارة إلى النجاح
                    const icon = btn.querySelector('i');
                    const originalClass = icon.className;
                    icon.className = 'fas fa-check text-success';
                    btn.title = 'تم النسخ!';
                    
                    // إعادة الأيقونة الأصلية بعد ثانيتين
                    setTimeout(function() {
                        icon.className = originalClass;
                        btn.title = 'نسخ الرسالة';
                    }, 2000);
                }).catch(function(err) {
                    console.error('فشل النسخ:', err);
                    alert('فشل نسخ النص. يرجى المحاولة مرة أخرى.');
                });
            } else {
                // Fallback للمتصفحات القديمة
                const textArea = document.createElement('textarea');
                textArea.value = textToCopy;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                document.body.appendChild(textArea);
                textArea.select();
                
                try {
                    document.execCommand('copy');
                    const icon = btn.querySelector('i');
                    const originalClass = icon.className;
                    icon.className = 'fas fa-check text-success';
                    btn.title = 'تم النسخ!';
                    
                    setTimeout(function() {
                        icon.className = originalClass;
                        btn.title = 'نسخ الرسالة';
                    }, 2000);
                } catch (err) {
                    console.error('فشل النسخ:', err);
                    alert('فشل نسخ النص. يرجى المحاولة مرة أخرى.');
                } finally {
                    document.body.removeChild(textArea);
                }
            }
        }
    });

    // Bookmark functionality
    document.addEventListener('click', function(e) {
        if (e.target.closest('.bookmark-btn')) {
            e.preventDefault();
            e.stopPropagation();
            
            const btn = e.target.closest('.bookmark-btn');
            const messageId = btn.dataset.messageId;
            const isBookmarked = btn.dataset.bookmarked === 'true';

            const conversationId = {{ $conversation->id }};
            fetch(`{{ url('student/ai/chatbot') }}/${conversationId}/messages/${messageId}/toggle-bookmark`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    btn.dataset.bookmarked = data.is_bookmarked ? 'true' : 'false';
                    const icon = btn.querySelector('i');
                    if (data.is_bookmarked) {
                        icon.classList.add('fas');
                        icon.classList.remove('far');
                        btn.title = 'إزالة الإشارة المرجعية';
                        btn.classList.add('text-warning');
                    } else {
                        icon.classList.add('far');
                        icon.classList.remove('fas');
                        btn.title = 'إضافة إشارة مرجعية';
                        btn.classList.remove('text-warning');
                    }
                }
            })
            .catch(error => {
                console.error('Error toggling bookmark:', error);
            });
        }
    });
});
</script>
@endpush

