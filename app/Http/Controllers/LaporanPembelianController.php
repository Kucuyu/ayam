<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use Illuminate\Http\Request;
use PDF;

class LaporanPembelianController extends Controller
{
  public function index(Request $request)
{
    $tanggalAwal = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
    $tanggalAkhir = date('Y-m-d');

    if ($request->has('tanggal_awal') && $request->tanggal_awal != "" && $request->has('tanggal_akhir') && $request->tanggal_akhir) {
        $tanggalAwal = $request->tanggal_awal;
        $tanggalAkhir = $request->tanggal_akhir;
    }

    return view('laporan_pembelian.index', compact('tanggalAwal', 'tanggalAkhir'));
}

  public function data($awal, $akhir)
{
    $no = 1;
    $data = [];
    $totalPembelian = 0;
    $totalItem = 0;
    $totalHarga = 0;

    while (strtotime($awal) <= strtotime($akhir)) {
        $tanggal = $awal;
        $awal = date('Y-m-d', strtotime("+1 day", strtotime($awal)));

        // Ambil pembelian berdasarkan tanggal
        $pembelian = Pembelian::with('karyawan')->where('created_at', 'LIKE', "%$tanggal%")->get();

        // Reset daily totals
        $dailyTotalItem = 0;
        $dailyTotalHarga = 0;
        $dailyTotalBayar = 0;

        foreach ($pembelian as $item) {
            $dailyTotalItem += $item->total_item;
            $dailyTotalHarga += $item->total_harga; // Tambahkan total harga per item
            $dailyTotalBayar += $item->bayar;
        }

        // Akumulasi total keseluruhan
        $totalItem += $dailyTotalItem;
        $totalHarga += $dailyTotalHarga;
        $totalPembelian += $dailyTotalBayar;

        $data[] = [
            'DT_RowIndex' => $no++,
            'tanggal' => tanggal_indonesia($tanggal, false),
            'karyawan' => $pembelian->first() ? $pembelian->first()->karyawan->nama : 'Tidak Diketahui',
            'total_item' => format_uang($dailyTotalItem),
            'total_harga' => format_uang($dailyTotalHarga), // Tampilkan total harga harian
            'pembelian' => format_uang($dailyTotalBayar),
        ];
    }

    // Tambahkan total pembelian di akhir laporan
    $data[] = [
        'DT_RowIndex' => '',
        'tanggal' => 'Total',
        'karyawan' => '',
        'total_item' => format_uang($totalItem), // Tampilkan total item keseluruhan
        'total_harga' => format_uang($totalHarga), // Tampilkan total harga keseluruhan
        'pembelian' => format_uang($totalPembelian),
    ];

    return datatables()->of($data)->make(true);
}

   public function exportPDF($awal, $akhir)
{
    $dataResponse = $this->data($awal, $akhir);
    $data = json_decode(json_encode($dataResponse->getData()->data), true); // Convert to array
    $pdf = PDF::loadView('laporan_pembelian.pdf', compact('awal', 'akhir', 'data'));
    $pdf->setPaper('a4', 'portrait');

    return $pdf->stream('Laporan-Pembelian-' . date('Y-m-d-his') . '.pdf');
}


}
