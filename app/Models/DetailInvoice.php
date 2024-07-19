<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailInvoice extends Model
{
    use HasFactory;
    protected $fillable = [
        'invoice_id',
        'karyawan_id',
        'gajipokok',
        'biayatransport',
        'bpjs_tk',
        'bpjs_kesehatan',
        'jaminan_pensiun',
        'management_fee',
        'total_biaya_jasa',
        'keterangan',
        'lokasi',
        'tunjangan_jabatan',
        'insentif',
        'kompensasi',
        'rapelan_bpjs',
        'total_gaji',
        'biaya_aplikasi',
        'potongan',
        'total_pembayaran',
        'total_hari_kerja',
        'realisasi_hari_kerja',
        'absen',
        'presentase_kehadiran',
        'biaya_lembur',
        'realisasi_invoice',  
        'subtotal_biaya_jasa',
    ];
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

}
