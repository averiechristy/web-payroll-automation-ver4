<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    use HasFactory;
    protected $fillable = [
        'nik',
        'payroll_code',
        'nama_karyawan',
        'no_amandemen',
        'nik_ktp',
        'penempatan_id',
        'posisi_id',
        'upah_pokok',
        'tunjangan_spv',
        'kode_cabang_pembayaran',
        'rcc_pembayaran',
        'management_fee',
        'jabatan',
        'bagian',
        'leader',
        'status_karyawan',
        'created_by',
        'updated_by',
    ];

    public function posisi()
    {

        return $this->belongsTo(Posisi::class, 'posisi_id');
    }

    
    public function penempatan()
    {

        return $this->belongsTo(Penempatan::class, 'penempatan_id');
    }
    public function mad()
    {

        return $this->hasMany(MAD::class);
    }

    public function gaji()
    {

        return $this->hasMany(Gaji::class);
    }

}
