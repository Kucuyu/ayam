@extends('layouts.master')

@section('title')
    Transaksi Pembelian
@endsection

@push('css')
<style>
    .tampil-bayar {
        font-size: 5em;
        text-align: center;
        height: 100px;
    }

    .tampil-terbilang {
        padding: 10px;
        background: #f0f0f0;
    }

    .table-pembelian tbody tr:last-child {
        display: none;
    }

    @media(max-width: 768px) {
        .tampil-bayar {
            font-size: 3em;
            height: 70px;
            padding-top: 5px;
        }
    }
</style>
@endpush

@section('breadcrumb')
    @parent
    <li class="active">Transaksi Pembelian</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <table>
                    <tr>
                        <td>Karyawan-</td>
                        <td>: {{ $karyawan->nama }}</td>
                    </tr>
                    <tr>
                        <td>Telepon</td>
                        <td>: {{ $karyawan->telepon }}</td>
                    </tr>
                    <tr>
                        <td>Alamat</td>
                        <td>: {{ $karyawan->alamat }}</td>
                    </tr>
                </table>
            </div>
            <div class="box-body">
                    
                <form class="form-stok">
                    @csrf
                    <div class="form-group row">
                        <label for="kode_stok" class="col-lg-2">Kode Stok</label>
                        <div class="col-lg-5">
                            <div class="input-group">
                                <input type="hidden" name="id_pembelian" id="id_pembelian" value="{{ $id_pembelian }}">
                                <input type="hidden" name="id_stok" id="id_stok">
                                <input type="text" class="form-control" name="kode_stok" id="kode_stok">
                                <span class="input-group-btn">
                                    <button onclick="tampilStok()" class="btn btn-info btn-flat" type="button"><i class="fa fa-arrow-right"></i></button>
                                </span>
                            </div>
                        </div>
                    </div>
                </form>

                <table class="table table-stiped table-bordered table-pembelian">
                    <thead>
                        <th width="5%">No</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Harga</th>
                        <th width="15%">Jumlah</th>
                        <th>Subtotal</th>
                        <th width="15%"><i class="fa fa-cog"></i></th>
                    </thead>
                </table>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="tampil-bayar bg-primary"></div>
                        <div class="tampil-terbilang"></div>
                    </div>
                    <div class="col-lg-4">
                        <form action="{{ route('pembelian.store') }}" class="form-pembelian" method="post">
                            @csrf
                            <input type="hidden" name="id_pembelian" value="{{ $id_pembelian }}">
                            <input type="hidden" name="total" id="total">
                            <input type="hidden" name="total_item" id="total_item">
                            <input type="hidden" name="bayar" id="bayar">

                            <div class="form-group row">
                                <label for="totalrp" class="col-lg-2 control-label">Total</label>
                                <div class="col-lg-8">
                                    <input type="text" id="totalrp" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="diskon" class="col-lg-2 control-label">Diskon</label>
                                <div class="col-lg-8">
                                    <input type="number" name="diskon" id="diskon" class="form-control" value="{{ $diskon }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="bayar" class="col-lg-2 control-label">Bayar</label>
                                <div class="col-lg-8">
                                    <input type="text" id="bayarrp" class="form-control">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="box-footer">
                <button type="submit" class="btn btn-primary btn-sm btn-flat pull-right btn-simpan"><i class="fa fa-floppy-o"></i> Simpan Transaksi</button>
            </div>
        </div>
    </div>
</div>

@includeIf('pembelian_detail.stok')
@endsection

@push('scripts')
<script>
    let table, table2;

    $(function () {
        $('body').addClass('sidebar-collapse');

        table = $('.table-pembelian').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('pembelian_detail.data', $id_pembelian) }}',
            },
            columns: [
                {data: 'DT_RowIndex', searchable: false, sortable: false},
                {data: 'kode_stok'},
                {data: 'nama_stok'},
                {data: 'harga_beli'},
                {data: 'jumlah'},
                {data: 'subtotal'},
                {data: 'aksi', searchable: false, sortable: false},
            ],
            dom: 'Brt',
            bSort: false,
            paginate: false
        })
        .on('draw.dt', function () {
            loadForm($('#diskon').val());
        });
        table2 = $('.table-stok').DataTable();

        $(document).on('input', '.quantity', function () {
            let id = $(this).data('id');
            let jumlah = parseInt($(this).val());

            if (jumlah < 1) {
                $(this).val(1);
                alert('Jumlah tidak boleh kurang dari 1');
                return;
            }
            if (jumlah > 10000) {
                $(this).val(10000);
                alert('Jumlah tidak boleh lebih dari 10000');
                return;
            }

            $.post(`{{ url('/pembelian_detail') }}/${id}`, {
                    '_token': $('[name=csrf-token]').attr('content'),
                    '_method': 'put',
                    'jumlah': jumlah
                })
                .done(response => {
                    $(this).on('mouseout', function () {
                        table.ajax.reload(() => loadForm($('#diskon').val()));
                    });
                })
                .fail(errors => {
                    alert('Tidak dapat menyimpan data');
                    return;
                });
        });

        $(document).on('input', '#diskon', function () {
            if ($(this).val() == "") {
                $(this).val(0).select();
            }

            loadForm($(this).val());
        });

        $('.btn-simpan').on('click', function () {
            $('.form-pembelian').submit();
        });
    });

    function tampilStok() {
        $('#modal-stok').modal('show');
    }

    function hideStok() {
        $('#modal-stok').modal('hide');
    }

    function pilihStok(id, kode) {
        $('#id_stok').val(id);
        $('#kode_stok').val(kode);
        hideStok();
        tambahStok();
    }

    function tambahStok() {
        $.post('{{ route('pembelian_detail.store') }}', $('.form-stok').serialize())
            .done(response => {
                $('#kode_stok').focus();
                table.ajax.reload(() => loadForm($('#diskon').val()));
            })
            .fail(errors => {
                alert('Tidak dapat menyimpan data');
                return;
            });
    }

    function deleteData(url) {
        if (confirm('Yakin ingin menghapus data terpilih?')) {
            $.post(url, {
                    '_token': $('[name=csrf-token]').attr('content'),
                    '_method': 'delete'
                })
                .done((response) => {
                    table.ajax.reload(() => loadForm($('#diskon').val()));
                })
                .fail((errors) => {
                    alert('Tidak dapat menghapus data');
                    return;
                });
        }
    }

    function loadForm(diskon = 0) {
        $('#total').val($('.total').text());
        $('#total_item').val($('.total_item').text());

        $.get(`{{ url('/pembelian_detail/loadform') }}/${diskon}/${$('.total').text()}`)
            .done(response => {
                $('#totalrp').val('Rp. '+ response.totalrp);
                $('#bayarrp').val('Rp. '+ response.bayarrp);
                $('#bayar').val(response.bayar);
                $('.tampil-bayar').text('Rp. '+ response.bayarrp);
                $('.tampil-terbilang').text(response.terbilang);
            })
            .fail(errors => {
                alert('Tidak dapat menampilkan data');
                return;
            })
    }
</script>
@endpush