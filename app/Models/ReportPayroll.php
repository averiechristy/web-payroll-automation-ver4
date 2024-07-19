<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportPayroll extends Model
{
    use HasFactory;
    protected $fillable = [
        'payroll_id',
        'karyawan_id',
        'adjusment_salary',
        'tunjangan',
        'uangsaku',
        'insentif',
        'overtime',
        'total_allowance',
        'kompensasi',
        'gajipokok',
        'total',        
    ];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class, 'payroll_id');
    }
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }


}
