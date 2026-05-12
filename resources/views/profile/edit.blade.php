@extends('admin.layouts.master')

@section('page-title')
    تعديل الملف الشخصي
@stop

@section('css')
    <style>
        .photo-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e9ecef;
        }
        
        .photo-upload {
            position: relative;
            display: inline-block;
        }
        
        .photo-upload input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
    </style>
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="page-header d-flex justify-content-between align-items-center my-4">
                <h5 class="page-title mb-0">تعديل الملف الشخصي</h5>
            </div>

            <!-- Success/Error Messages -->
            @if (session('status') === 'profile-updated')
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>نجح!</strong> تم تحديث الملف الشخصي بنجاح.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>خطأ!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>خطأ في البيانات!</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="row g-3">
                <!-- معلومات الملف الشخصي -->
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">معلومات الملف الشخصي</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('profile.update') }}">
                                @csrf
                                @method('PATCH')

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">الاسم الكامل <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               class="form-control @error('name') is-invalid @enderror" 
                                               id="name" 
                                               name="name" 
                                               value="{{ old('name', $user->name) }}" 
                                               required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="email" class="form-label">البريد الإلكتروني <span class="text-danger">*</span></label>
                                        <input type="email" 
                                               class="form-control @error('email') is-invalid @enderror" 
                                               id="email" 
                                               name="email" 
                                               value="{{ old('email', $user->email) }}" 
                                               required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    @if($user->phone)
                                    <div class="col-md-6">
                                        <label class="form-label">رقم الهاتف</label>
                                        <input type="text" 
                                               class="form-control" 
                                               value="{{ $user->phone }}" 
                                               disabled>
                                        <small class="text-muted">للتعديل، يرجى التواصل مع الأدمن</small>
                                    </div>
                                    @endif

                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> حفظ التغييرات
                                        </button>
                                        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                                            <i class="fas fa-times me-1"></i> إلغاء
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- معلومات الحساب -->
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                @if($user->photo)
                                    <img src="{{ media_public_url($user->photo) }}" 
                                         alt="{{ $user->name }}" 
                                         class="photo-preview">
                                @else
                                    <div class="photo-preview d-inline-flex align-items-center justify-content-center bg-primary text-white" 
                                         style="font-size: 48px;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                            <p class="text-muted mb-2">{{ $user->email }}</p>
                            
                            <div class="mb-2">
                                @foreach($user->getRoleNames() as $role)
                                    <span class="badge bg-primary me-1">{{ $role }}</span>
                                @endforeach
                            </div>

                            <div class="mb-2">
                                @if($user->is_active)
                                    <span class="badge bg-success">حساب نشط</span>
                                @else
                                    <span class="badge bg-danger">حساب غير نشط</span>
                                @endif
                            </div>

                            <p class="text-muted small mb-0">
                                آخر دخول:
                                {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'لا يوجد' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
@stop
