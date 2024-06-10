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
    
        // Check if headers match the expected headers
        $diff = array_diff($expectedHeaders, array_keys($row));
        if (!empty($diff)) {
            throw new Exception("File tidak sesuai");
        }

      
        $upah = $row['standarisasi_upah'];

        if (!is_numeric($row['standarisasi_upah'])) {
            throw new Exception("Format standarisasi upah $upah tidak valid.");
        }
    

  
        $existingProduct = Penempatan::where('kode_orange', $row['kode_orange'])->first();
       

        if($existingProduct) {
            return null;
        }
        
      

       
        
        $this->lastId++;


        return new Posisi([
            'id' => $this->lastId,
            'kode_orange' => $row['kode_orange'],
            'jenis_pekerjaan' => $row['jenis_pekerjaan'],
            'posisi' => $row['posisi'],
            'standarisasi_upah' => $row['standarisasi_upah'],
        ]);

    }
}
