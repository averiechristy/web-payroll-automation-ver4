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

class DivisiImport implements ToModel, WithStartRow, WithHeadingRow, WithMultipleSheets
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

        $loggedInUser = auth()->user();
        $loggedInUsername = $loggedInUser->nama_user; 
    
        $divisi = trim($row['nama_divisi']);


        if (empty($divisi)) {
            throw new Exception("Nama Divisi harus diisi.");
        }


        $existingProduct = Divisi::where('divisi', $divisi)->first();

       

        if ($existingProduct) {
            return null;
        }

        $this->lastId++;

        return new Divisi([
            'id' => $this->lastId,
            'divisi' => $divisi,
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
