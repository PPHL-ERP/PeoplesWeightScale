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

  {{-- Filters (6 types + vehicle) --}}
  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-2">
        <div class="col-md-3">
          <label class="form-label">Global Search</label>
          <input id="f_search" type="text" class="form-control"
                 placeholder="Txn/Vehicle/Customer/Vendor/Material/Sector/User">
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
      <div class="table-responsive">
        <table id="weights-table" class="table table-striped table-bordered w-100">
          <thead>
          <tr>
            <th>#</th>
            <th>Challan No</th>
            <th>W-Type</th>
            <th>Transfer</th>
            <th>Vehicle Type</th>
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
            <th>Images</th>
            <th>Print</th> {{-- NEW --}}
          </tr>
          </thead>
          <tbody></tbody>
          <tfoot>
          <tr>
            <th>#</th>
            <th>Challan No</th>
            <th>W-Type</th>
            <th>Transfer</th>
            <th>Vehicle Type</th>
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
            <th>Images</th>
            <th>Print</th> {{-- NEW --}}
          </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</main>

{{-- Demo Photo Modal --}}
<div class="modal fade" id="photoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Transaction Photos (Demo)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="photoCarousel" class="carousel slide" data-bs-ride="carousel">
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
      <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
    </div>
  </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<style>
  .badge-upper{text-transform:uppercase}
  .num{text-align:right}
  .thumb-btn{white-space:nowrap}
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
  // ---- helpers ----
  function fmt2(n){return Number(n||0).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})}
  function netKG(g,t,real){const R=Number(real||0);return (R&&R>0)?R.toFixed(2):(Number(g||0)-Number(t||0)).toFixed(2)}
  function fmtDate(v){const d=new Date(v);if(isNaN(d))return v||'';return [String(d.getDate()).padStart(2,'0'),String(d.getMonth()+1).padStart(2,'0'),d.getFullYear()].join('-')}
  function demoPhotos(){return['https://picsum.photos/id/1011/1200/700','https://picsum.photos/id/1015/1200/700','https://picsum.photos/id/1025/1200/700']}

  // Buildable URLs with placeholder :id (server renders the template, we replace client-side)
  const a4RouteTpl  = "{{ route('print.invoice', ':id') }}";
  const posRouteTpl = "{{ route('print.pos', ':id') }}";

  $(function(){
    const table = $('#weights-table').DataTable({
      processing:true,
      pageLength:25,
      order:[[20,'desc']], // Date column index (0-based)
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
      columns:[
        {data:null, orderable:false, searchable:false, render:(d,t,r,m)=>m.row+1},
        {data:'transaction_id'},
        {data:'weight_type'},
        {data:'transfer_type'},
        {data:'vehicle_type'},
        {data:'vehicle_no'},
        {data:'material'},
        {data:'productType'},
        {data:'gross_weight', className:'num', render:v=>fmt2(v)},
        {data:'tare_weight',  className:'num', render:v=>fmt2(v)},
        {data:null,           className:'num', render:r=>fmt2(netKG(r.gross_weight,r.tare_weight,r.real_net))},
        {data:'volume',  className:'num', render:v=>fmt2(v)},
        {data:'price',   className:'num', render:v=>fmt2(v)},
        {data:'discount',className:'num', render:v=>fmt2(v)},
        {data:'amount',  className:'num', render:v=>fmt2(v)},
        {data:'customer_name', defaultContent:'N/A'},
        {data:'vendor_name',   defaultContent:'N/A'},

        // Use your sector field here (fix duplicated customer_name)
        {data:'sector_name',   defaultContent:'N/A'},

        {data:'username',      defaultContent:'N/A'},
        {data:'status', render:v=>`<span class="badge ${v==='completed'?'bg-success':(v==='cancelled'?'bg-danger':'bg-secondary')} badge-upper">${v||'N/A'}</span>`},
        {data:'created_at', render:v=>fmtDate(v)},

        // Images column
        {data:null, orderable:false, searchable:false,
          render:r=>`<button class="btn btn-sm btn-outline-primary thumb-btn view-photos" data-id="${r.id}">
                       <i class="bi bi-images me-1"></i> View
                     </button>`},

        // PRINT column (A4 + POS)
        {data:null, orderable:false, searchable:false,
          render:r=>{
            const a4Url  = a4RouteTpl.replace(':id',  r.id);
            const posUrl = posRouteTpl.replace(':id', r.id);
            return `
              <div class="btn-group" role="group">
                <a href="${a4Url}" target="_blank" class="btn btn-sm btn-success" title="A4 PDF">
                  <i class="bi bi-file-earmark-pdf"></i> A4 PDF
                </a>
                <a href="${posUrl}" target="_blank" class="btn btn-sm btn-primary" title="POS Print">
                  <i class="bi bi-printer"></i> POS
                </a>
              </div>`;
          }
        }
      ]
    });

    // Apply / Reset filters
    $('#btnApply').on('click', ()=> table.ajax.reload());
    $('#btnReset').on('click', ()=>{
      $('#f_search,#f_vehicle_no').val('');
      $('#f_from,#f_to').val('');
      $('#f_weight_type,#f_transfer_type,#f_status').val('');
      table.ajax.reload();
    });

    // Demo photo modal
    $(document).on('click','.view-photos', function(){
      const inner = $('#photoCarouselInner').empty();
      demoPhotos().forEach((src,i)=> inner.append(
        `<div class="carousel-item ${i===0?'active':''}">
           <img src="${src}" class="d-block w-100" alt="photo ${i+1}">
         </div>`));
      new bootstrap.Modal(document.getElementById('photoModal')).show();
    });

    // (Optional) POS popup small window instead of new tab:
    // $(document).on('click', 'a[title="POS Print"]', function(e){
    //   e.preventDefault();
    //   window.open($(this).attr('href'), 'poswin', 'width=380,height=600,menubar=0,toolbar=0,location=0,status=0');
    // });
  });
</script>
@endpush
