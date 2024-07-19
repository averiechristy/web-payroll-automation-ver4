<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Allowance extends Model
{
    use HasFactory;
    protected $fillable = [
       'bulan',
       'tahun',
       'created_by',
       'created_at',
       'insentif_status',
       'updated_by',
     ];

     public function detailallowance()
     {
 
         return $this->hasMany(DetailAllowance::class);
     }
}
