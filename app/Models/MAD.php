<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MAD extends Model
{
    use HasFactory;

    protected $fillable = [
       'judul_mad',
       'bulan',
       'tahun',
       'status_mad',
        'created_by',
        'updated_by',
     
    ];

    public function detailmad()
    {

        return $this->hasMany(DetailMAD::class);
    }
    public function reportmad()
    {

        return $this->hasMany(ReportMAD::class);
    }
}
