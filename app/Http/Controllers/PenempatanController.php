<?php
namespace App\Http\Controllers;

use App\Exports\TemplatePenempatan;
use App\Imports\PenempatanImport;
use App\Models\Divisi;
use App\Models\Karyawan;
use App\Models\Organisasi;
use App\Models\Penempatan;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PenempatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */

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
                'Organisasi',
                'Divisi',
                'KCU Induk',
                'Nama Unit Kerja Penempatan',
                'Kode Cabang Pembayaran untuk Vendor MAD',
                'RCC Pembayaran untuk Vendor MAD',
                'Singkatan Divisi',
                'Kode SLID',
            ];
    
            if ($headingRow !== $expectedHeaders) {
                throw new \Exception("File tidak sesuai.");
            }

            $data = Excel::toCollection(new PenempatanImport, $file);

            // Filter baris yang hanya berisi nilai null
            $filteredData = $data->map(function ($sheet) {
                return $sheet->filter(function ($row) {
                    return $row->filter(function ($value) {
                        return !is_null($value);
                    })->isNotEmpty();
                });
            });
                        

            if ($filteredData->isEmpty() || $filteredData->first()->isEmpty()) {
                throw new \Exception("File harus diisi.");
            }
            $hasData = false;
            foreach ($data->first() as $row) {
                if ($row->filter()->isNotEmpty()) {
                    $hasData = true;
                    break;
                }
            }
            
            if (!$hasData) {
                throw new \Exception("File harus diisi.");
            }
            // Lakukan impor
            Excel::import(new PenempatanImport, $file);
    
            // Jika impor berhasil, tampilkan pesan sukses
            $request->session()->flash('success', "Penempatan berhasil ditambahkan.");
        } catch (\Exception $e) {
            // Jika terjadi exception, tangkap dan tampilkan pesan kesalahan
            $request->session()->flash('error',   $e->getMessage());
        }
    
        return redirect()->route('penempatan');
     }

    public function download()
    {
         // Panggil class export Anda, sesuaikan dengan struktur data Anda
         return Excel::download(new TemplatePenempatan(), 'templatepenempatan.xlsx');
    }
    public function index()
    {
        $penempatan = Penempatan::orderBy('created_at','desc')->get();
        
        
        return view('penempatan.index',[
            'penempatan' => $penempatan,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $organisasi = Organisasi::all()->sortBy('name');

        $divisi = Divisi::all()->sortBy('name');

        return view('penempatan.create',[
            'organisasi' => $organisasi,
            'divisi' => $divisi,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $loggedInUser = auth()->user();
        $loggedInUsername = $loggedInUser->nama_user; 
        // $canhitung = $request->has('flexCheckIndeterminate');
        
        // if($canhitung == true){
        //     $hitungtunjangan ="Yes";
        // } else if($canhitung == false) {
        //     $hitungtunjangan ="No";
        // }


        

        $organisasiid = $request->organisasi_id;
        $divisid = $request->divisi_id;

        $kodeorange = $request->kode_orange;
        $namaunit = $request -> nama_unit_kerja;
      
        $existingunit = Penempatan::where('nama_unit_kerja', $namaunit)->first();

        if ($existingunit) {
            // Jika organisasi sudah ada, tampilkan pesan error
            $request->session()->flash('error', "Penempatan sudah terdaftar.");
            return redirect()->route('penempatan');
        }

        $kcuinduk = $request -> kcu_induk;
        $kodecabang = $request -> kode_cabang_pembayaran;
        $rcc = $request -> rcc_pembayaran;
        $singkatan = $request -> singkatan_divisi;
        $slid = $request -> kode_slid;

        Penempatan::create([
            'kode_orange' => $kodeorange,
            'nama_unit_kerja' => $namaunit,
            'created_by' => $loggedInUsername,
            'kcu_induk' => $kcuinduk,
            'kode_cabang_pembayaran' => $kodecabang,
            'rcc_pembayaran' => $rcc,
            'singkatan_divisi' => $singkatan,
            'kode_slid' => $slid,           
            'divisi_id' => $divisid,
            'organisasi_id' => $organisasiid,
        ]);
        
        $request->session()->flash('success', 'Penempatan berhasil ditambahkan.');

        return redirect(route('penempatan'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = Penempatan::find($id);
        $organisasi = Organisasi::all()->sortBy('name');

        $divisi = Divisi::all()->sortBy('name');

        return view('penempatan.edit',[
            'data'=> $data,
            'organisasi' => $organisasi,
            'divisi' => $divisi,
        ]);
      
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
       
        // $canhitung = $request->has('flexCheckIndeterminate');
        
        // if($canhitung == true){
        //     $hitungtunjangan ="Yes";

        // } else if($canhitung == false) {
        //     $hitungtunjangan ="No";
        // }
        $loggedInUser = auth()->user();
        $loggedInUsername = $loggedInUser->nama_user; 

        $namaunit = $request -> nama_unit_kerja;
      
        $existingunit = Penempatan::where('nama_unit_kerja', $namaunit)
        ->where('id', '!=', $id)
        ->first();

        if ($existingunit) {
            // Jika organisasi sudah ada, tampilkan pesan error
            $request->session()->flash('error', "Penempatan sudah terdaftar.");
            return redirect()->route('penempatan');
        }

        $data = Penempatan::find($id);
        $data -> kode_orange = $request -> kode_orange;
        $data -> nama_unit_kerja = $request->nama_unit_kerja;
        $data -> divisi_id = $request->divisi_id;
        $data -> organisasi_id = $request->organisasi_id;
        $data -> kcu_induk = $request->kcu_induk;
        $data -> kode_cabang_pembayaran = $request->kode_cabang_pembayaran;
        $data -> rcc_pembayaran = $request -> rcc_pembayaran;
        $data -> singkatan_divisi = $request -> singkatan_divisi;
        $data -> kode_slid = $request -> kode_slid;
        $data -> updated_by = $loggedInUsername;
     

        $data -> save();
        $request->session()->flash('success', 'Penempatan berhasil diubah.');

        return redirect(route('penempatan'));

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $penempatan = Penempatan::find($id);

        if (Karyawan::where('penempatan_id', $penempatan->id)->exists()) {
            $request->session()->flash('error', "Tidak dapat menghapus penempatan, karena masih ada data karyawan yang berhubungan.");
            return redirect()->route('penempatan');
        }

    $penempatan->delete();

    $request->session()->flash('success', "Penempatan berhasil dihapus.");

    return redirect()->route('penempatan');
    }
}
