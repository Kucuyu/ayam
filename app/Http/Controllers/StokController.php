<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;
use App\Models\Stok;
use PDF;

class StokController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
{
    $kategori = Kategori::all()->pluck('nama_kategori', 'id_kategori');

    return view('stok.index', compact('kategori'));
}

    public function data()
    {
        $stok = Stok::leftJoin('kategori', 'kategori.id_kategori', 'stok.id_kategori')
            ->select('stok.*', 'nama_kategori')
            ->get();

        return datatables()
            ->of($stok)
            ->addIndexColumn()
            ->addColumn('select_all', function ($stok) {
                return '
                    <input type="checkbox" name="id_stok[]" value="'. $stok->id_stok .'">
                ';
            })
            ->addColumn('kode_stok', function ($stok) {
                return '<span class="label label-success">'. $stok->kode_stok .'</span>';
            })
            ->addColumn('harga_beli', function ($stok) {
                return format_uang($stok->harga_beli);
            })
            ->addColumn('harga_jual', function ($stok) {
                return format_uang($stok->harga_jual);
            })
            ->addColumn('stok', function ($stok) {
                return format_uang($stok->stok);
            })
            ->addColumn('aksi', function ($stok) {
                return '
                <div class="btn-group">
                    <button type="button" onclick="editForm(`'. route('stok.update', $stok->id_stok) .'`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-pencil"></i></button>
                    <button type="button" onclick="deleteData(`'. route('stok.destroy', $stok->id_stok) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                </div>
                ';
            })
            ->rawColumns(['aksi', 'kode_stok', 'select_all'])
            ->make(true);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $stok = Stok::latest()->first() ?? new Stok();
        $request['kode_stok'] = 'S'. tambah_nol_didepan((int)$stok->id_stok +1, 6);

        $stok = Stok::create($request->all());

        return response()->json('Data berhasil disimpan', 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $stok = Stok::find($id);

        return response()->json($stok);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $stok = Stok::find($id);
        $stok->update($request->all());

        return response()->json('Data berhasil disimpan', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $stok = Stok::find($id);
        $stok->delete();

        return response(null, 204);
    }

    public function deleteSelected(Request $request)
    {
        foreach ($request->id_stok as $id) {
            $stok = Stok::find($id);
            $stok->delete();
        }

        return response(null, 204);
    }

    public function cetakBarcode(Request $request)
    {
        $datastok = array();
        foreach ($request->id_stok as $id) {
            $stok = Stok::find($id);
            $datastok[] = $stok;
        }

        $no  = 1;
        $pdf = PDF::loadView('stok.barcode', compact('datastok', 'no'));
        $pdf->setPaper('a4', 'potrait');
        return $pdf->stream('stok.pdf');
    }
}