<?php

namespace App\Http\Controllers;

use App\Exports\ExportDataMAD;
use App\Exports\MADExport;
use App\Imports\MADImport;
use App\Models\Gaji;
use App\Models\Holiday;
use App\Models\Karyawan;
use App\Models\MAD;
use App\Models\Penempatan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use DateTime;
use Maatwebsite\Excel\Facades\Excel;

class MADController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    
     public function download()
     {
          return Excel::download(new MADExport(), 'templatemad.xlsx');
     }

     public function exportMad(Request $request)
     {
         $month = $request->input('month');
         $year = $request->input('year');
     
         return Excel::download(new ExportDataMAD($month, $year), 'mad_data.xlsx');
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
                'Tanggal Lembur',
                'Jam Mulai',
                'Jam Selesai',
                'Keterangan Lembur',
                'Keterangan Perbaikan'
            ];
    
            if ($headingRow !== $expectedHeaders) {
                throw new \Exception("File tidak sesuai.");
            }
            $data = Excel::toCollection(new MADImport, $file);

            if ($data->isEmpty() || $data->first()->isEmpty()) {
                throw new \Exception("File harus diisi.");

            }
            // Lakukan impor
            Excel::import(new MADImport, $file);
    
            // Jika impor berhasil, tampilkan pesan sukses
            $request->session()->flash('success', "MAD berhasil ditambahkan.");
        } catch (\Exception $e) {
            // Jika terjadi exception, tangkap dan tampilkan pesan kesalahan
            $request->session()->flash('error',   $e->getMessage());
        }
    
        return redirect()->route('mad');
     }

    public function index()
    {
        $mad = MAD::all();
        return view('mad.index',[
            'mad' => $mad
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $karyawan = Karyawan::orderBy('nama_karyawan', 'asc')->get();
        return view('mad.create',[
            'karyawan' => $karyawan,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    { 
        $karyawanid = $request->karyawan_id;
        $tanggallembur = $request->tanggal_lembur;
        $jammulai = $request->jam_mulai;
        $jamselesai = $request->jam_selesai;
        $keterangan = $request->keterangan_lembur;
        $perbaikan = $request -> keterangan_perbaikan;
    
        // Mengubah tanggal lembur menjadi objek DateTime
        $tanggalLembur = new DateTime($tanggallembur);
        $hariDalamMinggu = $tanggalLembur->format('N'); // 1 = Senin, ..., 7 = Minggu
    
        // Cek apakah hari Sabtu (6) atau Minggu (7)
        $isWeekend = ($hariDalamMinggu >= 6);
        $dataholiday = Holiday::where('date', $tanggallembur)->first();
      

        $statusHari = $dataholiday -> description;
        // Buat klien Guzzle
      
    $startTime = Carbon::createFromFormat('H:i', $jammulai);
    $endTime = Carbon::createFromFormat('H:i', $jamselesai);
  
    // Calculate the difference in hours
    $hoursWorked = $startTime->diffInHours($endTime);

    $datakaryawan = Karyawan::find($karyawanid);
  
   $penempatanid = $datakaryawan->penempatan_id;
   $datapenempatan = Penempatan::find($penempatanid);


    if($datapenempatan -> hitung_tunjangan == "Yes"){


        $gaji = Gaji::where('karyawan_id', $karyawanid)->where('tanggal_mulai_gaji', '<=', $tanggallembur)
        ->where('tanggal_selesai_gaji', '>=', $tanggallembur)
        ->first();

  

        if (is_null($gaji)) {
            $karyawan = Karyawan::find($karyawanid);
            $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
          
            $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");

            return redirect(route('mad'));
        }

    $upah = $gaji -> gaji;
    
 

    $tunjangan = Gaji::where('karyawan_id', $karyawanid)->where('tanggal_mulai_tunjangan', '<=', $tanggallembur)
        ->where('tanggal_selesai_tunjangan', '>=', $tanggallembur)
        ->first();

        if (is_null($tunjangan)) {
             $karyawan = Karyawan::find($karyawanid);
            $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
            $request->session()->flash('error', "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");

            return redirect(route('mad'));
        }

    $tunjanganamount = $tunjangan->tunjangan;


    $gajipokok = $upah + $tunjanganamount;

   } else if ($datapenempatan -> hitung_tunjangan =="No")
   {
    
    $gaji = Gaji::where('karyawan_id', $karyawanid)->where('tanggal_mulai_gaji', '<=', $tanggallembur)
    ->where('tanggal_selesai_gaji', '>=', $tanggallembur)
    ->first();

    if (is_null($gaji)) {
        $karyawan = Karyawan::find($karyawanid);
        $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
        $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");

        return redirect(route('mad'));
    }
    $tunjangan = Gaji::where('karyawan_id', $karyawanid)->where('tanggal_mulai_tunjangan', '<=', $tanggallembur)
    ->where('tanggal_selesai_tunjangan', '>=', $tanggallembur)
    ->first();

    if (is_null($tunjangan)) {
         $karyawan = Karyawan::find($karyawanid);
        $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
        $request->session()->flash('error', "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");

        return redirect(route('mad'));
    }

$tunjanganamount = $tunjangan->tunjangan;

    $upah = $gaji -> gaji;
    $gajipokok = $upah;
    
    } else if (is_null($datapenempatan->hitung_tunjangan)) {

        $gaji = Gaji::where('karyawan_id', $karyawanid)->where('tanggal_mulai_gaji', '<=', $tanggallembur)
        ->where('tanggal_selesai_gaji', '>=', $tanggallembur)
        ->first();
    
        if (is_null($gaji)) {
            $karyawan = Karyawan::find($karyawanid);
            $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
            $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
    
            return redirect(route('mad'));
        }
    
        $tunjangan = Gaji::where('karyawan_id', $karyawanid)->where('tanggal_mulai_tunjangan', '<=', $tanggallembur)
        ->where('tanggal_selesai_tunjangan', '>=', $tanggallembur)
        ->first();

        if (is_null($tunjangan)) {
             $karyawan = Karyawan::find($karyawanid);
            $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
            $request->session()->flash('error', "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");

            return redirect(route('mad'));
        }

    $tunjanganamount = $tunjangan->tunjangan;
        $upah = $gaji -> gaji;
        $gajipokok = $upah;
        
    
    }
   

    if ($statusHari == "Kerja"){

        if($hoursWorked <=1) {
            $jampertama = $hoursWorked;
            $jamkedua = 0;
            $jamketiga =0;
            $jamkeempat = 0;
        }
        else if($hoursWorked > 1) {
            $jampertama = 1;
            $sisajam = $hoursWorked - $jampertama;
            $jamkedua = $sisajam;
            $jamketiga =0;
            $jamkeempat = 0;

        }

        $biayajampertama = intval(round(($jampertama * 1.5 * $gajipokok) / 173));
        $biayajamkedua = intval(round(($jamkedua * 2 * $gajipokok) / 173));
        $biayajamketiga = 0;
        $biayajamkeempat =0;
        
    
    
    } else if($statusHari == "Libur"){

      if ($hoursWorked <= 7) {
        $jampertama = 0;
        $jamkedua = $hoursWorked;
        $jamketiga =0;
        $jamkeempat = 0;

      }
      else if ($hoursWorked > 7){

        $jampertama =0;
        $jamkedua = 7;
        $jamketiga =1;
        $sisajam = $hoursWorked - $jampertama - $jamkedua - $jamketiga;
        $jamkeempat = $sisajam;

      }


$biayajampertama = 0;
$biayajamkedua = intval(round(($jamkedua * 2 * $gajipokok) / 173));
$biayajamketiga =  intval(round(($jamketiga * 3 * $gajipokok) / 173));
$biayajamkeempat = intval(round(($jamkeempat * 4 * $gajipokok) / 173));

    }
   
    $subtotal = $biayajampertama + $biayajamkedua + $biayajamketiga + $biayajamkeempat;

    $managementfee = $datakaryawan->management_fee;
    $amountmanagement = intval(round($managementfee * $subtotal));

   $totalsebelumppn = $subtotal + $amountmanagement;
           
        MAD::create([
            'karyawan_id' => $karyawanid,
            'tanggal_lembur' => $tanggallembur,
            'jenis_hari' => $statusHari,
            'jam_mulai' => $jammulai,
            'jam_selesai' => $jamselesai,
            'jumlah_jam_lembur' => $hoursWorked,
            'jam_pertama' => $jampertama,
            'jam_kedua' => $jamkedua,
            'jam_ketiga' => $jamketiga,
            'jam_keempat' => $jamkeempat,
            'biaya_jam_pertama' => $biayajampertama,
            'biaya_jam_kedua' => $biayajamkedua,
            'biaya_jam_ketiga' => $biayajamketiga,
            'biaya_jam_keempat' => $biayajamkeempat,
            'subtotal' => $subtotal,
            'management_fee' => $managementfee,
            'management_fee_amount' => $amountmanagement,
            'total_sebelum_ppn' => $totalsebelumppn,
            'keterangan_lembur' => $keterangan,
            'keterangan_perbaikan' => $perbaikan,
            'gaji' => $upah,
            'tunjangan' => $tunjanganamount,
        ]);

        $request->session()->flash('success', 'MAD berhasil ditambahkan.');

        return redirect(route('mad'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = MAD::find($id);
        
        $karyawan = Karyawan::orderBy('nama_karyawan', 'asc')->get();

        return view('mad.edit',[
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
     
      $mad = MAD::find($id);

        $karyawanid = $request->karyawan_id;
        $tanggallembur = $request->tanggal_lembur;
        $jammulai = $request->jam_mulai;
        $jamselesai = $request->jam_selesai;
        $keterangan = $request->keterangan_lembur;
        $perbaikan = $request -> keterangan_perbaikan;
    
        // Mengubah tanggal lembur menjadi objek DateTime
        $tanggalLembur = new DateTime($tanggallembur);
        $hariDalamMinggu = $tanggalLembur->format('N'); // 1 = Senin, ..., 7 = Minggu
    
        // Cek apakah hari Sabtu (6) atau Minggu (7)
        $isWeekend = ($hariDalamMinggu >= 6);

        // Buat klien Guzzle
        $dataholiday = Holiday::where('date', $tanggallembur)->first();
      

        $statusHari = $dataholiday -> description;
       


        $startTime = Carbon::createFromFormat('H:i', $jammulai);
    $endTime = Carbon::createFromFormat('H:i', $jamselesai);

   

    // Calculate the difference in hours
    $hoursWorked = $startTime->diffInHours($endTime);

    $datakaryawan = Karyawan::find($karyawanid);
    $penempatanid = $datakaryawan->penempatan_id;
    $datapenempatan = Penempatan::find($penempatanid);
 
  
 
    if($datapenempatan -> hitung_tunjangan == "Yes"){


        $gaji = Gaji::where('karyawan_id', $karyawanid)->where('tanggal_mulai_gaji', '<=', $tanggallembur)
        ->where('tanggal_selesai_gaji', '>=', $tanggallembur)
        ->first();

  

        if (is_null($gaji)) {
            $karyawan = Karyawan::find($karyawanid);
            $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
          
            $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");

            return redirect(route('mad'));
        }

    $upah = $gaji -> gaji;
    
 

    $tunjangan = Gaji::where('karyawan_id', $karyawanid)->where('tanggal_mulai_tunjangan', '<=', $tanggallembur)
        ->where('tanggal_selesai_tunjangan', '>=', $tanggallembur)
        ->first();

        if (is_null($tunjangan)) {
             $karyawan = Karyawan::find($karyawanid);
            $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
            $request->session()->flash('error', "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");

            return redirect(route('mad'));
        }

    $tunjanganamount = $tunjangan->tunjangan;


    $gajipokok = $upah + $tunjanganamount;

   } else if ($datapenempatan -> hitung_tunjangan =="No")
   {
    
    $gaji = Gaji::where('karyawan_id', $karyawanid)->where('tanggal_mulai_gaji', '<=', $tanggallembur)
    ->where('tanggal_selesai_gaji', '>=', $tanggallembur)
    ->first();

    if (is_null($gaji)) {
        $karyawan = Karyawan::find($karyawanid);
        $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
        $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");

        return redirect(route('mad'));
    }
    $tunjangan = Gaji::where('karyawan_id', $karyawanid)->where('tanggal_mulai_tunjangan', '<=', $tanggallembur)
    ->where('tanggal_selesai_tunjangan', '>=', $tanggallembur)
    ->first();

    if (is_null($tunjangan)) {
         $karyawan = Karyawan::find($karyawanid);
        $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
        $request->session()->flash('error', "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");

        return redirect(route('mad'));
    }

$tunjanganamount = $tunjangan->tunjangan;

    $upah = $gaji -> gaji;
    $gajipokok = $upah;
    
    } else if (is_null($datapenempatan->hitung_tunjangan)) {

        $gaji = Gaji::where('karyawan_id', $karyawanid)->where('tanggal_mulai_gaji', '<=', $tanggallembur)
        ->where('tanggal_selesai_gaji', '>=', $tanggallembur)
        ->first();
    
        if (is_null($gaji)) {
            $karyawan = Karyawan::find($karyawanid);
            $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
            $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
    
            return redirect(route('mad'));
        }
    
        $tunjangan = Gaji::where('karyawan_id', $karyawanid)->where('tanggal_mulai_tunjangan', '<=', $tanggallembur)
        ->where('tanggal_selesai_tunjangan', '>=', $tanggallembur)
        ->first();

        if (is_null($tunjangan)) {
             $karyawan = Karyawan::find($karyawanid);
            $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
            $request->session()->flash('error', "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");

            return redirect(route('mad'));
        }

    $tunjanganamount = $tunjangan->tunjangan;
        $upah = $gaji -> gaji;
        $gajipokok = $upah;
        
    
    }
    

    if ($statusHari == "Kerja"){

        if($hoursWorked <=1){
            $jampertama = $hoursWorked;
            $jamkedua = 0;
            $jamketiga =0;
            $jamkeempat = 0;
        }
       else if($hoursWorked > 1) {
        $jampertama = 1;

        $sisajam = $hoursWorked - $jampertama;

        $jamkedua = $sisajam;
        $jamketiga =0;
        $jamkeempat = 0;
    }

        $biayajampertama = intval(round(($jampertama * 1.5 * $gajipokok) / 173));
        $biayajamkedua = intval(round(($jamkedua * 2 * $gajipokok) / 173));
        $biayajamketiga = 0;
        $biayajamkeempat =0;
        
    
    
    } else if($statusHari == "Libur"){

      if ($hoursWorked <= 7) {
        $jampertama = 0;
        $jamkedua = $hoursWorked;
        $jamketiga =0;
        $jamkeempat = 0;

      }
      else if ($hoursWorked > 7){

        $jampertama =0;
        $jamkedua = 7;
        $jamketiga =1;
        $sisajam = $hoursWorked - $jampertama - $jamkedua - $jamketiga;
        $jamkeempat = $sisajam;

      }


    $biayajampertama = 0;
    $biayajamkedua = intval(round(($jamkedua * 2 * $gajipokok) / 173));
    $biayajamketiga =  intval(round(($jamketiga * 3 * $gajipokok) / 173));
    $biayajamkeempat = intval(round(($jamkeempat * 4 * $gajipokok) / 173));

}
   
    $subtotal = $biayajampertama + $biayajamkedua + $biayajamketiga + $biayajamkeempat;

    $managementfee = $datakaryawan->management_fee;
    $amountmanagement = intval(round($managementfee * $subtotal));

    $totalsebelumppn = $subtotal + $amountmanagement;
          
        $mad->update([
            'karyawan_id' => $karyawanid,
            'tanggal_lembur' => $tanggallembur,
            'jenis_hari' => $statusHari,
            'jam_mulai' => $jammulai,
            'jam_selesai' => $jamselesai,
            'jumlah_jam_lembur' => $hoursWorked,
            'jam_pertama' => $jampertama,
            'jam_kedua' => $jamkedua,
            'jam_ketiga' => $jamketiga,
            'jam_keempat' => $jamkeempat,
            'biaya_jam_pertama' => $biayajampertama,
            'biaya_jam_kedua' => $biayajamkedua,
            'biaya_jam_ketiga' => $biayajamketiga,
            'biaya_jam_keempat' => $biayajamkeempat,
            'subtotal' => $subtotal,
            'management_fee' => $managementfee,
            'management_fee_amount' => $amountmanagement,
            'total_sebelum_ppn' => $totalsebelumppn,
            'keterangan_lembur' => $keterangan,
            'keterangan_perbaikan' => $perbaikan,
            'gaji' => $upah,
            'tunjangan' => $tunjanganamount,
        ]);

        $request->session()->flash('success', 'MAD berhasil diubah.');

        return redirect(route('mad'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $mad = MAD::find($id);

       

    $mad->delete();

    $request->session()->flash('success', "MAD berhasil dihapus.");

    return redirect()->route('mad');
    }
}
