<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    use HasFactory;
    protected $fillable = [
        'nik',
        'payroll_code',
        'nama_karyawan',
        'no_amandemen',
        'nik_ktp',
        'penempatan_id',
        'posisi_id',
        'upah_pokok',
        'tunjangan_spv',
        'kode_cabang_pembayaran',
        'rcc_pembayaran',
        'management_fee',
        'jabatan',
        'bagian',
        'leader',
        'status_karyawan',
        'created_by',
        'updated_by',
        'tanggal_awal_kontrak',
        'tanggal_akhir_kontrak',
        'tanggal_bergabung',
    ];

    public function posisi()
    {
        return $this->belongsTo(Posisi::class, 'posisi_id');
    }
    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    public function detailgajitm()
    {
        return $this->hasMany(DetailGajiTMdanKnowledge::class);
    }

    public function gajitm()
    {
        return $this->hasMany(GajiTMdanKnowledge::class);
    }

    public function kontrakkaryawan()
    {
        return $this->hasMany(KontrakKaryawan::class);
    }
    public function insentif()
    {
        return $this->hasMany(Insentif::class);
    }

    public function overtime()
    {
        return $this->hasMany(Overtime::class);
    }

    public function detailpayroll()
    {
        return $this->hasMany(DetailPayroll::class);
    }

    public function detailinvoice()
      {
  
          return $this->hasMany(DetailInvoice::class);
      }

    public function uangsaku()
    {

        return $this->hasMany(UangSakuDinas::class);
    }
    public function detaillembur()
    {

        return $this->hasMany(DetailLembur::class);
    }
    public function detailkompensasi()
    {

        return $this->hasMany(DetailKompensasi::class);
    }

    public function reportpayroll()
    {

        return $this->hasMany(ReportPayroll::class);
    }

    public function reportkompensasi()
    {

        return $this->hasMany(ReportKompensasi::class);
    }

    public function detailallowance()
    {

        return $this->hasMany(DetailAllowance::class);
    }
    public function reportlembur()
    {

        return $this->hasMany(ReportLembur::class);
    }
    public function reportmad()
    {

        return $this->hasMany(ReportMAD::class);
    }
    public function penempatan()
    {

        return $this->belongsTo(Penempatan::class, 'penempatan_id');
    }
    public function detailmad()
    {

        return $this->hasMany(DetailMAD::class);
    }

    public function gaji()
    {

        return $this->hasMany(Gaji::class);
    }

}
