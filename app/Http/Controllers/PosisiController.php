<?php

namespace App\Http\Controllers;

use App\Exports\TemplatePosisi;
use App\Imports\PosisiImport;
use App\Models\Karyawan;
use App\Models\Penempatan;
use App\Models\Posisi;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PosisiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function download()
    {
         // Panggil class export Anda, sesuaikan dengan struktur data Anda
         return Excel::download(new TemplatePosisi(), 'templateposisi.xlsx');
    }
    public function index()
    {

        $posisi = Posisi::orderBy('created_at','desc')->get();

        return view('posisi.index',[
            'posisi' => $posisi,
        ]);
    }

    public function import(Request $request){
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            $file = $request->file('file');

            $reader = Excel::toArray([], $file);
            $headingRow = $reader[0][0];

            $expectedHeaders = [
                'Kode Orange',
                'Jenis Pekerjaan',
                'Posisi',
                'Standarisasi Upah (%)',

            ];
    
            if ($headingRow !== $expectedHeaders) {
                throw new \Exception("File tidak sesuai.");
            }
            $data = Excel::toCollection(new PosisiImport, $file);

            if ($data->isEmpty() || $data->first()->isEmpty()) {
                throw new \Exception("File harus diisi.");

            }
            // Lakukan impor
            Excel::import(new PosisiImport, $file);
    
            // Jika impor berhasil, tampilkan pesan sukses
            $request->session()->flash('success', "Posisi berhasil ditambahkan.");
        } catch (\Exception $e) {
            // Jika terjadi exception, tangkap dan tampilkan pesan kesalahan
            $request->session()->flash('error',   $e->getMessage());
        }
    
        return redirect()->route('posisi');
     }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('posisi.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        $kodeorange = $request->kode_orange;
        $jenis = $request->jenis_pekerjaan;
        $posisi = $request ->posisi;
        $standarisasi = $request -> standarisasi_upah;

        Posisi::create([
           'kode_orange' => $kodeorange,
           'jenis_pekerjaan' => $jenis,
           'posisi' => $posisi,
           'standarisasi_upah' => $standarisasi 
        ]);

        $request->session()->flash('success', 'Posisi berhasil ditambahkan.');

        return redirect(route('posisi'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = Posisi::find($id);
        return view('posisi.edit',[
            'data' => $data,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = Posisi::find($id);
        $data->kode_orange = $request->kode_orange;
        $data->jenis_pekerjaan = $request->jenis_pekerjaan;
        $data->posisi = $request -> posisi;
        $data->standarisasi_upah = $request->standarisasi_upah;

        $data->save();

        
        $request->session()->flash('success', 'Posisi berhasil diubah.');

        return redirect(route('posisi'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $posisi = Posisi::find($id);

        if (Karyawan::where('posisi_id', $posisi->id)->exists()) {
            $request->session()->flash('error', "Tidak dapat menghapus posisi, karena masih ada data karyawan yang berhubungan.");
            return redirect()->route('posisi');
        }

    $posisi->delete();

    $request->session()->flash('success', "Posisi berhasil dihapus.");

    return redirect()->route('posisi');
    }
}
    

