<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailKonfigurasi extends Model
{
    use HasFactory;

    protected $fillable = [
       'konfigurasi_id',
        'penempatan_id',
        'hitung_tunjangan',
        'perhitungan_payroll',
        'buat_invoice',
      
    ];

    public function penempatan()
    {

        return $this->belongsTo(Penempatan::class, 'penempatan_id');
    }

}
