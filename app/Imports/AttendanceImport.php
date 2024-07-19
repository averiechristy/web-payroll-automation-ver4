<?php
namespace App\Imports;

use App\Models\Attendance;
use App\Models\Karyawan;
use App\Models\KodeAbsensi;
use Carbon\Carbon;
use Exception;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AttendanceImport implements ToModel, WithStartRow, WithHeadingRow, WithMultipleSheets
{
    private $lastId;
    private $rowNumber;
    private $allowedkaryawan = [];
    private $allowedcode = [];
    private $processedRows = [];

    public function __construct()
    {
        $this->lastId = Attendance::latest()->value('id') ?? 0;
        $this->allowedkaryawan = Karyawan::pluck('nama_karyawan')->toArray();
        $this->allowedcode = KodeAbsensi::pluck('kode')->toArray();
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
            empty($row['tanggal']) &&
            empty($row['shift']) &&
            empty($row['schedule_in']) &&
            empty($row['schedule_out']) &&
            empty($row['attendance_code']) &&
            empty($row['check_in']) &&
            empty($row['check_out']) &&
            empty($row['overtime_check_in']) &&
            empty($row['overtime_check_out'])
        ) {
            return null;
        }
    
        $attendancecode = $row['attendance_code'];

        
        if ($attendancecode === "") {
            $attendancecode = null;
        }
        if ($attendancecode !== null && !in_array($attendancecode, $this->allowedcode)) {
            throw new Exception("Kode attendance pada baris {$this->rowNumber} tidak valid.");
        }
    
        $karyawan = Karyawan::where('nama_karyawan', $row['nama_karyawan'])->first();
        if (!$karyawan) {
            throw new Exception("Karyawan pada baris {$this->rowNumber} tidak valid.");
        }
    
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
    
        $karyawanid = $karyawan->id;
        $namakaryawan = $karyawan->nama_karyawan;
    
        // Check for duplicate nama_karyawan and tanggal in the same file
        $uniqueKey = "{$row['nama_karyawan']}-{$formattedDate}";
        if (in_array($uniqueKey, $this->processedRows)) {
            throw new Exception("Terdapat data double karyawan {$namakaryawan} pada tanggal {$formattedDate}.");
        }
    
        $this->processedRows[] = $uniqueKey;
    
        $existingAttendance = Attendance::where('karyawan_id', $karyawanid)
            ->where('date', $formattedDate)
            ->first();
    
        if ($existingAttendance) {
            $formattedDateForException = $tanggalcarbon->format('d-m-Y');
            throw new Exception("Data kehadiran untuk karyawan {$namakaryawan} pada tanggal {$formattedDateForException} sudah terdaftar.");
        }
    
        if ($row['attendance_code'] == 'H') {
            if ($row['shift'] == 'dayoff') {
                if (empty($row['check_in']) || empty($row['check_out'])) {
                    throw new Exception("Check-in atau check-out tidak boleh kosong pada baris {$this->rowNumber}.");
                } elseif (empty($row['overtime_check_in']) || empty($row['overtime_check_out'])) {
                    throw new Exception("Overtime check-in atau overtime check-out tidak boleh kosong pada baris {$this->rowNumber}.");
                }
            } elseif ($row['shift'] == 'National Holiday') {
                if (empty($row['check_in']) || empty($row['check_out'])) {
                    throw new Exception("Check-in atau check-out tidak boleh kosong pada baris {$this->rowNumber}.");
                } elseif (empty($row['overtime_check_in']) || empty($row['overtime_check_out'])) {
                    throw new Exception("Overtime check-in atau overtime check-out tidak boleh kosong pada baris {$this->rowNumber}.");
                }
            } else {
                if (empty($row['check_in']) || empty($row['check_out'])) {
                    throw new Exception("Check-in atau check-out tidak boleh kosong pada baris {$this->rowNumber}.");
                }
            }
        }
    
        if (is_null($row['attendance_code']) && $row['shift'] != 'dayoff' && $row['shift'] != 'National Holiday') {
            throw new Exception("Attendance code tidak boleh kosong pada baris {$this->rowNumber}.");
        }
    
        if (!is_null($row['check_in']) && !is_null($row['check_out'])) {
            if ($row['check_in'] == $row['check_out']) {
                throw new Exception("Check-in dan check-out tidak boleh sama pada baris {$this->rowNumber}.");
            }
    
            if ($row['check_in'] > $row['check_out']) {
                throw new Exception("Waktu checkout tidak boleh kurang dari waktu checkin pada baris {$this->rowNumber}.");
            }
        }
    
        if (!is_null($row['overtime_check_in']) && !is_null($row['overtime_check_out'])) {
            if ($row['overtime_check_in'] == $row['overtime_check_out']) {
                throw new Exception("Overtime check-in dan overtime check-out tidak boleh sama pada baris {$this->rowNumber}.");
            }
    
            if ($row['overtime_check_in'] > $row['overtime_check_out']) {
                throw new Exception("Waktu overtime checkout tidak boleh kurang dari waktu overtime checkin pada baris {$this->rowNumber}.");
            }
        }
    
        // Handle schedule_in and schedule_out
        $schedulein = $this->convertToTime($row['schedule_in'], 'schedule_in');
        $scheduleout = $this->convertToTime($row['schedule_out'], 'schedule_out');
        $checkin = $this->convertToTime($row['check_in'], 'check_in', true);
        $checkout = $this->convertToTime($row['check_out'], 'check_out', true);
        $overtimecheckin = $this->convertToTime($row['overtime_check_in'], 'overtime_check_in', true);
        $overtimecheckout = $this->convertToTime($row['overtime_check_out'], 'overtime_check_out', true);
    
        $this->lastId++;
    
        Attendance::create([
            'id' => $this->lastId,
            'karyawan_id' => $karyawanid,
            'date' => $formattedDate,
            'shift' => $row['shift'],
            'schedule_in' => $schedulein,
            'schedule_out' => $scheduleout,
            'attendance_code' => $row['attendance_code'],
            'check_in' => $checkin,
            'check_out' => $checkout,
            'overtime_checkin' => $overtimecheckin,
            'overtime_checkout' => $overtimecheckout,
        ]);
    }
    
    private function convertToTime($time, $field, $allowNull = false)
    {
        if (is_null($time) && $allowNull) {
            return null;
        }
    
        if (is_numeric($time)) {
            // Excel time format
            $carbonTime = Carbon::createFromTime(0, 0, 0)->addMinutes($time * 24 * 60);
            return $carbonTime->format('H:i:s');
        } elseif (preg_match('/^\d{2}:\d{2}$/', $time)) {
            // Text time format (HH:MM)
            try {
                $carbonTime = Carbon::createFromFormat('H:i', $time);
                return $carbonTime->format('H:i:s');
            } catch (Exception $e) {
                throw new Exception("Format {$field} tidak valid pada baris {$this->rowNumber}.");
            }
        } else {
            throw new Exception("Format {$field} tidak valid pada baris {$this->rowNumber}.");
        }
    }
    

    public function sheets(): array
    {
        return [
            'Worksheet' => $this,
        ];
    }
}

