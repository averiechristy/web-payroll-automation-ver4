<?php

namespace App\Imports;

use App\Models\Gaji;
use App\Models\Karyawan;
use App\Models\KontrakKaryawan;
use App\Models\Penempatan;
use App\Models\Posisi;
use Carbon\Carbon;
use Exception;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class KontrakImport implements ToModel, WithStartRow, WithHeadingRow, WithMultipleSheets
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
       $karyawanid = $karyawan ->id;
       $namakaryawan = $karyawan -> nama_karyawan;
      

        $tanggalmulaigaji = $row['tanggal_awal_kontrak'];
        $tanggalselesaigaji = $row['tanggal_akhir_kontrak'];
      
        if ($tanggalmulaigaji == $tanggalselesaigaji) {
            throw new Exception("Tanggal awal kontrak tidak boleh sama dengan tanggal akhir kontrak.");
        }

        if ($tanggalmulaigaji > $tanggalselesaigaji) {
            throw new Exception("Tanggal awal kontrak tidak boleh lebih dari tanggal akhir kontrak.");
        }

       

        $datemulaigaji = Carbon::createFromDate(1900, 1, 1)->addDays($tanggalmulaigaji - 2);
        $dateselesaigaji = Carbon::createFromDate(1900, 1, 1)->addDays($tanggalselesaigaji - 2);
    
        

        $formattedDatemulaigaji = $datemulaigaji->format('Y-m-d');
        $formattedDateselesaigaji = $dateselesaigaji->format('Y-m-d');


        
        $existingContract = KontrakKaryawan::where('karyawan_id', $karyawanid)
        ->where(function($query) use ($formattedDatemulaigaji, $formattedDateselesaigaji) {
            $query->whereBetween('tanggal_awal_kontrak', [$formattedDatemulaigaji, $formattedDateselesaigaji])
                  ->orWhereBetween('tanggal_akhir_kontrak', [$formattedDatemulaigaji, $formattedDateselesaigaji])
                  ->orWhere(function($query) use ($formattedDatemulaigaji, $formattedDateselesaigaji) {
                      $query->where('tanggal_awal_kontrak', '<=', $formattedDatemulaigaji)
                            ->where('tanggal_akhir_kontrak', '>=', $formattedDateselesaigaji);
                  });
        })
        ->first();
              
      
            if ($existingContract) {
                throw new Exception("Kontrak karyawan $namakaryawan sudah terdaftar pada rentang tanggal yang sama.");
            }

       


      
          
  
        

        $this->lastId++;

        KontrakKaryawan::create([
            'id' => $this->lastId,
            'karyawan_id' => $karyawanid,

            'tanggal_awal_kontrak' => $formattedDatemulaigaji,
            'tanggal_akhir_kontrak' => $formattedDateselesaigaji,

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
