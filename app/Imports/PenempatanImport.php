<?php

namespace App\Imports;

use App\Models\Divisi;
use App\Models\Organisasi;
use App\Models\Penempatan;
use Exception;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStartRow;

class PenempatanImport implements ToModel, WithStartRow, WithHeadingRow, WithMultipleSheets
{        
    private $lastId;
    private $allowedorganisasi = [];

    private $alloweddivisi = [];

    public function __construct()
    {
        $this->lastId = Penempatan::latest()->value('id') ?? 0;
        $this->allowedorganisasi = Organisasi::pluck('organisasi')->toArray();
        $this->alloweddivisi = Divisi::pluck('divisi')->toArray();
    }
        
    public function startRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {           
        $loggedInUser = auth()->user();
        $loggedInUsername = $loggedInUser->nama_user; 

        $organisasi = trim($row['organisasi']);

        if (empty($organisasi)) {
            throw new Exception("Organisasi harus diisi.");
        }

        if (!in_array($organisasi, $this->allowedorganisasi)) {
            throw new Exception("Organisasi $organisasi tidak valid.");
        }

        $dataorganisasi = Organisasi::where('organisasi', $organisasi)->first();
        $organisasiid = $dataorganisasi->id;

        $divisi = trim($row['divisi']);

        if (empty($divisi)) {
            throw new Exception("Divisi harus diisi.");
        }

        if (!in_array($divisi, $this->alloweddivisi)) {
            throw new Exception("Divisi $divisi tidak valid.");
        }

        $kcu_induk = trim($row['kcu_induk']);

        if (empty($kcu_induk)) {
            throw new Exception("KCU Induk harus diisi.");
        }

        $datadivisi = Divisi::where('divisi', $divisi)->first();
        $divisiid = $datadivisi->id;

        $namaUnitKerja = trim($row['nama_unit_kerja_penempatan']);
        $existingProduct = Penempatan::where('nama_unit_kerja', $namaUnitKerja)->first();
       
        if (empty($namaUnitKerja)) {
            throw new Exception("Nama Unit Kerja harus diisi.");
        }

      
        $kodecabang = trim($row['kode_cabang_pembayaran_untuk_vendor_mad']);

  if (empty($kodecabang)) {
            throw new Exception("Kode Cabang Pembayaran harus diisi.");
        }


        $rcc = trim($row['rcc_pembayaran_untuk_vendor_mad']);

        if (empty($rcc)) {
            throw new Exception("RCC Pembayaran harus diisi.");
        }
        
        $singkatan = trim($row['singkatan_divisi']);

        if (empty($singkatan)) {
            throw new Exception("Singkatan Divisi harus diisi.");
        }

        $kodeslid = trim($row['kode_slid']);


     if (empty($kodeslid)) {
            throw new Exception("Kode SLID harus diisi.");
        }

        if ($existingProduct) {
            return null;
        }

        $this->lastId++;

        return new Penempatan([
            'id' => $this->lastId,
            'kode_orange' => trim($row['kode_orange']),
            'organisasi_id' => $organisasiid,
            'divisi_id' => $divisiid,
            'kcu_induk' => trim($row['kcu_induk']),
            'nama_unit_kerja' => $namaUnitKerja,
            'kode_cabang_pembayaran' => trim($row['kode_cabang_pembayaran_untuk_vendor_mad']),
            'rcc_pembayaran' => trim($row['rcc_pembayaran_untuk_vendor_mad']),
            'singkatan_divisi' => trim($row['singkatan_divisi']),
            'kode_slid' => trim($row['kode_slid']),
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
