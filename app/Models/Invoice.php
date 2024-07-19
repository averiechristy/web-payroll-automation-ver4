<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    protected $fillable = [
        'judul_invoice',
        'bulan',
        'tahun',
        'organisasi_id',
        'kode_invoice',
        'status_invoice',
        'penempatan_id',
        'created_by',
        'management_fee',
      ];

      public function organisasi()
      {
          return $this->belongsTo(Organisasi::class, 'organisasi_id');
      }

      public function penempatan()
      {
          return $this->belongsTo(Penempatan::class, 'penempatan_id');
      }

      public function detailinvoice()
      {
          return $this->hasMany(DetailInvoice::class);
      }
}
