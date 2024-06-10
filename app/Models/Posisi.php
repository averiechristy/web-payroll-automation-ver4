<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Posisi extends Model
{
    use HasFactory;
    protected $fillable = [
      'kode_orange',
      'jenis_pekerjaan',
      'posisi',
      'standarisasi_upah',
      'created_by',
      'updated_by',
    ];

    public function karyawan()
    {

        return $this->hasMany(Karyawan::class);
    }
}
