@php
    $account = $account ?? null;
    $isEdit = isset($account);
@endphp

<div class="row g-3">
    <div class="col-md-12">
        <label class="form-label">اسم الحساب <span class="text-danger">*</span></label>
        <input type="text" name="name" 
               class="form-control @error('name') is-invalid @enderror" 
               value="{{ old('name', $account->name ?? '') }}" 
               placeholder="مثال: حساب Zoom الرئيسي" required>
        @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12">
        <label class="form-label">النوع <span class="text-danger">*</span></label>
        <select name="type" class="form-select @error('type') is-invalid @enderror" id="account_type" required>
            <option value="api" {{ old('type', $account->type ?? 'api') === 'api' ? 'selected' : '' }}>API (Server-to-Server OAuth)</option>
            <option value="oauth" {{ old('type', $account->type ?? '') === 'oauth' ? 'selected' : '' }}>OAuth App</option>
        </select>
        <small class="text-muted">
            <strong>API:</strong> للحسابات الرسمية باستخدام Server-to-Server OAuth<br>
            <strong>OAuth App:</strong> للتطبيقات التي تستخدم OAuth 2.0
        </small>
        @error('type')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12" id="account_id_field" style="display: {{ old('type', $account->type ?? 'api') === 'api' ? 'block' : 'none' }};">
        <label class="form-label">Account ID <span class="text-danger">*</span> <small>(لحساب API فقط)</small></label>
        <input type="text" name="account_id" 
               class="form-control @error('account_id') is-invalid @enderror" 
               value="{{ old('account_id', $account->account_id ?? '') }}" 
               placeholder="Account ID من Zoom">
        <small class="text-muted">Account ID الخاص بحساب Zoom (مطلوب لحساب API فقط)</small>
        @error('account_id')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Client ID <span class="text-danger">*</span></label>
        <input type="text" name="client_id" 
               class="form-control @error('client_id') is-invalid @enderror" 
               value="{{ old('client_id', $account->client_id ?? '') }}" 
               placeholder="Client ID" required>
        @error('client_id')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Client Secret <span class="text-danger">*</span></label>
        <input type="password" name="client_secret" 
               class="form-control @error('client_secret') is-invalid @enderror" 
               value="" 
               placeholder="{{ $isEdit ? 'اتركه فارغاً إذا لم تريد تغييره' : 'Client Secret' }}"
               {{ !$isEdit ? 'required' : '' }}>
        @if($isEdit)
            <small class="text-muted">اتركه فارغاً إذا لم تريد تغيير Client Secret</small>
        @endif
        @error('client_secret')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">SDK Key <small>(اختياري)</small></label>
        <input type="text" name="sdk_key" 
               class="form-control @error('sdk_key') is-invalid @enderror" 
               value="{{ old('sdk_key', $account->sdk_key ?? '') }}" 
               placeholder="SDK Key">
        <small class="text-muted">Meeting SDK Key من Zoom</small>
        @error('sdk_key')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">SDK Secret <small>(اختياري)</small></label>
        <input type="password" name="sdk_secret" 
               class="form-control @error('sdk_secret') is-invalid @enderror" 
               value="" 
               placeholder="{{ $isEdit ? 'اتركه فارغاً إذا لم تريد تغييره' : 'SDK Secret' }}">
        @if($isEdit)
            <small class="text-muted">اتركه فارغاً إذا لم تريد تغيير SDK Secret</small>
        @endif
        @error('sdk_secret')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12" id="redirect_uri_field" style="display: {{ old('type', $account->type ?? '') === 'oauth' ? 'block' : 'none' }};">
        <label class="form-label">Redirect URI <small>(لحساب OAuth فقط)</small></label>
        <input type="url" name="redirect_uri" 
               class="form-control @error('redirect_uri') is-invalid @enderror" 
               value="{{ old('redirect_uri', $account->redirect_uri ?? '') }}" 
               placeholder="https://yourdomain.com/zoom/callback">
        <small class="text-muted">Redirect URI المسجل في Zoom OAuth App</small>
        @error('redirect_uri')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12">
        <label class="form-label">الوصف <small>(اختياري)</small></label>
        <textarea name="description" 
                  class="form-control @error('description') is-invalid @enderror" 
                  rows="2" 
                  placeholder="وصف مختصر للحساب">{{ old('description', $account->description ?? '') }}</textarea>
        @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_default" 
                   id="is_default" value="1"
                   {{ old('is_default', $account->is_default ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_default">
                تعيين كحساب افتراضي
            </label>
        </div>
        <small class="text-muted">سيتم استخدام هذا الحساب افتراضياً عند إنشاء جلسات جديدة</small>
    </div>

    <div class="col-md-6">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" 
                   id="is_active" value="1"
                   {{ old('is_active', $account->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">
                تفعيل الحساب
            </label>
        </div>
        <small class="text-muted">الحسابات غير النشطة لن تكون متاحة للاستخدام</small>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const accountTypeSelect = document.getElementById('account_type');
    const accountIdField = document.getElementById('account_id_field');
    const redirectUriField = document.getElementById('redirect_uri_field');

    if (accountTypeSelect) {
        accountTypeSelect.addEventListener('change', function() {
            if (this.value === 'api') {
                accountIdField.style.display = 'block';
                redirectUriField.style.display = 'none';
            } else {
                accountIdField.style.display = 'none';
                redirectUriField.style.display = 'block';
            }
        });
    }
});
</script>
@endpush


