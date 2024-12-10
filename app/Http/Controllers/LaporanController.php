<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use Illuminate\Http\Request;
use PDF;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $tanggalAwal = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
        $tanggalAkhir = date('Y-m-d');

        if ($request->has('tanggal_awal') && $request->tanggal_awal != "" && $request->has('tanggal_akhir') && $request->tanggal_akhir) {
            $tanggalAwal = $request->tanggal_awal;
            $tanggalAkhir = $request->tanggal_akhir;
        }

        return view('laporan.index', compact('tanggalAwal', 'tanggalAkhir'));
    }

   public function data($awal, $akhir, Request $request)
{
    $no = 1;
    $data = [];
    $totalPenjualan = 0;
    $totalItem = 0;
    $totalHarga = 0;

    while (strtotime($awal) <= strtotime($akhir)) {
        $tanggal = $awal;
        $awal = date('Y-m-d', strtotime("+1 day", strtotime($awal)));

        $penjualanQuery = Penjualan::with(['member', 'user'])
            ->where('created_at', 'LIKE', "%$tanggal%");

        if ($request->has('nama') && $request->nama) {
            $penjualanQuery->whereHas('member', function ($query) use ($request) {
                $query->where('nama', 'LIKE', "%{$request->nama}%");
            });
        }

        $penjualan = $penjualanQuery->get();

        $dailyTotalItem = 0;
        $dailyTotalHarga = 0;

        foreach ($penjualan as $item) {
            $dailyTotalItem += $item->total_item;
            $dailyTotalHarga += $item->total_harga;
        }

        $totalItem += $dailyTotalItem;
        $totalHarga += $dailyTotalHarga;

        foreach ($penjualan as $item) {
            $data[] = [
                'DT_RowIndex' => $no++,
                'tanggal' => tanggal_indonesia($tanggal, false),
                'nama' => $item->member ? $item->member->nama : '-',
                'total_item' => format_uang($item->total_item),
                'total_harga' => format_uang($item->total_harga),
                'status_member' => $item->member ? $item->member->status : '-',
                'poin_member' => $item->member ? $item->member->poin : '-',
                'kasir' => $item->user->name,
            ];
        }
    }

    $data[] = [
        'DT_RowIndex' => '',
        'tanggal' => 'Total',
        'nama' => '',
        'total_item' => format_uang($totalItem),
        'total_harga' => format_uang($totalHarga),
        'status_member' => '',
        'poin_member' => '',
        'kasir' => '',
    ];

    return datatables()->of($data)->make(true);
}

   public function exportPDF(Request $request, $awal, $akhir)
{
    // Mengirimkan $request ke data
    $dataResponse = $this->data($awal, $akhir, $request);
    
    // Mengonversi hasil ke array
    $data = json_decode(json_encode($dataResponse->getData()->data), true);
    
    $pdf = PDF::loadView('laporan.pdf', compact('awal', 'akhir', 'data'));
    $pdf->setPaper('a4', 'landscape');

    return $pdf->stream('Laporan-' . date('Y-m-d-his') . '.pdf');
}

}