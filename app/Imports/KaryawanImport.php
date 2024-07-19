<?php

namespace App\Imports;

use App\Models\Karyawan;
use App\Models\Penempatan;
use App\Models\Posisi;
use Exception;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class KaryawanImport implements ToModel, WithStartRow, WithHeadingRow, WithMultipleSheets
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

        if (
            empty($row['nik']) &&
            empty($row['payroll_code']) &&
            empty($row['nama']) &&
            empty($row['no_pbbamandemen']) &&
            empty($row['nik_ktp']) &&
            empty($row['unit_kerja_penempatan']) &&
            empty($row['posisi']) &&
            empty($row['management_fee']) &&
            empty($row['jabatan']) &&
            empty($row['bagian']) &&
            empty($row['leader']) &&
            empty($row['status']) &&
            empty($row['tanggal_bergabung']) 
        ) {
            return null;
        }

        $nik = trim($row['nik']);
        $payrollcode = trim($row['payroll_code']);
        $nama = trim($row['nama']);
        $noamandemen = trim($row['no_pbbamandemen']);
        $nikktp = trim($row['nik_ktp']);
        $unitkerja = trim($row['unit_kerja_penempatan']);
        $posisi = trim($row['posisi']);
       
        $management = trim($row['management_fee']);
        $jabatan = trim($row['jabatan']);
        $bagian = trim($row['bagian']);
        $leader = trim($row['leader']);
        $status = trim($row['status']);

        $tglbergabung = trim($row['tanggal_bergabung']);
      

        

        if (empty($nik)) {
            throw new Exception("NIK harus diisi.");
        }

        if (empty($payrollcode)) {
            throw new Exception("Payroll Code harus diisi.");
        }

        if (empty($nama)) {
            throw new Exception("Nama Karyawan harus diisi.");
        }

        if (empty($noamandemen)) {
            throw new Exception("No Amandemen harus diisi.");
        }

        if (empty($nikktp)) {
            throw new Exception("NIK KTP harus diisi.");
        }

        if (empty($unitkerja)) {
            throw new Exception("Unit Kerja Penempatan harus diisi.");
        }

        if (empty($posisi)) {
            throw new Exception("Posisi harus diisi.");
        }

        if (empty($management)) {
            throw new Exception("Management Fee harus diisi.");
        }

        if (empty($jabatan)) {
            throw new Exception("Jabatan harus diisi.");
        }

        if (empty($bagian)) {
            throw new Exception("Bagian harus diisi.");
        }

        if (empty($leader)) {
            throw new Exception("Leader harus diisi.");
        }

        if (empty($status)) {
            throw new Exception("Status harus diisi.");
        }

        if (empty($tglbergabung)) {
            throw new Exception("Tanggal bergabung harus diisi.");
        }

    

        $dateawalkontrak = Carbon::createFromDate(1900, 1, 1)->addDays($tglbergabung - 2);

        

        $formatteddateawalkontrak = $dateawalkontrak->format('Y-m-d');


        $datapenempatan = Penempatan::where('nama_unit_kerja', $unitkerja)->first();
        
        $penempatanid = $datapenempatan->id;
        $kodebayar = $datapenempatan->kode_cabang_pembayaran;
        $rcc = $datapenempatan->rcc_pembayaran;

        $dataposisi = Posisi::where('posisi', $posisi)->first();
        $posisiid = $dataposisi->id;
       
        $managementfee = $row['management_fee'];

        if (!is_numeric($row['management_fee'])) {
            throw new Exception("Format management fee $managementfee tidak valid.");
        }
    
        $existingkaryawan = Karyawan::where('nik', $nik)->first();

        if ($existingkaryawan) {
            return null;
        }

        $this->lastId++;

        $loggedInUser = auth()->user();
        $loggedInUsername = $loggedInUser->nama_user; 

        Karyawan::create([
            'id' => $this->lastId,
            'nik' => $nik,
            'payroll_code' => $payrollcode,
            'nama_karyawan' => $nama,
            'no_amandemen' => $noamandemen,
            'nik_ktp' => $nikktp,
            'penempatan_id' => $penempatanid,
            'posisi_id' => $posisiid,
            'kode_cabang_pembayaran' => $kodebayar,
            'rcc_pembayaran' => $rcc,
            'management_fee' => $management,
            'jabatan' => $jabatan,
            'bagian' => $bagian,
            'leader' => $leader,
            'status_karyawan' => $status,
            'tanggal_bergabung' => $formatteddateawalkontrak,
            'created_by' => $loggedInUsername,
        ]);
    }

    public function sheets(): array
    {
        return [
            'Worksheet' => $this,
        ];
    }
}

