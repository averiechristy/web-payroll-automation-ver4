<?php

namespace App\Imports;

use App\Models\Gaji;
use App\Models\Karyawan;
use App\Models\Penempatan;
use App\Models\Posisi;
use Carbon\Carbon;
use Exception;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class GajiImport implements ToModel, WithStartRow, WithHeadingRow, WithMultipleSheets
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
       
        $gaji = $row['gaji'];
       
        if (!is_numeric($row['gaji'])) {
            throw new Exception("Format gaji $gaji tidak valid.");
        }

        $tanggalmulaigaji = $row['tanggal_mulai_gaji'];
        $tanggalselesaigaji = $row['tanggal_selesai_gaji'];
        $tanggalmulaitunjangan = $row['tanggal_mulai_tunjangan'];
        $tanggalselesaitunjangan = $row['tanggal_selesai_tunjangan'];


        if ($tanggalmulaigaji == $tanggalselesaigaji) {
            throw new Exception("Tanggal mulai gaji tidak boleh sama dengan tanggal selesai gaji.");
        }

        if ($tanggalmulaigaji > $tanggalselesaigaji) {
            throw new Exception("Tanggal mulai gaji tidak boleh lebih dari tanggal selesai gaji.");
        }

        if ($tanggalmulaitunjangan == $tanggalselesaitunjangan) {
            throw new Exception("Tanggal mulai tunjangan tidak boleh sama dengan tanggal selesai tunjangan.");
        }

        if ($tanggalmulaitunjangan > $tanggalselesaitunjangan) {
            throw new Exception("Tanggal mulai tunjangan tidak boleh lebih dari tanggal selesai tunjangan.");
        }

        $datemulaigaji = Carbon::createFromDate(1900, 1, 1)->addDays($tanggalmulaigaji - 2);
        $dateselesaigaji = Carbon::createFromDate(1900, 1, 1)->addDays($tanggalselesaigaji - 2);
        $datemulaitunjangan = Carbon::createFromDate(1900, 1, 1)->addDays($tanggalmulaitunjangan - 2);
        $dateselesaitunjangan = Carbon::createFromDate(1900, 1, 1)->addDays($tanggalselesaitunjangan - 2);

        

        $formattedDatemulaigaji = $datemulaigaji->format('Y-m-d');
        $formattedDateselesaigaji = $dateselesaigaji->format('Y-m-d');

        $formattedDatemulaitunjangan = $datemulaitunjangan->format('Y-m-d');
        $formattedDateselesaitunjangan = $dateselesaitunjangan->format('Y-m-d');

        
        $existingEntryGaji = Gaji::where('karyawan_id', $karyawanid)
            ->where(function ($query) use ($formattedDatemulaigaji, $formattedDateselesaigaji) {
                $query->where(function ($q) use ($formattedDatemulaigaji) {
                    $q->where('tanggal_mulai_gaji', '<=', $formattedDatemulaigaji)
                      ->where('tanggal_selesai_gaji', '>=', $formattedDatemulaigaji);
                })
                ->orWhere(function ($q) use ($formattedDateselesaigaji) {
                    $q->where('tanggal_mulai_gaji', '<=', $formattedDateselesaigaji)
                      ->where('tanggal_selesai_gaji', '>=', $formattedDateselesaigaji);
                });
            })
            ->first();
              
      
            if ($existingEntryGaji) {
                throw new Exception("Gaji untuk karyawan $namakaryawan sudah terdaftar pada rentang tanggal yang sama.");
            }

        $tunjangan = $row['tunjangan'];
        
        if (!is_numeric($row['tunjangan'])) {
            throw new Exception("Format tunjangan $tunjangan tidak valid.");
        }


        $existingEntryTunjangan = Gaji::where('karyawan_id', $karyawanid)
        ->where(function ($query) use ($formattedDatemulaitunjangan, $formattedDateselesaitunjangan) {
            $query->where(function ($q) use ($formattedDatemulaitunjangan) {
                $q->where('tanggal_mulai_tunjangan', '<=', $formattedDatemulaitunjangan)
                  ->where('tanggal_selesai_tunjangan', '>=', $formattedDatemulaitunjangan);
            })
            ->orWhere(function ($q) use ($formattedDateselesaitunjangan) {
                $q->where('tanggal_mulai_tunjangan', '<=', $formattedDateselesaitunjangan)
                  ->where('tanggal_selesai_tunjangan', '>=', $formattedDateselesaitunjangan);
            });
        })
        ->first();
          
  
        if ($existingEntryTunjangan) {
            throw new Exception("Tunjangan untuk karyawan $namakaryawan sudah terdaftar pada rentang tanggal yang sama.");
        }

        $this->lastId++;

        Gaji::create([
            'id' => $this->lastId,
            'karyawan_id' => $karyawanid,
            'gaji' => $row['gaji'],
            'tunjangan' => $row['tunjangan'],
            'tanggal_mulai_gaji' => $formattedDatemulaigaji,
            'tanggal_selesai_gaji' => $formattedDateselesaigaji,
            'tanggal_mulai_tunjangan' => $formattedDatemulaitunjangan,
            'tanggal_selesai_tunjangan' => $formattedDateselesaitunjangan,
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
