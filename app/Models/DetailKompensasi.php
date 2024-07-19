<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailKompensasi extends Model
{
    use HasFactory;
    protected $fillable = [
        'kompensasi_id',
        'karyawan_id',
        'gaji',
        'tunjangan',
        'status',
        'masa_kerja',
        'total_kompensasi',
        'keterangan',
    ];


    public function kompensasi()
    {

        return $this->belongsTo(Kompensasi::class, 'kompensasi_id');
    }

    public function karyawan()
    {

        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

}
