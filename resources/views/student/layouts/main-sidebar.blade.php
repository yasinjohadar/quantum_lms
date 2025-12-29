        <!-- Start::app-sidebar -->
        <aside class="app-sidebar sticky" id="sidebar">

            <!-- Start::main-sidebar-header -->
            <div class="main-sidebar-header">
                <a href="{{ route('student.dashboard') }}" class="header-logo">
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
                        <!-- الصفحة الرئيسية -->
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

                        <!-- صفوفي -->
                        <li class="slide {{ request()->is('student/classes*') ? 'active' : '' }}">
                            <a href="{{ route('student.classes') }}" class="side-menu__item {{ request()->is('student/classes*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M20 6h-2.18c.11-.31.18-.65.18-1a2.996 2.996 0 00-5.5-1.65l-.5.67-.5-.68C10.96 2.54 10 2 9 2 7.34 2 6 3.34 6 5c0 .35.07.69.18 1H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-5-2c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zM9 4c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm11 15H4v-2h16v2zm0-5H4V8h5.08L7 10.83 8.62 12 11 8.76l1-1.36 1 1.36L15.38 12 17 10.83 14.92 8H20v6z"/>
                                </svg>
                                <span class="side-menu__label">صفوفي</span>
                            </a>
                        </li>

                        <!-- موادي الدراسية -->
                        <li class="slide {{ request()->is('student/subjects*') ? 'active' : '' }}">
                            <a href="{{ route('student.subjects') }}" class="side-menu__item {{ request()->is('student/subjects*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M18 2H8a2 2 0 00-2 2v14a2 2 0 002 2h10l4-4V4a2 2 0 00-2-2zm0 13v3h-3a1 1 0 01-1-1v-2h4zm-6-4H8V9h4zm4-3H8V6h8z"/>
                                </svg>
                                <span class="side-menu__label">موادي الدراسية</span>
                            </a>
                        </li>

                        <!-- تقدمي الدراسي -->
                        <li class="slide {{ request()->is('student/progress*') ? 'active' : '' }}">
                            <a href="{{ route('student.progress.index') }}" class="side-menu__item {{ request()->is('student/progress*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                                </svg>
                                <span class="side-menu__label">تقدمي الدراسي</span>
                            </a>
                        </li>

                        <!-- تقاريري -->
                        <li class="slide {{ request()->is('student/reports*') ? 'active' : '' }}">
                            <a href="{{ route('student.reports.index') }}" class="side-menu__item {{ request()->is('student/reports*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                                </svg>
                                <span class="side-menu__label">تقاريري</span>
                            </a>
                        </li>

                        <!-- الاختبارات المتاحة -->
                        <li class="slide {{ request()->is('student/quizzes') && !request()->is('student/quizzes/*') ? 'active' : '' }}">
                            <a href="{{ route('student.quizzes.index') }}" class="side-menu__item {{ request()->is('student/quizzes') && !request()->is('student/quizzes/*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2zm-7 14l-5-5 1.41-1.41L12 14.17l4.59-4.58L18 11l-6 6z"/>
                                </svg>
                                <span class="side-menu__label">الاختبارات المتاحة</span>
                            </a>
                        </li>

                        <!-- نتائج الاختبارات -->
                        <li class="slide {{ request()->is('student/quizzes/results') ? 'active' : '' }}">
                            <a href="{{ route('student.quizzes.results') }}" class="side-menu__item {{ request()->is('student/quizzes/results') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M19 3h-1V1h-2v2H8V1H6v2H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2zm0 16H5V8h14v11zM9 10H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2z"/>
                                </svg>
                                <span class="side-menu__label">نتائج الاختبارات</span>
                            </a>
                        </li>

                        <!-- الواجبات -->
                        <li class="slide {{ request()->is('student/assignments*') ? 'active' : '' }}">
                            <a href="{{ route('student.assignments.index') }}" class="side-menu__item {{ request()->is('student/assignments*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                                </svg>
                                <span class="side-menu__label">الواجبات</span>
                            </a>
                        </li>

                        <!-- الجلسات الحية -->
                        <li class="slide {{ request()->is('student/live-sessions*') ? 'active' : '' }}">
                            <a href="{{ route('student.live-sessions.index') }}" class="side-menu__item {{ request()->is('student/live-sessions*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                <span class="side-menu__label">الجلسات الحية</span>
                            </a>
                        </li>

                        <!-- سجل الحضور -->
                        <li class="slide {{ request()->is('student/attendance*') ? 'active' : '' }}">
                            <a href="{{ route('student.attendance.index') }}" class="side-menu__item {{ request()->is('student/attendance*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                                </svg>
                                <span class="side-menu__label">سجل الحضور</span>
                            </a>
                        </li>

                        <!-- التحفيز والإنجازات -->
                        <li class="slide has-sub {{ request()->is('student/gamification*') || request()->is('student/tasks*') ? 'open' : '' }}">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                                <span class="side-menu__label">التحفيز والإنجازات</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide {{ request()->is('student/gamification/dashboard') ? 'active' : '' }}">
                                    <a href="{{ route('student.gamification.dashboard') }}" class="side-menu__item {{ request()->is('student/gamification/dashboard') ? 'active' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                        </svg>
                                        <span class="side-menu__label">لوحة التحفيز</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('student/gamification/badges') ? 'active' : '' }}">
                                    <a href="{{ route('student.gamification.badges') }}" class="side-menu__item {{ request()->is('student/gamification/badges') ? 'active' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                                            <path d="M2 17l10 5 10-5"></path>
                                            <path d="M2 12l10 5 10-5"></path>
                                        </svg>
                                        <span class="side-menu__label">الشارات</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('student/gamification/achievements') ? 'active' : '' }}">
                                    <a href="{{ route('student.gamification.achievements') }}" class="side-menu__item {{ request()->is('student/gamification/achievements') ? 'active' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                                        </svg>
                                        <span class="side-menu__label">الإنجازات</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('student/gamification/leaderboard') ? 'active' : '' }}">
                                    <a href="{{ route('student.gamification.leaderboard') }}" class="side-menu__item {{ request()->is('student/gamification/leaderboard') ? 'active' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M3 3v18h18"></path>
                                            <path d="M18 7l-5 5-4-4-3 3"></path>
                                        </svg>
                                        <span class="side-menu__label">لوحة المتصدرين</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('student/gamification/challenges') ? 'active' : '' }}">
                                    <a href="{{ route('student.gamification.challenges') }}" class="side-menu__item {{ request()->is('student/gamification/challenges') ? 'active' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <path d="M12 6v6l4 2"></path>
                                        </svg>
                                        <span class="side-menu__label">التحديات</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('student/gamification/rewards') ? 'active' : '' }}">
                                    <a href="{{ route('student.gamification.rewards') }}" class="side-menu__item {{ request()->is('student/gamification/rewards') ? 'active' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M20 7h-4V4c0-1.11-.89-2-2-2h-4c-1.11 0-2 .89-2 2v3H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V9c0-1.11-.89-2-2-2zm-6 0h-4V4h4v3z"></path>
                                        </svg>
                                        <span class="side-menu__label">المكافآت</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('student/gamification/certificates') ? 'active' : '' }}">
                                    <a href="{{ route('student.gamification.certificates') }}" class="side-menu__item {{ request()->is('student/gamification/certificates') ? 'active' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                            <line x1="16" y1="13" x2="8" y2="13"></line>
                                            <line x1="16" y1="17" x2="8" y2="17"></line>
                                            <polyline points="10 9 9 9 8 9"></polyline>
                                        </svg>
                                        <span class="side-menu__label">الشهادات</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('student/gamification/stats') ? 'active' : '' }}">
                                    <a href="{{ route('student.gamification.stats') }}" class="side-menu__item {{ request()->is('student/gamification/stats') ? 'active' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M3 3v18h18"></path>
                                            <path d="M18 7l-5 5-4-4-3 3"></path>
                                        </svg>
                                        <span class="side-menu__label">إحصائياتي</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('student/tasks*') ? 'active' : '' }}">
                                    <a href="{{ route('student.tasks.index') }}" class="side-menu__item {{ request()->is('student/tasks*') ? 'active' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                                        </svg>
                                        <span class="side-menu__label">مهامي</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- المكتبة الرقمية -->
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
                                <li class="slide {{ request()->is('student/library') && !request()->is('student/library/*') ? 'active' : '' }}">
                                    <a href="{{ route('student.library.index') }}" class="side-menu__item {{ request()->is('student/library') && !request()->is('student/library/*') ? 'active' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                        </svg>
                                        <span class="side-menu__label">المكتبة</span>
                                    </a>
                                </li>
                                <li class="slide {{ request()->is('student/library/favorites') ? 'active' : '' }}">
                                    <a href="{{ route('student.library.favorites') }}" class="side-menu__item {{ request()->is('student/library/favorites') ? 'active' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                        </svg>
                                        <span class="side-menu__label">مفضلتي</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- التقويم -->
                        <li class="slide {{ request()->is('student/calendar*') ? 'active' : '' }}">
                            <a href="{{ route('student.calendar.index') }}" class="side-menu__item {{ request()->is('student/calendar*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/>
                                </svg>
                                <span class="side-menu__label">التقويم</span>
                            </a>
                        </li>

                        <!-- المساعد التعليمي -->
                        <li class="slide {{ request()->is('student/ai/chatbot*') ? 'active' : '' }}">
                            <a href="{{ route('student.ai.chatbot.index') }}" class="side-menu__item {{ request()->is('student/ai/chatbot*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-2 12H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z"/>
                                </svg>
                                <span class="side-menu__label">المساعد التعليمي</span>
                            </a>
                        </li>

                        <!-- الإشعارات -->
                        <li class="slide {{ request()->is('student/notifications*') ? 'active' : '' }}">
                            <a href="{{ route('student.notifications.index') }}" class="side-menu__item {{ request()->is('student/notifications*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
                                </svg>
                                <span class="side-menu__label">الإشعارات</span>
                            </a>
                        </li>
                        
                        <!-- طلب الانضمام -->
                        <li class="slide {{ request()->is('student/enrollments*') ? 'active' : '' }}">
                            <a href="{{ route('student.enrollments.index') }}" class="side-menu__item {{ request()->is('student/enrollments*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                                </svg>
                                <span class="side-menu__label">طلب الانضمام</span>
                            </a>
                        </li>

                        <!-- الملف الشخصي -->
                        <li class="slide {{ request()->is('student/profile') || request()->is('profile') ? 'active' : '' }}">
                            <a href="{{ route('student.profile') }}" class="side-menu__item {{ request()->is('student/profile') || request()->is('profile') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                </svg>
                                <span class="side-menu__label">الملف الشخصي</span>
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

        <style>
            /* جعل الروابط غير النشطة بلون رمادي */
            .app-sidebar .side-menu__item:not(.active) {
                color: #6b7280 !important;
            }
            
            .app-sidebar .side-menu__item:not(.active) .side-menu__label,
            .app-sidebar .side-menu__item:not(.active) .side-menu__icon,
            .app-sidebar .side-menu__item:not(.active) .side-menu__angle {
                color: #6b7280 !important;
                fill: #6b7280 !important;
            }
            
            /* الروابط النشطة فقط باللون الأزرق */
            .app-sidebar .side-menu__item.active {
                color: #4f46e5 !important;
            }
            
            .app-sidebar .side-menu__item.active .side-menu__label,
            .app-sidebar .side-menu__item.active .side-menu__icon,
            .app-sidebar .side-menu__item.active .side-menu__angle {
                color: #4f46e5 !important;
                fill: #4f46e5 !important;
            }
            
            /* عند hover على الروابط غير النشطة */
            .app-sidebar .side-menu__item:not(.active):hover {
                color: #4f46e5 !important;
            }
            
            .app-sidebar .side-menu__item:not(.active):hover .side-menu__label,
            .app-sidebar .side-menu__item:not(.active):hover .side-menu__icon,
            .app-sidebar .side-menu__item:not(.active):hover .side-menu__angle {
                color: #4f46e5 !important;
                fill: #4f46e5 !important;
            }
        </style>

        <script>
            // تحسين السكرول التلقائي للرابط النشط
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
                        }
                    }
                    
                    // الانتظار قليلاً لضمان تحديث العرض
                    setTimeout(function() {
                        // الحصول على موقع العنصر النشط
                        const menuRect = activeMenuItem.getBoundingClientRect();
                        const sidebarRect = sidebarScroll.getBoundingClientRect();
                        
                        // التحقق من أن العنصر مرئي
                        const isVisible = (
                            menuRect.top >= sidebarRect.top &&
                            menuRect.bottom <= sidebarRect.bottom
                        );
                        
                        if (!isVisible) {
                            // حساب الموقع النسبي
                            const relativeTop = menuRect.top - sidebarRect.top + sidebarScroll.scrollTop;
                            
                            // حساب الوسط المرئي للـ sidebar مع مساحة علوية صغيرة
                            const sidebarHeight = sidebarRect.height;
                            const menuItemHeight = menuRect.height;
                            const offset = 100; // مساحة علوية
                            const scrollPosition = relativeTop - offset;
                            
                            // السكرول بسلاسة للعنصر النشط
                            sidebarScroll.scrollTo({
                                top: Math.max(0, scrollPosition),
                                behavior: 'smooth'
                            });
                        }
                    }, 100);
                }
                
                // تنفيذ السكرول بعد تحميل الصفحة
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', function() {
                        setTimeout(scrollToActiveMenuItem, 500);
                    });
                } else {
                    setTimeout(scrollToActiveMenuItem, 500);
                }
            })();
        </script>
