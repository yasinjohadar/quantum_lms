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
                        <li class="slide {{ request()->routeIs('admin.dashboard') || request()->is('admin/dashboard*') ? 'active' : '' }}">
                            <a href="{{ route('admin.dashboard') }}" class="side-menu__item {{ request()->routeIs('admin.dashboard') || request()->is('admin/dashboard*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M4 10v7h3v-4h6v4h3v-7l-6-5z" opacity=".3"/><path d="M12 3 2 12h3v8h6v-6h2v6h6v-8h3z"/></svg>
                                <span class="side-menu__label">لوحة التحكم</span>
                            </a>
                        </li>

                        <li class="slide has-sub {{ request()->is('users*') || request()->is('admin/archived-users*') ? 'open' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M16 13c-1.66 0-3 1.34-3 3v3h8v-3c0-1.66-1.34-3-3-3h-2zm-8 0c-1.66 0-3 1.34-3 3v3h6v-3c0-1.66-1.34-3-3-3H8zm8-2a3 3 0 100-6 3 3 0 000 6zm-8 0a3 3 0 100-6 3 3 0 000 6z"/>
                                </svg>
                                <span class="side-menu__label">المستخدمون</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">المستخدمون</a>
                                </li>
                                <li class="slide {{ request()->is('users*') && !request()->is('admin/archived-users*') ? 'active' : '' }}">
                                    <a href="{{ route('users.index') }}" class="side-menu__item {{ request()->is('users*') && !request()->is('admin/archived-users*') ? 'active' : '' }}">
                                        <span class="side-menu__label">قائمة المستخدمين</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/archived-users*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.archived-users.index') }}" class="side-menu__item {{ request()->is('admin/archived-users*') ? 'active' : '' }}">
                                        <span class="side-menu__label">الأرشيف</span>
                                    </a>
                                </li>
                            </ul>
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
                                <li class="slide {{ request()->is('admin/enrollments/class-pending') ? 'active' : '' }}">
                                    <a href="{{ route('admin.enrollments.class-pending') }}" class="side-menu__item {{ request()->is('admin/enrollments/class-pending') ? 'active' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                                            <path d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                        </svg>
                                        <span class="side-menu__label">طلبات الصف المعلقة</span>
                                        @php
                                            $classPendingCount = \App\Models\ClassEnrollment::pending()->count();
                                        @endphp
                                        @if($classPendingCount > 0)
                                            <span class="badge bg-warning-transparent text-warning ms-auto">{{ $classPendingCount }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/enrollments/pending') ? 'active' : '' }}">
                                    <a href="{{ route('admin.enrollments.pending') }}" class="side-menu__item {{ request()->is('admin/enrollments/pending') ? 'active' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                                            <path d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                        </svg>
                                        <span class="side-menu__label">طلبات المواد المعلقة</span>
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
                                <li class="slide {{ request()->is('admin/reports/templates*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.reports.templates') }}" class="side-menu__item {{ request()->is('admin/reports/templates*') ? 'active' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" style="width: 18px; height: 18px;">
                                            <path d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                                        </svg>
                                        <span class="side-menu__label">قوالب التقارير</span>
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

                        <li class="slide {{ request()->is('admin/quiz-attempts/needs-grading') ? 'active' : '' }}">
                            <a href="{{ route('admin.quiz-attempts.needs-grading') }}" class="side-menu__item {{ request()->is('admin/quiz-attempts/needs-grading') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M19 3h-1V1h-2v2H8V1H6v2H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2zm0 16H5V8h14v11zM9 10H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm-8 4H7v2h2v-2zm4 0h-2v2h2v-2z"/>
                                </svg>
                                <span class="side-menu__label">بحاجة للتصحيح</span>
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

                        <li class="slide {{ request()->is('admin/notifications*') ? 'active' : '' }}">
                            <a href="{{ route('admin.notifications.create') }}" class="side-menu__item {{ request()->is('admin/notifications*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2zm-2 1H8v-6c0-2.48 1.51-4.5 4-4.5s4 2.02 4 4.5v6z"/>
                                </svg>
                                <span class="side-menu__label">إرسال إشعار</span>
                            </a>
                        </li>

                        <li class="slide has-sub {{ request()->is('admin/email-settings*') || request()->is('admin/email-logs*') || request()->is('admin/email-templates*') ? 'open' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                                </svg>
                                <span class="side-menu__label">البريد الإلكتروني</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">البريد الإلكتروني</a>
                                </li>
                                <li class="slide {{ request()->is('admin/email-settings*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.email-settings.index') }}" class="side-menu__item {{ request()->is('admin/email-settings*') ? 'active' : '' }}">
                                        <span class="side-menu__label">إعدادات SMTP</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/email-logs*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.email-logs.index') }}" class="side-menu__item {{ request()->is('admin/email-logs*') ? 'active' : '' }}">
                                        <span class="side-menu__label">سجل الإيميلات</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/email-templates*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.email-templates.index') }}" class="side-menu__item {{ request()->is('admin/email-templates*') ? 'active' : '' }}">
                                        <span class="side-menu__label">قوالب الإيميلات</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="slide has-sub {{ request()->is('admin/sms-settings*') || request()->is('admin/sms-logs*') || request()->is('admin/sms-templates*') ? 'open' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/>
                                </svg>
                                <span class="side-menu__label">SMS</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">SMS</a>
                                </li>
                                <li class="slide {{ request()->is('admin/sms-settings*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.sms-settings.index') }}" class="side-menu__item {{ request()->is('admin/sms-settings*') ? 'active' : '' }}">
                                        <span class="side-menu__label">إعدادات SMS</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/sms-logs*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.sms-logs.index') }}" class="side-menu__item {{ request()->is('admin/sms-logs*') ? 'active' : '' }}">
                                        <span class="side-menu__label">سجل SMS</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/sms-templates*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.sms-templates.index') }}" class="side-menu__item {{ request()->is('admin/sms-templates*') ? 'active' : '' }}">
                                        <span class="side-menu__label">قوالب SMS</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="slide has-sub {{ request()->is('admin/whatsapp-settings*') || request()->is('admin/whatsapp-messages*') ? 'open' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                </svg>
                                <span class="side-menu__label">WhatsApp</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">WhatsApp</a>
                                </li>
                                <li class="slide {{ request()->is('admin/whatsapp-settings*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.whatsapp-settings.index') }}" class="side-menu__item {{ request()->is('admin/whatsapp-settings*') ? 'active' : '' }}">
                                        <span class="side-menu__label">إعدادات WhatsApp</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('admin/whatsapp-messages*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.whatsapp-messages.index') }}" class="side-menu__item {{ request()->is('admin/whatsapp-messages*') ? 'active' : '' }}">
                                        <span class="side-menu__label">الرسائل</span>
                                    </a>
                                </li>
                            </ul>
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

                    </ul>
                    <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24"> <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path> </svg></div>
                </nav>
                <!-- End::nav -->

            </div>
            <!-- End::main-sidebar -->

        </aside>
        <!-- End::app-sidebar -->

        @push('styles')
        <style>
        /* تنسيق الرابط النشط - لون النص الأزرق فقط */
        .side-menu__item.active {
            color: #007bff !important;
        }

        .side-menu__item.active .side-menu__icon {
            color: #007bff !important;
            fill: #007bff !important;
        }

        .side-menu__item.active .side-menu__label {
            color: #007bff !important;
            font-weight: 600;
        }

        .slide.active > .side-menu__item {
            color: #007bff !important;
        }

        .slide.active > .side-menu__item .side-menu__icon {
            color: #007bff !important;
            fill: #007bff !important;
        }

        .slide.active > .side-menu__item .side-menu__label {
            color: #007bff !important;
            font-weight: 600;
        }

        /* الرابط النشط في القوائم الفرعية */
        .slide-menu .slide.active > .side-menu__item {
            color: #007bff !important;
        }

        .slide-menu .slide.active > .side-menu__item .side-menu__icon {
            color: #007bff !important;
            fill: #007bff !important;
        }

        .slide-menu .slide.active > .side-menu__item .side-menu__label {
            color: #007bff !important;
            font-weight: 600;
        }
        </style>
        @endpush

        @push('scripts')
        <script>
        // السكرول التلقائي للرابط النشط - يظهر في الأعلى
        (function() {
            function scrollToActiveMenuItem() {
                const activeMenuItem = document.querySelector('.side-menu__item.active');
                if (!activeMenuItem) return;
                
                const sidebarScroll = document.getElementById('sidebar-scroll');
                if (!sidebarScroll) return;
                
                // التأكد من فتح القوائم الفرعية التي تحتوي على الرابط النشط
                let parent = activeMenuItem.closest('.slide-menu');
                if (parent) {
                    let parentSlide = parent.closest('.slide.has-sub');
                    if (parentSlide && !parentSlide.classList.contains('open')) {
                        parentSlide.classList.add('open');
                        parent.style.display = 'block';
                        // إعادة حساب بعد فتح القائمة
                        setTimeout(scrollToActiveMenuItem, 300);
                        return;
                    }
                }
                
                // الحصول على العنصر الأب (slide) الذي يحتوي على الرابط النشط
                const slideElement = activeMenuItem.closest('.slide');
                if (!slideElement) return;
                
                // حساب الموقع بدقة
                setTimeout(function() {
                    const elementRect = slideElement.getBoundingClientRect();
                    const sidebarRect = sidebarScroll.getBoundingClientRect();
                    const currentScrollTop = sidebarScroll.scrollTop;
                    
                    // حساب المسافة من أعلى الـ sidebar
                    const elementTop = elementRect.top - sidebarRect.top + currentScrollTop;
                    
                    // السكرول بحيث يظهر العنصر في الأعلى (مع مساحة 10px)
                    const scrollPosition = Math.max(0, elementTop - 10);
                    
                    // استخدام scrollTo مع smooth behavior
                    if (Math.abs(sidebarScroll.scrollTop - scrollPosition) > 5) {
                        sidebarScroll.scrollTo({
                            top: scrollPosition,
                            behavior: 'smooth'
                        });
                    }
                }, 100);
            }
            
            // تنفيذ السكرول بعد تحميل الصفحة
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(scrollToActiveMenuItem, 700);
                });
            } else {
                setTimeout(scrollToActiveMenuItem, 700);
            }
        })();
        </script>
        @endpush