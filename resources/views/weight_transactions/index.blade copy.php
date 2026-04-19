@extends('layouts.dashboard')
@section('title','Weight Transactions')

@section('content')
<main class="page-content">
  <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">Tables</div>
    <div class="ps-3">
      <ol class="breadcrumb mb-0 p-0">
        <li class="breadcrumb-item"><a href="#"><i class="bx bx-home-alt"></i></a></li>
        <li class="breadcrumb-item active">Fartilizer Plant Weight Scale</li>
      </ol>
    </div>
  </div>

  <h3 class="mb-0 text-bold">Fartilizer Plant Weight Scale</h3>
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
    <table id="weights-table" class="table table-striped table-bordered table-sm w-100 nowrap">
      <thead>
        <tr>
          <th>#ID</th>
          <th>Print</th>
          <th class="photos-col">Photos</th>
          <th>Date</th>

          <th>Customer / Vendor</th>
          <th>Challan No</th>
          <th>W-Type</th>
          <th>Vehicle</th>
          <th>Material / Product</th>

          <th>Gross</th>
          <th>Tare</th>
          <th>D QTY</th>
          <th>Net</th>

          <th>Sector</th>
          <th>User</th>
          <th>Status</th>
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
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

<style>
  /* compact table */
  #weights-table { font-size:12px; width:100% !important; }
  #weights-table thead th, #weights-table tbody td{
    padding:4px 6px !important;
    vertical-align:middle;
    white-space:normal !important;     /* allow wrapping */
    word-break:break-word;
    box-sizing:border-box;
  }

  /* numbers right, net bold */
  #weights-table .num{ text-align:right; }
  #weights-table .col-net{ font-weight:700; }

  /* photos column */
  th.photos-col, td.photos-col{ width:120px !important; }
  td.photos-col{ white-space:nowrap !important; }

  /* thumbnails */
  .thumb-wrap{ display:flex; align-items:center; gap:4px; }
  .thumb-xxs{ width:42px; height:32px; object-fit:cover; border-radius:4px; border:1px solid #e5e7eb; }
  .thumb-box{ position:relative; display:inline-block; }
  .thumb-tag{ position:absolute; bottom:2px; left:2px; font-size:10px; padding:1px 4px; line-height:1; background:rgba(0,0,0,.65); color:#fff; border-radius:3px; }
  .thumb-more{ font-size:10px; padding:1px 6px; border-radius:10px; background:#eef2ff; color:#3730a3; margin-left:4px; }

  /* give combined columns some min room (desktop) */
  @media (min-width: 992px){
    #weights-table th:nth-child(5), #weights-table td:nth-child(5){ min-width:160px; } /* Customer/Vendor */
    #weights-table th:nth-child(14), #weights-table td:nth-child(14){ min-width:140px; } /* Customer/Vendor */
    #weights-table th:nth-child(8), #weights-table td:nth-child(8){ min-width:150px; } /* Vehicle */
    #weights-table th:nth-child(9), #weights-table td:nth-child(9){ min-width:160px; } /* Material/Product */
  }

  /* zoom viewer (unchanged from yours) */
  .zoom-container{ width:100%; height:78vh; overflow:hidden; background:#000; display:flex; align-items:center; justify-content:center; user-select:none; touch-action:none; }
  .zoom-img{ max-width:none; max-height:none; will-change:transform; transition:transform .05s linear; }
  .dragging{ cursor:grabbing !important; }
  .zoom-container:not(.dragging){ cursor:grab; }
</style>


@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<script>
  // ---- helpers ----
  function fmt2(n){return Number(n||0).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})}
  function netKG(g,t,real){const R=Number(real||0);return (R&&R>0)?R.toFixed(2):(Number(g||0)-Number(t||0)).toFixed(2)}
  function fmtDate(v){const d=new Date(v);if(isNaN(d))return v||'';return [String(d.getDate()).padStart(2,'0'),String(d.getMonth()+1).padStart(2,'0'),d.getFullYear()].join('-')}
  function pickGrossTareAll(photos){
    const out={gross:null,tare:null,rest:[]}; if(!Array.isArray(photos)) return out;
    for(const p of photos){const m=String(p.mode||'').toLowerCase();
      if(!out.gross && m==='gross'){out.gross=p;continue;}
      if(!out.tare  && m==='tare'){out.tare=p;continue;}
      out.rest.push(p);}
    return out;
  }
  const a4RouteTpl  = "{{ route('print.invoice', ':id') }}";
  const posRouteTpl = "{{ route('print.pos', ':id') }}";

  // ---- DataTable ----
  $(function(){
    const table = $('#weights-table').DataTable({
      processing:true,
      serverSide:false,    // client-side for 200 rows; set true if you need
      deferRender:true,
      autoWidth:false,
      responsive:{ details:{ type:'column', target:'tr' } },
        order:[[3,'desc']],
        pageLength: 50,                                  // ⬅️ NEW: default rows per page
        lengthMenu: [[50,100],[50,100,'All']], // (optional) dropdown options
      responsive:{
        details: { type: 'column', target: 'tr' }  // collapse to child rows on small screens
      },
      // no scrollX -> avoids header/body misalignment
      order:[[3,'desc']],
      ajax:{
        url:"{{ route('weight_transactions.datatable') }}",
        data:d=>{
          d.search_text   = $('#f_search').val();
          d.from_date     = $('#f_from').val();
          d.to_date       = $('#f_to').val();
          d.weight_type   = $('#f_weight_type').val();
          d.transfer_type = $('#f_transfer_type').val();
          d.status        = $('#f_status').val();
          d.vehicle_no    = $('#f_vehicle_no').val();
        },
        dataSrc:'data'
      },
      columns:[
        {data:'id'}, // #
        {data:null, orderable:false, searchable:false, render:r=>{
          const a4Url=a4RouteTpl.replace(':id',r.id), posUrl=posRouteTpl.replace(':id',r.id);
          return `<div class="btn-group" role="group">
            <a href="${a4Url}" target="_blank" class="btn btn-success btn-xxs" title="A4"><i class="bi bi-file-earmark-pdf"></i></a>
            <a href="${posUrl}" target="_blank" class="btn btn-primary btn-xxs" title="POS"><i class="bi bi-printer"></i></a>
          </div>`;
        }},
        {data:null, orderable:false, searchable:false, className:'photos-col', render:r=>{
          const photos = Array.isArray(r.photos)?r.photos:[];
          const {gross,tare,rest}=pickGrossTareAll(photos);
          const payload = JSON.stringify(photos).replace(/"/g,'&quot;');
          const g = gross? `<span class="thumb-box" title="Gross"><img src="${gross.url}" class="thumb-xxs" loading="lazy"><span class="thumb-tag">G</span></span>`:'';
          const t = tare ? `<span class="thumb-box" title="Tare"><img src="${tare.url}"  class="thumb-xxs" loading="lazy"><span class="thumb-tag">T</span></span>`:'';
          const more = rest.length? `<span class="thumb-more">+${rest.length}</span>`:'';
          return `<div class="d-flex align-items-center">
            <div class="thumb-wrap open-photos" data-photos="${payload}" style="cursor:pointer">${g}${t}${more}</div>
            <button class="btn btn-outline-${photos.length?'primary':'secondary'} btn-xxs ms-1 view-photos" data-photos="${payload}" title="${photos.length?'View photos':'No photos'}">
              <i class="bi bi-images"></i>
            </button>
          </div>`;
        }},
        {data:'created_at', render:v=>fmtDate(v)}, // Date

        // Customer / Vendor
        {data:null, render:r=>`<div><div>${r.customer_name||'N/A'}</div><div class="text-muted small">${r.vendor_name||'—'}</div></div>`},

        {data:'transaction_id', defaultContent:'N/A'},          // Challan No
        {data:'weight_type',    defaultContent:'N/A'},          // W-Type

        // Vehicle (type + no)
        {data:null, render:r=>{
          const vt=r.vehicle_type||'N/A', vn=r.vehicle_no||'N/A';
          return `<div><span class="badge bg-secondary me-1">${vt}</span><strong>${vn}</strong></div>`;
        }},

        // Material / Product
        {data:null, render:r=>`<div><div>${r.material||'N/A'}</div><div class="text-muted small">${r.productType||'—'}</div></div>`},

        {data:'gross_weight', className:'num', render:v=>fmt2(v)}, // Gross
        {data:'tare_weight',  className:'num', render:v=>fmt2(v)}, // Tare
        {data:'deduction',    className:'num', render:v=>fmt2(v)}, // D QTY

        // Net (bold)
        {data:null, className:'num col-net', render:r=>fmt2(netKG(r.gross_weight,r.tare_weight,r.real_net))},

        {data:'sector_name',  defaultContent:'N/A'}, // Sector
        {data:'username',     defaultContent:'N/A'}, // User
        {data:'status', render:v=>{
          const s=String(v||'').toLowerCase();
          const cls = s==='completed'?'bg-success': s==='cancelled'?'bg-danger': s==='active'?'bg-info':'bg-secondary';
          return `<span class="badge ${cls} badge-upper">${v||'N/A'}</span>`;
        }}
      ],
      initComplete:function(){ this.api().columns.adjust(); },
      drawCallback:function(){ this.api().columns.adjust(); }
    });

    // filters
    $('#btnApply').on('click', ()=> table.ajax.reload());
    $('#btnReset').on('click', ()=>{
      $('#f_search,#f_vehicle_no').val('');
      $('#f_from,#f_to').val('');
      $('#f_weight_type,#f_transfer_type,#f_status').val('');
      table.ajax.reload();
    });
  });
</script>

<!-- Photo modal opener (zoom-enabled) — keep just this one -->
<script>
  let Z = { scale:1, x:0, y:0, container:null, img:null };
  function applyZoom(){ if(!Z.img) return; Z.img.style.transform=`translate(${Z.x}px, ${Z.y}px) scale(${Z.scale})`; }
  function resetZoom(){ Z.scale=1; Z.x=0; Z.y=0; applyZoom(); }
  function bindZoomEvents(c){
    let isDown=false,sx=0,sy=0,tx=0,ty=0;
    c.addEventListener('mousedown',e=>{isDown=true;c.classList.add('dragging');sx=e.clientX;sy=e.clientY;tx=Z.x;ty=Z.y;});
    window.addEventListener('mouseup',()=>{isDown=false;c.classList.remove('dragging');});
    window.addEventListener('mousemove',e=>{ if(!isDown) return; Z.x=tx+(e.clientX-sx); Z.y=ty+(e.clientY-sy); applyZoom(); });
    c.addEventListener('wheel',e=>{e.preventDefault(); const d=-Math.sign(e.deltaY)*0.15; Z.scale=Math.min(6,Math.max(1,Z.scale+d)); applyZoom(); },{passive:false});
    c.addEventListener('dblclick',()=>{ Z.scale = (Z.scale>1.2)?1:2.5; Z.x=0; Z.y=0; applyZoom(); });
  }
  function buildCarouselSlides(list){
    const $inner = $('#photoCarouselInner').empty();
    list.forEach((p,i)=>$inner.append(
      `<div class="carousel-item ${i===0?'active':''}">
        <div class="zoom-container"><img src="${p.url}" class="zoom-img" alt="photo ${i+1}"></div>
        ${p.mode?`<div class="carousel-caption d-none d-md-block"><span class="badge bg-dark">${String(p.mode).toUpperCase()}</span></div>`:''}
      </div>`
    ));
    document.querySelectorAll('#photoCarouselInner .zoom-container').forEach(bindZoomEvents);
    const s=document.querySelector('#photoCarouselInner .carousel-item.active');
    if(s){ Z.container=s.querySelector('.zoom-container'); Z.img=s.querySelector('.zoom-img'); resetZoom(); }
  }
  const photoModalEl=document.getElementById('photoModal');
  const photoModal=bootstrap.Modal.getOrCreateInstance(photoModalEl,{backdrop:true,keyboard:true,focus:true});
  $(document).on('click','.view-photos, .open-photos, .thumb-xxs',function(e){
    e.preventDefault();
    const payload=$(this).attr('data-photos')||$(this).closest('[data-photos]').attr('data-photos');
    let arr; try{arr=JSON.parse(payload||'[]')}catch{arr=[]}
    if(!arr.length) arr=[{url:'https://picsum.photos/id/1011/1600/900'}];
    buildCarouselSlides(arr); photoModal.show();
  });
  photoModalEl.addEventListener('hidden.bs.modal',()=>{
    $('#photoCarouselInner').empty(); Z={scale:1,x:0,y:0,container:null,img:null};
  });
  document.addEventListener('click',e=>{
    if (e.target.id==='zoomInBtn'){ Z.scale=Math.min(6,Z.scale+0.25); applyZoom(); }
    if (e.target.id==='zoomOutBtn'){ Z.scale=Math.max(1,Z.scale-0.25); applyZoom(); }
    if (e.target.id==='zoomResetBtn'){ resetZoom(); }
    if (e.target.id==='fullToggleBtn'){ document.querySelector('#photoModal .modal-dialog').classList.toggle('modal-fullscreen'); }
  });
</script>



@endpush
