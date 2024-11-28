<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use App\Models\Setting;
use Illuminate\Http\Request;

class PenjualanDetailController extends Controller
{
    public function index()
    {
        $produk = Produk::orderBy('nama_produk')->get();
        $member = Member::orderBy('nama')->get();
        $diskon = Setting::first()->diskon ?? 0;

        // Cek apakah ada transaksi yang sedang berjalan
        if ($id_penjualan = session('id_penjualan')) {
            $penjualan = Penjualan::find($id_penjualan);
            $memberSelected = $penjualan->member ?? new Member();

            return view('penjualan_detail.index', compact('produk', 'member', 'diskon', 'id_penjualan', 'penjualan', 'memberSelected'));
        } else {
            if (auth()->user()->level == 1) {
                return redirect()->route('transaksi.baru');
            } else {
                return redirect()->route('home');
            }
        }
    }

    public function data($id)
    {
        $detail = PenjualanDetail::with('produk')
            ->where('id_penjualan', $id)
            ->get();

        $data = array();
        $total = 0;
        $total_item = 0;

        foreach ($detail as $item) {
            $row = array();
            $row['kode_produk'] = '<span class="label label-success">'. $item->produk['kode_produk'] .'</span';
            $row['nama_produk'] = $item->produk['nama_produk'];
            $row['harga_jual']  = 'Rp. '. format_uang($item->harga_jual);
            $row['jumlah']      = '<input type="number" class="form-control input-sm quantity" data-id="'. $item->id_penjualan_detail .'" value="'. $item->jumlah .'">';
            $row['diskon']      = $item->diskon . '%';
            $row['subtotal']    = 'Rp. '. format_uang($item->subtotal);
            $row['aksi']        = '<div class="btn-group">
                                    <button onclick="deleteData(`'. route('transaksi.destroy', $item->id_penjualan_detail) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                                </div>';
            $data[] = $row;

            $total += $item->harga_jual * $item->jumlah - (($item->diskon * $item->jumlah) / 100 * $item->harga_jual);;
            $total_item += $item->jumlah;
        }
        $data[] = [
            'kode_produk' => '
                <div class="total hide">'. $total .'</div>
                <div class="total_item hide">'. $total_item .'</div>',
            'nama_produk' => '',
            'harga_jual'  => '',
            'jumlah'      => '',
            'diskon'      => '',
            'subtotal'    => '',
            'aksi'        => '',
        ];

        return datatables()
            ->of($data)
            ->addIndexColumn()
            ->rawColumns(['aksi', 'kode_produk', 'jumlah'])
            ->make(true);
    }

    public function store(Request $request)
{
    $produk = Produk::where('id_produk', $request->id_produk)->first();
    if (!$produk) {
        return response()->json('Data gagal disimpan', 400);
    }

    // Ambil diskon jika ada
    $diskon = $request->diskon ?? $produk->diskon; // Memastikan diskon diterapkan

    // Hitung subtotal dengan diskon
    $subtotal = $produk->harga_jual - ($diskon / 100 * $produk->harga_jual);

    $detail = new PenjualanDetail();
    $detail->id_penjualan = $request->id_penjualan;
    $detail->id_produk = $produk->id_produk;
    $detail->harga_jual = $produk->harga_jual;
    $detail->jumlah = 1; // Jika ingin jumlah dinamis, pastikan ini sesuai
    $detail->diskon = $diskon;
    $detail->subtotal = $subtotal;
    $detail->save();

    return response()->json('Data berhasil disimpan', 200);
}


  public function update(Request $request, $id)
{
    $detail = PenjualanDetail::find($id);
    
    // Perbarui jumlah dan diskon
    $detail->jumlah = $request->jumlah;
    
    // Hitung subtotal dengan diskon yang benar
    $detail->subtotal = $detail->harga_jual * $request->jumlah - (($detail->diskon * $request->jumlah) / 100 * $detail->harga_jual);
    
    $detail->update();
}


    public function destroy($id)
    {
        $detail = PenjualanDetail::find($id);
        $detail->delete();

        return response(null, 204);
    }

   public function loadForm($diskon = 0, $total = 0, $diterima = 0)
{
    $member = Member::find(request('id_member'));
    $memberDiskon = 0;

    if ($member) {
        if ($member->poin >= 6000) {
            $memberDiskon = 20; // Diskon Platinum
        } elseif ($member->poin >= 4000) {
            $memberDiskon = 10; // Diskon Gold
        } elseif ($member->poin >= 2000) {
            $memberDiskon = 5;  // Diskon Bronze
        }
    }

    // Menghitung total diskon berdasarkan diskon transaksi dan member
    $totalDiskon = $total - ($diskon / 100 * $total) - ($memberDiskon / 100 * $total);
    $kembali = ($diterima != 0) ? $diterima - $totalDiskon : 0;

    return response()->json([
        'totalrp' => format_uang($total),
        'bayar' => $totalDiskon,
        'bayarrp' => format_uang($totalDiskon),
        'kembalirp' => format_uang($kembali),
        'terbilang' => ucwords(terbilang($totalDiskon) . ' Rupiah'),
    ]);
}


}