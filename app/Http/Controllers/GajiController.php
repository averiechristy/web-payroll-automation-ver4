<?php

namespace App\Http\Controllers;

use App\Models\Gaji;
use App\Models\Karyawan;
use Illuminate\Http\Request;

class GajiController extends Controller
{

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
        
        $data -> karyawan_id = $karyawanid;
        $data -> gaji = $gaji;
        $data -> tunjangan = $tunjangan;
        $data -> tanggal_mulai_gaji = $mulaigaji;
        $data -> tanggal_selesai_gaji = $selesaigaji;
        $data -> tanggal_mulai_tunjangan = $mulaitunjangan;
        $data -> tanggal_selesai_tunjangan = $selesaitunjangan;
        $data->save();

        $request->session()->flash('success', "Gaji dan tunjangan berhasil diubah.");

        return redirect()->route('gaji');

    }

    /**
     * Remove the specified resource from storage.
     */

    public function destroy(string $id)
    {
        
        $gaji = Gaji::find($id);
        dd($gaji);
        
    }

}
