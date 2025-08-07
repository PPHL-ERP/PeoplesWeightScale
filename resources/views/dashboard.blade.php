

<!doctype html>
<html lang="en" class="light-theme">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('assets/images/favicon-32x32.png') }}" type="image/png" />
  <link href="{{ asset('assets/plugins/simplebar/css/simplebar.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/vectormap/jquery-jvectormap-2.0.2.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/bootstrap-extended.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/icons.css') }}" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
  <link href="{{ asset('assets/css/pace.min.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/dark-theme.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/light-theme.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/semi-dark.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/css/header-colors.css') }}" rel="stylesheet" />
  <title>Dashboard</title>
</head>
<body>
  <div class="wrapper">
    @include('components.dashboard-header')
    @include('components.dashboard-sidebar')
    <main class="page-content">
      <!-- Static dashboard content from template (cards, charts, statistics, product actions, etc.) -->
      <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-2 row-cols-xxl-4">
        <div class="col">
          <div class="card radius-10">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">Total Orders</p>
                        <h4 class="my-1">4805</h4>
                        <p class="mb-0 font-13 text-success"><i class="bi bi-caret-up-fill"></i> 5% from last week</p>
                    </div>
                    <div class="widget-icon-large bg-gradient-purple text-white ms-auto"><i class="bi bi-basket2-fill"></i></div>
                </div>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card radius-10">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">Total Revenue</p>
                        <h4 class="my-1">$24K</h4>
                        <p class="mb-0 font-13 text-success"><i class="bi bi-caret-up-fill"></i> 4.6 from last week</p>
                    </div>
                    <div class="widget-icon-large bg-gradient-success text-white ms-auto"><i class="bi bi-currency-exchange"></i></div>
                </div>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card radius-10">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">Total Customers</p>
                        <h4 class="my-1">5.8K</h4>
                        <p class="mb-0 font-13 text-danger"><i class="bi bi-caret-down-fill"></i> 2.7 from last week</p>
                    </div>
                    <div class="widget-icon-large bg-gradient-danger text-white ms-auto"><i class="bi bi-people-fill"></i></div>
                </div>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card radius-10">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">Bounce Rate</p>
                        <h4 class="my-1">38.15%</h4>
                        <p class="mb-0 font-13 text-success"><i class="bi bi-caret-up-fill"></i> 12.2% from last week</p>
                    </div>
                    <div class="widget-icon-large bg-gradient-info text-white ms-auto"><i class="bi bi-bar-chart-line-fill"></i></div>
                </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-12 col-lg-8 col-xl-8 d-flex">
          <div class="card radius-10 w-100">
            <div class="card-body">
              <div class="row row-cols-1 row-cols-lg-2 g-3 align-items-center">
                <div class="col">
                  <h5 class="mb-0">Sales Figures</h5>
                </div>
                <div class="col">
                  <div class="d-flex align-items-center justify-content-sm-end gap-3 cursor-pointer">
                    <div class="font-13"><i class="bi bi-circle-fill text-primary"></i><span class="ms-2">Sales</span></div>
                    <div class="font-13"><i class="bi bi-circle-fill text-success"></i><span class="ms-2">Orders</span></div>
                  </div>
                </div>
              </div>
              <div id="chart1"></div>
            </div>
          </div>
        </div>
        <div class="col-12 col-lg-4 col-xl-4 d-flex">
          <div class="card radius-10 w-100">
            <div class="card-header bg-transparent">
              <div class="row g-3 align-items-center">
                <div class="col">
                  <h5 class="mb-0">Statistics</h5>
                </div>
                <div class="col">
                  <div class="d-flex align-items-center justify-content-end gap-3 cursor-pointer">
                    <div class="dropdown">
                      <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots-vertical font-22 text-option"></i></a>
                      <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="javascript:;">Action</a></li>
                        <li><a class="dropdown-item" href="javascript:;">Another action</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="javascript:;">Something else here</a></li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="card-body">
              <div id="chart2"></div>
            </div>
            <ul class="list-group list-group-flush mb-0">
              <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">New Orders<span class="badge bg-primary badge-pill">25%</span></li>
              <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">Completed<span class="badge bg-orange badge-pill">65%</span></li>
              <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">Pending<span class="badge bg-success badge-pill">10%</span></li>
            </ul>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-12 col-lg-6 col-xl-6 d-flex">
          <div class="card radius-10 w-100">
            <div class="card-header bg-transparent">
              <div class="row g-3 align-items-center">
                <div class="col">
                  <h5 class="mb-0">Statistics</h5>
                </div>
                <div class="col">
                  <div class="d-flex align-items-center justify-content-end gap-3 cursor-pointer">
                    <div class="dropdown">
                      <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots-vertical font-22 text-option"></i></a>
                      <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="javascript:;">Action</a></li>
                        <li><a class="dropdown-item" href="javascript:;">Another action</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="javascript:;">Something else here</a></li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="card-body">
              <div class="d-lg-flex align-items-center justify-content-center gap-2">
                <div id="chart3"></div>
                <ul class="list-group list-group-flush">
                  <li class="list-group-item"><i class="bi bi-circle-fill text-purple me-1"></i> Visitors:<span class="me-1">89</span></li>
                  <li class="list-group-item"><i class="bi bi-circle-fill text-info me-1"></i> Subscribers:<span class="me-1">45</span></li>
                  <li class="list-group-item"><i class="bi bi-circle-fill text-pink me-1"></i> Contributor:<span class="me-1">35</span></li>
                  <li class="list-group-item"><i class="bi bi-circle-fill text-success me-1"></i> Author:<span class="me-1">62</span></li>
                </ul>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12 col-lg-6 col-xl-6 d-flex">
          <div class="card radius-10 w-100">
            <div class="card-body">
              <div class="row row-cols-1 row-cols-lg-2 g-3 align-items-center">
                <div class="col">
                  <h5 class="mb-0">Product Actions</h5>
                </div>
                <div class="col">
                  <div class="d-flex align-items-center justify-content-sm-end gap-3 cursor-pointer">
                    <div class="font-13"><i class="bi bi-circle-fill text-primary"></i><span class="ms-2">Views</span></div>
                    <div class="font-13"><i class="bi bi-circle-fill text-pink"></i><span class="ms-2">Clicks</span></div>
                  </div>
                </div>
              </div>
              <div id="chart4"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-12 col-lg-6 col-xl-4 d-flex">
          <div class="card radius-10 w-100">
            <div class="card-header bg-transparent">
              <div class="row g-3 align-items-center">
                <div class="col">
                  <h5 class="mb-0">Top Categories</h5>
                </div>
                <div class="col">
                  <div class="d-flex align-items-center justify-content-end gap-3 cursor-pointer">
                    <div class="dropdown">
                      <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots-vertical font-22 text-option"></i></a>
                      <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="javascript:;">Action</a></li>
                        <li><a class="dropdown-item" href="javascript:;">Another action</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="javascript:;">Something else here</a></li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="card-body">
              <div class="categories">
                <div class="progress-wrapper">
                  <p class="mb-2">Electronic <span class="float-end">85%</span></p>
                  <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-gradient-purple" role="progressbar" style="width: 85%;"></div>
                  </div>
                </div>
                <div class="my-3 border-top"></div>
                <div class="progress-wrapper">
                  <p class="mb-2">Furniture <span class="float-end">70%</span></p>
                  <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-gradient-danger" role="progressbar" style="width: 70%;"></div>
                  </div>
                </div>
                <div class="my-3 border-top"></div>
                <div class="progress-wrapper">
                  <p class="mb-2">Fashion <span class="float-end">66%</span></p>
                  <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-gradient-success" role="progressbar" style="width: 66%;"></div>
                  </div>
                </div>
                <div class="my-3 border-top"></div>
                <div class="progress-wrapper">
                  <p class="mb-2">Mobiles <span class="float-end">76%</span></p>
                  <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-gradient-info" role="progressbar" style="width: 76%;"></div>
                  </div>
                </div>
                <div class="my-3 border-top"></div>
                <div class="progress-wrapper">
                  <p class="mb-2">Accessories <span class="float-end">80%</span></p>
                  <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-gradient-warning" role="progressbar" style="width: 80%;"></div>
                  </div>
                </div>
                <div class="my-3 border-top"></div>
                <div class="progress-wrapper">
                  <p class="mb-2">Watches <span class="float-end">65%</span></p>
                  <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-gradient-voilet" role="progressbar" style="width: 65%;"></div>
                  </div>
                </div>
                <div class="my-3 border-top"></div>
                <div class="progress-wrapper">
                  <p class="mb-2">Sports <span class="float-end">45%</span></p>
                  <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-gradient-royal" role="progressbar" style="width: 45%;"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12 col-lg-6 col-xl-4 d-flex">
          <div class="card radius-10 w-100">
            <div class="card-header bg-transparent">
              <div class="row g-3 align-items-center">
                <div class="col">
                  <h5 class="mb-0">Best Products</h5>
                </div>
                <div class="col">
                  <div class="d-flex align-items-center justify-content-end gap-3 cursor-pointer">
                    <div class="dropdown">
                      <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots-vertical font-22 text-option"></i></a>
                      <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="javascript:;">Action</a></li>
                        <li><a class="dropdown-item" href="javascript:;">Another action</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="javascript:;">Something else here</a></li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="card-body p-0">
              <div class="best-product p-2 mb-3">
                <div class="best-product-item">
                  <div class="d-flex align-items-center gap-3">
                    <div class="product-box border">
                      <img src="{{ asset('assets/images/products/01.png') }}" alt="">
                    </div>
                    <div class="product-info">
                      <h6 class="product-name mb-1">White Polo T-Shirt</h6>
                      <div class="product-rating mb-0">
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                      </div>
                    </div>
                    <div class="sales-count ms-auto">
                      <p class="mb-0">245 Sales</p>
                    </div>
                  </div>
                </div>
                <!-- Repeat for other products as in template, using asset() for images -->
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
    @include('components.dashboard-footer')
  </div>
  <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/simplebar/js/simplebar.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js') }}"></script>
  <script src="{{ asset('assets/js/pace.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/vectormap/jquery-jvectormap-2.0.2.min.js') }}"></script>
  <script src="{{ asset('assets/plugins/vectormap/jquery-jvectormap-world-mill-en.js') }}"></script>
  <script src="{{ asset('assets/plugins/apexcharts-bundle/js/apexcharts.min.js') }}"></script>
  <script src="{{ asset('assets/js/app.js') }}"></script>
  <script src="{{ asset('assets/js/index.js') }}"></script>
</body>
</html>
