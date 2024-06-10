<?php

namespace App\Http\Controllers;

use App\Exports\TemplateKaryawan;
use App\Imports\KaryawanImport;
use App\Models\Karyawan;
use App\Models\MAD;
use App\Models\Penempatan;
use App\Models\Posisi;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class KaryawanController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     public function download()
     {
          // Panggil class export Anda, sesuaikan dengan struktur data Anda
          return Excel::download(new TemplateKaryawan(), 'templatekaryawan.xlsx');
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
                'NIK',
                'Payroll Code',
                'Nama',
                'No PBB/Amandemen',
                'NIK KTP',
                'Unit Kerja Penempatan',
                'Posisi',
                'Upah Pokok',
                'Tunjangan Supervisor',
                'Management Fee (%)',
                'Jabatan',
                'Bagian',
                'Leader',
                'Status',

            ];


            if ($headingRow !== $expectedHeaders) {
              
                throw new \Exception("File tidak sesuai.");
            }
            $data = Excel::toCollection(new KaryawanImport, $file);

            if ($data->isEmpty() || $data->first()->isEmpty()) {
                throw new \Exception("File harus diisi.");

            }
            // Lakukan impor
            Excel::import(new KaryawanImport, $file);
    
            // Jika impor berhasil, tampilkan pesan sukses
            $request->session()->flash('success', "Karyawan berhasil ditambahkan.");
        } catch (\Exception $e) {
            // Jika terjadi exception, tangkap dan tampilkan pesan kesalahan
            $request->session()->flash('error',   $e->getMessage());
        }
    
        return redirect()->route('karyawan');
     }

    public function index()
    {
        $karyawan = Karyawan::orderBy('created_at','desc')->get();

        return view('karyawan.index',[
            'karyawan' => $karyawan,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $penempatan = Penempatan::all();
        $posisi = Posisi::all();
        
        return view('karyawan.create',[
            'penempatan' => $penempatan,
            'posisi' => $posisi,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
      
        $nik = $request->nik;
        $payroll = $request->payroll_code;
        $nama = $request->nama_karyawan;
        $amandemen = $request->no_amandemen;
        $nikktp = $request->nik_ktp;
        $penempatan = $request->penempatan_id;
        $posisi = $request->posisi_id;
        $jabatan = $request->jabatan;
        $bagian = $request->bagian;
        $upah = $request->upah_pokok;
        $tunjangan = $request->tunjangan_spv;
        $management = $request->management_fee;
        $leader = $request->leader;
        $status = $request->status_karyawan;

        $datapenempatan = Penempatan::find($penempatan);
       
        $managementconvert = $management / 100;

        $kodecabangbayar = $datapenempatan->kode_cabang_pembayaran;
        $rcc = $datapenempatan -> rcc_pembayaran;


      
        $existingdata = Karyawan::where('nik_ktp', $nikktp)->first();

        if($existingdata){
            $request->session()->flash('error', 'NIK sudah terdaftar.');

            return redirect(route('karyawan'));
        }

        Karyawan::create([
            'nik' => $nik,
            'payroll_code' => $payroll,
            'nama_karyawan' => $nama,
            'no_amandemen' => $amandemen,
            'nik_ktp' => $nikktp,
            'penempatan_id' => $penempatan,
            'posisi_id' => $posisi,
            'jabatan'=> $jabatan,
            'bagian' => $bagian,
            'upah_pokok' => $upah,
            'tunjangan_spv'=> $tunjangan,
            'kode_cabang_pembayaran' => $kodecabangbayar,
            'rcc_pembayaran' => $rcc,
            'management_fee'=>   $managementconvert,
            'leader' => $leader,
            'status_karyawan'=> $status,

        ]);

        $request->session()->flash('success', 'Karyawan berhasil ditambahkan.');

        return redirect(route('karyawan'));
    
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = Karyawan::find($id);
        $penempatan = Penempatan::all();
        $posisi = Posisi::all();

        $management = $data->management_fee;

        $convertfee = $management * 100;

        return view('karyawan.edit',[
            'data' => $data,
            'penempatan' => $penempatan,
            'posisi' => $posisi,
            'convertfee' => $convertfee,
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
      
        $karyawan = Karyawan::find($id);

        $nik = $request->nik;
        $payroll = $request->payroll_code;
        $nama = $request->nama_karyawan;
        $amandemen = $request->no_amandemen;
        $nikktp = $request->nik_ktp;
        $penempatan = $request->penempatan_id;
        $posisi = $request->posisi_id;
        $jabatan = $request->jabatan;
        $bagian = $request->bagian;
        $upah = $request->upah_pokok;
        $tunjangan = $request->tunjangan_spv;
        $management = $request->management_fee;
        $leader = $request->leader;
        $status = $request->status_karyawan;

        $datapenempatan = Penempatan::find($penempatan);
       
        $managementconvert = $management / 100;
        $kodecabangbayar = $datapenempatan->kode_cabang_pembayaran;
        $rcc = $datapenempatan -> rcc_pembayaran;

        $existingdata = Karyawan::where('nik_ktp', $nikktp)->where('id', '<>', $id)->first();
        if($existingdata){
            $request->session()->flash('error', 'NIK sudah terdaftar.');

            return redirect(route('karyawan'));
        }

        
        $karyawan->update([
            'nik' => $nik,
            'payroll_code' => $payroll,
            'nama_karyawan' => $nama,
            'no_amandemen' => $amandemen,
            'nik_ktp' => $nikktp,
            'penempatan_id' => $penempatan,
            'posisi_id' => $posisi,
            'jabatan'=> $jabatan,
            'bagian' => $bagian,
            'upah_pokok' => $upah,
            'tunjangan_spv'=> $tunjangan,
            'kode_cabang_pembayaran' => $kodecabangbayar,
            'rcc_pembayaran' => $rcc,
            'management_fee'=> $managementconvert,
            'leader' => $leader,
            'status_karyawan'=> $status,

        ]);

        $request->session()->flash('success', 'Karyawan berhasil diubah.');

        return redirect(route('karyawan'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $karyawan = Karyawan::find($id);

        if (MAD::where('karyawan_id', $karyawan->id)->exists()) {
            $request->session()->flash('error', "Tidak dapat menghapus karyawan, karena masih ada data MAD yang berhubungan.");
            return redirect()->route('karyawan');
        }

        $karyawan->delete();

    $request->session()->flash('success', "Karyawan berhasil dihapus.");

    return redirect()->route('karyawan');
    }

}
