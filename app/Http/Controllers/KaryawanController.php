<?php

namespace App\Http\Controllers;

use App\Exports\TemplateKaryawan;
use App\Imports\KaryawanImport;
use App\Models\Attendance;
use App\Models\DetailAllowance;
use App\Models\DetailKompensasi;
use App\Models\DetailLembur;
use App\Models\DetailMAD;
use App\Models\DetailPayroll;
use App\Models\Gaji;
use App\Models\Karyawan;
use App\Models\KontrakKaryawan;
use App\Models\Lembur;
use App\Models\MAD;
use App\Models\Overtime;
use App\Models\Penempatan;
use App\Models\Posisi;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class KaryawanController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     public function nonaktifkaryawan(Request $request)
{
    $karyawan = Karyawan::find($request->karyawan_id);
    $loggedInUser = auth()->user();
    $loggedInUsername = $loggedInUser->nama_user;  

   
    $karyawan->tanggal_resign = $request->tanggal_resign;
    // $karyawan->status_kerja = 'Tidak Aktif';
    $karyawan->updated_by = $loggedInUsername;
    $karyawan->save();

    return redirect()->route('karyawan')->with('success', 'Karyawan berhasil dinonaktifkan.');
}


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
                'Management Fee (%)',
                'Jabatan',
                'Bagian',
                'Leader',
                'Status',
                'Tanggal Bergabung',
               
            ];

            
            if ($headingRow !== $expectedHeaders) {
              
                throw new \Exception("File tidak sesuai.");
            }
            $data = Excel::toCollection(new KaryawanImport, $file);

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

        $loggedInUser = auth()->user();
        $loggedInUsername = $loggedInUser->nama_user;  
      
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

        $tanggalawal = $request->tanggal_awal_kontrak;
        $tanggalakhir = $request->tanggal_akhir_kontrak;
        $tanggalbergabung = $request->tanggal_bergabung;

        $datapenempatan = Penempatan::find($penempatan);
       
        $managementconvert = $management / 100;

        $kodecabangbayar = $datapenempatan->kode_cabang_pembayaran;
        $rcc = $datapenempatan -> rcc_pembayaran;


      
        $existingdata = Karyawan::where('nik', $nik)->first();

        if($existingdata){
            $request->session()->flash('error', 'NIK (Employee ID) sudah terdaftar.');

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
            'kode_cabang_pembayaran' => $kodecabangbayar,
            'rcc_pembayaran' => $rcc,
            'management_fee'=>   $managementconvert,
            'leader' => $leader,
            'status_karyawan'=> $status,
            'tanggal_awal_kontrak' => $tanggalawal,
            'tanggal_akhir_kontrak' => $tanggalakhir,
            'created_by' => $loggedInUsername,
            'tanggal_bergabung' => $tanggalbergabung,
            // 'status_kerja' => "Aktif",

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
        $loggedInUser = auth()->user();
        $loggedInUsername = $loggedInUser->nama_user;  
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
        $management = $request->management_fee;
        $leader = $request->leader;
        $status = $request->status_karyawan;
        $tanggalawal = $request->tanggal_awal_kontrak;
        $tanggalakhir = $request->tanggal_akhir_kontrak;

        $datapenempatan = Penempatan::find($penempatan);
       
        $managementconvert = $management / 100;
        $kodecabangbayar = $datapenempatan->kode_cabang_pembayaran;
        $rcc = $datapenempatan -> rcc_pembayaran;

        $existingdata = Karyawan::where('nik', $nik)
        ->where('id', '!=', $id)
        ->first();

        if($existingdata){
            $request->session()->flash('error', 'NIK (Employee ID) sudah terdaftar.');

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
            'kode_cabang_pembayaran' => $kodecabangbayar,
            'rcc_pembayaran' => $rcc,
            'management_fee'=> $managementconvert,
            'leader' => $leader,
            'status_karyawan'=> $status,
            'tanggal_awal_kontrak' => $tanggalawal,
            'tanggal_akhir_kontrak' => $tanggalakhir,
            'updated_by'=> $loggedInUsername,
            // 'status_kerja' => "Aktif",

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

        if(KontrakKaryawan::where('karyawan_id', $karyawan->id)->exists()){
            $request->session()->flash('error', "Tidak dapat menghapus karyawan, karena masih ada data kontrak yang berhubungan.");
            return redirect()->route('karyawan');
        }

        if(Gaji::where('karyawan_id', $karyawan->id)->exists()){
            $request->session()->flash('error', "Tidak dapat menghapus karyawan, karena masih ada data gaji & tunjangan yang berhubungan.");
            return redirect()->route('karyawan');
        }

        if(Attendance::where('karyawan_id', $karyawan->id)->exists()){
            $request->session()->flash('error', "Tidak dapat menghapus karyawan, karena masih ada data attendance yang berhubungan.");
            return redirect()->route('karyawan');
        }

        if(Overtime::where('karyawan_id', $karyawan->id)->exists()){
            $request->session()->flash('error', "Tidak dapat menghapus karyawan, karena masih ada data overtime yang berhubungan.");
            return redirect()->route('karyawan');
        }

        if(DetailMAD::where('karyawan_id', $karyawan->id)->exists()){
            $request->session()->flash('error', "Tidak dapat menghapus karyawan, karena masih ada data MAD yang berhubungan.");
            return redirect()->route('karyawan');
        }

        if(DetailKompensasi::where('karyawan_id', $karyawan->id)->exists()){
            $request->session()->flash('error', "Tidak dapat menghapus karyawan, karena masih ada data kompensasi yang berhubungan.");
            return redirect()->route('karyawan');
        }

        if(DetailLembur::where('karyawan_id', $karyawan->id)->exists()){
            $request->session()->flash('error', "Tidak dapat menghapus karyawan, karena masih ada data lembur yang berhubungan.");
            return redirect()->route('karyawan');
        }
        if(DetailAllowance::where('karyawan_id', $karyawan->id)->exists()){
            $request->session()->flash('error', "Tidak dapat menghapus karyawan, karena masih ada data uang saku & insentif yang berhubungan.");
            return redirect()->route('karyawan');
        }

        if(DetailPayroll::where('karyawan_id', $karyawan->id)->exists()){
            $request->session()->flash('error', "Tidak dapat menghapus karyawan, karena masih ada data payroll yang berhubungan.");
            return redirect()->route('karyawan');
        }
        $karyawan->delete();

    $request->session()->flash('success', "Karyawan berhasil dihapus.");

    return redirect()->route('karyawan');
    }

}
