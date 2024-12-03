<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\Member;
use Illuminate\Http\Request;
use PDF;

class LaporanController extends Controller
{
public function index(Request $request)
{
    $tanggalAwal = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
    $tanggalAkhir = date('Y-m-d');
    $memberId = $request->get('member_id', null);

    if ($request->has('tanggal_awal') && $request->tanggal_awal != "" && $request->has('tanggal_akhir') && $request->tanggal_akhir) {
        $tanggalAwal = $request->tanggal_awal;
        $tanggalAkhir = $request->tanggal_akhir;
    }

    // Ambil semua data members
    $members = Member::all();

    return view('laporan.index', compact('tanggalAwal', 'tanggalAkhir', 'memberId', 'members'));
}


public function getData($awal, $akhir, $memberId = null)
{
    $no = 1; // Nomor urut
    $data = array();
    $total_pendapatan = 0;

    while (strtotime($awal) <= strtotime($akhir)) {
        $tanggal = $awal;
        $awal = date('Y-m-d', strtotime("+1 day", strtotime($awal)));

        $penjualanQuery = Penjualan::where('created_at', 'LIKE', "%$tanggal%");
        $pembelianQuery = Pembelian::where('created_at', 'LIKE', "%$tanggal%");

        if ($memberId) {
            $penjualanQuery->where('id_member', $memberId);
        }

        $total_penjualan = $penjualanQuery->sum('bayar');
        $total_pembelian = $pembelianQuery->sum('bayar');

        $pendapatan = $total_penjualan - $total_pembelian;
        $total_pendapatan += $pendapatan;

        // Ambil informasi member jika ada filter
        $member = $memberId ? Member::find($memberId) : null;

        $row = [
            'DT_RowIndex' => $no++, // Tambahkan nomor urut
            'tanggal' => tanggal_indonesia($tanggal, false),
            'penjualan' => format_uang($total_penjualan),
            'pembelian' => format_uang($total_pembelian),
            'pendapatan' => format_uang($pendapatan),
            'poin' => $member ? $member->poin : '-', // Poin member atau '-'
            'status' => $member ? $member->status : '-', // Status member atau '-'
        ];
        $data[] = $row;
    }

    $data[] = [
        'DT_RowIndex' => '', // Kosongkan untuk total pendapatan
        'tanggal' => '',
        'penjualan' => '',
        'pembelian' => 'Total Pendapatan',
        'pendapatan' => format_uang($total_pendapatan),
        'poin' => '-', // Kosongkan poin di total
        'status' => '-', // Kosongkan status di total
    ];

    return $data;
}


public function data($awal, $akhir, Request $request)
{
    $memberId = $request->get('member_id', null); // Tambahkan filter member
    $data = $this->getData($awal, $akhir, $memberId);
    
    return datatables()->of($data)->make(true);
}

    public function exportPDF($awal, $akhir)
    {
        $data = $this->getData($awal, $akhir);
        $pdf  = PDF::loadView('laporan.pdf', compact('awal', 'akhir', 'data'));
        $pdf->setPaper('a4', 'potrait');
        
        return $pdf->stream('Laporan-pendapatan-'. date('Y-m-d-his') .'.pdf');
    }

    
}