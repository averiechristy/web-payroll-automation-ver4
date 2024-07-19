<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TesterManual extends Model
{
    use HasFactory;

    protected $fillable = [
       'organisasi_id',
'bulan',
'tahun',
'judul_invoicetm',
'created_by',
'status_invoicetm',
'management_fee',
     ];


     public function detailtestermanual()
     {
 
         return $this->hasMany(DetailTesterManual::class);
     }


     public function organisasi()
     {
         return $this->belongsTo(Organisasi::class, 'organisasi_id');
     }
 

}
