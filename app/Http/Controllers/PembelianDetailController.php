<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Stok;
use App\Models\Karyawan;
use Illuminate\Http\Request;

class PembelianDetailController extends Controller
{
    public function index()
    {
        $id_pembelian = session('id_pembelian');
        $stok = Stok::orderBy('nama_stok')->get();
        $karyawan = Karyawan::find(session('id_karyawan'));
        $diskon = Pembelian::find($id_pembelian)->diskon ?? 0;

        if (! $karyawan) {
            abort(404);
        }

        return view('pembelian_detail.index', compact('id_pembelian', 'stok', 'karyawan', 'diskon'));
    }

    public function data($id)
    {
        $detail = PembelianDetail::with('stok')
            ->where('id_pembelian', $id)
            ->get();
        $data = array();
        $total = 0;
        $total_item = 0;

        foreach ($detail as $item) {
            $row = array();
            $row['kode_stok'] = '<span class="label label-success">'. $item->stok['kode_stok'] .'</span';
            $row['nama_stok'] = $item->stok['nama_stok'];
            $row['harga_beli']  = 'Rp. '. format_uang($item->harga_beli);
            $row['jumlah']      = '<input type="number" class="form-control input-sm quantity" data-id="'. $item->id_pembelian_detail .'" value="'. $item->jumlah .'">';
            $row['subtotal']    = 'Rp. '. format_uang($item->subtotal);
            $row['aksi']        = '<div class="btn-group">
                                    <button onclick="deleteData(`'. route('pembelian_detail.destroy', $item->id_pembelian_detail) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                                </div>';
            $data[] = $row;

            $total += $item->harga_beli * $item->jumlah;
            $total_item += $item->jumlah;
        }
        $data[] = [
            'kode_stok' => '
                <div class="total hide">'. $total .'</div>
                <div class="total_item hide">'. $total_item .'</div>',
            'nama_stok' => '',
            'harga_beli'  => '',
            'jumlah'      => '',
            'subtotal'    => '',
            'aksi'        => '',
        ];

        return datatables()
            ->of($data)
            ->addIndexColumn()
            ->rawColumns(['aksi', 'kode_stok', 'jumlah'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $stok = Stok::where('id_stok', $request->id_stok)->first();
        if (! $stok) {
            return response()->json('Data gagal disimpan', 400);
        }

        $detail = new PembelianDetail();
        $detail->id_pembelian = $request->id_pembelian;
        $detail->id_stok = $stok->id_stok;
        $detail->harga_beli = $stok->harga_beli;
        $detail->jumlah = 1;
        $detail->subtotal = $stok->harga_beli;
        $detail->save();

        return response()->json('Data berhasil disimpan', 200);
    }

    public function update(Request $request, $id)
    {
        $detail = PembelianDetail::find($id);
        $detail->jumlah = $request->jumlah;
        $detail->subtotal = $detail->harga_beli * $request->jumlah;
        $detail->update();
    }

    public function destroy($id)
    {
        $detail = PembelianDetail::find($id);
        $detail->delete();

        return response(null, 204);
    }

    public function loadForm($diskon, $total)
    {
        $bayar = $total - ($diskon / 100 * $total);
        $data  = [
            'totalrp' => format_uang($total),
            'bayar' => $bayar,
            'bayarrp' => format_uang($bayar),
            'terbilang' => ucwords(terbilang($bayar). ' Rupiah')
        ];

        return response()->json($data);
    }
}