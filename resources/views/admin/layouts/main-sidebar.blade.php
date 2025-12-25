        <!-- Start::app-sidebar -->
        <aside class="app-sidebar sticky" id="sidebar">

            <!-- Start::main-sidebar-header -->
            <div class="main-sidebar-header">
                <a href="{{ route('admin.dashboard') }}" class="header-logo">
                    <img src="{{ asset('assets/images/brand-logos/desktop-logo.png') }}" alt="logo" class="desktop-logo">
                    <img src="{{ asset('assets/images/brand-logos/toggle-logo.png') }}" alt="logo" class="toggle-logo">
                    <img src="{{ asset('assets/images/brand-logos/desktop-white.png') }}" alt="logo" class="desktop-white">
                    <img src="{{ asset('assets/images/brand-logos/toggle-white.png') }}" alt="logo" class="toggle-white">
                </a>
            </div>
            <!-- End::main-sidebar-header -->

            <!-- Start::main-sidebar -->
            <div class="main-sidebar" id="sidebar-scroll">

                <!-- Start::nav -->
                <nav class="main-menu-container nav nav-pills flex-column sub-open">
                    <div class="slide-left" id="slide-left">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24"> <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path> </svg>
                    </div>
                    <ul class="main-menu">
                        <!-- Start::slide__category -->
                        <li class="slide__category"><span class="category-name">مركز الإدارة</span></li>
                        <!-- End::slide__category -->

                        <!-- Start::dashboard & basic links -->
                        <li class="slide {{ request()->is('/') ? 'active' : '' }}">
                            <a href="/" class="side-menu__item {{ request()->is('/') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M4 10v7h3v-4h6v4h3v-7l-6-5z" opacity=".3"/><path d="M12 3 2 12h3v8h6v-6h2v6h6v-8h3z"/></svg>
                                <span class="side-menu__label">الصفحة الرئيسية</span>
                            </a>
                        </li>

                        <li class="slide {{ request()->is('roles*') ? 'active' : '' }}">
                            <a href="{{ route('roles.index') }}" class="side-menu__item {{ request()->is('roles*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M12 1L3 5v6c0 5 3.8 9.7 9 11 5.2-1.3 9-6 9-11V5l-9-4zm0 2.18L18.09 6 12 8.82 5.91 6 12 3.18zM5 9.24l7 3.11 7-3.11V11c0 4-2.6 7.7-7 8.94C7.6 18.7 5 15 5 11V9.24z"/>
                                </svg>
                                <span class="side-menu__label">الصلاحيات</span>
                            </a>
                        </li>

                        <li class="slide {{ request()->is('users*') ? 'active' : '' }}">
                            <a href="{{ route('users.index') }}" class="side-menu__item {{ request()->is('users*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M16 13c-1.66 0-3 1.34-3 3v3h8v-3c0-1.66-1.34-3-3-3h-2zm-8 0c-1.66 0-3 1.34-3 3v3h6v-3c0-1.66-1.34-3-3-3H8zm8-2a3 3 0 100-6 3 3 0 000 6zm-8 0a3 3 0 100-6 3 3 0 000 6z"/>
                                </svg>
                                <span class="side-menu__label">المستخدمون</span>
                            </a>
                        </li>

                        <li class="slide {{ request()->is('admin/stages*') ? 'active' : '' }}">
                            <a href="{{ route('admin.stages.index') }}" class="side-menu__item {{ request()->is('admin/stages*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z"/>
                                </svg>
                                <span class="side-menu__label">المراحل الدراسية</span>
                            </a>
                        </li>

                        <li class="slide {{ request()->is('admin/classes*') ? 'active' : '' }}">
                            <a href="{{ route('admin.classes.index') }}" class="side-menu__item {{ request()->is('admin/classes*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M6 4h12v2H6zm0 4h12v2H6zm0 4h8v2H6zm0 4h8v2H6z"/>
                                </svg>
                                <span class="side-menu__label">الصفوف الدراسية</span>
                            </a>
                        </li>

                        <li class="slide {{ request()->is('admin/subjects*') ? 'active' : '' }}">
                            <a href="{{ route('admin.subjects.index') }}" class="side-menu__item {{ request()->is('admin/subjects*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M18 2H8a2 2 0 00-2 2v14a2 2 0 002 2h10l4-4V4a2 2 0 00-2-2zm0 13v3h-3a1 1 0 01-1-1v-2h4zm-6-4H8V9h4zm4-3H8V6h8z"/>
                                </svg>
                                <span class="side-menu__label">المواد الدراسية</span>
                            </a>
                        </li>

                        <li class="slide has-sub {{ request()->is('admin/enrollments*') ? 'open' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                                </svg>
                                <span class="side-menu__label">الانضمامات</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">الانضمامات</a>
                                </li>
                                <li class="slide {{ request()->is('admin/enrollments/pending') ? 'active' : '' }}">
                                    <a href="{{ route('admin.enrollments.pending') }}" class="side-menu__item {{ request()->is('admin/enrollments/pending') ? 'active' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                                            <path d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                        </svg>
                                        <span class="side-menu__label">طلبات معلقة</span>
                                        @php
                                            $pendingCount = \App\Models\Enrollment::pending()->count();
                                        @endphp
                                        @if($pendingCount > 0)
                                            <span class="badge bg-warning-transparent text-warning ms-auto">{{ $pendingCount }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/enrollments') && !request()->is('admin/enrollments/pending') && !request()->is('admin/enrollments/create') ? 'active' : '' }}">
                                    <a href="{{ route('admin.enrollments.index') }}" class="side-menu__item {{ request()->is('admin/enrollments') && !request()->is('admin/enrollments/pending') && !request()->is('admin/enrollments/create') ? 'active' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                                            <path d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                                        </svg>
                                        <span class="side-menu__label">جميع الانضمامات</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="slide {{ request()->is('admin/groups*') ? 'active' : '' }}">
                            <a href="{{ route('admin.groups.index') }}" class="side-menu__item {{ request()->is('admin/groups*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                                </svg>
                                <span class="side-menu__label">المجموعات</span>
                            </a>
                        </li>

                        <li class="slide {{ request()->is('admin/student-progress*') ? 'active' : '' }}">
                            <a href="{{ route('admin.student-progress.index') }}" class="side-menu__item {{ request()->is('admin/student-progress*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                                </svg>
                                <span class="side-menu__label">مراقبة تقدم الطلاب</span>
                            </a>
                        </li>

                        <li class="slide {{ request()->is('admin/reviews*') ? 'active' : '' }}">
                            <a href="{{ route('admin.reviews.index') }}" class="side-menu__item {{ request()->is('admin/reviews*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                                <span class="side-menu__label">التقييمات</span>
                            </a>
                        </li>

                        <li class="slide has-sub {{ request()->is('admin/reports*') || request()->is('admin/report-templates*') ? 'open' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                                </svg>
                                <span class="side-menu__label">التقارير والتحليلات</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">التقارير والتحليلات</a>
                                </li>
                                <li class="slide {{ request()->is('admin/reports') && !request()->is('admin/reports/*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.reports.index') }}" class="side-menu__item {{ request()->is('admin/reports') && !request()->is('admin/reports/*') ? 'active' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                                            <path d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                                        </svg>
                                        <span class="side-menu__label">التقارير</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/report-templates*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.report-templates.index') }}" class="side-menu__item {{ request()->is('admin/report-templates*') ? 'active' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                                            <path d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                                        </svg>
                                        <span class="side-menu__label">قوالب التقارير</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="slide has-sub {{ request()->is('admin/gamification*') || request()->is('admin/badges*') || request()->is('admin/achievements*') || request()->is('admin/levels*') || request()->is('admin/challenges*') || request()->is('admin/rewards*') || request()->is('admin/certificates*') || request()->is('admin/leaderboards*') || request()->is('admin/daily-tasks*') || request()->is('admin/weekly-tasks*') ? 'open' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                                <span class="side-menu__label">نظام التحفيز</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">نظام التحفيز</a>
                                </li>
                                <li class="slide {{ request()->is('admin/gamification*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.gamification.index') }}" class="side-menu__item {{ request()->is('admin/gamification*') ? 'active' : '' }}">
                                        <span class="side-menu__label">لوحة التحكم</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/badges*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.badges.index') }}" class="side-menu__item {{ request()->is('admin/badges*') ? 'active' : '' }}">
                                        <span class="side-menu__label">الشارات</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/achievements*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.achievements.index') }}" class="side-menu__item {{ request()->is('admin/achievements*') ? 'active' : '' }}">
                                        <span class="side-menu__label">الإنجازات</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/levels*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.levels.index') }}" class="side-menu__item {{ request()->is('admin/levels*') ? 'active' : '' }}">
                                        <span class="side-menu__label">المستويات</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/challenges*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.challenges.index') }}" class="side-menu__item {{ request()->is('admin/challenges*') ? 'active' : '' }}">
                                        <span class="side-menu__label">التحديات</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/rewards*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.rewards.index') }}" class="side-menu__item {{ request()->is('admin/rewards*') ? 'active' : '' }}">
                                        <span class="side-menu__label">المكافآت</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/certificates*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.certificates.index') }}" class="side-menu__item {{ request()->is('admin/certificates*') ? 'active' : '' }}">
                                        <span class="side-menu__label">الشهادات</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/leaderboards*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.leaderboards.index') }}" class="side-menu__item {{ request()->is('admin/leaderboards*') ? 'active' : '' }}">
                                        <span class="side-menu__label">لوحة المتصدرين</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/daily-tasks*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.daily-tasks.index') }}" class="side-menu__item {{ request()->is('admin/daily-tasks*') ? 'active' : '' }}">
                                        <span class="side-menu__label">المهام اليومية</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/weekly-tasks*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.weekly-tasks.index') }}" class="side-menu__item {{ request()->is('admin/weekly-tasks*') ? 'active' : '' }}">
                                        <span class="side-menu__label">المهام الأسبوعية</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/gamification/settings*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.gamification.settings') }}" class="side-menu__item {{ request()->is('admin/gamification/settings*') ? 'active' : '' }}">
                                        <span class="side-menu__label">إعدادات التحفيز</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <!-- الإشعارات المخصصة -->
                        <li class="slide {{ request()->is('admin/notifications*') ? 'active' : '' }}">
                            <a href="{{ route('admin.notifications.create') }}" class="side-menu__item {{ request()->is('admin/notifications*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2zm-2 1H8v-6c0-2.48 1.51-4.5 4-4.5s4 2.02 4 4.5v6z"/>
                                </svg>
                                <span class="side-menu__label">إرسال إشعار</span>
                            </a>
                        </li>

                        <li class="slide {{ request()->is('admin/settings*') ? 'active' : '' }}">
                            <a href="{{ route('admin.settings.index') }}" class="side-menu__item {{ request()->is('admin/settings*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94L14.4 2.81c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/>
                                </svg>
                                <span class="side-menu__label">الإعدادات</span>
                            </a>
                        </li>

                        <!-- Start::slide - سجلات الدخول والجلسات -->
                        <li class="slide has-sub {{ request()->is('admin/login-logs*') || request()->is('admin/user-sessions*') ? 'open' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                                </svg>
                                <span class="side-menu__label">المراقبة والأمان</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">المراقبة والأمان</a>
                                </li>
                                <li class="slide {{ request()->is('admin/login-logs*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.login-logs.index') }}" class="side-menu__item {{ request()->is('admin/login-logs*') ? 'active' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                                            <path d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                                        </svg>
                                        <span class="side-menu__label">سجلات الدخول</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/user-sessions*') && !request()->is('admin/user-sessions/*/activities') ? 'active' : '' }}">
                                    <a href="{{ route('admin.user-sessions.index') }}" class="side-menu__item {{ request()->is('admin/user-sessions*') && !request()->is('admin/user-sessions/*/activities') ? 'active' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                                            <path d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/>
                                        </svg>
                                        <span class="side-menu__label">جلسات المستخدمين</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide__category -->
                        <li class="slide__category"><span class="category-name">نظام الاختبارات</span></li>
                        <!-- End::slide__category -->

                        <li class="slide {{ request()->is('admin/questions*') ? 'active' : '' }}">
                            <a href="{{ route('admin.questions.index') }}" class="side-menu__item {{ request()->is('admin/questions*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M4 6H2v14a2 2 0 002 2h14v-2H4V6zm16-4H8a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V4a2 2 0 00-2-2zm0 14H8V4h12v12zM10 9h8v2h-8zm0 3h4v2h-4zm0-6h8v2h-8z"/>
                                </svg>
                                <span class="side-menu__label">بنك الأسئلة</span>
                            </a>
                        </li>

                        <li class="slide {{ request()->is('admin/quizzes*') ? 'active' : '' }}">
                            <a href="{{ route('admin.quizzes.index') }}" class="side-menu__item {{ request()->is('admin/quizzes*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2zm-7 14l-5-5 1.41-1.41L12 14.17l4.59-4.58L18 11l-6 6z"/>
                                </svg>
                                <span class="side-menu__label">الاختبارات</span>
                            </a>
                        </li>

                        <li class="slide {{ request()->is('admin/assignments*') ? 'active' : '' }}">
                            <a href="{{ route('admin.assignments.index') }}" class="side-menu__item {{ request()->is('admin/assignments*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                                </svg>
                                <span class="side-menu__label">الواجبات</span>
                            </a>
                        </li>

                        <li class="slide has-sub {{ request()->is('admin/library*') ? 'open' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>
                                </svg>
                                <span class="side-menu__label">المكتبة الرقمية</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">المكتبة الرقمية</a>
                                </li>
                                <li class="slide {{ request()->is('admin/library/dashboard') ? 'active' : '' }}">
                                    <a href="{{ route('admin.library.dashboard') }}" class="side-menu__item {{ request()->is('admin/library/dashboard') ? 'active' : '' }}">
                                        <span class="side-menu__label">لوحة المكتبة</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/library/items*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.library.items.index') }}" class="side-menu__item {{ request()->is('admin/library/items*') ? 'active' : '' }}">
                                        <span class="side-menu__label">العناصر</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/library/categories*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.library.categories.index') }}" class="side-menu__item {{ request()->is('admin/library/categories*') ? 'active' : '' }}">
                                        <span class="side-menu__label">التصنيفات</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/library/tags*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.library.tags.index') }}" class="side-menu__item {{ request()->is('admin/library/tags*') ? 'active' : '' }}">
                                        <span class="side-menu__label">الوسوم</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="slide has-sub {{ request()->is('admin/calendar*') ? 'open' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/>
                                </svg>
                                <span class="side-menu__label">التقويم والجدولة</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">التقويم والجدولة</a>
                                </li>
                                <li class="slide {{ request()->is('admin/calendar') && !request()->is('admin/calendar/*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.calendar.index') }}" class="side-menu__item {{ request()->is('admin/calendar') && !request()->is('admin/calendar/*') ? 'active' : '' }}">
                                        <span class="side-menu__label">التقويم</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/calendar/events*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.calendar.events.index') }}" class="side-menu__item {{ request()->is('admin/calendar/events*') ? 'active' : '' }}">
                                        <span class="side-menu__label">الأحداث</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/calendar/reminders*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.calendar.reminders.index') }}" class="side-menu__item {{ request()->is('admin/calendar/reminders*') ? 'active' : '' }}">
                                        <span class="side-menu__label">التذكيرات</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="slide has-sub {{ request()->is('admin/ai*') ? 'open' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                                <span class="side-menu__label">الذكاء الاصطناعي</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">الذكاء الاصطناعي</a>
                                </li>
                                <li class="slide {{ request()->is('admin/ai/models*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.ai.models.index') }}" class="side-menu__item {{ request()->is('admin/ai/models*') ? 'active' : '' }}">
                                        <span class="side-menu__label">موديلات AI</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/ai/question-generations*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.ai.question-generations.index') }}" class="side-menu__item {{ request()->is('admin/ai/question-generations*') ? 'active' : '' }}">
                                        <span class="side-menu__label">توليد الأسئلة</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/ai/question-solutions*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.ai.question-solutions.index') }}" class="side-menu__item {{ request()->is('admin/ai/question-solutions*') ? 'active' : '' }}">
                                        <span class="side-menu__label">حلول AI</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/ai/settings*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.ai.settings.index') }}" class="side-menu__item {{ request()->is('admin/ai/settings*') ? 'active' : '' }}">
                                        <span class="side-menu__label">الإعدادات</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="slide {{ request()->is('admin/analytics-dashboard') ? 'active' : '' }}">
                            <a href="{{ route('admin.analytics.dashboard') }}" class="side-menu__item {{ request()->is('admin/analytics-dashboard') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M3 13h4v8H3zM10 3h4v18h-4zM17 8h4v13h-4z"/>
                                </svg>
                                <span class="side-menu__label">لوحة التحليلات</span>
                            </a>
                        </li>

                        <li class="slide has-sub {{ request()->is('admin/backup*') ? 'open' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM14 13v4h-4v-4H7l5-5 5 5h-3z"/>
                                </svg>
                                <span class="side-menu__label">النسخ الاحتياطي</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">النسخ الاحتياطي</a>
                                </li>
                                <li class="slide {{ request()->is('admin/backups*') && !request()->is('admin/backup-schedules*') && !request()->is('admin/backup-storage*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.backups.index') }}" class="side-menu__item {{ request()->is('admin/backups*') && !request()->is('admin/backup-schedules*') && !request()->is('admin/backup-storage*') ? 'active' : '' }}">
                                        <span class="side-menu__label">النسخ الاحتياطية</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/backup-schedules*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.backup-schedules.index') }}" class="side-menu__item {{ request()->is('admin/backup-schedules*') ? 'active' : '' }}">
                                        <span class="side-menu__label">الجدولة</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/backup-storage*') && !request()->is('admin/backup-storage/analytics*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.backup-storage.index') }}" class="side-menu__item {{ request()->is('admin/backup-storage*') && !request()->is('admin/backup-storage/analytics*') ? 'active' : '' }}">
                                        <span class="side-menu__label">إعدادات التخزين</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/backup-storage/analytics*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.backup-storage.analytics') }}" class="side-menu__item {{ request()->is('admin/backup-storage/analytics*') ? 'active' : '' }}">
                                        <span class="side-menu__label">تحليلات التخزين</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="slide has-sub {{ request()->is('admin/app-storage*') || request()->is('admin/storage-disk-mappings*') ? 'open' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M10 4H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2h-8l-2-2z"/>
                                </svg>
                                <span class="side-menu__label">إعدادات التخزين</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">إعدادات التخزين</a>
                                </li>
                                <li class="slide {{ request()->is('admin/app-storage/configs*') && !request()->is('admin/app-storage/analytics*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.app-storage.configs.index') }}" class="side-menu__item {{ request()->is('admin/app-storage/configs*') && !request()->is('admin/app-storage/analytics*') ? 'active' : '' }}">
                                        <span class="side-menu__label">أماكن التخزين</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/storage-disk-mappings*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.storage-disk-mappings.index') }}" class="side-menu__item {{ request()->is('admin/storage-disk-mappings*') ? 'active' : '' }}">
                                        <span class="side-menu__label">Disk Mappings</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/app-storage/analytics*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.app-storage.analytics') }}" class="side-menu__item {{ request()->is('admin/app-storage/analytics*') ? 'active' : '' }}">
                                        <span class="side-menu__label">تحليلات التخزين</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="slide {{ request()->is('admin/quiz-attempts/needs-grading') ? 'active' : '' }}">
                            <a href="{{ route('admin.quiz-attempts.needs-grading') }}" class="side-menu__item {{ request()->is('admin/quiz-attempts/needs-grading') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M19 3h-1V1h-2v2H8V1H6v2H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2zm0 16H5V8h14v11zM9 10H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm-8 4H7v2h2v-2zm4 0h-2v2h2v-2z"/>
                                </svg>
                                <span class="side-menu__label">بحاجة للتصحيح</span>
                            </a>
                        </li>

                        <li class="slide {{ request()->is('admin/analytics-dashboard') ? 'active' : '' }}">
                            <a href="{{ route('admin.analytics.dashboard') }}" class="side-menu__item {{ request()->is('admin/analytics-dashboard') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M3 13h4v8H3zM10 3h4v18h-4zM17 8h4v13h-4z"/>
                                </svg>
                                <span class="side-menu__label">لوحة التحليلات</span>
                            </a>
                        </li>

                        <!-- End::dashboard & basic links -->









                        {{-- <!-- Start::slide -->
                        <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M4 12c0 4.08 3.06 7.44 7 7.93V4.07C7.05 4.56 4 7.92 4 12z" opacity=".3"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93s3.05-7.44 7-7.93v15.86zm2-15.86c1.03.13 2 .45 2.87.93H13v-.93zM13 7h5.24c.25.31.48.65.68 1H13V7zm0 3h6.74c.08.33.15.66.19 1H13v-1zm0 9.93V19h2.87c-.87.48-1.84.8-2.87.93zM18.24 17H13v-1h5.92c-.2.35-.43.69-.68 1zm1.5-3H13v-1h6.93c-.04.34-.11.67-.19 1z"/></svg>
                                <span class="side-menu__label">الاعدادات</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">Apps</a>
                                </li>
                                <li class="slide">
                                    <a href="cards.html" class="side-menu__item">الاعدادات العامة</a>
                                </li>
                                <li class="slide">
                                    <a href="{{route("roles.index")}}" class="side-menu__item">الصلاحيات</a>
                                </li>
                                <li class="slide">
                                    <a href="{{route("users.index")}}" class="side-menu__item">المستخدمون</a>
                                </li>

                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <!-- End::slide --> --}}


                    </ul>
                    <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24"> <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path> </svg></div>
                </nav>
                <!-- End::nav -->

            </div>
            <!-- End::main-sidebar -->

        </aside>
        <!-- End::app-sidebar -->
