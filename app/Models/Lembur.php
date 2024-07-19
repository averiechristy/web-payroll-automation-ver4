<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lembur extends Model
{
    use HasFactory;
    protected $fillable = [

        'judul_lembur',
        'bulan',
        'tahun',
        'organisasi_id',
        'currentbulan',
        'currenttahun',
        'status_lembur',
        'created_by'

    ];

    public function organisasi()
    {

        return $this->belongsTo(Organisasi::class, 'organisasi_id');
    }
    public function reportlembur()
    {

        return $this->hasMany(ReportLembur::class);
    }
    public function detaillembur()
    {

        return $this->hasMany(DetailLembur::class);
    }
}
