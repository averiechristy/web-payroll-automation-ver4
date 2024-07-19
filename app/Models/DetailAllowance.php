<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailAllowance extends Model
{
    use HasFactory;

    protected $fillable = [
        'allowance_id',
      'karyawan_id',
      'insentif',
      'uang_saku',
       
     ];
     public function allowance()
     {
 
         return $this->belongsTo(Allowance::class, 'allowance_id');
     }

     public function karyawan()
     {
 
         return $this->belongsTo(Karyawan::class, 'karyawan_id');
     }

}
