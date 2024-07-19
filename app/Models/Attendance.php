<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
       'karyawan_id',
       'date',
       'shift',
       'schedule_in',
       'schedule_out',
       'attendance_code',
       'check_in',
       'check_out',
       'overtime_checkin',
       'overtime_checkout',
       'created_by'
    ];
    public function karyawan()
    {

        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }
}
