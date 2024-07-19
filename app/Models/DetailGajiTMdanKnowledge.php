<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailGajiTMdanKnowledge extends Model
{
    use HasFactory;

    protected $fillable = [
    'detailgajitm_id',
'karyawan_id',
'gaji_tm',
'cadangan_tfknowledge',

            ];


            public function gajitm()
            {
        
                return $this->belongsTo(GajiTMdanKnowledge::class, 'detailgajitm_id');
            }

            public function karyawan()
            {
        
                return $this->belongsTo(Karyawan::class, 'karyawan_id');
            }
       
       
}
