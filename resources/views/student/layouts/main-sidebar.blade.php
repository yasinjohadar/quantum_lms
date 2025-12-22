        <!-- Start::app-sidebar -->
        <aside class="app-sidebar sticky" id="sidebar">

            <!-- Start::main-sidebar-header -->
            <div class="main-sidebar-header">
                <a href="index.html" class="header-logo">
                    <img src="../assets/images/brand-logos/desktop-logo.png" alt="logo" class="desktop-logo">
                    <img src="../assets/images/brand-logos/toggle-logo.png" alt="logo" class="toggle-logo">
                    <img src="../assets/images/brand-logos/desktop-white.png" alt="logo" class="desktop-white">
                    <img src="../assets/images/brand-logos/toggle-white.png" alt="logo" class="toggle-white">
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
                        <li class="slide__category"><span class="category-name">لوحة التحكم</span></li>
                        <!-- End::slide__category -->

                        <!-- Start::slide -->
                        <li class="slide {{ request()->is('student/dashboard') || request()->is('student') ? 'active' : '' }}">
                            <a href="{{ route('student.dashboard') }}" class="side-menu__item {{ request()->is('student/dashboard') || request()->is('student') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0V0z" fill="none"/>
                                    <path d="M4 10v7h3v-4h6v4h3v-7l-6-5z" opacity=".3"/>
                                    <path d="M12 3 2 12h3v8h6v-6h2v6h6v-8h3z"/>
                                </svg>
                                <span class="side-menu__label">الصفحة الرئيسية</span>
                            </a>
                        </li>

                        <!-- Start::slide__category -->
                        <li class="slide__category"><span class="category-name">المواد الدراسية</span></li>
                        <!-- End::slide__category -->

                        <li class="slide {{ request()->is('student/classes*') ? 'active' : '' }}">
                            <a href="{{ route('student.classes') }}" class="side-menu__item {{ request()->is('student/classes*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M4 6h16v2H4zm0 5h16v2H4zm0 5h16v2H4z"/>
                                </svg>
                                <span class="side-menu__label">صفوفي</span>
                            </a>
                        </li>

                        <li class="slide {{ request()->is('student/subjects*') ? 'active' : '' }}">
                            <a href="{{ route('student.subjects') }}" class="side-menu__item {{ request()->is('student/subjects*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M18 2H8a2 2 0 00-2 2v14a2 2 0 002 2h10l4-4V4a2 2 0 00-2-2zm0 13v3h-3a1 1 0 01-1-1v-2h4zm-6-4H8V9h4zm4-3H8V6h8z"/>
                                </svg>
                                <span class="side-menu__label">موادي الدراسية</span>
                            </a>
                        </li>

                        <li class="slide {{ request()->is('student/progress*') ? 'active' : '' }}">
                            <a href="{{ route('student.progress.index') }}" class="side-menu__item {{ request()->is('student/progress*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                                </svg>
                                <span class="side-menu__label">تقدمي الدراسي</span>
                            </a>
                        </li>

                        <li class="slide {{ request()->is('student/reports*') ? 'active' : '' }}">
                            <a href="{{ route('student.reports.index') }}" class="side-menu__item {{ request()->is('student/reports*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                                </svg>
                                <span class="side-menu__label">تقاريري</span>
                            </a>
                        </li>

                        <!-- Start::slide__category -->
                        <li class="slide__category"><span class="category-name">نظام التحفيز</span></li>
                        <!-- End::slide__category -->

                        <li class="slide has-sub {{ request()->is('student/gamification*') || request()->is('student/notifications*') || request()->is('student/tasks*') ? 'open' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                                <span class="side-menu__label">التحفيز والإنجازات</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">التحفيز والإنجازات</a>
                                </li>
                                <li class="slide {{ request()->is('student/gamification/dashboard') ? 'active' : '' }}">
                                    <a href="{{ route('student.gamification.dashboard') }}" class="side-menu__item {{ request()->is('student/gamification/dashboard') ? 'active' : '' }}">
                                        <span class="side-menu__label">لوحة التحفيز</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('student/gamification/badges') ? 'active' : '' }}">
                                    <a href="{{ route('student.gamification.badges') }}" class="side-menu__item {{ request()->is('student/gamification/badges') ? 'active' : '' }}">
                                        <span class="side-menu__label">الشارات</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('student/gamification/achievements') ? 'active' : '' }}">
                                    <a href="{{ route('student.gamification.achievements') }}" class="side-menu__item {{ request()->is('student/gamification/achievements') ? 'active' : '' }}">
                                        <span class="side-menu__label">الإنجازات</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('student/gamification/leaderboard') ? 'active' : '' }}">
                                    <a href="{{ route('student.gamification.leaderboard') }}" class="side-menu__item {{ request()->is('student/gamification/leaderboard') ? 'active' : '' }}">
                                        <span class="side-menu__label">لوحة المتصدرين</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('student/gamification/challenges') ? 'active' : '' }}">
                                    <a href="{{ route('student.gamification.challenges') }}" class="side-menu__item {{ request()->is('student/gamification/challenges') ? 'active' : '' }}">
                                        <span class="side-menu__label">التحديات</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('student/gamification/rewards') ? 'active' : '' }}">
                                    <a href="{{ route('student.gamification.rewards') }}" class="side-menu__item {{ request()->is('student/gamification/rewards') ? 'active' : '' }}">
                                        <span class="side-menu__label">المكافآت</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('student/gamification/certificates') ? 'active' : '' }}">
                                    <a href="{{ route('student.gamification.certificates') }}" class="side-menu__item {{ request()->is('student/gamification/certificates') ? 'active' : '' }}">
                                        <span class="side-menu__label">الشهادات</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('student/gamification/stats') ? 'active' : '' }}">
                                    <a href="{{ route('student.gamification.stats') }}" class="side-menu__item {{ request()->is('student/gamification/stats') ? 'active' : '' }}">
                                        <span class="side-menu__label">إحصائياتي</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('student/tasks*') ? 'active' : '' }}">
                                    <a href="{{ route('student.tasks.index') }}" class="side-menu__item {{ request()->is('student/tasks*') ? 'active' : '' }}">
                                        <span class="side-menu__label">مهامي</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="slide {{ request()->is('student/notifications*') ? 'active' : '' }}">
                            <a href="{{ route('student.notifications.index') }}" class="side-menu__item {{ request()->is('student/notifications*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
                                </svg>
                                <span class="side-menu__label">الإشعارات</span>
                            </a>
                        </li>
                        
                        <li class="slide {{ request()->is('student/enrollments*') ? 'active' : '' }}">
                            <a href="{{ route('student.enrollments.index') }}" class="side-menu__item {{ request()->is('student/enrollments*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                                </svg>
                                <span class="side-menu__label">طلب الانضمام</span>
                            </a>
                        </li>

                        <!-- Start::slide__category -->
                        <li class="slide__category"><span class="category-name">الاختبارات</span></li>
                        <!-- End::slide__category -->

                        <li class="slide">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2zm-7 14l-5-5 1.41-1.41L12 14.17l4.59-4.58L18 11l-6 6z"/>
                                </svg>
                                <span class="side-menu__label">الاختبارات المتاحة</span>
                            </a>
                        </li>

                        <li class="slide">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M19 3h-1V1h-2v2H8V1H6v2H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2zm0 16H5V8h14v11zM9 10H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2z"/>
                                </svg>
                                <span class="side-menu__label">نتائج الاختبارات</span>
                            </a>
                        </li>

                        <li class="slide {{ request()->is('student/assignments*') ? 'active' : '' }}">
                            <a href="{{ route('student.assignments.index') }}" class="side-menu__item {{ request()->is('student/assignments*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                                </svg>
                                <span class="side-menu__label">الواجبات</span>
                            </a>
                        </li>

                        <li class="slide has-sub {{ request()->is('student/library*') ? 'open' : '' }}">
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
                                <li class="slide {{ request()->is('student/library') && !request()->is('student/library/*') ? 'active' : '' }}">
                                    <a href="{{ route('student.library.index') }}" class="side-menu__item {{ request()->is('student/library') && !request()->is('student/library/*') ? 'active' : '' }}">
                                        <span class="side-menu__label">المكتبة</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('student/library/favorites') ? 'active' : '' }}">
                                    <a href="{{ route('student.library.favorites') }}" class="side-menu__item {{ request()->is('student/library/favorites') ? 'active' : '' }}">
                                        <span class="side-menu__label">مفضلتي</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="slide {{ request()->is('student/calendar*') ? 'active' : '' }}">
                            <a href="{{ route('student.calendar.index') }}" class="side-menu__item {{ request()->is('student/calendar*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/>
                                </svg>
                                <span class="side-menu__label">التقويم</span>
                            </a>
                        </li>

                        <li class="slide {{ request()->is('student/ai/chatbot*') ? 'active' : '' }}">
                            <a href="{{ route('student.ai.chatbot.index') }}" class="side-menu__item {{ request()->is('student/ai/chatbot*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-2 12H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z"/>
                                </svg>
                                <span class="side-menu__label">المساعد التعليمي</span>
                            </a>
                        </li>

                        <!-- Start::slide__category -->
                        <li class="slide__category"><span class="category-name">الحساب</span></li>
                        <!-- End::slide__category -->

                        <li class="slide {{ request()->is('student/profile') || request()->is('profile') ? 'active' : '' }}">
                            <a href="{{ route('student.profile') }}" class="side-menu__item {{ request()->is('student/profile') || request()->is('profile') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                </svg>
                                <span class="side-menu__label">الملف الشخصي</span>
                            </a>
                        </li>

                        <!-- End::slide -->









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
