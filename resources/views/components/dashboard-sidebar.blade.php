{{-- Sidebar extracted from dashboard template (index.html) --}}
<aside class="sidebar-wrapper">
  <div class="iconmenu">
    <div class="nav-toggle-box">
      <div class="nav-toggle-icon"><i class="bi bi-list"></i></div>
    </div>
    <ul class="nav nav-pills flex-column">
      <li class="nav-item" data-bs-toggle="tooltip" data-bs-placement="right" title="Home">
        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#pills-dashboards" type="button"><i class="bi bi-house-door-fill"></i></button>
      </li>
      {{-- <li class="nav-item" data-bs-toggle="tooltip" data-bs-placement="right" title="Application">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pills-application" type="button"><i class="bi bi-grid-fill"></i></button>
      </li>
      <li class="nav-item" data-bs-toggle="tooltip" data-bs-placement="right" title="Widgets">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pills-widgets" type="button"><i class="bi bi-briefcase-fill"></i></button>
      </li>
      <li class="nav-item" data-bs-toggle="tooltip" data-bs-placement="right" title="eCommerce">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pills-ecommerce" type="button"><i class="bi bi-bag-check-fill"></i></button>
      </li>
      <li class="nav-item" data-bs-toggle="tooltip" data-bs-placement="right" title="Components">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pills-components" type="button"><i class="bi bi-bookmark-star-fill"></i></button>
      </li>
      <li class="nav-item" data-bs-toggle="tooltip" data-bs-placement="right" title="Forms">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pills-forms" type="button"><i class="bi bi-file-earmark-break-fill"></i></button>
      </li>
      <li class="nav-item" data-bs-toggle="tooltip" data-bs-placement="right" title="Tables">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pills-tables" type="button"><i class="bi bi-file-earmark-spreadsheet-fill"></i></button>
      </li>
      <li class="nav-item" data-bs-toggle="tooltip" data-bs-placement="right" title="Authentication">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pills-authentication" type="button"><i class="bi bi-lock-fill"></i></button>
      </li>
      <li class="nav-item" data-bs-toggle="tooltip" data-bs-placement="right" title="Icons">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pills-icons" type="button"><i class="bi bi-cloud-arrow-down-fill"></i></button>
      </li>
      <li class="nav-item" data-bs-toggle="tooltip" data-bs-placement="right" title="Content">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pills-content" type="button"><i class="bi bi-cone-striped"></i></button>
      </li>
      <li class="nav-item" data-bs-toggle="tooltip" data-bs-placement="right" title="Charts">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pills-charts" type="button"><i class="bi bi-pie-chart-fill"></i></button>
      </li>
      <li class="nav-item" data-bs-toggle="tooltip" data-bs-placement="right" title="Maps">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pills-maps" type="button"><i class="bi bi-pin-map-fill"></i></button>
      </li>
      <li class="nav-item" data-bs-toggle="tooltip" data-bs-placement="right" title="Pages">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pills-pages" type="button"><i class="bi bi-award-fill"></i></button>
      </li> --}}
    </ul>
  </div>
  <div class="textmenu">
    <div class="brand-logo">
      <img src="{{ asset('assets/images/brand-logo-2.png') }}" width="140" alt=""/>
    </div>
    <div class="tab-content">
      <div class="tab-pane fade show active" id="pills-dashboards">
        <div class="list-group list-group-flush">
          <div class="list-group-item">
            <div class="d-flex w-100 justify-content-between">
              <h5 class="mb-0">Peoples Weighthub</h5>
            </div>
            <small class="mb-0">“Connect. Weigh. Control.”</small>
          </div>
          {{-- <a href="#" class="list-group-item"><i class="bi bi-cart-plus"></i> e-Commerce</a>
          <a href="#" class="list-group-item"><i class="bi bi-wallet"></i> Sales</a>
          <a href="#" class="list-group-item"><i class="bi bi-bar-chart-line"></i> Analytics</a>
          <a href="#" class="list-group-item"><i class="bi bi-archive"></i> Project Management</a> --}}
          <a href="{{ route('dashboard') }}" class="list-group-item"><i class="bi bi-cast"></i> Scale Dashboard</a>
          <a href="{{ route('dashboard-table') }}" class="list-group-item"><i class="bi bi-table"></i> Weight Table</a>
        </div>
      </div>
      {{-- Other tab panes can be added here as needed --}}
    </div>
  </div>
</aside>
