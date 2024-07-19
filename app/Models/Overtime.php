<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Overtime extends Model
{
    use HasFactory;

    protected $fillable = [
        'karyawan_id',
        'branch',
        'date',
        'overtime_duration',
        'overtime_payment',
        'overtime_multiplier',
        'overtime_rate',
        'created_by',
    ];

    public function karyawan()
    {

        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }
}
