<?php

namespace App\Imports;

use App\Models\Karyawan;
use App\Models\Penempatan;
use App\Models\Posisi;
use Exception;
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
        $expectedHeaders = [
            'nik',
            'payroll_code',
            'nama',
            'no_pbbamandemen',
            'nik_ktp',
            'unit_kerja_penempatan',
            'posisi',
            'upah_pokok',
            'tunjangan_supervisor',
            'management_fee',
            'jabatan',
            'bagian',
            'leader',
            'status',
        ];
    
        // Check if headers match the expected headers
        $diff = array_diff($expectedHeaders, array_keys($row));
       
        if (!empty($diff)) {
            throw new Exception("File tidak ssesuai");
        }

        $nik = $row['nik'];
        $payrollcode = $row['payroll_code'];
        $nama = $row['nama'];
        $noamandemen = $row['no_pbbamandemen'];
        $nikktp = $row['nik_ktp'];
        $unitkerja = $row['unit_kerja_penempatan'];
        $posisi = $row['posisi'];
        $upah = $row['upah_pokok'];
        $tunjanganspv = $row['tunjangan_supervisor'];
        $management = $row['management_fee'];
        $jabatan = $row['jabatan'];
        $bagian = $row['bagian'];
        $leader = $row['leader'];
        $status = $row['status'];

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
    

        $existingkaryawan = Karyawan::where('nama_karyawan', $nama)->first();

        if ($existingkaryawan) {
            return null;
        }

        $this->lastId++;

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
        ]);
    }

    public function sheets(): array
    {
        return [
            'Worksheet' => $this,
        ];
    }
}
