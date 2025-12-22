@extends('student.layouts.master')

@section('page-title')
    المساعد التعليمي
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">المساعد التعليمي</h5>
            </div>
            <div>
                <a href="{{ route('student.ai.chatbot.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> محادثة جديدة
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
                        @forelse($conversations as $conversation)
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">
                                                <a href="{{ route('student.ai.chatbot.show', $conversation->id) }}" class="text-decoration-none">
                                                    {{ $conversation->title ?? 'محادثة بدون عنوان' }}
                                                </a>
                                            </h6>
                                            <p class="text-muted mb-1">
                                                @if($conversation->subject)
                                                    <span class="badge bg-info me-2">{{ $conversation->subject->name }}</span>
                                                @endif
                                                @if($conversation->lesson)
                                                    <span class="badge bg-secondary me-2">{{ $conversation->lesson->title }}</span>
                                                @endif
                                                <span class="badge bg-primary">{{ \App\Models\AIConversation::TYPES[$conversation->conversation_type] }}</span>
                                            </p>
                                            <small class="text-muted">
                                                {{ $conversation->message_count }} رسالة
                                                @if($conversation->last_message_at)
                                                    • آخر رسالة: {{ $conversation->last_message_at->diffForHumans() }}
                                                @endif
                                            </small>
                                        </div>
                                        <form action="{{ route('student.ai.chatbot.destroy', $conversation->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه المحادثة؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                <p class="text-muted">لا توجد محادثات بعد.</p>
                                <a href="{{ route('student.ai.chatbot.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> إنشاء محادثة جديدة
                                </a>
                            </div>
                        @endforelse

                        @if($conversations->hasPages())
                            <div class="mt-3">
                                {{ $conversations->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

