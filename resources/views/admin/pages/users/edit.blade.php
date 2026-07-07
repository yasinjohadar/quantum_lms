@extends('admin.layouts.master')

@section('page-title')
    تعديل المستخدم
@stop

@push('styles')
    @include('admin.pages.users.partials.users-edit-styles')
    @include('admin.pages.users.partials.user-subscription-shared-styles')
@endpush

@section('content')
    @php
        $isStudent = $user->hasRole('student');
        $returnContext = old('return_context', request('return_context', request('role')));
        $selectedRoles = old('roles', $user->roles->pluck('name')->toArray());
        $emailRequired = ! $isStudent && (bool) array_intersect($selectedRoles, ['admin', 'teacher', 'supervisor']);
        $initial = mb_strtoupper(mb_substr(trim($user->name), 0, 1));
        $cancelUrl = route('users.index');
        if (in_array($returnContext, ['admin', 'manage', 'supervisor', 'teacher'], true)) {
            $cancelUrl = match ($returnContext) {
                'admin' => route('admin.admins.index'),
                'manage' => route('admin.users.manage'),
                'supervisor' => route('admin.supervisors.assignments.index'),
                'teacher' => route('admin.teachers.assignments.index'),
                default => route('users.index'),
            };
        }
    @endphp

    <div class="main-content app-content user-edit-page">
        <div class="container-fluid">
            <div class="user-edit-layout user-edit-layout--wide">

                <nav class="user-edit-breadcrumb" aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">الطلاب</a></li>
                        <li class="breadcrumb-item active" aria-current="page">تعديل</li>
                    </ol>
                </nav>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show py-2 mb-3" role="alert">
                        <i class="bi bi-check-circle me-1"></i>{!! session('success') !!}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show py-2 mb-3" role="alert">
                        <i class="bi bi-exclamation-triangle me-1"></i>{!! session('error') !!}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show py-2 mb-3" role="alert">
                        <ul class="mb-0 small">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="user-edit-toolbar">
                    <a href="{{ $cancelUrl }}"><i class="bi bi-arrow-right"></i> رجوع للقائمة</a>
                    <a href="{{ route('users.show', $user) }}"><i class="bi bi-person-badge"></i> عرض الملف</a>
                </div>

                <div class="user-edit-card">
                    <div class="user-edit-card__banner"></div>

                    <div class="user-edit-card__profile">
                        <div class="user-edit-card__avatar">{{ $initial }}</div>
                        <h1 class="user-edit-card__title">
                            {{ $isStudent ? 'تعديل بيانات الطالب' : 'تعديل المستخدم' }}
                        </h1>
                        <p class="user-edit-card__name">{{ $user->name }}</p>

                        @if ($isStudent)
                            <span class="user-edit-status user-edit-status--locked">
                                <i class="bi bi-shield-lock-fill"></i>
                                طالب — الدور مقفول
                            </span>
                        @else
                            @foreach ($user->roles as $role)
                                <span class="user-edit-status user-edit-status--student">{{ $role->name }}</span>
                            @endforeach
                        @endif
                    </div>

                    <div class="user-edit-card__form">
                        <form method="POST" action="{{ route('users.update', $user->id) }}" novalidate>
                            @csrf
                            @method('PUT')

                            @if (in_array($returnContext, ['supervisor', 'teacher', 'admin', 'manage'], true))
                                <input type="hidden" name="return_context" value="{{ $returnContext }}">
                            @endif

                            @if ($isStudent)
                                <div class="user-edit-info">
                                    <span class="user-edit-info__icon"><i class="bi bi-info-lg"></i></span>
                                    <span>يمكنك تعديل <strong>الاسم</strong> و<strong>رقم الهاتف</strong> و<strong>تواريخ انتهاء الاشتراكات</strong> لكل صف.</span>
                                </div>
                            @endif

                            <div class="user-edit-field">
                                <label for="name">الاسم الكامل <span class="text-danger">*</span></label>
                                <div class="input-group @error('name') is-invalid @enderror">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text"
                                           id="name"
                                           class="form-control @error('name') is-invalid @enderror"
                                           name="name"
                                           value="{{ old('name', $user->name) }}"
                                           placeholder="أدخل الاسم الكامل"
                                           required
                                           autocomplete="name">
                                </div>
                                @error('name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="user-edit-field">
                                <label for="phone">رقم الهاتف</label>
                                <div class="input-group @error('phone') is-invalid @enderror">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input type="tel"
                                           id="phone"
                                           class="form-control @error('phone') is-invalid @enderror"
                                           name="phone"
                                           value="{{ old('phone', $user->phone) }}"
                                           placeholder="+9665xxxxxxxx"
                                           dir="ltr"
                                           autocomplete="tel">
                                </div>
                                @error('phone')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            @if ($isStudent)
                                @include('admin.pages.users.partials.user-edit-subscriptions', [
                                    'user' => $user,
                                    'classSubscriptions' => $classSubscriptions ?? [],
                                ])
                            @endif

                            @unless ($isStudent)
                                @if ($emailRequired)
                                    <div class="user-edit-field">
                                        <label for="email">البريد الإلكتروني <span class="text-danger">*</span></label>
                                        <div class="input-group @error('email') is-invalid @enderror">
                                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                            <input type="email"
                                                   id="email"
                                                   class="form-control @error('email') is-invalid @enderror"
                                                   name="email"
                                                   value="{{ old('email', $user->email) }}"
                                                   placeholder="email@example.com"
                                                   dir="ltr"
                                                   required>
                                        </div>
                                        @error('email')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endif

                                <div class="user-edit-switch">
                                    <span class="fw-semibold small">تفعيل الحساب</span>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                               id="is_active" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                    </div>
                                </div>

                                @can('user-edit')
                                    <div class="user-edit-field">
                                        <label>الأدوار</label>
                                        <div class="user-edit-roles">
                                            @foreach ($roles as $role)
                                                <label class="user-edit-role-chip">
                                                    <input type="checkbox"
                                                           name="roles[]"
                                                           value="{{ $role->name }}"
                                                           {{ in_array($role->name, $selectedRoles, true) ? 'checked' : '' }}>
                                                    {{ $role->name }}
                                                </label>
                                            @endforeach
                                        </div>
                                        @error('roles')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                        @error('roles.*')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endcan
                            @endunless

                            <div class="user-edit-actions">
                                <a href="{{ $cancelUrl }}" class="btn btn-cancel">إلغاء</a>
                                <button type="submit" class="btn btn-save">
                                    <i class="bi bi-check2-circle me-1"></i> حفظ التعديلات
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.alert-success').forEach(function(alert) {
                setTimeout(function() {
                    bootstrap.Alert.getOrCreateInstance(alert).close();
                }, 5000);
            });
        });
    </script>
    @can('user-edit')
        @if ($isStudent ?? false)
            @include('admin.pages.users.partials.user-subscription-date-script')
        @endif
    @endcan
@endpush
