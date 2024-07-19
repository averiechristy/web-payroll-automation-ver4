<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportLembur extends Model
{
    use HasFactory;
    protected $fillable = [
        'lembur_id',
        'karyawan_id',
        'total_jam_pertama_hari_kerja',
        'total_jam_kedua_hari_kerja',
        'total_biaya_jam_pertama_hari_kerja',
        'total_biaya_jam_kedua_hari_kerja',
        'total_jam_kedua_hari_libur',
        'total_jam_ketiga_hari_libur',
        'total_jam_keempat_hari_libur',
        'total_biaya_jam_kedua_hari_libur',
        'total_biaya_jam_ketiga_hari_libur',
        'total_biaya_jam_keempat_hari_libur',
        'total_jam',
        'total_biaya_lembur'
    ];

    public function lembur()
    {

        return $this->belongsTo(Lembur::class, 'lembur_id');
    }

    public function karyawan()
    {

        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }
}
