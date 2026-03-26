@foreach ($users as $user)
    @can('user-impersonate')
        <div class="modal fade" id="impersonateModal{{ $user->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">تسجيل الدخول كالمستخدم</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>هل تريد تسجيل الدخول كالمستخدم <strong>{{ $user->name }}</strong>؟</p>
                        <p class="text-muted small">سيتم حفظ حسابك الأصلي ويمكنك العودة إليه في أي وقت.</p>
                        <div class="alert alert-info">
                            <strong>الرابط المباشر (صلاحية ساعة واحدة):</strong><br>
                            @php
                                $impersonateUrl = URL::temporarySignedRoute(
                                    'users.impersonate.link',
                                    now()->addHour(),
                                    ['user' => $user->id]
                                );
                            @endphp
                            <input type="text" class="form-control mt-2"
                                   value="{{ $impersonateUrl }}"
                                   readonly id="impersonateLink{{ $user->id }}">
                            <button type="button" class="btn btn-sm btn-secondary mt-2"
                                    onclick="copyLink({{ $user->id }})">
                                <i class="fas fa-copy me-1"></i> نسخ الرابط
                            </button>
                            <small class="text-muted d-block mt-1">
                                <i class="fas fa-info-circle me-1"></i> هذا الرابط صالح لمدة ساعة واحدة فقط
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <form action="{{ route('users.impersonate', $user->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt me-1"></i> تسجيل الدخول
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endforeach

