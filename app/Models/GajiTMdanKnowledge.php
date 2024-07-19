<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GajiTMdanKnowledge extends Model
{
    use HasFactory;

    protected $fillable = [
        'bulan',
        'tahun',
        'created_by',
        'karyawan_id',
'gaji_tm',
'cadangan_tfknowledge',
    ];

    public function detailgajitm()
    {
        return $this->hasMany(DetailGajiTMdanKnowledge::class);
    }

    public function karyawan()
    {

        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

}
