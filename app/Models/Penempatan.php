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
        'hitung_tunjangan',
        'divisi_id',
        'organisasi_id',
    ];

    public function karyawan()
    {
        return $this->hasMany(Karyawan::class);
    }

    public function konfigurasi()
    {
        return $this->hasMany(Konfigurasi::class);
    }

    public function detailkonfigurasi()
    {
        return $this->hasMany(DetailKonfigurasi::class);
    }

    public function invoice()
    {
        return $this->hasMany(Invoice::class);
    }
    public function organisasi()
    {

        return $this->belongsTo(Organisasi::class, 'organisasi_id');
    }

    public function divisi()
    {

        return $this->belongsTo(Divisi::class, 'divisi_id');
    }
}
