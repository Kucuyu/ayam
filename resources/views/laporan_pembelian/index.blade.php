@extends('layouts.master')

@section('title')
    Laporan Pembelian {{ tanggal_indonesia($tanggalAwal, false) }} s/d {{ tanggal_indonesia($tanggalAkhir, false) }}
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('/AdminLTE-2/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
@endpush

@section('breadcrumb')
    @parent
    <li class="active">Laporan Pembelian</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <button onclick="updatePeriode()" class="btn btn-info btn-xs btn-flat"><i class="fa fa-calendar"></i> Ubah Periode</button>
                <form action="{{ route('laporan_pembelian.export_pdf', [$tanggalAwal, $tanggalAkhir]) }}" method="POST" target="_blank" style="display: inline;">
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
                        <th>Karyawan</th>
                        <th>Total Item</th>
                        <th>Total Harga</th>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@includeIf('laporan_pembelian.form')
@endsection

@push('scripts')
<script src="{{ asset('/AdminLTE-2/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
<script>
    let table;

    $(function () {
        // Inisialisasi DataTables
        table = $('.table').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('laporan_pembelian.data', [$tanggalAwal, $tanggalAkhir]) }}',
            },
            columns: [
                {data: 'DT_RowIndex', searchable: false, sortable: false}, // Nomor urut
                {data: 'tanggal'}, // Kolom tanggal
                {data: 'karyawan'},
                {data: 'total_item'}, // Kolom total pembelian
                {data: 'total_harga'},
                
            ],
            dom: 'Brt',
            bSort: false,
            bPaginate: false,
        });

        // Inisialisasi Datepicker
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
    });

    // Fungsi untuk membuka modal filter periode
    function updatePeriode() {
        $('#modal-form').modal('show');
    }
</script>
@endpush
