<?php

namespace App\Imports;

use App\Models\Penempatan;
use App\Models\Posisi;
use App\Models\Product;
use App\Models\Produk;
use App\Models\Supplier;
use Exception;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;

class PosisiImport implements ToModel, WithStartRow, WithHeadingRow
{

    
    private $lastId;
 

    public function __construct()
    {
        $this->lastId = Posisi::latest()->value('id') ?? 0;
        
    }
        
    public function startRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {           
        
        $expectedHeaders = [
           'kode_orange',
            'jenis_pekerjaan',
            'posisi',
            'standarisasi_upah',
           
        ];

        $loggedInUser = auth()->user();
        $loggedInUsername = $loggedInUser->nama_user;  
    
        // Check if headers match the expected headers
        $diff = array_diff($expectedHeaders, array_keys($row));
        if (!empty($diff)) {
            throw new Exception("File tidak sesuai");
        }


        $kodeorange = trim($row['kode_orange']);

        if (empty($kodeorange)) {
            throw new Exception("Kode Orange harus diisi.");
        }

        $jeniskerja = trim( $row['jenis_pekerjaan']);


  if (empty($jeniskerja)) {
            throw new Exception("Jenis Pekerjaan harus diisi.");
        }
      
        $posisi = trim( $row['posisi']);

        if (empty($posisi)) {
            throw new Exception("Posisi harus diisi.");
        }
      

        $upah = $row['standarisasi_upah'];

        if (!is_numeric($row['standarisasi_upah'])) {
            throw new Exception("Format standarisasi upah $upah tidak valid.");
        }
    

        
  
        $existingProduct = Posisi::where('posisi', $row['posisi'])->first();
       

        if($existingProduct) {
            return null;
        }
        
      

       
        
        $this->lastId++;


        return new Posisi([
            'id' => $this->lastId,
            'kode_orange' => trim($row['kode_orange']),
            'jenis_pekerjaan' =>trim( $row['jenis_pekerjaan']),
            'posisi' => trim($row['posisi']),
            'standarisasi_upah' =>trim( $row['standarisasi_upah']),
            'created_by' => $loggedInUsername,
        ]);

    }
}
