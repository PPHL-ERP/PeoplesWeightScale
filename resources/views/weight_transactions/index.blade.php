@extends('layouts.dashboard')
@section('title','Weight Transactions')

@section('content')
<main class="page-content">
  <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Tables</div>
    <div class="ps-3">
      <ol class="breadcrumb mb-0 p-0">
        <li class="breadcrumb-item"><a href="#"><i class="bx bx-home-alt"></i></a></li>
        <li class="breadcrumb-item active">Weight Transactions</li>
      </ol>
    </div>
  </div>

  <h6 class="mb-0 text-uppercase">Scale Weight INFO</h6>
  <hr/>

  {{-- Filters --}}
  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-2">
        <div class="col-md-3">
          <label class="form-label">Global Search</label>
          <input id="f_search" type="text" class="form-control" placeholder="Txn/Vehicle/Customer/Material/Sector/User">
        </div>
        <div class="col-md-2">
          <label class="form-label">From</label>
          <input id="f_from" type="date" class="form-control">
        </div>
        <div class="col-md-2">
          <label class="form-label">To</label>
          <input id="f_to" type="date" class="form-control">
        </div>
        <div class="col-md-2">
          <label class="form-label">Weight Type</label>
          <select id="f_weight_type" class="form-select">
            <option value="">All</option>
            <option>Sales</option>
            <option>Purchase</option>
            <option>Weighing Service</option>
          </select>
        </div>
        <div class="col-md-1">
          <label class="form-label">Transfer</label>
          <select id="f_transfer_type" class="form-select">
            <option value="">All</option>
            <option>In</option>
            <option>Out</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Status</label>
          <select id="f_status" class="form-select">
            <option value="">All</option>
            <option>Active</option>
            <option>completed</option>
            <option>cancelled</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Vehicle No</label>
          <input id="f_vehicle_no" type="text" class="form-control" placeholder="e.g. DHA-23-4456">
        </div>

        <div class="col-md-3 d-flex align-items-end gap-2">
          <button id="btnApply" class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i>Apply</button>
          <button id="btnReset" class="btn btn-outline-secondary" title="Reset filters">
            <i class="bi bi-arrow-counterclockwise"></i>
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- Table --}}
  <div class="card">
    <div class="card-body">
      <table id="weights-table" class="table table-striped table-bordered table-sm w-100">
        <thead>
        <tr>
          <th>#</th>
          <th>Print</th>
          <th class="photos-col">Photos</th> {{-- new: after Print --}}
          <th>Challan No</th>
          <th>W-Type</th>
          <th>Transfer</th>
          <th>V Type</th>
          <th>Vehicle No</th>
          <th>Material</th>
          <th>Product</th>
          <th>Gross</th>
          <th>Tare</th>
          <th>Net</th>
          <th>Vol</th>
          <th>Price</th>
          <th>Disc</th>
          <th>Amount</th>
          <th>Customer</th>
          <th>Vendor</th>
          <th>Sector</th>
          <th>User</th>
          <th>Status</th>
          <th>Date</th>
        </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</main>

{{-- Photo Modal --}}
{{-- Photo Modal (zoomable) --}}
<div class="modal fade" id="photoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-fullscreen-md-down">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Transaction Photos</h5>
        <div class="d-flex align-items-center gap-1">
          <button type="button" class="btn btn-sm btn-outline-secondary" id="zoomOutBtn" title="Zoom out (−)">−</button>
          <button type="button" class="btn btn-sm btn-outline-secondary" id="zoomInBtn"  title="Zoom in (+)">+</button>
          <button type="button" class="btn btn-sm btn-outline-secondary" id="zoomResetBtn" title="Reset">Reset</button>
          <button type="button" class="btn btn-sm btn-outline-secondary" id="fullToggleBtn" title="Fullscreen">⛶</button>
          <button type="button" class="btn-close ms-1" data-bs-dismiss="modal"></button>
        </div>
      </div>
      <div class="modal-body p-0">
        <div id="photoCarousel" class="carousel slide" data-bs-ride="false">
          <div class="carousel-inner" id="photoCarouselInner"></div>
          <button class="carousel-control-prev" type="button" data-bs-target="#photoCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
            <span class="visually-hidden">Prev</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#photoCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>
      </div>
      <div class="modal-footer py-2">
        <small class="text-muted">Tip: mouse wheel or +/− to zoom, drag to move, double-click to toggle zoom.</small>
      </div>
    </div>
  </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<style>
  /* table compact */
  #weights-table { font-size: 12px; }
  #weights-table.dataTable thead th,
  #weights-table.dataTable tbody td {
    padding: 4px 6px !important;
    white-space: nowrap;
    box-sizing: border-box;
    vertical-align: middle;
  }
  .badge-upper { text-transform: uppercase }
  .num { text-align: right }
  .btn-xxs { padding: .15rem .35rem; font-size: 11px; line-height: 1.2; }

  /* photos column visuals */
  th.photos-col, td.photos-col { width: 120px !important; }
  .thumb-wrap{ display:flex; align-items:center; gap:4px; }
  .thumb-xxs{
    width:42px; height:32px; object-fit:cover;
    border-radius:4px; border:1px solid #e5e7eb;
  }
  .thumb-box{ position:relative; display:inline-block; }
  .thumb-tag{
    position:absolute; bottom:2px; left:2px;
    font-size:10px; padding:1px 4px; line-height:1;
    background:rgba(0,0,0,.65); color:#fff; border-radius:3px;
  }
  .thumb-more{
    font-size:10px; padding:1px 6px; border-radius:10px;
    background:#eef2ff; color:#3730a3; margin-left:4px;
  }

  /* wrapper */
  div.dataTables_wrapper { width: 100%; }

  /* zoom viewer */
  .zoom-container{
    width:100%; height: 78vh;      /* big viewing area */
    overflow:hidden; background:#000;
    display:flex; align-items:center; justify-content:center;
    user-select:none; touch-action:none; /* we implement our own */
  }
  .zoom-img{
    max-width: none; max-height: none;   /* allow oversize */
    will-change: transform;
    transition: transform 0.05s linear;  /* slight smoothing */
  }
  .dragging { cursor: grabbing !important; }
  .zoom-container:not(.dragging){ cursor: grab; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
  // ---------- helpers ----------
  function fmt2(n){return Number(n||0).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})}
  function netKG(g,t,real){const R=Number(real||0);return (R&&R>0)?R.toFixed(2):(Number(g||0)-Number(t||0)).toFixed(2)}
  function fmtDate(v){const d=new Date(v);if(isNaN(d))return v||'';return [String(d.getDate()).padStart(2,'0'),String(d.getMonth()+1).padStart(2,'0'),d.getFullYear()].join('-')}
  function demoPhotos(){return['https://picsum.photos/id/1011/1200/700','https://picsum.photos/id/1015/1200/700','https://picsum.photos/id/1025/1200/700']}

  // pick gross/tare and count rest
  function pickGrossTareAll(photos){
    const out = {gross:null, tare:null, rest:[]};
    if (!Array.isArray(photos)) return out;
    for (const p of photos){
      const m = String(p.mode||'').toLowerCase();
      if (!out.gross && m==='gross') { out.gross = p; continue; }
      if (!out.tare  && m==='tare')  { out.tare  = p; continue; }
      out.rest.push(p);
    }
    return out;
  }

  const a4RouteTpl  = "{{ route('print.invoice', ':id') }}";
  const posRouteTpl = "{{ route('print.pos', ':id') }}";

  // ---------- datatable ----------
  $(function(){
    const table = $('#weights-table').DataTable({
      processing:true,
      pageLength:50,
      lengthMenu:[50,100,150,200],
      scrollX:true,
      scrollCollapse:true,
      autoWidth:true,
      responsive:false,
      fixedHeader:false,
      // NOTE: Date is now column index 22 (0-based)
      order:[[22,'desc']], // newest first
      ajax:{
        url: "{{ route('weight_transactions.datatable') }}",
        data: function(d){
          d.search_text   = $('#f_search').val();
          d.from_date     = $('#f_from').val();
          d.to_date       = $('#f_to').val();
          d.weight_type   = $('#f_weight_type').val();
          d.transfer_type = $('#f_transfer_type').val();
          d.status        = $('#f_status').val();
          d.vehicle_no    = $('#f_vehicle_no').val();
        }
      },
      columnDefs: [
        { targets: 2, className: 'photos-col' }, // Photos col
      ],
      columns:[
        {data:'id'},
        {data:null, orderable:false, searchable:false, render:r=>{
          const a4Url  = a4RouteTpl.replace(':id', r.id);
          const posUrl = posRouteTpl.replace(':id', r.id);
          return `
            <div class="btn-group" role="group">
              <a href="${a4Url}" target="_blank" class="btn btn-success btn-xxs" title="A4"><i class="bi bi-file-earmark-pdf"></i></a>
              <a href="${posUrl}" target="_blank" class="btn btn-primary btn-xxs" title="POS"><i class="bi bi-printer"></i></a>
            </div>`;
        }},
        // Photos (Gross + Tare + +N)
        {data:null, orderable:false, searchable:false, render:r=>{
          const photos = Array.isArray(r.photos) ? r.photos : [];
          const {gross, tare, rest} = pickGrossTareAll(photos);
          const hasAny = photos.length > 0;
          const payload = JSON.stringify(photos).replace(/"/g,'&quot;');

          const g = gross ? `
            <span class="thumb-box" title="Gross">
              <img src="${gross.url}" alt="gross" class="thumb-xxs" loading="lazy">
              <span class="thumb-tag">G</span>
            </span>` : '';

          const t = tare ? `
            <span class="thumb-box" title="Tare">
              <img src="${tare.url}" alt="tare" class="thumb-xxs" loading="lazy">
              <span class="thumb-tag">T</span>
            </span>` : '';

          const more = rest.length>0 ? `<span class="thumb-more">+${rest.length}</span>` : '';

          return `
            <div class="d-flex align-items-center">
              <div class="thumb-wrap open-photos" data-photos="${payload}" style="cursor:pointer">
                ${g}${t}${more}
              </div>
              <button class="btn btn-outline-${hasAny?'primary':'secondary'} btn-xxs ms-1 view-photos"
                data-photos="${payload}" title="${hasAny?'View photos':'No photos'}">
                <i class="bi bi-images"></i>
              </button>
            </div>`;
        }},
        {data:'transaction_id'},
        {data:'weight_type'},
        {data:'transfer_type'},
        {data:'vehicle_type', defaultContent:'N/A'},
        {data:'vehicle_no'},
        {data:'material', defaultContent:'N/A'},
        {data:'productType', defaultContent:'N/A'},
        {data:'gross_weight', className:'num', render:v=>fmt2(v)},
        {data:'tare_weight',  className:'num', render:v=>fmt2(v)},
        {data:null,           className:'num', render:r=>fmt2(netKG(r.gross_weight,r.tare_weight,r.real_net))},
        {data:'volume',  className:'num', render:v=>fmt2(v)},
        {data:'price',   className:'num', render:v=>fmt2(v)},
        {data:'discount',className:'num', render:v=>fmt2(v)},
        {data:'amount',  className:'num', render:v=>fmt2(v)},
        {data:'customer_name', defaultContent:'N/A'},
        {data:'vendor_name',   defaultContent:'N/A'},
        {data:'sector_name',   defaultContent:'N/A'},
        {data:'username',      defaultContent:'N/A'},
        {data:'status', render:v=>{
          const s=String(v||'').toLowerCase();
          const cls = s==='completed'?'bg-success':(s==='cancelled'?'bg-danger':(s==='active'?'bg-info':'bg-secondary'));
          return `<span class="badge ${cls} badge-upper">${v||'N/A'}</span>`;
        }},
        {data:'created_at', render:v=>fmtDate(v)}
      ],
      initComplete: function(){ this.api().columns.adjust(); },
      drawCallback: function(){ this.api().columns.adjust(); }
    });

    // keep aligned on resize
    $(window).on('resize', function(){ table.columns.adjust(); });

    // Apply / Reset filters
    $('#btnApply').on('click', ()=> table.ajax.reload());
    $('#btnReset').on('click', ()=>{
      $('#f_search,#f_vehicle_no').val('');
      $('#f_from,#f_to').val('');
      $('#f_weight_type,#f_transfer_type,#f_status').val('');
      table.ajax.reload();
    });

    // Open modal from either the thumbs area or the button
    $(document).on('click','.view-photos, .open-photos', function(){
      const payload = $(this).attr('data-photos') || $(this).closest('.open-photos').attr('data-photos');
      let arr; try { arr = JSON.parse(payload || '[]'); } catch(e){ arr=[]; }
      if (!arr.length) arr = demoPhotos().map(u=>({url:u,mode:''}));

      const inner = $('#photoCarouselInner').empty();
      arr.forEach((p,i)=> inner.append(
        `<div class="carousel-item ${i===0?'active':''}">
           <img src="${p.url}" class="d-block w-100" alt="photo ${i+1}">
           ${p.mode?`<div class="carousel-caption d-none d-md-block">
              <span class="badge bg-dark">${String(p.mode).toUpperCase()}</span>
            </div>`:''}
         </div>`
      ));
      new bootstrap.Modal(document.getElementById('photoModal')).show();
    });
  });
</script>

<script>
  // --- Zoom state ---
  let Z = { scale:1, x:0, y:0, container:null, img:null };

  function applyZoom(){
    if (!Z.img) return;
    Z.img.style.transform = `translate(${Z.x}px, ${Z.y}px) scale(${Z.scale})`;
  }
  function resetZoom(toFit=true){
    Z.scale = toFit ? 1 : Z.scale;
    Z.x = 0; Z.y = 0; applyZoom();
  }
  function setActiveSlideZoom(slideEl){
    Z.container = slideEl.querySelector('.zoom-container');
    Z.img = slideEl.querySelector('.zoom-img');
    resetZoom(true);
  }
  function bindZoomEvents(container){
    let isDown=false, startX=0, startY=0, startTX=0, startTY=0;
    container.addEventListener('mousedown', (e)=>{
      isDown=true; container.classList.add('dragging');
      startX = e.clientX; startY = e.clientY;
      startTX= Z.x; startTY= Z.y;
    });
    window.addEventListener('mouseup', ()=>{ isDown=false; container.classList.remove('dragging'); });
    window.addEventListener('mousemove', (e)=>{
      if(!isDown) return;
      Z.x = startTX + (e.clientX - startX);
      Z.y = startTY + (e.clientY - startY);
      applyZoom();
    });
    container.addEventListener('wheel', (e)=>{
      e.preventDefault();
      const delta = -Math.sign(e.deltaY) * 0.15;
      const prev = Z.scale;
      Z.scale = Math.min(6, Math.max(1, Z.scale + delta));
      const rect = container.getBoundingClientRect();
      const cx = e.clientX - rect.left - rect.width/2;
      const cy = e.clientY - rect.top  - rect.height/2;
      Z.x = (Z.x - cx) * (Z.scale/prev) + cx;
      Z.y = (Z.y - cy) * (Z.scale/prev) + cy;
      applyZoom();
    }, {passive:false});
    container.addEventListener('dblclick', ()=>{
      Z.scale = (Z.scale>1.2) ? 1 : 2.5;
      Z.x=0; Z.y=0; applyZoom();
    });
  }

  function buildCarouselSlides(list){
    const $inner = $('#photoCarouselInner').empty();
    list.forEach((p,i)=> $inner.append(
      `<div class="carousel-item ${i===0?'active':''}">
         <div class="zoom-container">
           <img src="${p.url}" class="zoom-img" alt="photo ${i+1}">
         </div>
         ${p.mode?`<div class="carousel-caption d-none d-md-block">
            <span class="badge bg-dark">${String(p.mode).toUpperCase()}</span>
          </div>`:''}
       </div>`
    ));
    document.querySelectorAll('#photoCarouselInner .zoom-container')
      .forEach(bindZoomEvents);

    const active = document.querySelector('#photoCarouselInner .carousel-item.active');
    if (active) setActiveSlideZoom(active);

    // (Re)attach a single listener to update zoom target on slide change
    const carEl = document.getElementById('photoCarousel');
    // Ensure we have a carousel instance (and don't create multiples)
    let car = bootstrap.Carousel.getInstance(carEl);
    if (!car) car = new bootstrap.Carousel(carEl, { interval:false, ride:false, wrap:true });
    carEl.addEventListener('slid.bs.carousel', ()=>{
      const s = document.querySelector('#photoCarouselInner .carousel-item.active');
      if (s) setActiveSlideZoom(s);
    }, {once:true}); // once to avoid stacking listeners
  }

  // ======= SINGLE modal instance (IMPORTANT) =======
  const photoModalEl = document.getElementById('photoModal');
  const photoModal   = bootstrap.Modal.getOrCreateInstance(photoModalEl, {
    backdrop: true, keyboard: true, focus: true
  });

  // Cleanup on close so backdrop/body classes reset properly
  photoModalEl.addEventListener('hidden.bs.modal', ()=>{
    // Remove fullscreen if toggled
    document.querySelector('#photoModal .modal-dialog')
      .classList.remove('modal-fullscreen');

    // Dispose carousel to remove handlers and internal state
    const carEl = document.getElementById('photoCarousel');
    const car = bootstrap.Carousel.getInstance(carEl);
    if (car) car.dispose();

    // Clear slides & reset zoom state
    $('#photoCarouselInner').empty();
    Z = { scale:1, x:0, y:0, container:null, img:null };

    // Extra safety: if something left a stray backdrop, clean it
    $('.modal-backdrop').remove();
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
  });

  // Toolbar buttons
  document.addEventListener('click', (e)=>{
    if (e.target.id==='zoomInBtn'){  Z.scale = Math.min(6, Z.scale+0.25); applyZoom(); }
    if (e.target.id==='zoomOutBtn'){ Z.scale = Math.max(1, Z.scale-0.25); applyZoom(); }
    if (e.target.id==='zoomResetBtn'){ resetZoom(true); }
    if (e.target.id==='fullToggleBtn'){
      document.querySelector('#photoModal .modal-dialog')
        .classList.toggle('modal-fullscreen');
    }
  });

  // ======= The ONLY opener handler (remove any duplicates) =======
  $(document).on('click','.view-photos, .open-photos, .thumb-xxs', function(e){
    e.preventDefault();
    const payload = $(this).attr('data-photos') || $(this).closest('[data-photos]').attr('data-photos');
    let arr; try { arr = JSON.parse(payload || '[]'); } catch(_) { arr = []; }
    if (!arr.length) arr = [{url:'https://picsum.photos/id/1011/1600/900'}];

    buildCarouselSlides(arr);
    photoModal.show(); // use the single instance (don’t new Modal() repeatedly)
  });
</script>


@endpush
