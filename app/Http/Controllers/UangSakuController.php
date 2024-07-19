<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Organisasi;
use App\Models\UangSakuDinas;
use DateTime;
use Illuminate\Http\Request;

class UangSakuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $uangsaku = UangSakuDinas::orderBy('created_at','desc')->get();
        return view ('uangsaku.index',[
            'uangsaku' => $uangsaku,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $karyawan = Karyawan::all();
        


        return view('uangsaku.create',[
            'karyawan' => $karyawan,
           
        ]);

    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {

        $karyawanid = $request->karyawan_id;
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $uangsaku = $request->uang_saku;
        $loggedInUser = auth()->user();
        $loggedInUsername = $loggedInUser->nama_user; 
      

        $datakaryawan = Karyawan::find($karyawanid);
        $namakaryawan = $datakaryawan->nama_karyawan;

        $dateObj = DateTime::createFromFormat('!m', $bulan);
    $bulanNama = $dateObj->format('F'); // This will give the full month name


        $existingdata = UangSakuDinas::where('karyawan_id', $karyawanid)
        ->where('bulan', $bulan)
        ->where('tahun',$tahun)
        ->first();

        if ($existingdata){
            $request->session()->flash('error', "Uang saku untuk karyawan $namakaryawan pada $bulanNama $tahun sudah terdafatar.");

            return redirect()->route('uangsaku');
        }


        UangSakuDinas::create([
            'karyawan_id' => $karyawanid,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'uang_saku' => $uangsaku,
            'created_by' => $loggedInUsername,
        ]);

        $request->session()->flash('success', 'Uang saku berhasil ditambahkan.');

        return redirect(route('uangsaku'));
       
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = UangSakuDinas::find($id);
        $karyawan = Karyawan::all();

        return view('uangsaku.edit',[
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
        $data = UangSakuDinas::find($id);
        $karyawanid = $request->karyawan_id;
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $uangsaku = $request->uang_saku;
        $loggedInUser = auth()->user();
        $loggedInUsername = $loggedInUser->nama_user; 


        $datakaryawan = Karyawan::find($karyawanid);
        $namakaryawan = $datakaryawan->nama_karyawan;

        $dateObj = DateTime::createFromFormat('!m', $bulan);
        $bulanNama = $dateObj->format('F'); // This will give the full month name

        $existingdata = UangSakuDinas::where('karyawan_id', $karyawanid)
        ->where('bulan', $bulan)
        ->where('tahun',$tahun)
        ->where('id', '<>', $id)
        ->first();

        if ($existingdata){
            $request->session()->flash('error', "Uang saku untuk karyawan $namakaryawan pada $bulanNama $tahun sudah terdaftar,");
            return redirect()->route('uangsaku');
        }

        $data -> karyawan_id = $karyawanid;
        $data ->bulan = $bulan;
        $data ->tahun = $tahun;
        $data -> uang_saku = $uangsaku;
        $data->updated_by = $loggedInUsername;
        $data->save();

        $request->session()->flash('success', 'Uang saku berhasil diubah.');

        return redirect(route('uangsaku'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $uangsaku = UangSakuDinas::find($id);

        
        
    }
}
