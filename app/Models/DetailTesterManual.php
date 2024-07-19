<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailTesterManual extends Model
{
    use HasFactory;

    protected $fillable = [
     
'testermanual_id',
'karyawan_id',
'tanggallembur',
'totaljamlemburharikerja',
'totaljamlemburharilibur',
'biayalemburrekap',
'totalharikerja',
'realisasiharikerja',
'absen',
'presentase_kehadiran',
'biayalembur',
'biayajasaperbulan',
'realisasiinvoice',
      ];

      public function testermanual()
      {
          return $this->belongsTo(TesterManual::class, 'testermanual_id');
      }

      public function karyawan()
      {
          return $this->belongsTo(Karyawan::class, 'karyawan_id');
      }

}
