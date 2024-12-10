@extends('layouts.master')

@section('title')
    Laporan Penjualan {{ tanggal_indonesia($tanggalAwal, false) }} s/d {{ tanggal_indonesia($tanggalAkhir, false) }}
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('/AdminLTE-2/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
@endpush

@section('breadcrumb')
    @parent
    <li class="active">Laporan Penjualan</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <button onclick="updatePeriode()" class="btn btn-info btn-xs btn-flat"><i class="fa fa-calendar"></i> Ubah Periode</button>
                <form action="{{ route('laporan.export_pdf', [$tanggalAwal, $tanggalAkhir]) }}" method="POST" target="_blank" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-success btn-xs btn-flat">
                        <i class="fa fa-file-pdf-o"></i> Export PDF
                    </button>
                </form>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-stiped table-bordered">
                    <thead>
                        <th width="5%">No</th>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Total Item</th>
                        <th>Total Harga</th>
                        <th>Status Member</th>
                        <th>Poin Member</th>
                        <th>Kasir</th>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@includeIf('laporan.form')
@endsection

@push('scripts')
<script src="{{ asset('/AdminLTE-2/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
<script>
    let table;

    $(function () {
        table = $('.table').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('laporan.data', [$tanggalAwal, $tanggalAkhir]) }}',
                data: function (d) {
                    d.nama = $('#nama').val(); // Kirim nilai nama dari input filter
                }
            },
            columns: [
                {data: 'DT_RowIndex', searchable: false, sortable: false},
                {data: 'tanggal'},
                {data: 'nama'},
                {data: 'total_item'},
                {data: 'total_harga'},
                {data: 'status_member'},
                {data: 'poin_member'},
                {data: 'kasir'},
            ],
            dom: 'Brt',
            bSort: false,
            bPaginate: false,
        });

        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
    });

    function updatePeriode() {
        $('#modal-form').modal('show');
    }

    function filterData() {
        table.ajax.reload();
    }
</script>

@endpush