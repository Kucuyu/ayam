<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use Illuminate\Http\Request;

class PenjualanDetailController extends Controller
{
    public function index()
{
    $produk = Produk::orderBy('nama_produk')->get();
    $member = Member::orderBy('nama')->get();

    // Cek apakah ada transaksi yang sedang berjalan
    if ($id_penjualan = session('id_penjualan')) {
        $penjualan = Penjualan::find($id_penjualan);
        $memberSelected = $penjualan->member ?? new Member();

        // Hitung diskon berdasarkan poin member
        $diskon = 0; // Default diskon jika member tidak ada
        if ($memberSelected->poin >= 6000) {
            $diskon = 20; // Diskon Platinum
        } elseif ($memberSelected->poin >= 4000) {
            $diskon = 10; // Diskon Gold
        } elseif ($memberSelected->poin >= 2000) {
            $diskon = 5;  // Diskon Bronze
        }

        // Kirim ke view
        return view('penjualan_detail.index', compact('produk', 'member', 'id_penjualan', 'penjualan', 'memberSelected', 'diskon'));
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
            $row['kode_produk'] = '<span class="label label-success">'. $item->produk['kode_produk'] .'</span>';
            $row['nama_produk'] = $item->produk['nama_produk'];
            $row['harga_jual']  = 'Rp. '. format_uang($item->harga_jual);
            $row['jumlah']      = '<input type="number" class="form-control input-sm quantity" data-id="'. $item->id_penjualan_detail .'" value="'. $item->jumlah .'">';
            $row['diskon']      = $item->diskon . '%';
            $row['subtotal']    = 'Rp. '. format_uang($item->subtotal);
            $row['aksi']        = '<div class="btn-group">
                                    <button onclick="deleteData(`'. route('transaksi.destroy', $item->id_penjualan_detail) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                                </div>';
            $data[] = $row;

            $total += $item->harga_jual * $item->jumlah - (($item->diskon * $item->jumlah) / 100 * $item->harga_jual);
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
            return response()->json('Produk tidak ditemukan', 400);
        }

        // Ambil diskon dari request atau produk
        $diskon = $request->diskon ?? $produk->diskon;

        // Hitung subtotal
        $subtotal = $produk->harga_jual - ($diskon / 100 * $produk->harga_jual);

        // Ambil member yang sedang melakukan transaksi
        $member = Member::find($request->id_member);
        $totalDiskon = 0;
        
        // Jika member memiliki poin dan diskon diterapkan
        if ($member && $member->poin >= 2000) {
            $totalDiskon = ($diskon / 100) * $produk->harga_jual;

            // Mengurangi poin member berdasarkan diskon yang diterima
            $poinYangDigunakan = ($totalDiskon / $produk->harga_jual) * $member->poin;
            $member->poin -= $poinYangDigunakan;  // Mengurangi poin member
            $member->save();  // Simpan perubahan poin
        }

        // Menyimpan detail penjualan
        $detail = new PenjualanDetail();
        $detail->id_penjualan = $request->id_penjualan;
        $detail->id_produk = $produk->id_produk;
        $detail->harga_jual = $produk->harga_jual;
        $detail->jumlah = $request->jumlah ?? 1; // Jumlah produk
        $detail->diskon = $diskon;
        $detail->subtotal = $subtotal;
        $detail->save();

        return response()->json('Data berhasil disimpan', 200);
    }

    public function update(Request $request, $id)
    {
        $detail = PenjualanDetail::find($id);
        
        $diskon = $request->diskon ?? $detail->diskon; // Gunakan diskon dari request atau yang ada di detail
        $jumlah = $request->jumlah;
        
        // Hitung subtotal dengan diskon dan jumlah baru
        $subtotal = $detail->harga_jual * $jumlah - (($diskon * $jumlah) / 100 * $detail->harga_jual);
        
        // Update detail
        $detail->jumlah = $jumlah;
        $detail->subtotal = $subtotal;
        $detail->update();

        // Mengurangi poin member jika diskon digunakan
        $member = Member::find($detail->penjualan->id_member); // Ambil member dari penjualan terkait
        if ($member && $member->poin >= 2000) {
            $totalDiskon = ($diskon / 100) * $detail->harga_jual * $jumlah;
            $poinYangDigunakan = ($totalDiskon / $detail->harga_jual) * $member->poin;
            $member->poin -= $poinYangDigunakan;  // Mengurangi poin member
            $member->save();  // Simpan perubahan poin
        }

        return response()->json('Detail transaksi berhasil diperbarui', 200);
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

            $memberDiskon = $this->getDiskonByPoin($member->poin);
            $diskon = max($diskon, $memberDiskon);
            // Perhitungan diskon berdasarkan poin member
            if ($member->poin >= 6000) {
                $memberDiskon = 20; // Diskon Platinum
            } elseif ($member->poin >= 4000) {
                $memberDiskon = 10; // Diskon Gold
            } elseif ($member->poin >= 2000) {
                $memberDiskon = 5;  // Diskon Bronze
            }

            // Mengurangi poin sesuai diskon
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

        return response()->json([
            'totalrp' => format_uang($total),
            'bayar' => $total - ($diskon / 100 * $total),  // Jika tidak ada member, hanya diskon transaksi
            'bayarrp' => format_uang($total - ($diskon / 100 * $total)),
            'kembalirp' => format_uang($diterima - ($total - ($diskon / 100 * $total))),
            'terbilang' => ucwords(terbilang($total - ($diskon / 100 * $total)) . ' Rupiah'),
        ]);
    }

    public function getDiskonByPoin($poin)
{
    if ($poin >= 6000) {
        return 20; // Diskon Platinum
    } elseif ($poin >= 4000) {
        return 10; // Diskon Gold
    } elseif ($poin >= 2000) {
        return 5;  // Diskon Bronze
    }
    return 0; // Tidak ada diskon
}

public function getDiskonByPoinMember(Request $request)
{
    $member = Member::find($request->id_member);
    
    if (!$member) {
        return response()->json(['diskon' => 0]);
    }
    
    $diskon = $this->getDiskonByPoin($member->poin);
    
    return response()->json([
        'diskon' => $diskon,
        'poin' => $member->poin
    ]);
}
}
