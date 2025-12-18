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

                        <li class="slide {{ request()->is('admin/enrollments*') ? 'active' : '' }}">
                            <a href="{{ route('admin.enrollments.index') }}" class="side-menu__item {{ request()->is('admin/enrollments*') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                                </svg>
                                <span class="side-menu__label">الانضمامات</span>
                            </a>
                        </li>

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

                        <li class="slide {{ request()->is('admin/quiz-attempts/needs-grading') ? 'active' : '' }}">
                            <a href="{{ route('admin.quiz-attempts.needs-grading') }}" class="side-menu__item {{ request()->is('admin/quiz-attempts/needs-grading') ? 'active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                    <path d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M19 3h-1V1h-2v2H8V1H6v2H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2zm0 16H5V8h14v11zM9 10H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm-8 4H7v2h2v-2zm4 0h-2v2h2v-2z"/>
                                </svg>
                                <span class="side-menu__label">بحاجة للتصحيح</span>
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
