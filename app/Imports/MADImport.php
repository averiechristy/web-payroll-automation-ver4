<?php

namespace App\Imports;

use App\Models\Gaji;
use App\Models\Holiday;
use App\Models\Karyawan;
use App\Models\MAD;
use App\Models\Penempatan;
use App\Models\Product;
use App\Models\Produk;
use App\Models\Supplier;
use Carbon\Carbon;
use Exception;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStartRow;
use GuzzleHttp\Client;
use DateTime;

class MADImport implements ToModel, WithStartRow, WithHeadingRow,  WithMultipleSheets
{

    
    private $lastId;
  

    public function __construct()
    {
        $this->lastId = Penempatan::latest()->value('id') ?? 0;
        
    }
        
    public function startRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {           
        

        
$excelDate = $row['tanggal_lembur'];
$excelTimeStart = $row['jam_mulai'];
$excelTimeEnd =  $row['jam_selesai'];

       $date = Carbon::createFromDate(1900, 1, 1)->addDays($excelDate - 2);

// Konversi jam
$timeStart = Carbon::createFromTime(0, 0, 0)->addMinutes($excelTimeStart * 24 * 60);
$timeEnd = Carbon::createFromTime(0, 0, 0)->addMinutes($excelTimeEnd * 24 * 60);
  
$formattedDate = $date->format('Y-m-d');
$formattedTimeStart = $timeStart->format('H:i:s');
$formattedTimeEnd = $timeEnd->format('H:i:s');

$tanggalLembur = new DateTime($formattedDate);
$hariDalamMinggu = $tanggalLembur->format('N'); // 1 = Senin, ..., 7 = Minggu

// Cek apakah hari Sabtu (6) atau Minggu (7)
$isWeekend = ($hariDalamMinggu >= 6);
$dataholiday = Holiday::where('date', $tanggalLembur)->first();
      
$statusHari = $dataholiday -> description;

// Tentukan apakah hari libur atau hari kerja


// Calculate the difference in hours
$hoursWorked = $timeStart->diffInHours($timeEnd);


$karyawan = Karyawan::where('nama_karyawan', $row['nama_karyawan'])->first();

$karyawanid = $karyawan -> id;
$gajipokok = $karyawan->upah_pokok;


$datakaryawan = Karyawan::find($karyawanid);
  

$penempatanid = $datakaryawan->penempatan_id;
$datapenempatan = Penempatan::find($penempatanid);




if($datapenempatan -> hitung_tunjangan == "Yes"){


    $gaji = Gaji::where('karyawan_id', $karyawanid)->where('tanggal_mulai_gaji', '<=', $formattedDate)
    ->where('tanggal_selesai_gaji', '>=', $formattedDate)
    ->first();



    if (is_null($gaji)) {
        $karyawan = Karyawan::find($karyawanid);
        $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
      
        throw new Exception( "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");

       
    }

$upah = $gaji -> gaji;


$tunjangan = Gaji::where('karyawan_id', $karyawanid)->where('tanggal_mulai_tunjangan', '<=', $formattedDate)
    ->where('tanggal_selesai_tunjangan', '>=', $formattedDate)
    ->first();

    if (is_null($tunjangan)) {
         $karyawan = Karyawan::find($karyawanid);
        $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
        throw new Exception( "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");

    }

$tunjanganamount = $tunjangan->tunjangan;


$gajipokok = $upah + $tunjanganamount;

} else if ($datapenempatan -> hitung_tunjangan =="No")
{

$gaji = Gaji::where('karyawan_id', $karyawanid)->where('tanggal_mulai_gaji', '<=', $formattedDate)
->where('tanggal_selesai_gaji', '>=', $formattedDate)
->first();

if (is_null($gaji)) {
    $karyawan = Karyawan::find($karyawanid);
    $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
    throw new Exception( "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");

   
}
$tunjangan = Gaji::where('karyawan_id', $karyawanid)->where('tanggal_mulai_tunjangan', '<=', $formattedDate)
->where('tanggal_selesai_tunjangan', '>=', $formattedDate)
->first();

if (is_null($tunjangan)) {
     $karyawan = Karyawan::find($karyawanid);
    $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
    throw new Exception( "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");

   
}

$tunjanganamount = $tunjangan->tunjangan;

$upah = $gaji -> gaji;
$gajipokok = $upah;

} else if (is_null($datapenempatan->hitung_tunjangan)) {

    $gaji = Gaji::where('karyawan_id', $karyawanid)->where('tanggal_mulai_gaji', '<=', $formattedDate)
    ->where('tanggal_selesai_gaji', '>=', $formattedDate)
    ->first();

    if (is_null($gaji)) {
        $karyawan = Karyawan::find($karyawanid);
        $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
        throw new Exception( "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");

       
    }

    $tunjangan = Gaji::where('karyawan_id', $karyawanid)->where('tanggal_mulai_tunjangan', '<=', $formattedDate)
    ->where('tanggal_selesai_tunjangan', '>=', $formattedDate)
    ->first();

    if (is_null($tunjangan)) {
         $karyawan = Karyawan::find($karyawanid);
        $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
        throw new Exception( "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");

       
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

$managementfee = $karyawan->management_fee;
$amountmanagement = intval(round($managementfee  * $subtotal));

$totalsebelumppn = $subtotal + $amountmanagement;


$existingmad = MAD::where('tanggal_lembur', $formattedDate)->first();

if ($existingmad) {
    return null;
}

        $this->lastId++;

   

        return new MAD([
            'id' => $this->lastId,
            'karyawan_id' => $karyawanid,
            'tanggal_lembur' => $formattedDate,
            'jenis_hari' => $statusHari,
            'jam_mulai' => $formattedTimeStart,
            'jam_selesai' => $formattedTimeEnd,
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
            'gaji' => $upah,
            'tunjangan' => $tunjanganamount,
            'management_fee' => $managementfee,
            'management_fee_amount' => $amountmanagement,
            'total_sebelum_ppn' => $totalsebelumppn,
            'keterangan_lembur' => $row['keterangan_lembur'],
            'keterangan_perbaikan' => $row['keterangan_perbaikan'],

        ]);

    }

    public function sheets(): array
    {
        return [
            'Worksheet' => $this,
        ];
    }
}
