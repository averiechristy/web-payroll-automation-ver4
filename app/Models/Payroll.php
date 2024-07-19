<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'judul_payroll',
        'organisasi_id',
        'bulan',
        'tahun',
        'status_payroll',
        'created_by',  
    ];


    public function organisasi()
    {
        return $this->belongsTo(Organisasi::class, 'organisasi_id');
    }
    public function detailpayroll()
    {

        return $this->hasMany(DetailPayroll::class);
    }

    public function reportpayroll()
    {

        return $this->hasMany(ReportPayroll::class);
    }

}
