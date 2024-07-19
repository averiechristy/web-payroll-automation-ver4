<?php

namespace App\Http\Controllers;

use App\Exports\ExportDataMAD;
use App\Exports\MADExport;
use App\Imports\MADImport;
use App\Models\Attendance;
use App\Models\DetailKonfigurasi;
use App\Models\DetailMAD;
use App\Models\Gaji;
use App\Models\Holiday;
use App\Models\Karyawan;
use App\Models\Konfigurasi;
use App\Models\MAD;
use App\Models\Organisasi;
use App\Models\Overtime;
use App\Models\Penempatan;
use App\Models\Posisi;
use App\Models\ReportMAD;
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
        $mad = MAD::orderBy('created_at','desc')->get();
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
    public function closeMad(Request $request, $id)
    {
        
        $mad = MAD::find($id);
        $mad -> status_mad = "Closing";
        $mad->save();
       

        $dataLembur = json_decode($request->input('dataLembur'), true);
      

        foreach ($dataLembur as $lembur) {

            

            $detailMAD = new DetailMAD();
            $detailMAD->mad_id = $mad->id;
            $detailMAD->karyawan_id = $lembur['karyawan_id'];
            $detailMAD->tanggal_lembur = $lembur['tanggal_lembur'];
            $detailMAD->jenis_hari = $lembur['jenis_hari'];
            $detailMAD->jam_mulai = $lembur['jam_mulai'];
            $detailMAD->jam_selesai = $lembur['jam_selesai'];
            $detailMAD->jumlah_jam_lembur = $lembur['jumlah_jam_lembur'];
            $detailMAD->jam_pertama = $lembur['jam_pertama'];
            $detailMAD->jam_kedua = $lembur['jam_kedua'];
            $detailMAD->jam_ketiga = $lembur['jam_ketiga'];
            $detailMAD->jam_keempat = $lembur['jam_keempat'];
            $detailMAD->biaya_jam_pertama = $lembur['biaya_jam_pertama'];
            $detailMAD->biaya_jam_kedua = $lembur['biaya_jam_kedua'];
            $detailMAD->biaya_jam_ketiga = $lembur['biaya_jam_ketiga'];
            $detailMAD->biaya_jam_keempat = $lembur['biaya_jam_keempat'];
            $detailMAD->subtotal = $lembur['subtotal'];
            $detailMAD->management_fee = $lembur['management_fee'];
            $detailMAD->management_fee_amount = $lembur['amount_management'];
            $detailMAD->total_sebelum_ppn = $lembur['total_sebelum_ppn'];
            $detailMAD->gaji =  $lembur['upah'];
            $detailMAD->tunjangan =  $lembur['tunjanganamount'];
            
            $detailMAD->save();
        }

        return redirect()->back()->with('success', 'MAD berhasil closing dan tersimpan.');
    }

    public function tampilmad(Request $request, $id)
    {
        $mad = MAD::find($id);
        $bulan = $mad->bulan;
        $tahun = $mad->tahun;
    
        $overtimeData = Overtime::whereYear('date', $tahun)
            ->whereMonth('date', $bulan)
            ->whereHas('karyawan', function ($query) {
                $query->whereHas('penempatan', function ($query) {
                    $query->whereHas('organisasi', function ($query) {
                        $query->where('organisasi', 'MAD');
                    });
                });
            })
        ->get();
    
        $dataLembur = [];
    
        foreach ($overtimeData as $data) {
            $tanggallembur = $data->date;
            $karyawanID = $data->karyawan_id;
            $dataKaryawan = Karyawan::find($karyawanID);
            $nama = $dataKaryawan->nama_karyawan;
            $nik = $dataKaryawan->nik_ktp;
            $noamandemen = $dataKaryawan->no_amandemen;
    
            $tanggalLembur = new DateTime($tanggallembur);
            $hariDalamMinggu = $tanggalLembur->format('N');
    
            $isWeekend = ($hariDalamMinggu >= 6);
            $dataholiday = Holiday::where('date', $tanggallembur)->first();
            $statusHari = $dataholiday ? $dataholiday->description : 'Kerja';
    
            $hoursWorked = $data->overtime_payment;
    
            $karyawanid = $data->karyawan_id;
            $datakaryawan = Karyawan::find($karyawanid);
            $penempatanid = $datakaryawan->penempatan_id;
            $datapenempatan = Penempatan::find($penempatanid);
            $organisasiid = $datapenempatan->organisasi_id;
            $dataorganisasi = Organisasi::find($organisasiid);
            $namaorganisasi = $dataorganisasi->organisasi;
            $namapenempatan = $datapenempatan->nama_unit_kerja;
            $posisiid = $datakaryawan->posisi_id;
        $dataposisi = Posisi::find($posisiid);
            $namaposisi = $dataposisi->posisi;
    
            $konfigurasi = DetailKonfigurasi::where('penempatan_id', $penempatanid)->first();
    
            if (!$konfigurasi) {
                
                if (!$konfigurasi) {
                    $request->session()->flash('error', "Konfigurasi untuk organisasi $namapenempatan belum terdaftar");
                    return redirect(route('mad'));
                }
            }
    
            $hitungTunjangan = $konfigurasi->hitung_tunjangan;
    
            if ($hitungTunjangan == "Yes") {
                $gaji = Gaji::where('karyawan_id', $karyawanid)->where('tanggal_mulai_gaji', '<=', $tanggallembur)
                    ->where('tanggal_selesai_gaji', '>=', $tanggallembur)
                    ->first();
    
                if (is_null($gaji)) {
                    $request->session()->flash('error', "Gaji untuk karyawan $nama belum ditambahkan.");
                    return redirect(route('mad'));
                }
    
                $upah = $gaji->gaji;
    
                $tunjangan = Gaji::where('karyawan_id', $karyawanid)->where('tanggal_mulai_tunjangan', '<=', $tanggallembur)
                    ->where('tanggal_selesai_tunjangan', '>=', $tanggallembur)
                    ->first();
    


                if (is_null($tunjangan)) {
                    $request->session()->flash('error', "Tunjangan untuk karyawan $nama belum ditambahkan.");
                    return redirect(route('mad'));
                }
    
                $tunjanganamount = $tunjangan->tunjangan;
                $gajipokok = $upah + $tunjanganamount;
            } else {
                $gaji = Gaji::where('karyawan_id', $karyawanid)->where('tanggal_mulai_gaji', '<=', $tanggallembur)
                    ->where('tanggal_selesai_gaji', '>=', $tanggallembur)
                    ->first();
    
                if (is_null($gaji)) {
                    $request->session()->flash('error', "Gaji untuk karyawan $nama belum ditambahkan.");
                    return redirect(route('mad'));
                }
    
                $upah = $gaji->gaji;
                $gajipokok = $upah;
                $tunjanganamount = $gaji->tunjangan;
            }
    
            if ($statusHari == "Kerja") {
                if ($hoursWorked <= 1) {
                    $jampertama = $hoursWorked;
                    $jamkedua = 0;
                    $jamketiga = 0;
                    $jamkeempat = 0;
                } else {
                    $jampertama = 1;
                    $sisajam = $hoursWorked - $jampertama;
                    $jamkedua = $sisajam;
                    $jamketiga = 0;
                    $jamkeempat = 0;
                }
    
                $biayajampertama = intval(round(($jampertama * 1.5 * $gajipokok) / 173));
                $biayajamkedua = intval(round(($jamkedua * 2 * $gajipokok) / 173));
                $biayajamketiga = 0;
                $biayajamkeempat = 0;
            } else {
                if ($hoursWorked <= 7) {
                    $jampertama = 0;
                    $jamkedua = $hoursWorked;
                    $jamketiga = 0;
                    $jamkeempat = 0;
                } else {
                    $jampertama = 0;
                    $jamkedua = 7;
                    $sisajam = $hoursWorked - 7;
                    $jamketiga = min(1, $sisajam);
                    $jamkeempat = max(0, $sisajam - 1);
                }
    
                $biayajampertama = 0;
                $biayajamkedua = intval(round(($jamkedua * 2 * $gajipokok) / 173));
                $biayajamketiga = intval(round(($jamketiga * 3 * $gajipokok) / 173));
                $biayajamkeempat = intval(round(($jamkeempat * 4 * $gajipokok) / 173));
            }
    
            $subtotal = $biayajampertama + $biayajamkedua + $biayajamketiga + $biayajamkeempat;
            $managementfee = $datakaryawan->management_fee;
            $amountmanagement = intval(round($managementfee * $subtotal));
            $totalsebelumppn = $subtotal + $amountmanagement;
    
            $attendance = Attendance::where('karyawan_id', $karyawanID)
                ->where('date', $tanggallembur)
                ->first();
    
            if (!$attendance) {
                $formattedDate = Carbon::parse($tanggallembur)->format('d-m-Y');
                $request->session()->flash('error', "Data attendance untuk $nama pada tanggal $formattedDate tidak terdaftar.");
                return redirect(route('mad'));
            }
    
            if ($statusHari == "Kerja") {
                $checkout = $attendance->check_out;
                $scheduleout = $attendance->schedule_out;
                $startTime = Carbon::createFromFormat('H:i:s', $scheduleout);
                $jammulai = $startTime;
                $jamselesai = $startTime->copy()->addHours($hoursWorked);
                $formattedjammulai = $jammulai->format('H:i:s');
                $formattedjamselesai = $jamselesai->format('H:i:s');
            } else {
                $overtimecheckin = $attendance->overtime_checkin;
                $overtimecheckout = $attendance->overtime_checkout;
                $checkin = $attendance->check_in;
                $checkout = $attendance->check_out;
    
                $jammulai = max($checkin, $overtimecheckin);
                $jamselesai = min($checkout, $overtimecheckout);
                $formattedjammulai = Carbon::createFromFormat('H:i:s', $jammulai)->format('H:i:s');
                $formattedjamselesai = Carbon::createFromFormat('H:i:s', $jamselesai)->format('H:i:s');
            }
    
            $lembur = $hoursWorked;
            $rcc = $datapenempatan->rcc_pembayaran;
            $kodepembayaran = $datapenempatan->kode_cabang_pembayaran;
            $kodeslid = $datapenempatan->kode_slid;

            
            $dataLembur[] = [
                'mad_id' => $mad->id,
                'no_amandemen' => $noamandemen,
                'nik' => $nik,
                'nama_karyawan' => $nama,
                'nama_penempatan' => $namapenempatan,
                'nama_posisi' => $namaposisi,
                'rcc' => $rcc,
                'kodepembayaran' => $kodepembayaran,
                'karyawan_id' => $karyawanID,
                'upah'=> $upah,
                'tunjanganamount' => $tunjanganamount,
                'tanggal_lembur' => $tanggallembur,
                'jenis_hari' => $statusHari,
                'jumlah_jam_lembur' => $lembur,
                'jam_mulai' => $formattedjammulai,
                'jam_selesai' => $formattedjamselesai,
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
                'amount_management' => $amountmanagement,
                'total_sebelum_ppn' => $totalsebelumppn,
                'kodeslid' => $kodeslid,
            ];
        }
        

        return view('mad.tampilmad',[
            'dataLembur' => $dataLembur,
            'mad' => $mad,
        ]);
    }
    
    public function batalkanClosing($id, Request $request)
    {
        $mad = Mad::find($id);
        $bulan = $mad->bulan;

        $timestamp = strtotime($bulan); // Ubah string tanggal ke timestamp
       
        $bulannama = Carbon::createFromFormat('m', $bulan)->translatedFormat('F');


        $tahun = $mad->tahun;

        $madid = $mad->id;

        DetailMAD::where('mad_id', $madid)->delete();


        if ($mad) {
            $mad->status_mad = 'Created';
            $mad->save();
        }
        
        return redirect()->route('mad')->with('success', "Closing laporan MAD bulan $bulannama $tahun berhasil dibatalkan");

       
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    { 

        $bulan = $request->bulan;
        $tahun = $request->tahun;

        if ($bulan == 1) {
            $bulanSebelumnya = 12;
            $tahunSebelumnya = $tahun - 1;
        } else {
            $bulanSebelumnya = $bulan - 1;
            $tahunSebelumnya = $tahun;
        }


        $checkclosing = MAD::where('bulan', $bulanSebelumnya)
                   ->where('tahun', $tahunSebelumnya)
                   ->first();
      
     

        if($checkclosing && $checkclosing->status_mad =="Created"){
            $request->session()->flash('error', "Laporan MAD pada bulan $bulanSebelumnya belum closing");
            return redirect(route('mad'));
        }

        $overtimeData = Overtime::whereYear('date', $tahun)
        ->whereMonth('date', $bulan)
        ->whereHas('karyawan', function ($query) {
            $query->whereHas('penempatan', function ($query) {
                $query->whereHas('organisasi', function ($query) {
                    $query->where('organisasi', 'MAD');
                });
            });
        })
        ->get();

       
        $loggedInUser = auth()->user(); 
        $loggedInUserName = $loggedInUser->nama_user;

        $dateObj = DateTime::createFromFormat('!m', $bulan);
        $bulanNama = $dateObj->format('F'); // This will give the full month name

        $existingdata = MAD::where('bulan', $bulan)->where('tahun', $tahun)->first();

        if ($existingdata) {
            $request->session()->flash('error', "MAD untuk bulan $bulanNama $tahun sudah dibuat.");
            return redirect()->back();
        }

        if ($overtimeData->isEmpty()) {
           
            $request->session()->flash('error', "Tidak ada data lembur pada $bulanNama $tahun");
            return redirect(route('mad'));
        }
        
        foreach($overtimeData as $data) {
            
           $tanggallembur = $data->date;
           $karyawanID = $data->karyawan_id;
           $dataKaryawan = Karyawan::find($karyawanID);
           $nama = $dataKaryawan->nama_karyawan;

           $tanggalLembur = new DateTime($tanggallembur);
           $hariDalamMinggu = $tanggalLembur->format('N'); 
       
           // Cek apakah hari Sabtu (6) atau Minggu (7)
           $isWeekend = ($hariDalamMinggu >= 6);
           $dataholiday = Holiday::where('date', $tanggallembur)->first();
           $statusHari = $dataholiday -> description;

           $hoursWorked = $data->overtime_payment;

           $karyawanid = $data->karyawan_id;
           $datakaryawan = Karyawan::find($karyawanid);
           $penempatanid = $datakaryawan->penempatan_id;
           $datapenempatan = Penempatan::find($penempatanid);
           $organisasiid = $datapenempatan->organisasi_id;
           $dataorganisasi = Organisasi::find($organisasiid);
           $namapenempatan = $datapenempatan->nama_unit_kerja;
           $namaorganisasi = $dataorganisasi->organisasi;

           $konfigurasi = DetailKonfigurasi::where('penempatan_id', $penempatanid)->first();
    
           if (!$konfigurasi) {
               
               if (!$konfigurasi) {
                   $request->session()->flash('error', "Konfigurasi untuk organisasi $namapenempatan belum terdaftar");
                   return redirect(route('mad'));
               }
           }

$hitungTunjangan = $konfigurasi->hitung_tunjangan;

if($hitungTunjangan == "Yes"){


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

} else if ($hitungTunjangan =="No")
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

} else if (is_null($hitungTunjangan)) {

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
    $sisajamone = $hoursWorked-7;
    if($sisajamone >= 1){
    $jamketiga =1;
    $sisajam = $hoursWorked - $jampertama - $jamkedua - $jamketiga;
    
    $jamkeempat = $sisajam;
    } else if($sisajamone <1){
        $jamketiga=$sisajamone;
        $jamkeempat = 0;
    }

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



$attendance = Attendance::where('karyawan_id', $karyawanID)
->where('date', $tanggallembur)
->first();

if(!$attendance)
{
    $formattedDate = Carbon::parse($tanggallembur)->format('d-m-Y');
    $request->session()->flash('error', "Data attendance untuk $nama pada tanggal $formattedDate tidak terdaftar.");
    return redirect(route('mad'));
}


 if($statusHari =="Kerja"){

    $checkout = $attendance ->check_out;
   
    $scheduleout = $attendance->schedule_out;
    $startTime = Carbon::createFromFormat('H:i:s', $scheduleout);
    $endTime = Carbon::createFromFormat('H:i:s', $checkout);
    
    

    $jammulai = $startTime;
    $jamselesai = $startTime->copy()->addHours($hoursWorked);

    $formattedjammulai = $jammulai->format('H:i:s');
$formattedjamselesai = $jamselesai->format('H:i:s');

$lembur = $hoursWorked;


 }  
 else if ($statusHari =="Libur"){

    $overtimecheckin = $attendance->overtime_checkin;
    $overtimecheckout = $attendance->overtime_checkout;
    $checkin = $attendance->check_in;
    $checkout = $attendance->check_out;

    if ($checkin > $overtimecheckin) {
        $jammulai = $checkin;
    } elseif ($checkin < $overtimecheckin) {
        $jammulai = $overtimecheckin;
    } elseif ($checkin == $overtimecheckin) {
        $jammulai = $checkin;
    }

    // Kondisi untuk checkout
    if ($checkout > $overtimecheckout) {
        $jamselesai = $overtimecheckout;
    } elseif ($checkout < $overtimecheckout) {
        $jamselesai = $checkout;
    }elseif ($checkout == $overtimecheckout) {
        $jamselesai = $checkout;
    }

    // $startTime = Carbon::createFromFormat('H:i:s', $jammulai);
    // $endTime = Carbon::createFromFormat('H:i:s', $jamselesai);
    
    // $minutesWorkedCheck = $startTime->diffInMinutes($endTime);
    // $hoursWorkedCheck = $minutesWorkedCheck / 60;

    // if($hoursWorkedCheck < $hoursWorked){
    //     $lembur = $hoursWorkedCheck;
    // } else if ($hoursWorkedCheck > $hoursWorked){
    //     $lembur = $hoursWorked;
    // } else if ($hoursWorked == $hoursWorkedCheck){
    //     $lembur = $hoursWorked;
        
    // }

    $lembur = $hoursWorked;
 }


    // DetailMAD::create([
    //         'mad_id' => $mad->id,
    //         'karyawan_id' => $karyawanID,
    //         'tanggal_lembur' => $tanggallembur,
    //         'jenis_hari' => $statusHari,
    //         'jumlah_jam_lembur' => $lembur,
    //         'jam_mulai' => $formattedjammulai,
    //         'jam_selesai' => $formattedjamselesai,
    //         'jam_pertama' => $jampertama,
    //         'jam_kedua' => $jamkedua,
    //         'jam_ketiga' => $jamketiga,
    //         'jam_keempat' => $jamkeempat,
    //         'biaya_jam_pertama' => $biayajampertama,
    //         'biaya_jam_kedua' => $biayajamkedua,
    //         'biaya_jam_ketiga' => $biayajamketiga,
    //         'biaya_jam_keempat' => $biayajamkeempat,
    //         'subtotal' => $subtotal,
    //         'management_fee' => $managementfee,
    //         'management_fee_amount' => $amountmanagement,
    //         'total_sebelum_ppn' => $totalsebelumppn,
    //         'gaji' => $upah,
    //         'tunjangan' => $tunjanganamount,
    //     ]);

        }

        $mad = MAD::create([
            'judul_mad' => "Laporan MAD $bulanNama $tahun",
            'bulan' => $bulan,
            'tahun' => $tahun,
            'created_by' => $loggedInUser->nama_user,
        ]);
        
        $request->session()->flash('success', "MAD berhasil dibuat.");

        return redirect()->route('mad');
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
    public function tampildetail($id){

      

        $mad = MAD::find($id);
        $detailmad = DetailMAD::with('mad')->where('mad_id', $id)->get();

     
        return view('mad.detail',[
            'mad' => $mad,
            'detailmad' => $detailmad,
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
