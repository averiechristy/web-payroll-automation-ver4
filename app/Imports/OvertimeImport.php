<?php

namespace App\Imports;

use App\Models\Attendance;
use App\Models\Gaji;
use App\Models\Karyawan;
use App\Models\Overtime;
use App\Models\Penempatan;
use App\Models\Posisi;
use Carbon\Carbon;
use Exception;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class OvertimeImport implements ToModel, WithStartRow, WithHeadingRow, WithMultipleSheets
{
    private $lastId;
    
    private $rowNumber;

    private $processedRows = [];
  
    public function __construct()
    {
        $this->lastId = Overtime::latest()->value('id') ?? 0;      
        $this->rowNumber = 1; 
    }
        
    public function startRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {              

       
        $this->rowNumber++;            
        if (
            empty($row['nama_karyawan']) &&
            empty($row['branch']) &&
            empty($row['tanggal']) &&
            empty($row['overtime_duration']) &&
            empty($row['overtime_payment']) &&
            empty($row['overtime_multiplier']) &&
            empty($row['overtime_rate']) 
        ) {
            return null;
        }

        if(!is_numeric($row['overtime_duration'])){
            throw new Exception("Overtime duration pada baris {$this->rowNumber} tidak valid.");
        }

        if(!is_numeric($row['overtime_payment'])){
            throw new Exception("Overtime payment pada baris {$this->rowNumber} tidak valid.");
        }

        if(!is_numeric($row['overtime_multiplier'])){
            throw new Exception("Overtime multiplier pada baris {$this->rowNumber} tidak valid.");
        }
        
        if(!is_numeric($row['overtime_rate'])){
            throw new Exception("Overtime rate pada baris {$this->rowNumber} tidak valid.");
        }

        $karyawan = Karyawan::where('nama_karyawan', $row['nama_karyawan'])->first();
        if (!$karyawan) {
            throw new Exception("Karyawan pada baris {$this->rowNumber} tidak valid.");
        }

        $karyawanid = $karyawan->id;
        $namakaryawan = $karyawan->nama_karyawan;
     
        $tanggal = $row['tanggal'];

    
        
        if (is_numeric($tanggal)) {
            // Excel date serial number format
            $tanggalcarbon = Carbon::createFromDate(1900, 1, 1)->addDays($tanggal - 2);
            $formattedDate = $tanggalcarbon->format('Y-m-d');
        } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            // Text date format (YYYY-MM-DD)
            try {
                $tanggalcarbon = Carbon::createFromFormat('Y-m-d', $tanggal);
                $formattedDate = $tanggalcarbon->format('Y-m-d');
            } catch (Exception $e) {
                throw new Exception("Format tanggal tidak valid pada baris {$this->rowNumber}.");
            }
        } else {
            throw new Exception("Format tanggal tidak valid pada baris {$this->rowNumber}.");
        }
    


        $uniqueKey = "{$row['nama_karyawan']}-{$formattedDate}";
        if (in_array($uniqueKey, $this->processedRows)) {
            throw new Exception("Terdapat data double karyawan {$namakaryawan} pada tanggal {$formattedDate}.");
        }

        $this->processedRows[] = $uniqueKey;

        $existingovertime = Overtime::where('karyawan_id', $karyawanid)
            ->where('date', $formattedDate)
            ->first();
   
        if ($existingovertime) {
            $formattedDateForException = $tanggalcarbon->format('d-m-Y');
            throw new Exception("Data lembur untuk karyawan {$namakaryawan} pada tanggal {$formattedDateForException} sudah terdaftar.");
        }

        $this->lastId++;
     
        return new Overtime([
            'id' => $this->lastId,
            'karyawan_id' => $karyawanid,
            'date' => $formattedDate,
            'branch' => $row['branch'],
            'overtime_duration' => $row['overtime_duration'],
            'overtime_payment' => $row['overtime_payment'],
            'overtime_multiplier' => $row['overtime_multiplier'],
            'overtime_rate' => $row['overtime_rate'],
        ]);
    }

    public function sheets(): array
    {
        return [
            'Worksheet' => $this,
        ];
    }
}
