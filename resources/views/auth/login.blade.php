
<!doctype html>
<html lang="en" class="light-theme">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('assets/images/favicon-32x32.png') }}" type="image/png" />
  <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/bootstrap-extended.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/icons.css') }}" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
  <link href="{{ asset('assets/css/pace.min.css') }}" rel="stylesheet" />
  <title>Login</title>
</head>
<body class="bg-surface">
  <div class="wrapper">
    @include('components.guest-header')
    <main class="authentication-content">
      <div class="container">
        <div class="mt-4">
          <div class="card rounded-0 overflow-hidden shadow-none border mb-5 mb-lg-0">
            <div class="row g-0">
              <div class="col-12 order-1 col-xl-8 d-flex align-items-center justify-content-center border-end">
                <img src="{{ asset('assets/images/loginPageimg.png') }}" class="img-fluid" alt="">
              </div>
              <div class="col-12 col-xl-4 order-xl-2">
                <div class="card-body p-4 p-sm-5">
                    <div class="rounded-3 bg-primary bg-gradient text-white fw-bold d-flex align-items-center justify-content-center me-3" style="width:44px;height:20px;">
                        PW
                      </div>
                      <h3 class="card-title mb-0">
                        Peoples <span class="text-primary brand-gradient">WeighHub</span>
                      </h3>
                    <p class="mb-1 fst-italic text-muted">“Connect. Weigh. Control.”</p>

                    </div>

                    <!-- Section title with subtle highlight -->
                    <h5 class="card-title fw-bold mb-2 hl-underline bold text-center">Sign In</h5>

                    <!-- Taglines -->
                  <form class="form-body" method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="row g-3">
                      <div class="col-12">
                        <label for="inputEmailAddress" class="form-label ">User Name/Email </label>
                        <div class="ms-auto position-relative">
                          <div class="position-absolute top-50 translate-middle-y search-icon px-3"><i class="bi bi-envelope-fill"></i></div>
                          <input type="text" name="email" class="form-control radius-30 ps-5" id="inputEmailAddress" placeholder="Email" required autofocus>
                        </div>
                      </div>
                      <div class="col-12">
                        <label for="inputChoosePassword" class="form-label bold">Enter Password</label>
                        <div class="ms-auto position-relative">
                          <div class="position-absolute top-50 translate-middle-y search-icon px-3"><i class="bi bi-lock-fill"></i></div>
                          <input type="password" name="password" class="form-control radius-30 ps-5" id="inputChoosePassword" placeholder="Password" required>
                        </div>
                      </div>
                      <div class="col-6">
                        <div class="form-check form-switch">
                          <input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked" checked="">
                          <label class="form-check-label" for="flexSwitchCheckChecked">Remember Me</label>
                        </div>
                      </div>
                      {{-- <div class="col-6 text-end">
                        <a href="#">Forgot Password ?</a>
                      </div> --}}
                      <div class="col-12">
                        <div class="d-grid">
                          <button type="submit" class="btn btn-primary radius-30">Sign In</button>
                        </div>
                      </div>
                      <div class="col-12">
                        {{-- <div class="login-separater text-center"> <span>OR SIGN IN WITH EMAIL</span> --}}
                          <hr>
                        </div>
                      </div>
                      <div class="col-12">
                        <div class="d-flex align-items-center gap-3 justify-content-center">
                          <button type="button" class="btn btn-white text-danger"><i class="bi bi-google me-0"></i></button>
                          <button type="button" class="btn btn-white text-primary"><i class="bi bi-linkedin me-0"></i></button>
                          <button type="button" class="btn btn-white text-info"><i class="bi bi-facebook me-0"></i></button>
                        </div>
                      </div>
                      <div class="col-12 text-center">
                        {{-- <p class="mb-0">Don't have an account yet? <a href="#">Sign up here</a></p> --}}
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
    @include('components.guest-footer')
  </div>
  <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
  <script src="{{ asset('assets/js/pace.min.js') }}"></script>
</body>
</html>
