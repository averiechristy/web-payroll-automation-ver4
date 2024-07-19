<?php

namespace App\Imports;

use App\Models\DetailGajiTMdanKnowledge;
use App\Models\Gaji;
use App\Models\GajiTMdanKnowledge;
use App\Models\Karyawan;
use App\Models\Penempatan;
use App\Models\Posisi;
use Carbon\Carbon;
use Exception;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class GajiTMImport implements ToModel, WithStartRow, WithHeadingRow, WithMultipleSheets
{
    private $lastId;

    public function __construct()
    {
        $this->lastId = Karyawan::latest()->value('id') ?? 0;      
    }
        
    public function startRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {
        $loggedInUser = auth()->user();
        $loggedInUsername = $loggedInUser->nama_user; 


        $karyawan = Karyawan::where('nama_karyawan', $row['nama_karyawan'])->first();
        $karyawanid = $karyawan->id;
        $namakaryawan = $karyawan->nama_karyawan;

        // Konversi nama bulan ke angka
        $row['bulan'] = $this->convertMonthToNumber($row['bulan']);

        $bulan = $row['bulan'];
        $tahun = $row['tahun'];
        $cadangan = $row['cadangan_transfer_knowledge'];
        $gaji = $row['gaji'];


        $existingdata = GajiTMdanKnowledge::where('karyawan_id', $karyawanid)
        ->where('bulan', $bulan)
        ->where('tahun', $tahun)
        ->first();

   

        if($existingdata){
            throw new Exception("Gaji dan Cadangan Transfer Knowledge untuk $namakaryawan pada bulan $bulan tahun $tahun sudah terdaftar.");
        }



        $this->lastId++;

        $gajitm = new GajiTMdanKnowledge();
        $gajitm->id = $this->lastId;
        $gajitm->bulan = $bulan;
        $gajitm->tahun = $tahun;
        $gajitm->karyawan_id = $karyawanid;
        $gajitm->gaji_tm = $gaji;
        $gajitm->cadangan_tfknowledge = $cadangan;
        $gajitm->created_by = $loggedInUsername;
        $gajitm->save();

    
    }

    private function convertMonthToNumber(string $month): int
    {
        $months = [
            'januari' => 1,
            'februari' => 2,
            'maret' => 3,
            'april' => 4,
            'mei' => 5,
            'juni' => 6,
            'juli' => 7,
            'agustus' => 8,
            'september' => 9,
            'oktober' => 10,
            'november' => 11,
            'desember' => 12,
        ];

        $month = strtolower($month); // pastikan semua huruf kecil
        return $months[$month] ?? 0; // return 0 jika tidak ditemukan
    }

    public function sheets(): array
    {
        return [
            'Worksheet' => $this,
        ];
    }
}
