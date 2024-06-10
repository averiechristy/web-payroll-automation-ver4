<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penempatan extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_orange',
        'wilayah',
        'divisi',
        'kcu_induk',
        'nama_unit_kerja',
        'kode_cabang_pembayaran',
        'rcc_pembayaran',
        'singkatan_divisi',
        'kode_slid',
        'created_by',
        'updated_by',
        'hitung_tunjangan'
    ];

    public function karyawan()
    {

        return $this->hasMany(Karyawan::class);
    }
}
