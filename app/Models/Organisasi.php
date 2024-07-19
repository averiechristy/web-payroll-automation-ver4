<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organisasi extends Model
{
    use HasFactory;
    protected $fillable = [
        'organisasi',
        'created_by',
        'updated_by',
    ];
    public function invoice()
    {
        return $this->hasMany(Invoice::class);
    }
    
    public function testermanual()
     {
 
         return $this->hasMany(TesterManual::class);
     }
    
    public function organisasi()
    {

        return $this->hasMany(Organisasi::class);
    }

    public function penempatan()
    {
        return $this->hasMany(Penempatan::class);
    }
    public function konfigurasi()
    {
        return $this->hasMany(Konfigurasi::class);
    }

    public function lembur()
    {

        return $this->hasMany(Lembur::class);
    }
}
