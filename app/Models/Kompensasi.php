<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kompensasi extends Model
{
    use HasFactory;

    protected $fillable = [
      'bulan',
      'tahun',
      'judul',
      'status_kompensasi',
      'created_by',
    ];

    public function detailkompensasi()
    {

        return $this->hasMany(DetailKompensasi::class);
    }

    public function reportkompensasi()
    {

        return $this->hasMany(ReportKompensasi::class);
    }
}
