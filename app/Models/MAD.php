<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MAD extends Model
{
    use HasFactory;

    protected $fillable = [
        'karyawan_id',
        'tanggal_lembur',
        'jenis_hari',
        'jam_mulai',
        'jam_selesai',
        'jumlah_jam_lembur',
        'jam_pertama',
        'jam_kedua',
        'jam_ketiga',
        'jam_keempat',
        'biaya_jam_pertama',
        'biaya_jam_kedua',
        'biaya_jam_ketiga',
        'biaya_jam_keempat',
        'subtotal',
        'management_fee',
        'management_fee_amount',
        'total_sebelum_ppn',
        'keterangan_lembur',
        'keterangan_perbaikan',
        'created_by',
        'updated_by',
        'gaji',
        'tunjangan',
    ];

    public function karyawan()
    {

        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }
}
