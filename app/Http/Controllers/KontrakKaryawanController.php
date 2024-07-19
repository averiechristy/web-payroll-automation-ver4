<?php

namespace App\Http\Controllers;

use App\Exports\TemplateKontrak;
use App\Imports\KontrakImport;
use App\Models\Karyawan;
use App\Models\KontrakKaryawan;
use Carbon\Carbon;
use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel;
class KontrakKaryawanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    
    public function index()
    {

        $kontrak = KontrakKaryawan::orderBy('created_at','desc')->get();
        return view ('kontrak.index',[
            'kontrak' => $kontrak
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $karyawan = Karyawan::all();
        return view('kontrak.create',[
            'karyawan' => $karyawan,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {

        $karyawanid = $request->karyawan_id;
        $tglawal = $request->tanggal_awal_kontrak;
        $tglakhir = $request->tanggal_akhir_kontrak;
        $loggedInUser = auth()->user();
        $loggedInUsername = $loggedInUser->nama_user; 
    
        // Check for overlapping contracts
        $existingContract = KontrakKaryawan::where('karyawan_id', $karyawanid)
            ->where(function($query) use ($tglawal, $tglakhir) {
                $query->whereBetween('tanggal_awal_kontrak', [$tglawal, $tglakhir])
                      ->orWhereBetween('tanggal_akhir_kontrak', [$tglawal, $tglakhir])
                      ->orWhere(function($query) use ($tglawal, $tglakhir) {
                          $query->where('tanggal_awal_kontrak', '<=', $tglawal)
                                ->where('tanggal_akhir_kontrak', '>=', $tglakhir);
                      });
            })
        ->first();
    

        if ($existingContract) {
            $karyawan = Karyawan::find($karyawanid);
            $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
            $request->session()->flash('error', "Kontrak untuk karyawan $namaKaryawan sudah terdaftar pada rentang tanggal yang sama.");
            return redirect()->route('kontrak');
        }
    

        KontrakKaryawan::create([
            'karyawan_id' => $karyawanid,
            'tanggal_awal_kontrak' => $tglawal,
            'tanggal_akhir_kontrak' => $tglakhir,
            'created_by' => $loggedInUsername,
        ]);
    
        $request->session()->flash('success', "Kontrak karyawan berhasil ditambahkan.");
        return redirect()->route('kontrak');
    }
    

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = KontrakKaryawan::find($id);
        $karyawan = Karyawan::all();

        return view('kontrak.edit',[
            'data' => $data,
            'karyawan' => $karyawan,
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
        $data = KontrakKaryawan::find($id);

        $karyawanid = $request->karyawan_id;
        $tglawal = $request->tanggal_awal_kontrak;
        $tglakhir = $request->tanggal_akhir_kontrak;

        $data -> karyawan_id = $karyawanid;
        $data -> tanggal_awal_kontrak = $tglawal;
        $data->tanggal_akhir_kontrak = $tglakhir;


        $existingContract = KontrakKaryawan::where('karyawan_id', $karyawanid)
        ->where(function($query) use ($tglawal, $tglakhir) {
            $query->whereBetween('tanggal_awal_kontrak', [$tglawal, $tglakhir])
                  ->orWhereBetween('tanggal_akhir_kontrak', [$tglawal, $tglakhir])
                  ->orWhere(function($query) use ($tglawal, $tglakhir) {
                      $query->where('tanggal_awal_kontrak', '<=', $tglawal)
                            ->where('tanggal_akhir_kontrak', '>=', $tglakhir);
                  });
        })
        ->where('id', '<>', $id)
        ->first();

    if ($existingContract) {
        $karyawan = Karyawan::find($karyawanid);
        $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
        $request->session()->flash('error', "Kontrak untuk karyawan $namaKaryawan sudah terdaftar pada rentang tanggal yang sama.");
        return redirect()->route('kontrak');
    }

        $data->save();

        $request->session()->flash('success', "Kontrak karyawan berhasil diubah.");
        return redirect()->route('kontrak');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $kontrak = KontrakKaryawan::find($id);
    
        $tanggalSekarang = Carbon::now();

        $kontrakberjalan = $tanggalSekarang->between($kontrak->tanggal_awal_kontrak, $kontrak->tanggal_akhir_kontrak);
    
        if ($kontrakberjalan ) {
            $request->session()->flash('error', "Tidak bisa menghapus data, periode kontrak sudah berjalan.");

            return redirect()->route('kontrak');
        }

        $kontrak->delete();
        $request->session()->flash('success', "Kontrak karyawan berhasil dihapus.");

        return redirect()->route('kontrak');

    }


    public function download()
    {
         // Panggil class export Anda, sesuaikan dengan struktur data Anda
         return Excel::download(new TemplateKontrak(), 'templatekontrakkaryawan.xlsx');
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
                'Nama Karyawan',
                'Tanggal Awal Kontrak',
                'Tanggal Akhir Kontrak',
            ];
    
            if ($headingRow !== $expectedHeaders) {
                throw new \Exception("File tidak sesuai.");
            }
            $data = Excel::toCollection(new KontrakImport(), $file);

            if ($data->isEmpty() || $data->first()->isEmpty()) {
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
            Excel::import(new KontrakImport(), $file);
    
            // Jika impor berhasil, tampilkan pesan sukses
            $request->session()->flash('success', "Kontrak berhasil ditambahkan.");
        } catch (\Exception $e) {
            // Jika terjadi exception, tangkap dan tampilkan pesan kesalahan
            $request->session()->flash('error',   $e->getMessage());
        }
    
        return redirect()->route('kontrak');
     }
}
