@extends('student.layouts.master')

@section('page-title')
    {{ $conversation->title ?? 'المحادثة' }}
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">{{ $conversation->title ?? 'المحادثة' }}</h5>
            </div>
            <div>
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
                                <div class="message mb-3 {{ $message->role === 'user' ? 'text-end' : 'text-start' }}">
                                    <div class="d-inline-block p-3 rounded {{ $message->role === 'user' ? 'bg-primary text-white' : 'bg-light' }}" style="max-width: 70%;">
                                        <div class="message-content">{!! nl2br(e($message->content)) !!}</div>
                                        <small class="d-block mt-2 {{ $message->role === 'user' ? 'text-white-50' : 'text-muted' }}">
                                            {{ $message->created_at->format('H:i') }}
                                        </small>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="chat-input mt-3 border-top pt-3">
                            <form id="message-form" action="{{ route('student.ai.chatbot.send-message', $conversation->id) }}" method="POST">
                                @csrf
                                <div class="input-group">
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

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const message = input.value.trim();
        if (!message) return;

        // إضافة رسالة المستخدم
        addMessageToChat('user', message);
        input.value = '';
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        // إرسال الطلب
        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ message: message })
        })
        .then(response => response.json())
        .then(data => {
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> إرسال';

            if (data.success) {
                addMessageToChat('assistant', data.message.content);
            } else {
                addMessageToChat('assistant', 'عذراً، حدث خطأ: ' + (data.error || 'خطأ غير معروف'));
            }
        })
        .catch(error => {
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> إرسال';
            addMessageToChat('assistant', 'عذراً، حدث خطأ في الاتصال.');
        });
    });

    function addMessageToChat(role, content) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message mb-3 ${role === 'user' ? 'text-end' : 'text-start'}`;
        
        const bgClass = role === 'user' ? 'bg-primary text-white' : 'bg-light';
        const timeClass = role === 'user' ? 'text-white-50' : 'text-muted';
        const time = new Date().toLocaleTimeString('ar-SA', { hour: '2-digit', minute: '2-digit' });

        messageDiv.innerHTML = `
            <div class="d-inline-block p-3 rounded ${bgClass}" style="max-width: 70%;">
                <div class="message-content">${content.replace(/\n/g, '<br>')}</div>
                <small class="d-block mt-2 ${timeClass}">${time}</small>
            </div>
        `;

        messagesContainer.appendChild(messageDiv);
        scrollToBottom();
    }
});
</script>
@endpush
@stop

