<?php

namespace App\Imports;

use App\Models\Penempatan;
use App\Models\Product;
use App\Models\Produk;
use App\Models\Supplier;
use Exception;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;

class PenempatanImport implements ToModel, WithStartRow, WithHeadingRow
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
        
       
     
        $expectedHeaders = [
           'kode_orange',
           'wilayah',
           'divisi',
           'kcu_induk',
           'nama_unit_kerja_penempatan',
           'kode_cabang_pembayaran_untuk_vendor_mad',
           'rcc_pembayaran_untuk_vendor_mad',
           'singkatan_divisi',
           'kode_slid',
           
        ];
    
        // Check if headers match the expected headers
        $diff = array_diff($expectedHeaders, array_keys($row));
       
        if (!empty($diff)) {
            throw new Exception("File tidak sesuai");
        }

      
    
  
        $existingProduct = Penempatan::where('kode_orange', $row['kode_orange'])->first();
       

        if($existingProduct) {
            return null;
        }
        
      

       
        
        $this->lastId++;


        return new Penempatan([
            'id' => $this->lastId,
            'kode_orange' => $row['kode_orange'],
            'wilayah' => $row['wilayah'],
            'divisi' => $row['divisi'],
            'kcu_induk' => $row['kcu_induk'],
            'nama_unit_kerja' => $row['nama_unit_kerja_penempatan'],
            'kode_cabang_pembayaran' => $row['kode_cabang_pembayaran_untuk_vendor_mad'],
            'rcc_pembayaran' => $row['rcc_pembayaran_untuk_vendor_mad'],
            'singkatan_divisi' => $row['singkatan_divisi'],
            'kode_slid' => $row['kode_slid'],
            
        ]);

    }
}
