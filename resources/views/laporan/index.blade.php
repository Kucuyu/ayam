@extends('layouts.master')

@section('title')
    Laporan Pendapatan {{ tanggal_indonesia($tanggalAwal, false) }} s/d {{ tanggal_indonesia($tanggalAkhir, false) }}
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('/AdminLTE-2/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
@endpush

@section('breadcrumb')
    @parent
    <li class="active">Laporan</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <button onclick="updatePeriode()" class="btn btn-info btn-xs btn-flat"><i class="fa fa-plus-circle"></i> Ubah Periode</button>
               <form action="{{ route('laporan.export_pdf', [$tanggalAwal, $tanggalAkhir]) }}" method="POST" target="_blank" style="display: inline;">
    @csrf
    <button type="submit" class="btn btn-success btn-xs btn-flat">
        <i class="fa fa-file-excel-o"></i> Export PDF
    </button>
</form
            </div>
            <div class="box-body table-responsive">
                <table class="table table-stiped table-bordered">
    <thead>
        <th width="5%">No</th>
        <th>Tanggal</th>
        <th>Penjualan</th>
        <th>Pembelian</th>
        <th>Pendapatan</th>
        <th>Poin Member</th> <!-- Tambahkan kolom poin -->
        <th>Status Member</th> <!-- Tambahkan kolom status -->
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
        // Inisialisasi DataTables
        table = $('.table').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('laporan.data', [$tanggalAwal, $tanggalAkhir]) }}',
                data: function (d) {
                    d.member_id = $('#member_id').val(); // Ambil ID Member dari dropdown filter
                },
            },
            columns: [
                {data: 'DT_RowIndex', searchable: false, sortable: false}, // Kolom nomor urut
                {data: 'tanggal'}, // Kolom tanggal
                {data: 'penjualan'}, // Kolom penjualan
                {data: 'pembelian'}, // Kolom pembelian
                {data: 'pendapatan'}, // Kolom pendapatan
                {data: 'poin'}, // Kolom poin member
                {data: 'status'} // Kolom status member
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

        // Event ketika memilih member
        $('#member_id').on('change', function () {
            toggleColumns();
            table.ajax.reload(); // Reload data tabel
        });

        // Fungsi untuk menyembunyikan/memunculkan kolom berdasarkan pilihan member
        function toggleColumns() {
            const memberId = $('#member_id').val();
            if (memberId) {
                // Jika member dipilih, sembunyikan kolom pembelian dan pendapatan
                table.columns(3).visible(false); // Kolom pembelian (indeks ke-3)
                table.columns(4).visible(false); // Kolom pendapatan (indeks ke-4)
                table.columns(5).visible(true); // Kolom poin member (indeks ke-5)
                table.columns(6).visible(true); // Kolom status member (indeks ke-6)
            } else {
                // Jika member tidak dipilih, sembunyikan kolom poin member dan status member
                table.columns(3).visible(true); // Kolom pembelian (indeks ke-3)
                table.columns(4).visible(true); // Kolom pendapatan (indeks ke-4)
                table.columns(5).visible(false); // Kolom poin member (indeks ke-5)
                table.columns(6).visible(false); // Kolom status member (indeks ke-6)
            }
        }

        // Panggil fungsi saat tabel selesai dimuat
        table.on('draw', function () {
            toggleColumns(); // Pastikan kolom tetap sesuai kondisi pilihan member
        });
    });

    // Fungsi untuk membuka modal filter periode dan member
    function updatePeriode() {
        $('#modal-form').modal('show');
    }
</script>




@endpush