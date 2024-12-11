<?php

use App\Http\Controllers\{
    DashboardController,
    KategoriController,
    LaporanController,
    ProdukController,
    StokController,
    MemberController,
    KaryawanController,
    PengeluaranController,
    PembelianController,
    PembelianDetailController,
    PenjualanController,
    PenjualanDetailController,
    SettingController,
    UserController,
    LaporanPembelianController,
    LaporanPenjualanController,

};
use Illuminate\Support\Facades\Route;


Route::get('/', fn () => redirect() ->route('login'));

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('home');
    })->name('dashboard');
});





Route::group (['middleware' => 'auth'], function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/kategori/data', [KategoriController::class, 'data'])->name('kategori.data');
    Route::resource('/kategori', KategoriController::class);

    Route::get('/produk/data', [ProdukController::class, 'data'])->name('produk.data');
    Route::post('/produk/delete-selected', [ProdukController::class, 'deleteSelected'])->name('produk.delete_selected');
    Route::post('/produk/cetak-barcode', [ProdukController::class, 'cetakBarcode'])->name('produk.cetak_barcode');
    Route::resource('/produk', ProdukController::class);

    Route::get('/stok/data', [StokController::class, 'data'])->name('stok.data');
    Route::post('/stok/delete-selected', [StokController::class, 'deleteSelected'])->name('stok.delete_selected');
    Route::post('/stok/cetak-barcode', [StokController::class, 'cetakBarcode'])->name('stok.cetak_barcode');
    Route::resource('/stok', StokController::class);

    Route::get('/member/data', [MemberController::class, 'data'])->name('member.data');
    Route::post('/member/cetak-member', [MemberController::class, 'cetakMember'])->name('member.cetak_member');
    Route::resource('/member', MemberController::class);

    Route::get('/karyawan/data', [KaryawanController::class, 'data'])->name('karyawan.data');
    Route::resource('/karyawan', KaryawanController::class);
    
    Route::get('/pembelian/data', [PembelianController::class, 'data'])->name('pembelian.data');
    Route::get('/pembelian/{id}/create', [PembelianController::class, 'create'])->name('pembelian.create');
    Route::resource('/pembelian', PembelianController::class)
        ->except('create');

    Route::get('/pembelian_detail/{id}/data', [PembelianDetailController::class, 'data'])->name('pembelian_detail.data');
    Route::get('/pembelian_detail/loadform/{diskon}/{total}', [PembelianDetailController::class, 'loadForm'])->name('pembelian_detail.load_form');
    Route::resource('/pembelian_detail', PembelianDetailController::class)
        ->except('create', 'show', 'edit');

    Route::get('/penjualan/data', [PenjualanController::class, 'data'])->name('penjualan.data');
    Route::get('/penjualan', [PenjualanController::class, 'index'])->name('penjualan.index');
    Route::get('/penjualan/{id}', [PenjualanController::class, 'show'])->name('penjualan.show');
    Route::delete('/penjualan/{id}', [PenjualanController::class, 'destroy'])->name('penjualan.destroy');

    // Routes yang dipindah dari group level
    Route::get('/transaksi/baru', [PenjualanController::class, 'create'])->name('transaksi.baru');
    Route::post('/transaksi/simpan', [PenjualanController::class, 'store'])->name('transaksi.simpan');
    Route::get('/transaksi/selesai', [PenjualanController::class, 'selesai'])->name('transaksi.selesai');
    Route::get('/transaksi/nota-kecil', [PenjualanController::class, 'notaKecil'])->name('transaksi.nota_kecil');
    Route::get('/transaksi/nota-besar', [PenjualanController::class, 'notaBesar'])->name('transaksi.nota_besar');

    Route::get('/transaksi/{id}/data', [PenjualanDetailController::class, 'data'])->name('transaksi.data');
    Route::get('/transaksi/get-diskon-by-poin', [PenjualanDetailController::class, 'getDiskonByPoinMember'])->name('transaksi.get-diskon-by-poin');
    Route::get('/transaksi/loadform/{diskon}/{total}/{diterima}', [PenjualanDetailController::class, 'loadForm'])->name('transaksi.load_form');
    Route::resource('/transaksi', PenjualanDetailController::class)
        ->except('create', 'show', 'edit');

    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/data/{awal?}/{akhir?}/{status?}', [LaporanController::class, 'data'])->name('laporan.data');
    Route::post('/Laporan/pdf/{awal}/{akhir}',[LaporanController::class, 'exportPDF'])->name('laporan.export_pdf');

    Route::get('/user/data', [UserController::class, 'data'])->name('user.data');
    Route::resource('/user', UserController::class);
    

    Route::get('/profil', [UserController::class, 'profil'])->name('user.profil');
    Route::post('/profil', [UserController::class, 'updateProfil'])->name('user.update_profil');

    Route::get('/laporan-pembelian', [LaporanPembelianController::class, 'index'])->name('laporan_pembelian.index');
    Route::get('/laporan-pembelian/data/{awal}/{akhir}', [LaporanPembelianController::class, 'data'])->name('laporan_pembelian.data');
    Route::post('/laporan-pembelian/pdf/{awal}/{akhir}', [LaporanPembelianController::class, 'exportPDF'])->name('laporan_pembelian.export_pdf');

});