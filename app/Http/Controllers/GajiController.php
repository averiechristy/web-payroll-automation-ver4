<?php

namespace App\Http\Controllers;

use App\Exports\TemplateGaji;
use App\Imports\GajiImport;
use App\Models\Gaji;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class GajiController extends Controller
{

    public function download()
    {
         // Panggil class export Anda, sesuaikan dengan struktur data Anda
         return Excel::download(new TemplateGaji(), 'templategaji&tunjangan.xlsx');
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
                'Gaji',
                'Tanggal Mulai Gaji',
                'Tanggal Selesai Gaji',
                'Tunjangan',
                'Tanggal Mulai Tunjangan',
                'Tanggal Selesai Tunjangan',
            ];
    
            if ($headingRow !== $expectedHeaders) {
                throw new \Exception("File tidak sesuai.");
            }
            $data = Excel::toCollection(new GajiImport, $file);

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
            Excel::import(new GajiImport, $file);
    
            // Jika impor berhasil, tampilkan pesan sukses
            $request->session()->flash('success', "Gaji berhasil ditambahkan.");
        } catch (\Exception $e) {
            // Jika terjadi exception, tangkap dan tampilkan pesan kesalahan
            $request->session()->flash('error',   $e->getMessage());
        }
    
        return redirect()->route('gaji');
     }


    public function index()
    {
        $gaji = Gaji::orderBy('created_at','desc')->get();
        return view('gaji.index',[
            'gaji' => $gaji,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $karyawan = Karyawan::all();
        return view('gaji.create',[
            'karyawan' => $karyawan,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $karyawanid = $request->karyawan_id;
        $gaji = $request -> gaji;
        $tanggalmulaigaji = $request->tanggal_mulai_gaji;
        $tanggalselesaigaji = $request->tanggal_selesai_gaji;
        $tunjangan = $request->tunjangan;
        $tanggalmulaitunjangan = $request-> tanggal_mulai_tunjangan;
        $tanggalselesaitunjangan = $request-> tanggal_selesai_tunjangan;

        $loggedInUser = auth()->user();
        $loggedInUsername = $loggedInUser->nama_user; 

        $existingEntryGaji = Gaji::where('karyawan_id', $request->karyawan_id)
        ->where(function ($query) use ($request) {
            $query->where(function ($q) use ($request) {
                $q->where('tanggal_mulai_gaji', '>=', $request->tanggal_mulai_gaji)
                    ->where('tanggal_mulai_gaji', '<=', $request->tanggal_selesai_gaji);
            })
            ->orWhere(function ($q) use ($request) {
                $q->where('tanggal_selesai_gaji', '>=', $request->tanggal_mulai_gaji)
                    ->where('tanggal_selesai_gaji', '<=', $request->tanggal_selesai_gaji);
            })
            ->orWhere(function ($q) use ($request) {
                $q->where('tanggal_mulai_gaji', '<=', $request->tanggal_mulai_gaji)
                    ->where('tanggal_selesai_gaji', '>=', $request->tanggal_selesai_gaji);
            });
        })
        ->orWhere(function ($query) use ($request) {
            $query->where('tanggal_mulai_gaji', '<=', $request->tanggal_mulai_gaji)
                ->where('tanggal_selesai_gaji', '>=', $request->tanggal_mulai_gaji);
        })
        ->orWhere(function ($query) use ($request) {
            $query->where('tanggal_mulai_gaji', '>=', $request->tanggal_mulai_gaji)
                ->where('tanggal_selesai_gaji', '<=', $request->tanggal_selesai_gaji);
        })
        ->first();

    
        $existingEntryTunjangan = Gaji::where('karyawan_id', $request->karyawan_id)
    ->where(function ($query) use ($request) {
        $query->where(function ($q) use ($request) {
            $q->where('tanggal_mulai_tunjangan', '>=', $request->tanggal_mulai_tunjangan)
                ->where('tanggal_mulai_tunjangan', '<=', $request->tanggal_selesai_tunjangan);
        })
        ->orWhere(function ($q) use ($request) {
            $q->where('tanggal_selesai_tunjangan', '>=', $request->tanggal_mulai_tunjangan)
                ->where('tanggal_selesai_tunjangan', '<=', $request->tanggal_selesai_tunjangan);
        })
        ->orWhere(function ($q) use ($request) {
            $q->where('tanggal_mulai_tunjangan', '<=', $request->tanggal_mulai_tunjangan)
                ->where('tanggal_selesai_tunjangan', '>=', $request->tanggal_selesai_tunjangan);
        });
    })
    ->orWhere(function ($query) use ($request) {
        $query->where('tanggal_mulai_tunjangan', '<=', $request->tanggal_mulai_tunjangan)
            ->where('tanggal_selesai_tunjangan', '>=', $request->tanggal_mulai_tunjangan);
    })
    ->orWhere(function ($query) use ($request) {
        $query->where('tanggal_mulai_tunjangan', '>=', $request->tanggal_mulai_tunjangan)
            ->where('tanggal_selesai_tunjangan', '<=', $request->tanggal_selesai_tunjangan);
    })
    ->first();

        
        if ($existingEntryGaji && $existingEntryTunjangan) {
            $karyawan = Karyawan::find($karyawanid);
            $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
            $request->session()->flash('error', "Gaji dan tunjangan untuk karyawan $namaKaryawan sudah terdaftar pada rentang tanggal yang sama.");
            return redirect(route('gaji'))->withInput();
        }
        
        if ($existingEntryGaji) {
            $karyawan = Karyawan::find($karyawanid);
            $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
            $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan sudah terdaftar pada rentang tanggal yang sama.");
            return redirect(route('gaji'))->withInput();
        }
        
        if ($existingEntryTunjangan) {
            $karyawan = Karyawan::find($karyawanid);
            $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
            $request->session()->flash('error', "Tunjangan untuk karyawan $namaKaryawan sudah terdaftar pada rentang tanggal yang sama.");
            return redirect(route('gaji'))->withInput();
        }
        

        Gaji::create([
            'karyawan_id' => $karyawanid,
            'gaji' => $gaji,
            'tanggal_mulai_gaji' => $tanggalmulaigaji,
            'tanggal_selesai_gaji' => $tanggalselesaigaji,
            'tunjangan' => $tunjangan, 
            'tanggal_mulai_tunjangan' => $tanggalmulaitunjangan,
            'tanggal_selesai_tunjangan' => $tanggalselesaitunjangan,
            'created_by' => $loggedInUsername,
        ]);

        $request->session()->flash('success', "Gaji dan tunjangan berhasil ditambahkan.");
        return redirect()->route('gaji');

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = Gaji::find($id);
        $karyawan = Karyawan::all();

        return view('gaji.edit',[
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

        $data = Gaji::find($id);
        $karyawanid = $request->karyawan_id;
        $gaji = $request->gaji;
        $mulaigaji = $request->tanggal_mulai_gaji;
        $selesaigaji = $request->tanggal_selesai_gaji;
        $tunjangan = $request -> tunjangan;
        $mulaitunjangan = $request->tanggal_mulai_tunjangan;
        $selesaitunjangan = $request->tanggal_selesai_tunjangan;
        $existingEntryGaji = Gaji::where('karyawan_id', $request->karyawan_id)
        ->where(function ($query) use ($request) {
            $query->where(function ($q) use ($request) {
                $q->where('tanggal_mulai_gaji', '<=', $request->tanggal_mulai_gaji)
                    ->where('tanggal_selesai_gaji', '>=', $request->tanggal_mulai_gaji);
            })
            ->orWhere(function ($q) use ($request) {
                $q->where('tanggal_mulai_gaji', '<=', $request->tanggal_selesai_gaji)
                    ->where('tanggal_selesai_gaji', '>=', $request->tanggal_selesai_gaji);
            });
        })
        ->where('id', '<>', $id)
        ->first();

        $existingEntryTunjangan = Gaji::where('karyawan_id', $request->karyawan_id)
        ->where(function ($query) use ($request) {
            $query->where(function ($q) use ($request) {
                $q->where('tanggal_mulai_tunjangan', '<=', $request->tanggal_mulai_tunjangan)
                    ->where('tanggal_selesai_tunjangan', '>=', $request->tanggal_mulai_tunjangan);
            })
            ->orWhere(function ($q) use ($request) {
                $q->where('tanggal_mulai_tunjangan', '<=', $request->tanggal_selesai_tunjangan)
                    ->where('tanggal_selesai_tunjangan', '>=', $request->tanggal_selesai_tunjangan);
            });
        })
        ->where('id', '<>', $id)
        ->first();
        
        if ($existingEntryGaji && $existingEntryTunjangan) {
            $karyawan = Karyawan::find($karyawanid);
            $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
            $request->session()->flash('error', "Gaji dan tunjangan untuk karyawan $namaKaryawan sudah terdaftar pada rentang tanggal yang sama.");
            return redirect(route('gaji'))->withInput();
        }
        
        if ($existingEntryGaji) {
            $karyawan = Karyawan::find($karyawanid);
            $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
            $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan sudah terdaftar pada rentang tanggal yang sama.");
            return redirect(route('gaji'))->withInput();
        }
        
        if ($existingEntryTunjangan) {
            $karyawan = Karyawan::find($karyawanid);
            $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
            $request->session()->flash('error', "Tunjangan untuk karyawan $namaKaryawan sudah terdaftar pada rentang tanggal yang sama.");
            return redirect(route('gaji'))->withInput();
        }
        
        $loggedInUser = auth()->user();
        $loggedInUsername = $loggedInUser->nama_user; 

        $data -> karyawan_id = $karyawanid;
        $data -> gaji = $gaji;
        $data -> tunjangan = $tunjangan;
        $data -> tanggal_mulai_gaji = $mulaigaji;
        $data -> tanggal_selesai_gaji = $selesaigaji;
        $data -> tanggal_mulai_tunjangan = $mulaitunjangan;
        $data -> tanggal_selesai_tunjangan = $selesaitunjangan;
        $data -> updated_by = $loggedInUsername;
        $data->save();



        $request->session()->flash('success', "Gaji dan tunjangan berhasil diubah.");

        return redirect()->route('gaji');

    }

    /**
     * Remove the specified resource from storage.
     */

    public function destroy(Request $request, $id)
    {
        
        $gaji = Gaji::find($id);
    

        $tanggalSekarang = Carbon::now();

        $gajiBerjalan = $tanggalSekarang->between($gaji->tanggal_mulai_gaji, $gaji->tanggal_selesai_gaji);
        $tunjanganBerjalan = $tanggalSekarang->between($gaji->tanggal_mulai_tunjangan, $gaji->tanggal_selesai_tunjangan);
    
        if ($gajiBerjalan || $tunjanganBerjalan) {
            $request->session()->flash('error', "Tidak bisa menghapus data, periode gaji & tunjangan sudah berjalan.");

            return redirect()->route('gaji');
        }


    $gajiBerakhir = $tanggalSekarang->gt($gaji->tanggal_selesai_gaji);
    $tunjanganBerakhir = $tanggalSekarang->gt($gaji->tanggal_selesai_tunjangan);

    if ( $gajiBerakhir || $tunjanganBerakhir) {
        $request->session()->flash('error', "Tidak bisa menghapus data, periode gaji & tunjangan sudah berakhir.");

        return redirect()->route('gaji');    
    }
        $gaji->delete();
        $request->session()->flash('success', "Gaji berhasil dihapus.");

        return redirect()->route('gaji');
    }

}
