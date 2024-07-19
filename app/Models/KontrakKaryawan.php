<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KontrakKaryawan extends Model
{
    use HasFactory;

    protected $fillable = [

        'karyawan_id',
        'tanggal_awal_kontrak',
        'tanggal_akhir_kontrak',
        'created_by',
        'updated_by',
    ];
    public function karyawan()
    {

        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

}
