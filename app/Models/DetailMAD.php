<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailMAD extends Model
{
    use HasFactory;

    protected $fillable = [
        'mad_id',
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
        'gaji',
        'tunjangan',
    ];

    public function karyawan()
    {

        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }
    public function mad()
    {

        return $this->belongsTo(MAD::class, 'mad_id');
    }
}
