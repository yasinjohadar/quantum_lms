@extends('student.layouts.master')

@section('page-title')
تفضيلات الإشعارات
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">تفضيلات الإشعارات</h4>
                <p class="mb-0 text-muted">اختر أنواع الإشعارات والقنوات المفضلة لك.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card custom-card">
            <div class="card-body">
                <form method="POST" action="{{ route('student.notifications.preferences.update') }}">
                    @csrf

                    <div class="table-responsive">
                        <table class="table table-bordered text-nowrap">
                            <thead>
                                <tr>
                                    <th>نوع الإشعار</th>
                                    <th class="text-center">داخل المنصة</th>
                                    <th class="text-center">بريد إلكتروني</th>
                                    <th class="text-center">SMS</th>
                                    <th class="text-center">إيقاف كامل</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($preferences as $key => $pref)
                                    <tr>
                                        <td>{{ $pref['label'] }}</td>
                                        <td class="text-center">
                                            <input type="checkbox" name="preferences[{{ $key }}][via_database]" value="1"
                                                   {{ $pref['via_database'] ? 'checked' : '' }}>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" name="preferences[{{ $key }}][via_email]" value="1"
                                                   {{ $pref['via_email'] ? 'checked' : '' }}>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" name="preferences[{{ $key }}][via_sms]" value="1"
                                                   {{ $pref['via_sms'] ? 'checked' : '' }} disabled>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" name="preferences[{{ $key }}][muted]" value="1"
                                                   {{ $pref['muted'] ? 'checked' : '' }}>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 text-end">
                        <button type="submit" class="btn btn-primary">
                            حفظ التغييرات
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop


