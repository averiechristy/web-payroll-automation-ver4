<?php

namespace App\Http\Controllers;

use App\Models\Insentif;
use App\Models\Karyawan;
use DateTime;
use Illuminate\Http\Request;

class InsentifController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

    $insentif = Insentif::all();
      return view('insentif.index',[
        'insentif' => $insentif,
      ]);   
      
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $karyawan = Karyawan::all();
        
        return view('insentif.create',[
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
        $insentif = $request->insentif;
        $loggedInUser = auth()->user();
        $loggedInUsername = $loggedInUser->nama_user; 
      

        $datakaryawan = Karyawan::find($karyawanid);
        $namakaryawan = $datakaryawan->nama_karyawan;

        $dateObj = DateTime::createFromFormat('!m', $bulan);
    $bulanNama = $dateObj->format('F'); // This will give the full month name


        $existingdata = Insentif::where('karyawan_id', $karyawanid)
        ->where('bulan', $bulan)
        ->where('tahun',$tahun)
        ->first();

        if ($existingdata){
            $request->session()->flash('error', "Insentif untuk karyawan $namakaryawan pada $bulanNama $tahun sudah terdafatar.");

            return redirect()->route('insentif');
        }

        Insentif::create([
            'karyawan_id' => $karyawanid,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'insentif' => $insentif,
            'created_by' => $loggedInUsername,
        ]);

        $request->session()->flash('success', 'Insentif berhasil ditambahkan.');

        return redirect(route('insentif'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = Insentif::find($id);
        $karyawan = Karyawan::all();

        return view('insentif.edit',[
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
        $data = Insentif::find($id);
        $karyawanid = $request->karyawan_id;
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $insentif = $request->insentif;
        $loggedInUser = auth()->user();
        $loggedInUsername = $loggedInUser->nama_user; 

        $datakaryawan = Karyawan::find($karyawanid);
        $namakaryawan = $datakaryawan->nama_karyawan;

        $dateObj = DateTime::createFromFormat('!m', $bulan);
        $bulanNama = $dateObj->format('F'); // This will give the full month name

        $existingdata = Insentif::where('karyawan_id', $karyawanid)
        ->where('bulan', $bulan)
        ->where('tahun',$tahun)
        ->where('id', '<>', $id)
        ->first();

        if ($existingdata){
            $request->session()->flash('error', "Insentif untuk karyawan $namakaryawan pada $bulanNama $tahun sudah terdaftar,");
            return redirect()->route('insentif');
        }

        $data -> karyawan_id = $karyawanid;
        $data ->bulan = $bulan;
        $data ->tahun = $tahun;
        $data -> insentif = $insentif;
        $data->updated_by = $loggedInUsername;
        $data->save();

        $request->session()->flash('success', 'Insentif berhasil diubah.');

        return redirect(route('insentif'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
