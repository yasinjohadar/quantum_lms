
<style>
/* Force remove all borders and outlines from navbar elements */
.buttom-header .nav-item,
.buttom-header .nav-link,
.buttom-header .navbar-nav .nav-item,
.buttom-header .navbar-nav .nav-link,
header .nav-item,
header .nav-link,
header .navbar-nav .nav-item,
header .navbar-nav .nav-link {
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
    background: transparent !important;
}
.buttom-header .nav-item:focus,
.buttom-header .nav-link:focus,
.buttom-header .nav-item:focus-visible,
.buttom-header .nav-link:focus-visible,
.buttom-header .nav-item:focus-within,
.buttom-header .nav-link:focus-within,
.buttom-header .nav-item.active,
.buttom-header .nav-link.active,
.buttom-header .nav-item:hover,
.buttom-header .nav-link:hover {
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
    background: transparent !important;
}
</style>

  <!-- Start Header -->
<header id="siteHeader">
    <div class="top-header d-flex align-items-center py-2">
        <div class="container">
            <div class="row">

                <div class="col-6 col-md-4">
                    <a href="/">
                        <img width="55" style="max-height:42px;height:auto;width:auto;" src="{{ asset('frontend/images/logo-footer.webp') }}" alt="logo">
                    </a>
                </div>



                <div class="col-6 col-md-4 d-flex align-items-center">
                    <input type="text" class="form-control" placeholder="اكتب   للبحث ">
                </div>




                <div class="col-md-4 left-header d-flex align-items-center d-none d-lg-flex justify-content-end">
                    <a href="/blog" role="button" >
                        <span>المدونة</span>
                        <i class="fa-solid fa-book"></i>
                    </a>
                    @auth
                        @php
                            $user = auth()->user();
                            $dashboardRoute = $user->hasRole('admin') ? route('admin.dashboard') : ($user->hasRole('student') ? route('student.dashboard') : route('dashboard'));
                        @endphp
                        <a href="{{ $dashboardRoute }}" role="button">
                            <span>لوحة التحكم</span>
                            <i class="fa-solid fa-tachometer-alt"></i>
                        </a>
                    @else
                        <a href="/login" role="button">
                            <span>تسجيل الدخول</span>
                            <i class="fa-solid fa-user"></i>
                        </a>
                    @endauth
                    <a href="/courses">
                        <span>الكورسات</span>
                        <i class="fa-solid fa-computer"></i>
                    </a>
                </div>



            </div>
        </div>
    </div>

    <div class="buttom-header">
        <nav class="navbar navbar-expand-lg bg-body-tertiary">
            <div class="container">

              <!-- <a class="navbar-brand" href="#">Navbar</a> -->
              <button class="navbar-toggler menu-icon" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
              </button>

              <!-- Social + theme toggle (left side) -->
              <div class="header-social d-flex" aria-label="social-links">
                <div class="header-social__icons d-none d-lg-flex">
                  <a href="#" class="header-social__link" aria-label="facebook"><i class="fa-brands fa-facebook-f"></i></a>
                  <a href="#" class="header-social__link" aria-label="instagram"><i class="fa-brands fa-instagram"></i></a>
                  <a href="#" class="header-social__link" aria-label="telegram"><i class="fa-brands fa-telegram"></i></a>
                  <a href="#" class="header-social__link" aria-label="youtube"><i class="fa-brands fa-youtube"></i></a>
                </div>

                <button type="button" class="header-theme-toggle" id="headerThemeToggle" aria-label="toggle-header-theme">
                  <i class="fa-solid fa-moon"></i>
                </button>
              </div>


              <div>
                <button class="navbar-toggler menu-icon" type="button" data-bs-toggle="offcanvas" href="#cart" >
                    <i class="fa-solid fa-cart-shopping"></i>
                  </button>
                @auth
                    @php
                        $user = auth()->user();
                        $dashboardRoute = $user->hasRole('admin') ? route('admin.dashboard') : ($user->hasRole('student') ? route('student.dashboard') : route('dashboard'));
                    @endphp
                    <a href="{{ $dashboardRoute }}" class="navbar-toggler menu-icon" style="border:none!important;outline:none!important;box-shadow:none!important;background:transparent!important;">
                        <i class="fa-solid fa-tachometer-alt"></i>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="navbar-toggler menu-icon" style="border:none!important;outline:none!important;box-shadow:none!important;background:transparent!important;">
                        <i class="fa-solid fa-user"></i>
                    </a>
                @endauth
              </div>


              <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                  <li class="nav-item" style="border:none!important;outline:none!important;box-shadow:none!important;">
                    <a class="nav-link active" style="border:none!important;outline:none!important;box-shadow:none!important;" aria-current="page" href="/">الرئيسية</a>
                  </li>
                  <li class="nav-item" style="border:none!important;outline:none!important;box-shadow:none!important;">
                    <a class="nav-link" style="border:none!important;outline:none!important;box-shadow:none!important;" href="#">الكورسات</a>
                  </li>
                  <li class="nav-item" style="border:none!important;outline:none!important;box-shadow:none!important;">
                    <a class="nav-link" style="border:none!important;outline:none!important;box-shadow:none!important;" href="#">آراء الطلاب</a>
                  </li>
         
                  <li class="nav-item" style="border:none!important;outline:none!important;box-shadow:none!important;">
                    <a class="nav-link" style="border:none!important;outline:none!important;box-shadow:none!important;" href="#">من نحن</a>
                  </li>
                  <li class="nav-item" style="border:none!important;outline:none!important;box-shadow:none!important;">
                    <a class="nav-link" style="border:none!important;outline:none!important;box-shadow:none!important;" href="#">المدونة</a>
                  </li>
                  <li class="nav-item" style="border:none!important;outline:none!important;box-shadow:none!important;">
                    <a class="nav-link" style="border:none!important;outline:none!important;box-shadow:none!important;" href="#">اتصل بنا</a>
                  </li>


                </ul>

              </div>
            </div>
          </nav>
    </div>
</header>
  <!-- End Header -->
