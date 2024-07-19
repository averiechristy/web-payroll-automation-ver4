<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Konfigurasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'organisasi_id',
        'penempatan_id',
        'hitung_tunjangan',
        'perhitungan_payroll',
        'created_by',
        'buat_invoice',
        'updated_by',
    ];

    public function penempatan()
    {
        return $this->belongsTo(Penempatan::class, 'penempatan_id');
    }

    public function organisasi()
    {

        return $this->belongsTo(Organisasi::class, 'organisasi_id');
    }

}
