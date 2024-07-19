<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insentif extends Model
{
    use HasFactory;

    protected $fillable = [
        'karyawan_id',
        'bulan',
        'tahun',
        'insentif',
        'created_by',
        'updated_by',
     ];

     public function karyawan()
     {  
         return $this->belongsTo(Karyawan::class, 'karyawan_id');
     }
}
