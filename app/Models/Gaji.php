<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gaji extends Model
{
    use HasFactory;

    protected $fillable = [
    'karyawan_id',
    'tunjangan',
     'gaji',
     'tanggal_mulai_gaji',
     'tanggal_selesai_gaji',
     'tanggal_mulai_tunjangan',
     'tanggal_selesai_tunjangan',
    ];

    public function karyawan()
    {

        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }
}
