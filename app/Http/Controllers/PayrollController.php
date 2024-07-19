<?php

namespace App\Http\Controllers;

use App\Models\Allowance;
use App\Models\Attendance;
use App\Models\DetailAllowance;
use App\Models\DetailKompensasi;
use App\Models\DetailKonfigurasi;
use App\Models\DetailLembur;
use App\Models\DetailPayroll;
use App\Models\Gaji;
use App\Models\Holiday;
use App\Models\Insentif;
use App\Models\Karyawan;
use App\Models\Kompensasi;
use App\Models\Konfigurasi;
use App\Models\KontrakKaryawan;
use App\Models\Lembur;
use App\Models\Organisasi;
use App\Models\Payroll;
use App\Models\Penempatan;
use App\Models\Posisi;
use App\Models\ReportPayroll;
use App\Models\UangSakuDinas;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PayrollController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payroll = Payroll::orderBy('created_at','desc')->get();
        $organisasi = Organisasi::all();
        return view('payroll.index',[
            'organisasi' => $organisasi,
            'payroll' => $payroll,
        ]);
    }


    public function tampilpayroll ($id){
        $payroll = Payroll::find($id);
        $bulan = $payroll->bulan;
        $tahun = $payroll->tahun;
        $organisasiid = $payroll->organisasi_id;


        if ($bulan == 1) {
            $bulanSebelumnya = 12;
            $tahunSebelumnya = $tahun - 1;
        } else {
            $bulanSebelumnya = $bulan - 1;
            $tahunSebelumnya = $tahun;
        }

        $checkclosing = Payroll::where('bulan', $bulanSebelumnya)
        ->where('tahun', $tahunSebelumnya)
        ->where('organisasi_id', $organisasiid)
        ->first();




        $kompensasiexsist = Kompensasi::where('bulan', $bulan)
        ->where('tahun', $tahun)
        ->where('status_kompensasi', 'Closing')
        -> first();


        $lemburexsist = Lembur::where('bulan', $bulanSebelumnya)
        ->where('tahun', $tahunSebelumnya)
        ->where('organisasi_id', $organisasiid)
        ->where('status_lembur', 'Closing')
        -> first();

    


        $dataorganisasi = Organisasi::find($organisasiid);
        $namaorganisasi =$dataorganisasi->organisasi;
        $loggedInUser = auth()->user(); 
        $loggedInUserName = $loggedInUser->nama_user;
    
        $dateObj = DateTime::createFromFormat('!m', $bulan);
        $bulanNama = $dateObj->format('F'); // This will give the full month name
        
        $startDate = "$tahun-$bulan-01";
    $endDate = date("Y-m-t", strtotime($startDate)); // Last day of the month

    // Query to get employees based on the conditions

    $karyawan = Karyawan::whereHas('penempatan', function ($query) use ($organisasiid) {
        $query->where('organisasi_id', $organisasiid);
    })->whereHas('kontrakkaryawan', function ($query) use ($startDate, $endDate) {
        $query->where(function ($query) use ($startDate, $endDate) {
            $query->where('tanggal_awal_kontrak', '<=', $endDate)
                  ->where('tanggal_akhir_kontrak', '>=', $startDate);
        })->orWhere(function ($query) use ($startDate, $endDate) {
            $query->where('tanggal_awal_kontrak', '>=', $startDate)
                  ->where('tanggal_awal_kontrak', '<=', $endDate);
        })->orWhere(function ($query) use ($startDate, $endDate) {
            $query->where('tanggal_akhir_kontrak', '>=', $startDate)
                  ->where('tanggal_akhir_kontrak', '<=', $endDate);
        });
    })->whereHas('kontrakkaryawan', function ($query) use ($bulan, $tahun) {
        $query->where(function ($query) use ($bulan, $tahun) {
            $query->whereMonth('tanggal_awal_kontrak', '!=', $bulan)
                  ->orWhereYear('tanggal_awal_kontrak', '!=', $tahun);
        })->orWhere(function ($query) use ($bulan, $tahun) {
            $query->whereMonth('tanggal_awal_kontrak', $bulan)
                  ->whereYear('tanggal_awal_kontrak', $tahun)
                  ->whereDay('tanggal_awal_kontrak', '<', 15);
        });
    })->get();

    foreach ($karyawan as $item){

        $tanggal_awal = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
$tanggal_akhir = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();
      
        $karyawanid = $item->id;
        $penempatanid = $item->penempatan_id;
        $tanggalresign = $item->tanggal_resign;


        $kontrak = KontrakKaryawan::where('karyawan_id', $karyawanid)
        ->where(function ($query) use ($tanggal_awal, $tanggal_akhir) {
            $query->whereBetween('tanggal_awal_kontrak', [$tanggal_awal, $tanggal_akhir])
                ->orWhereBetween('tanggal_akhir_kontrak', [$tanggal_awal, $tanggal_akhir])
                ->orWhere(function ($query) use ($tanggal_awal, $tanggal_akhir) {
                    $query->where('tanggal_awal_kontrak', '<=', $tanggal_awal)
                        ->where('tanggal_akhir_kontrak', '>=', $tanggal_akhir);
                });
        })
        ->orderBy('tanggal_awal_kontrak', 'desc') // Mengurutkan berdasarkan tgl_mulai secara descending
        ->first(); // Mengambil satu data pertama (yang paling terbaru)
    


        $kontrakberakhir = $kontrak->tanggal_akhir_kontrak;
        $kontrakberakhirnew = new DateTime($kontrak->tanggal_akhir_kontrak);


       

        $bulanabis= $kontrakberakhirnew->format('n'); // bulan tanpa leading zero
        $tahunabis= $kontrakberakhirnew->format('Y'); // tahun dengan 4 digit
        $hariabis= $kontrakberakhirnew->format('j'); // hari tanpa leading zero


        $tanggalresignnew = new DateTime($item->tanggal_resign);
        $bulanresign= $tanggalresignnew->format('n'); // bulan tanpa leading zero
        $tahunresign= $tanggalresignnew->format('Y'); // tahun dengan 4 digit
        $hariresign= $tanggalresignnew->format('j'); // hari tanpa leading zero


       


        if($kontrakberakhir && $bulan == $bulanabis && $tahun == $tahunabis && $tanggalresign == null){

            

            $awalkontrak = $item->tanggal_awal_kontrak;
            $akhirkontrak = $item->tanggal_akhir_kontrak;

            $konfigurasi = DetailKonfigurasi::where('penempatan_id', $penempatanid)->first();

            $datapenempatan = Penempatan::find($penempatanid);
            $namapenempatan = $datapenempatan->nama_unit_kerja;

       
            $hitungpayroll = $konfigurasi->perhitungan_payroll;

            $hitungtujangan = $konfigurasi ->hitung_tunjangan;
          

            if($hitungpayroll == "kalender"){

                $startDateNow = "$tahun-$bulan-01";
                    $endDateNow = date("Y-m-t", strtotime($startDateNow)); // Last day of the month

                $akhir = new DateTime($kontrakberakhir);
                $awal = new DateTime($startDateNow);
                $interval = $akhir->diff($awal);
                
                $hari = $interval->days + 1; 

                 if($hitungtujangan=="Yes"){

                    $tglmulai = "$tahun-$bulan-01";
                    $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
    
                    $gaji = Gaji::where('karyawan_id', $karyawanid)
                    ->where(function($query) use ($tglmulai, $tglselesai) {
                        $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                              ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                              ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                  $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                        ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                              });
                    })
                    ->first();
                
                $tunjangandata = Gaji::where('karyawan_id', $karyawanid)
                    ->where(function($query) use ($tglmulai, $tglselesai) {
                        $query->whereBetween('tanggal_mulai_tunjangan', [$tglmulai, $tglselesai])
                              ->orWhereBetween('tanggal_selesai_tunjangan', [$tglmulai, $tglselesai])
                              ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                  $query->where('tanggal_mulai_tunjangan', '<=', $tglmulai)
                                        ->where('tanggal_selesai_tunjangan', '>=', $tglselesai);
                              });
                    })
                    ->first();
    
                        
    
                       

                       
                    
    
                       $upah = $gaji->gaji;
                       $tunjangan = $tunjangandata ->tunjangan;
                       $gajipokok = $upah + $tunjangan;
    
                    }else  if($hitungtujangan=="No"){
                        $tglmulai = "$tahun-$bulan-01";
                        $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
        
                        $gaji = Gaji::where('karyawan_id', $karyawanid)
                        ->where(function($query) use ($tglmulai, $tglselesai) {
                            $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                                  ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                                  ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                      $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                            ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                                  });
                        })
                        ->first();
                    
                   

                       
    
                       
                        $gajipokok = $gaji->gaji;
    
                    }

                    $divide = ((strtotime($endDateNow) - strtotime($startDateNow)) / (60 * 60 * 24))+1;

                    

                }else if ($hitungpayroll =="harikerja"){

                    $startDateNow = "$tahun-$bulan-01";
                    $endDateNow = date("Y-m-t", strtotime($startDateNow)); // Last day of the month

                    $holidays = Holiday::whereBetween('date', [$startDateNow, $kontrakberakhir])->get();

                    $hari = 0;

            
                    
                    foreach ($holidays as $holiday) {
                        $description = $holiday->description;

                       
                        // Cek apakah organisasiid berada dalam organisasi_pengecualian
                        if ($holiday->pengecualian_organisasi && in_array($organisasiid, json_decode($holiday->pengecualian_organisasi))) {

                           
                            // Ubah deskripsi berdasarkan kebalikan dari kondisi aslinya
                            if ($description === 'Kerja') {
                                $description = 'Libur';
                            } else {
                                $description = 'Kerja';
                            }

                            
                        }
                    
                        // Hitung hanya jika deskripsi tetap 'Kerja'
                        if ($description === 'Kerja') {
                            $hari++;
                        }
                    }

                    

                if($hitungtujangan=="Yes"){

                    $tglmulai = "$tahun-$bulan-01";
                    $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
    
                    $gaji = Gaji::where('karyawan_id', $karyawanid)
                    ->where(function($query) use ($tglmulai, $tglselesai) {
                        $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                              ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                              ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                  $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                        ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                              });
                    })
                    ->first();
                
                $tunjangandata = Gaji::where('karyawan_id', $karyawanid)
                    ->where(function($query) use ($tglmulai, $tglselesai) {
                        $query->whereBetween('tanggal_mulai_tunjangan', [$tglmulai, $tglselesai])
                              ->orWhereBetween('tanggal_selesai_tunjangan', [$tglmulai, $tglselesai])
                              ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                  $query->where('tanggal_mulai_tunjangan', '<=', $tglmulai)
                                        ->where('tanggal_selesai_tunjangan', '>=', $tglselesai);
                              });
                    })
                    ->first();
    
                        
    
                      
                     
    
                       $upah = $gaji->gaji;
                       $tunjangan = $tunjangandata ->tunjangan;
                       $gajipokok = $upah + $tunjangan;
                     
                }else  if($hitungtujangan=="No"){

                    $tglmulai = "$tahun-$bulan-01";
                    $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
                    $gaji = Gaji::where('karyawan_id', $karyawanid)
                    ->where(function($query) use ($tglmulai, $tglselesai) {
                        $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                              ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                              ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                  $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                        ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                              });
                    })
                    ->first();
                
               
                        
    
                       
                     
                        
                        $gajipokok = $gaji->gaji;
    
                }

                    $mulaidate = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
                    $akhirdate = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();
            
                
                    $holidays = Holiday::whereBetween('date', [$mulaidate, $akhirdate])->get();

                    $divide = 0;

            
                    
                    foreach ($holidays as $holiday) {
                        $description = $holiday->description;

                       
                        // Cek apakah organisasiid berada dalam organisasi_pengecualian
                        if ($holiday->pengecualian_organisasi && in_array($organisasiid, json_decode($holiday->pengecualian_organisasi))) {

                           
                            // Ubah deskripsi berdasarkan kebalikan dari kondisi aslinya
                            if ($description === 'Kerja') {
                                $description = 'Libur';
                            } else {
                                $description = 'Kerja';
                            }

                            
                        }
                    
                        // Hitung hanya jika deskripsi tetap 'Kerja'
                        if ($description === 'Kerja') {
                            $divide++;
                        }
                    }



                }
            
              $gajiabis=  intval(round((($hari / $divide) *$gajipokok)));


        }

       else if ($tanggalresign && (($bulanresign < $bulan && $tahun == $tahunresign) || ($bulan == 1 && $bulanresign == 12 && $tahun == $tahunresign + 1))) {

            $gajiresignafter = 0;
        }
                   
        else if($tanggalresign && $bulan == $bulanresign && $tahun == $tahunresign){

            $awalkontrak = $item->tanggal_awal_kontrak;
            $akhirkontrak = $item->tanggal_akhir_kontrak;

            $konfigurasi = DetailKonfigurasi::where('penempatan_id', $penempatanid)->first();

            $datapenempatan = Penempatan::find($penempatanid);
            $namapenempatan = $datapenempatan->nama_unit_kerja;

       
    
            $hitungpayroll = $konfigurasi->perhitungan_payroll;

            $hitungtujangan = $konfigurasi ->hitung_tunjangan;

            if($hitungpayroll == "kalender"){

                $startDateNow = "$tahun-$bulan-01";
                    $endDateNow = date("Y-m-t", strtotime($startDateNow)); // Last day of the month

                $resign = new DateTime($tanggalresign);
                $awal = new DateTime($startDateNow);
                $interval = $resign->diff($awal);
                
                $hari = $interval->days + 1; 

              
                 if($hitungtujangan=="Yes"){

                    $tglmulai = "$tahun-$bulan-01";
                    $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
    
                    $gaji = Gaji::where('karyawan_id', $karyawanid)
                    ->where(function($query) use ($tglmulai, $tglselesai) {
                        $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                              ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                              ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                  $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                        ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                              });
                    })
                    ->first();
                
                $tunjangandata = Gaji::where('karyawan_id', $karyawanid)
                    ->where(function($query) use ($tglmulai, $tglselesai) {
                        $query->whereBetween('tanggal_mulai_tunjangan', [$tglmulai, $tglselesai])
                              ->orWhereBetween('tanggal_selesai_tunjangan', [$tglmulai, $tglselesai])
                              ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                  $query->where('tanggal_mulai_tunjangan', '<=', $tglmulai)
                                        ->where('tanggal_selesai_tunjangan', '>=', $tglselesai);
                              });
                    })
                    ->first();
                        
    
                        if (is_null($gaji)) {
                            $karyawan = Karyawan::find($karyawanid);
                            $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                          
                            $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                    
                            return redirect(route('payroll'));
                        }

                        if (is_null($tunjangandata)) {
                            $karyawan = Karyawan::find($karyawanid);
                            $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                          
                            $request->session()->flash('error', "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");
                    
                            return redirect(route('payroll'));
                        }
                    
    
                       $upah = $gaji->gaji;
                       $tunjangan = $tunjangandata ->tunjangan;
                       $gajipokok = $upah + $tunjangan;
                     
                    }else  if($hitungtujangan=="No"){
                        $tglmulai = "$tahun-$bulan-01";
                        $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
        
                        $gaji = Gaji::where('karyawan_id', $karyawanid)
                        ->where(function($query) use ($tglmulai, $tglselesai) {
                            $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                                  ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                                  ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                      $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                            ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                                  });
                        })
                        ->first();
                    
                   

                    
        
                            
        
                            if (is_null($gaji)) {
                                $karyawan = Karyawan::find($karyawanid);
                                $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                              
                                $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                        
                                return redirect(route('payroll'));
                            }

                            
                        $gajipokok = $gaji->gaji;
    
                    }

                    $divide = ((strtotime($endDateNow) - strtotime($startDateNow)) / (60 * 60 * 24))+1;

                    

                }else if ($hitungpayroll =="harikerja"){

                    $startDateNow = "$tahun-$bulan-01";
                    $endDateNow = date("Y-m-t", strtotime($startDateNow)); // Last day of the month

                    $holidays = Holiday::whereBetween('date', [$startDateNow, $tanggalresign])->get();

                    $hari = 0;

            
                    
                    foreach ($holidays as $holiday) {
                        $description = $holiday->description;

                       
                        // Cek apakah organisasiid berada dalam organisasi_pengecualian
                        if ($holiday->pengecualian_organisasi && in_array($organisasiid, json_decode($holiday->pengecualian_organisasi))) {

                           
                            // Ubah deskripsi berdasarkan kebalikan dari kondisi aslinya
                            if ($description === 'Kerja') {
                                $description = 'Libur';
                            } else {
                                $description = 'Kerja';
                            }

                            
                        }
                    
                        // Hitung hanya jika deskripsi tetap 'Kerja'
                        if ($description === 'Kerja') {
                            $hari++;
                        }
                    }

if($hitungtujangan=="Yes"){

    $tglmulai = "$tahun-$bulan-01";
    $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month

    $gaji = Gaji::where('karyawan_id', $karyawanid)
    ->where(function($query) use ($tglmulai, $tglselesai) {
        $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
              ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
              ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                  $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                        ->where('tanggal_selesai_gaji', '>=', $tglselesai);
              });
    })
    ->first();

$tunjangandata = Gaji::where('karyawan_id', $karyawanid)
    ->where(function($query) use ($tglmulai, $tglselesai) {
        $query->whereBetween('tanggal_mulai_tunjangan', [$tglmulai, $tglselesai])
              ->orWhereBetween('tanggal_selesai_tunjangan', [$tglmulai, $tglselesai])
              ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                  $query->where('tanggal_mulai_tunjangan', '<=', $tglmulai)
                        ->where('tanggal_selesai_tunjangan', '>=', $tglselesai);
              });
    })
    ->first();

        

        if (is_null($gaji)) {
            $karyawan = Karyawan::find($karyawanid);
            $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
          
            $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
    
            return redirect(route('payroll'));
        }

        if (is_null($tunjangandata)) {
            $karyawan = Karyawan::find($karyawanid);
            $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
          
            $request->session()->flash('error', "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");
    
            return redirect(route('payroll'));
        }
    

       $upah = $gaji->gaji;
       $tunjangan = $tunjangandata ->tunjangan;
       $gajipokok = $upah + $tunjangan;
    
                     
                    }else  if($hitungtujangan=="No"){


                        $tglmulai = "$tahun-$bulan-01";
                    $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
    
                    $gaji = Gaji::where('karyawan_id', $karyawanid)
                    ->where(function($query) use ($tglmulai, $tglselesai) {
                        $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                              ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                              ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                  $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                        ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                              });
                    })
                    ->first();
                
              

                   
                        
    
                        if (is_null($gaji)) {
                            $karyawan = Karyawan::find($karyawanid);
                            $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                          
                            $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                    
                            return redirect(route('payroll'));
                        }

                       
                    
    
                      
                        $gajipokok = $gaji->gaji;
    
                    }
                    $mulaidate = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
                    $akhirdate = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();
            
                
                    $holidays = Holiday::whereBetween('date', [$mulaidate, $akhirdate])->get();

                    $divide = 0;

            
                    
                    foreach ($holidays as $holiday) {
                        $description = $holiday->description;

                       
                        // Cek apakah organisasiid berada dalam organisasi_pengecualian
                        if ($holiday->pengecualian_organisasi && in_array($organisasiid, json_decode($holiday->pengecualian_organisasi))) {

                           
                            // Ubah deskripsi berdasarkan kebalikan dari kondisi aslinya
                            if ($description === 'Kerja') {
                                $description = 'Libur';
                            } else {
                                $description = 'Kerja';
                            }

                            
                        }
                    
                        // Hitung hanya jika deskripsi tetap 'Kerja'
                        if ($description === 'Kerja') {
                            $divide++;
                        }
                    }


                }
            
              $gajiresign=  intval(round((($hari / $divide) *$gajipokok)));

             

        }

        
       

            $tanggalAwalKontrak = new DateTime($item->tanggal_bergabung);
            $bulanKontrak = $tanggalAwalKontrak->format('n'); // bulan tanpa leading zero
            $tahunKontrak = $tanggalAwalKontrak->format('Y'); // tahun dengan 4 digit
            $hariKontrak = $tanggalAwalKontrak->format('j'); // hari tanpa leading zero
            $awalkontrak = $item->tanggal_bergabung;
            $akhirkontrak = $item->tanggal_akhir_kontrak;

      

            $tglmulai = "$tahun-$bulan-01";
            $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month

            $tglmulai = "$tahun-$bulan-01";
            $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month

            $gaji = Gaji::where('karyawan_id', $karyawanid)
            ->where('tanggal_mulai_gaji', '<=', $tglmulai)
            ->where('tanggal_selesai_gaji', '>=', $tglselesai)
            ->first();

            $tunjangandata = Gaji::where('karyawan_id', $karyawanid)
            ->where('tanggal_mulai_tunjangan', '<=', $tglmulai)
            ->where('tanggal_selesai_tunjangan', '>=', $tglselesai)
            ->first();

                

               

            // Adjusment Awal Join
        
            if ($bulanKontrak == $bulanSebelumnya && $tahunKontrak == $tahunSebelumnya && $hariKontrak > 15) {
            

                $konfigurasi = DetailKonfigurasi::where('penempatan_id', $penempatanid)->first();

                $datapenempatan = Penempatan::find($penempatanid);
                $namapenempatan = $datapenempatan->nama_unit_kerja;

            if (!$konfigurasi) {
                
                if (!$konfigurasi) {
                    $request->session()->flash('error', "Konfigurasi untuk organisasi $namapenempatan belum terdaftar");
                    return redirect(route('payroll'));
                }
            }
        
                $hitungpayroll = $konfigurasi->perhitungan_payroll;
    
                $hitungtujangan = $konfigurasi ->hitung_tunjangan;

              

                if($hitungpayroll == "kalender"){

                    $startDatebefore = "$tahunSebelumnya-$bulanSebelumnya-01";
                    $endDatebefore = date("Y-m-t", strtotime($startDatebefore)); // Last day of the month

                    $awalkontraktgl = $kontrak->tanggal_awal_kontrak;

                    $end = new DateTime($endDatebefore);
                    $awalKontrak = new DateTime($awalkontraktgl);
                    $interval = $end->diff($awalKontrak);
                    
                    $days = $interval->days + 1; ;

                    if($hitungtujangan=="Yes"){

                        $tglmulai = "$tahun-$bulan-01";
                        $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
        
                        $gaji = Gaji::where('karyawan_id', $karyawanid)
                        ->where(function($query) use ($tglmulai, $tglselesai) {
                            $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                                  ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                                  ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                      $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                            ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                                  });
                        })
                        ->first();
                    
                    $tunjangandata = Gaji::where('karyawan_id', $karyawanid)
                        ->where(function($query) use ($tglmulai, $tglselesai) {
                            $query->whereBetween('tanggal_mulai_tunjangan', [$tglmulai, $tglselesai])
                                  ->orWhereBetween('tanggal_selesai_tunjangan', [$tglmulai, $tglselesai])
                                  ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                      $query->where('tanggal_mulai_tunjangan', '<=', $tglmulai)
                                            ->where('tanggal_selesai_tunjangan', '>=', $tglselesai);
                                  });
                        })
                        ->first();
                            
        
                            if (is_null($gaji)) {
                                $karyawan = Karyawan::find($karyawanid);
                                $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                              
                                $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                        
                                return redirect(route('payroll'));
                            }

                            if (is_null($tunjangandata)) {
                                $karyawan = Karyawan::find($karyawanid);
                                $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                              
                                $request->session()->flash('error', "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");
                        
                                return redirect(route('payroll'));
                            }
                        
        
                           $upah = $gaji->gaji;
                           $tunjangan = $tunjangandata ->tunjangan;
                           $gajipokok = $upah + $tunjangan;
                     
                    }else  if($hitungtujangan=="No"){
                        $tglmulai = "$tahun-$bulan-01";
                        $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
        
                        $gaji = Gaji::where('karyawan_id', $karyawanid)
                        ->where(function($query) use ($tglmulai, $tglselesai) {
                            $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                                  ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                                  ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                      $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                            ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                                  });
                        })
                        ->first();
                    
                    $tunjangandata = Gaji::where('karyawan_id', $karyawanid)
                        ->where(function($query) use ($tglmulai, $tglselesai) {
                            $query->whereBetween('tanggal_mulai_tunjangan', [$tglmulai, $tglselesai])
                                  ->orWhereBetween('tanggal_selesai_tunjangan', [$tglmulai, $tglselesai])
                                  ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                      $query->where('tanggal_mulai_tunjangan', '<=', $tglmulai)
                                            ->where('tanggal_selesai_tunjangan', '>=', $tglselesai);
                                  });
                        })
                        ->first();

                   
                            
        
                            if (is_null($gaji)) {
                                $karyawan = Karyawan::find($karyawanid);
                                $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                              
                                $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                        
                                return redirect(route('payroll'));
                            }

                           
                        
        
                          
                        $gajipokok = $gaji->gaji;
    
                    }

                    $pembagi = ((strtotime($endDatebefore) - strtotime($startDatebefore)) / (60 * 60 * 24))+1;


                  
                  
                  
                    
                }else if ($hitungpayroll =="harikerja"){

                    $startDate = Carbon::createFromDate($kontrak->tanggal_awal_kontrak);
                    $endDate = Carbon::createFromDate($tahunSebelumnya, $bulanSebelumnya, 1)->endOfMonth();
                
                  

                    $holidays = Holiday::whereBetween('date', [$startDate, $endDate])->get();

                    $days = 0;

            
                    
                    foreach ($holidays as $holiday) {
                        $description = $holiday->description;

                       
                        // Cek apakah organisasiid berada dalam organisasi_pengecualian
                        if ($holiday->pengecualian_organisasi && in_array($organisasiid, json_decode($holiday->pengecualian_organisasi))) {

                           
                            // Ubah deskripsi berdasarkan kebalikan dari kondisi aslinya
                            if ($description === 'Kerja') {
                                $description = 'Libur';
                            } else {
                                $description = 'Kerja';
                            }

                            
                        }
                    
                        // Hitung hanya jika deskripsi tetap 'Kerja'
                        if ($description === 'Kerja') {
                            $days++;
                        }
                    }

                    if($hitungtujangan=="Yes"){

                        $tglmulai = "$tahun-$bulan-01";
                        $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
        
                        $gaji = Gaji::where('karyawan_id', $karyawanid)
                        ->where(function($query) use ($tglmulai, $tglselesai) {
                            $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                                  ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                                  ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                      $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                            ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                                  });
                        })
                        ->first();
                    
                    $tunjangandata = Gaji::where('karyawan_id', $karyawanid)
                        ->where(function($query) use ($tglmulai, $tglselesai) {
                            $query->whereBetween('tanggal_mulai_tunjangan', [$tglmulai, $tglselesai])
                                  ->orWhereBetween('tanggal_selesai_tunjangan', [$tglmulai, $tglselesai])
                                  ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                      $query->where('tanggal_mulai_tunjangan', '<=', $tglmulai)
                                            ->where('tanggal_selesai_tunjangan', '>=', $tglselesai);
                                  });
                        })
                        ->first();
                            
        
                            if (is_null($gaji)) {
                                $karyawan = Karyawan::find($karyawanid);
                                $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                              
                                $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                        
                                return redirect(route('payroll'));
                            }

                            if (is_null($tunjangandata)) {
                                $karyawan = Karyawan::find($karyawanid);
                                $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                              
                                $request->session()->flash('error', "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");
                        
                                return redirect(route('payroll'));
                            }
                        
        
                           $upah = $gaji->gaji;
                           $tunjangan = $tunjangandata ->tunjangan;
                           $gajipokok = $upah + $tunjangan;
    
                     
                    }else  if($hitungtujangan=="No"){
                        $tglmulai = "$tahun-$bulan-01";
                        $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
        
                        $gaji = Gaji::where('karyawan_id', $karyawanid)
                        ->where(function($query) use ($tglmulai, $tglselesai) {
                            $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                                  ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                                  ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                      $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                            ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                                  });
                        })
                        ->first();
                    
                  

                      
        
                            
        
                            if (is_null($gaji)) {
                                $karyawan = Karyawan::find($karyawanid);
                                $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                              
                                $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                        
                                return redirect(route('payroll'));
                            }

                         
        
                          
                        $gajipokok = $gaji->gaji;
    
                    }

                    $mulaidate = Carbon::createFromDate($tahunSebelumnya, $bulanSebelumnya, 1)->startOfMonth();
                    $akhirdate = Carbon::createFromDate($tahunSebelumnya, $bulanSebelumnya, 1)->endOfMonth();
                
                
                    $holidays = Holiday::whereBetween('date', [$mulaidate, $akhirdate])->get();

                    $pembagi = 0;

            
                    
                    foreach ($holidays as $holiday) {
                        $description = $holiday->description;

                       
                        // Cek apakah organisasiid berada dalam organisasi_pengecualian
                        if ($holiday->pengecualian_organisasi && in_array($organisasiid, json_decode($holiday->pengecualian_organisasi))) {

                           
                            // Ubah deskripsi berdasarkan kebalikan dari kondisi aslinya
                            if ($description === 'Kerja') {
                                $description = 'Libur';
                            } else {
                                $description = 'Kerja';
                            }

                            
                        }
                    
                        // Hitung hanya jika deskripsi tetap 'Kerja'
                        if ($description === 'Kerja') {
                            $pembagi++;
                        }
                    }
                               
                                
                         
                }

                $adjusmentawaljoin = intval(round((($days / $pembagi) *$gajipokok)));
             

        
            } elseif ($bulanKontrak == $bulan && $tahunKontrak == $tahun && $hariKontrak <=15){

                $konfigurasi = DetailKonfigurasi::where('penempatan_id', $penempatanid)->first();

                $datapenempatan = Penempatan::find($penempatanid);
                $namapenempatan = $datapenempatan->nama_unit_kerja;

            if (!$konfigurasi) {
                
                if (!$konfigurasi) {
                    $request->session()->flash('error', "Konfigurasi untuk organisasi $namapenempatan belum terdaftar");
                    return redirect(route('payroll'));
                }
            }
        
                $hitungpayroll = $konfigurasi->perhitungan_payroll;
    
                $hitungtujangan = $konfigurasi ->hitung_tunjangan;


                if($hitungpayroll == "kalender") {

                    $startDateNow = "$tahun-$bulan-01";
                    $endDateNow = date("Y-m-t", strtotime($startDateNow)); // Last day of the month

                    $awalkontraktgl = $kontrak->tanggal_awal_kontrak;

                    $end = new DateTime($endDateNow);
                    $awalKontrak = new DateTime($awalkontraktgl);
                    $interval = $end->diff($awalKontrak);
                    
                    $days = $interval->days + 1; 

                    if($hitungtujangan=="Yes"){

                        $tglmulai = "$tahun-$bulan-01";
                        $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
        
                        $gaji = Gaji::where('karyawan_id', $karyawanid)
                        ->where(function($query) use ($tglmulai, $tglselesai) {
                            $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                                  ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                                  ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                      $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                            ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                                  });
                        })
                        ->first();
                    
                    $tunjangandata = Gaji::where('karyawan_id', $karyawanid)
                        ->where(function($query) use ($tglmulai, $tglselesai) {
                            $query->whereBetween('tanggal_mulai_tunjangan', [$tglmulai, $tglselesai])
                                  ->orWhereBetween('tanggal_selesai_tunjangan', [$tglmulai, $tglselesai])
                                  ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                      $query->where('tanggal_mulai_tunjangan', '<=', $tglmulai)
                                            ->where('tanggal_selesai_tunjangan', '>=', $tglselesai);
                                  });
                        })
                        ->first();
        
                            
        
                            if (is_null($gaji)) {
                                $karyawan = Karyawan::find($karyawanid);
                                $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                              
                                $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                        
                                return redirect(route('payroll'));
                            }

                            if (is_null($tunjangandata)) {
                                $karyawan = Karyawan::find($karyawanid);
                                $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                              
                                $request->session()->flash('error', "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");
                        
                                return redirect(route('payroll'));
                            }
                        
        
                           $upah = $gaji->gaji;
                           $tunjangan = $tunjangandata ->tunjangan;
                           $gajipokok = $upah + $tunjangan;
    
                     
                    }else  if($hitungtujangan=="No"){
                        $tglmulai = "$tahun-$bulan-01";
                        $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
        
                        $gaji = Gaji::where('karyawan_id', $karyawanid)
                        ->where(function($query) use ($tglmulai, $tglselesai) {
                            $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                                  ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                                  ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                      $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                            ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                                  });
                        })
                        ->first();
                    
                  

                        
        
                            
        
                            if (is_null($gaji)) {
                                $karyawan = Karyawan::find($karyawanid);
                                $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                              
                                $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                        
                                return redirect(route('payroll'));
                            }

                          
                        
        
                         
                        $gajipokok = $gaji->gaji;
    
                    }

                    $pembagi = ((strtotime($endDateNow) - strtotime($startDateNow)) / (60 * 60 * 24))+1;





                }else if ($hitungpayroll =="harikerja"){

                    $startDate = Carbon::createFromDate($kontrak->tanggal_awal_kontrak);
                    $endDate = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();
                
                    $holidays = Holiday::whereBetween('date', [$startDate, $endDate])->get();

                    $days = 0;

            
                    
                    foreach ($holidays as $holiday) {
                        $description = $holiday->description;

                       
                        // Cek apakah organisasiid berada dalam organisasi_pengecualian
                        if ($holiday->pengecualian_organisasi && in_array($organisasiid, json_decode($holiday->pengecualian_organisasi))) {

                           
                            // Ubah deskripsi berdasarkan kebalikan dari kondisi aslinya
                            if ($description === 'Kerja') {
                                $description = 'Libur';
                            } else {
                                $description = 'Kerja';
                            }

                            
                        }
                    
                        // Hitung hanya jika deskripsi tetap 'Kerja'
                        if ($description === 'Kerja') {
                            $days++;
                        }
                    }
                    if($hitungtujangan=="Yes"){

                        $tglmulai = "$tahun-$bulan-01";
                        $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
                        $gaji = Gaji::where('karyawan_id', $karyawanid)
                        ->where(function($query) use ($tglmulai, $tglselesai) {
                            $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                                  ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                                  ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                      $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                            ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                                  });
                        })
                        ->first();
                    
                    $tunjangandata = Gaji::where('karyawan_id', $karyawanid)
                        ->where(function($query) use ($tglmulai, $tglselesai) {
                            $query->whereBetween('tanggal_mulai_tunjangan', [$tglmulai, $tglselesai])
                                  ->orWhereBetween('tanggal_selesai_tunjangan', [$tglmulai, $tglselesai])
                                  ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                      $query->where('tanggal_mulai_tunjangan', '<=', $tglmulai)
                                            ->where('tanggal_selesai_tunjangan', '>=', $tglselesai);
                                  });
                        })
                        ->first();
        
                            
        
                            if (is_null($gaji)) {
                                $karyawan = Karyawan::find($karyawanid);
                                $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                              
                                $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                        
                                return redirect(route('payroll'));
                            }

                            if (is_null($tunjangandata)) {
                                $karyawan = Karyawan::find($karyawanid);
                                $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                              
                                $request->session()->flash('error', "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");
                        
                                return redirect(route('payroll'));
                            }
                        
        
                           $upah = $gaji->gaji;
                           $tunjangan = $tunjangandata ->tunjangan;
                           $gajipokok = $upah + $tunjangan;
    
                     
                    }else  if($hitungtujangan=="No"){
                        $tglmulai = "$tahun-$bulan-01";
                        $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
        
                        $gaji = Gaji::where('karyawan_id', $karyawanid)
                        ->where(function($query) use ($tglmulai, $tglselesai) {
                            $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                                  ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                                  ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                      $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                            ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                                  });
                        })
                        ->first();
                    
                   

        
                            
        
                            if (is_null($gaji)) {
                                $karyawan = Karyawan::find($karyawanid);
                                $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                              
                                $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                        
                                return redirect(route('payroll'));
                            }

        
                          
                        $gajipokok = $gaji->gaji;
    
                    }

                    $mulaidate = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
                    $akhirdate = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();
                
                
                    $holidays = Holiday::whereBetween('date', [$mulaidate, $akhirdate])->get();

                    $pembagi = 0;

            
                    
                    foreach ($holidays as $holiday) {
                        $description = $holiday->description;

                       
                        // Cek apakah organisasiid berada dalam organisasi_pengecualian
                        if ($holiday->pengecualian_organisasi && in_array($organisasiid, json_decode($holiday->pengecualian_organisasi))) {

                           
                            // Ubah deskripsi berdasarkan kebalikan dari kondisi aslinya
                            if ($description === 'Kerja') {
                                $description = 'Libur';
                            } else {
                                $description = 'Kerja';
                            }

                            
                        }
                    
                        // Hitung hanya jika deskripsi tetap 'Kerja'
                        if ($description === 'Kerja') {
                            $pembagi++;
                        }
                    }
                    
                }

                $adjusmentawaljoin = intval(round((($days / $pembagi) *$gajipokok)));


            } else {
                $adjusmentawaljoin=0;
            }
        
           
           
            //Akhir Adjustment Awal JOin



            //AdjustmentAbsensi

            $attendanceCount = Attendance::where('karyawan_id', $karyawanid)
            ->whereIn('attendance_code', ['I', 'STD', 'UL'])
            ->whereMonth('date', $bulan)
            ->whereYear('date', $tahun)
            ->count();
        
           

            $konfigurasi = DetailKonfigurasi::where('penempatan_id', $penempatanid)->first();

            $datapenempatan = Penempatan::find($penempatanid);
            $namapenempatan = $datapenempatan->nama_unit_kerja;

        if (!$konfigurasi) {
            
            if (!$konfigurasi) {
                $request->session()->flash('error', "Konfigurasi untuk organisasi $namapenempatan belum terdaftar");
                return redirect(route('payroll'));
            }
        }
    
            $hitungpayroll = $konfigurasi->perhitungan_payroll;

            $hitungtujangan = $konfigurasi ->hitung_tunjangan;
       

        $hitungpayroll = $konfigurasi->perhitungan_payroll;

        $hitungtujangan = $konfigurasi -> hitung_tunjangan;

      
       if($attendanceCount >0){
        if($hitungpayroll =="harikerja"){

            if($hitungtujangan=="Yes"){

                $tglmulai = "$tahun-$bulan-01";
                $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
                $gaji = Gaji::where('karyawan_id', $karyawanid)
                ->where(function($query) use ($tglmulai, $tglselesai) {
                    $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                          ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                          ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                              $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                    ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                          });
                })
                ->first();
            
            $tunjangandata = Gaji::where('karyawan_id', $karyawanid)
                ->where(function($query) use ($tglmulai, $tglselesai) {
                    $query->whereBetween('tanggal_mulai_tunjangan', [$tglmulai, $tglselesai])
                          ->orWhereBetween('tanggal_selesai_tunjangan', [$tglmulai, $tglselesai])
                          ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                              $query->where('tanggal_mulai_tunjangan', '<=', $tglmulai)
                                    ->where('tanggal_selesai_tunjangan', '>=', $tglselesai);
                          });
                })
                ->first();

                    

                    if (is_null($gaji)) {
                        $karyawan = Karyawan::find($karyawanid);
                        $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                      
                        $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                
                        return redirect(route('payroll'));
                    }

                    if (is_null($tunjangandata)) {
                        $karyawan = Karyawan::find($karyawanid);
                        $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                      
                        $request->session()->flash('error', "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");
                
                        return redirect(route('payroll'));
                    }
                

                   $upah = $gaji->gaji;
                   $tunjangan = $tunjangandata ->tunjangan;
                   $gajipokok = $upah + $tunjangan;

             
            }else  if($hitungtujangan=="No"){
                $tglmulai = "$tahun-$bulan-01";
                $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month

                $gaji = Gaji::where('karyawan_id', $karyawanid)
                ->where(function($query) use ($tglmulai, $tglselesai) {
                    $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                          ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                          ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                              $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                    ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                          });
                })
                ->first();
            
            

                    

                    if (is_null($gaji)) {
                        $karyawan = Karyawan::find($karyawanid);
                        $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                      
                        $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                
                        return redirect(route('payroll'));
                    }

                   
                

                  
                $gajipokok = $gaji->gaji;

            }

            $startDate = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
$endDate = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();


          
            
            $holidays = Holiday::whereBetween('date', [$startDate, $endDate])->get();

            $harikerjacount = 0;

    
            
            foreach ($holidays as $holiday) {
                $description = $holiday->description;

               
                // Cek apakah organisasiid berada dalam organisasi_pengecualian
                if ($holiday->pengecualian_organisasi && in_array($organisasiid, json_decode($holiday->pengecualian_organisasi))) {

                   
                    // Ubah deskripsi berdasarkan kebalikan dari kondisi aslinya
                    if ($description === 'Kerja') {
                        $description = 'Libur';
                    } else {
                        $description = 'Kerja';
                    }

                    
                }
            
                // Hitung hanya jika deskripsi tetap 'Kerja'
                if ($description === 'Kerja') {
                    $harikerjacount++;
                }
            }

            $adjustmentsalaryattendance = intval(round((($attendanceCount / $harikerjacount) *$gajipokok)));

           


        }else if ($hitungpayroll=="kalender"){

            if($hitungtujangan=="Yes"){

                $tglmulai = "$tahun-$bulan-01";
                $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
                $gaji = Gaji::where('karyawan_id', $karyawanid)
                ->where(function($query) use ($tglmulai, $tglselesai) {
                    $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                          ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                          ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                              $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                    ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                          });
                })
                ->first();
            
            $tunjangandata = Gaji::where('karyawan_id', $karyawanid)
                ->where(function($query) use ($tglmulai, $tglselesai) {
                    $query->whereBetween('tanggal_mulai_tunjangan', [$tglmulai, $tglselesai])
                          ->orWhereBetween('tanggal_selesai_tunjangan', [$tglmulai, $tglselesai])
                          ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                              $query->where('tanggal_mulai_tunjangan', '<=', $tglmulai)
                                    ->where('tanggal_selesai_tunjangan', '>=', $tglselesai);
                          });
                })
                ->first();

                    

                    if (is_null($gaji)) {
                        $karyawan = Karyawan::find($karyawanid);
                        $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                      
                        $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                
                        return redirect(route('payroll'));
                    }

                    if (is_null($tunjangandata)) {
                        $karyawan = Karyawan::find($karyawanid);
                        $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                      
                        $request->session()->flash('error', "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");
                
                        return redirect(route('payroll'));
                    }
                

                   $upah = $gaji->gaji;
                   $tunjangan = $tunjangandata ->tunjangan;
                   $gajipokok = $upah + $tunjangan;

             
            }else  if($hitungtujangan=="No"){
                $tglmulai = "$tahun-$bulan-01";
                $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
                $gaji = Gaji::where('karyawan_id', $karyawanid)
                ->where(function($query) use ($tglmulai, $tglselesai) {
                    $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                          ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                          ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                              $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                    ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                          });
                })
                ->first();
            
           
                
                    

                    if (is_null($gaji)) {
                        $karyawan = Karyawan::find($karyawanid);
                        $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                      
                        $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                
                        return redirect(route('payroll'));
                    }

                   
                

                 
                $gajipokok = $gaji->gaji;

            }

            $jumlahHari = $startDate->diffInDays($endDate) + 1;
            $adjustmentsalaryattendance = intval(round((($attendanceCount / $jumlahHari) *$gajipokok)));

        }
        
    } elseif ($attendanceCount <=0){
        $adjustmentsalaryattendance = 0;

        $tglmulai = "$tahun-$bulan-01";
        $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month

        $gaji = Gaji::where('karyawan_id', $karyawanid)
        ->where(function($query) use ($tglmulai, $tglselesai) {
            $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                  ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                  ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                      $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                            ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                  });
        })
        ->first();
    
    $tunjangandata = Gaji::where('karyawan_id', $karyawanid)
        ->where(function($query) use ($tglmulai, $tglselesai) {
            $query->whereBetween('tanggal_mulai_tunjangan', [$tglmulai, $tglselesai])
                  ->orWhereBetween('tanggal_selesai_tunjangan', [$tglmulai, $tglselesai])
                  ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                      $query->where('tanggal_mulai_tunjangan', '<=', $tglmulai)
                            ->where('tanggal_selesai_tunjangan', '>=', $tglselesai);
                  });
        })
        ->first();

            

            if (is_null($gaji)) {
                $karyawan = Karyawan::find($karyawanid);
                $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
              
                $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
        
                return redirect(route('payroll'));
            }

            if (is_null($tunjangandata)) {
                $karyawan = Karyawan::find($karyawanid);
                $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
              
                $request->session()->flash('error', "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");
        
                return redirect(route('payroll'));
            }
        

        $tunjanganjabatan = $tunjangandata->tunjangan;
        $upah = $gaji -> gaji;

    }
    
    


    if ($tanggalresign && (($bulanresign < $bulan && $tahun == $tahunresign) || ($bulan == 1 && $bulanresign == 12 && $tahun == $tahunresign + 1))) {
        $tunjanganjabatan = 0;
        } else {
            $tunjanganjabatan = $gaji->tunjangan;
        }
    
        $allowance = Allowance::where('bulan', $bulan)
        ->where('tahun', $tahun)
        ->first();

        if(!$allowance){
            $request->session()->flash('error', "Silakan buat uang saku & insentif pada bulan $bulan tahun $tahun ");
            
            return redirect(route('payroll'));
        }

        $allowanceid = $allowance->id;

        $detailallowance = DetailAllowance::where('allowance_id', $allowanceid)
        ->where ('karyawan_id', $karyawanid)
        ->first();

        if ($detailallowance) {

            $uangsaku = $detailallowance->uang_saku;
            $insentif = $detailallowance->insentif;
        } else {

            $uangsaku = 0;
            $insentif =0;
        }




    $lembur = Lembur::where('bulan', $bulanSebelumnya)
    ->where('tahun', $tahunSebelumnya)
    ->where('organisasi_id', $organisasiid)
    ->first();


    $lemburid = $lembur->id;
    $detailembur = DetailLembur::where('lembur_id', $lemburid)->where('karyawan_id', $karyawanid)->first();

    
    if(!$detailembur){
        $overtime=0;
    } else{
        $overtime = $detailembur->total_biaya_lembur;
    }


    
   
      
    $kompensasi = Kompensasi::where('bulan', $bulan)
    ->where('tahun', $tahun)
    ->first();

    $kompensasiid = $kompensasi->id;

    $detailkompensasi = DetailKompensasi::where('kompensasi_id', $kompensasiid)->where('karyawan_id', $karyawanid)->first();

   

    if($hitungtujangan == "Yes"){

        $totalallowance = $tunjanganjabatan + $uangsaku + $insentif + $overtime;

    }else if ($hitungtujangan =="No"){
    
        $totalallowance =  $uangsaku + $insentif + $overtime;

    }


    if(!$detailkompensasi){
        $totalkompensasi = 0;
    }else{
      $totalkompensasi = $detailkompensasi->total_kompensasi;
    }

   
    if ($bulanKontrak == $bulan && $tahunKontrak == $tahun && $hariKontrak <=15){
        $upah = $adjusmentawaljoin;
    } else if($tanggalresign && $bulan == $bulanresign && $tahun == $tahunresign){
        $upah = $gajiresign;
    } else  if ($tanggalresign && (($bulanresign < $bulan && $tahun == $tahunresign) || ($bulan == 1 && $bulanresign == 12 && $tahun == $tahunresign + 1))) {
        $upah = $gajiresignafter;
    } else  if($kontrakberakhir && $bulan == $bulanabis && $tahun == $tahunabis && $tanggalresign == null) {
        $upah = $gajiabis;
    }
  


    if ($bulanKontrak == $bulan && $tahunKontrak == $tahun && $hariKontrak <=15){
        $adjusmentjoinhitung = 0;
    } else {
        $adjusmentjoinhitung = $adjusmentawaljoin;
    }
    
    $adjustmentsalary = $adjusmentjoinhitung - $adjustmentsalaryattendance;

   
  
    $total = $upah + $adjustmentsalary + $totalallowance + $totalkompensasi;

   
       
       
     
        $datakaryawan = Karyawan::find($karyawanid);
        $namakaryawan = $datakaryawan->nama_karyawan;
        $nik = $datakaryawan->nik;
        $payrollcode = $datakaryawan->payroll_code;

        $posisiid = $datakaryawan->posisi_id;
        $dataposisi = Posisi::find($posisiid);
        $namaposisi = $dataposisi->posisi;
        
       


        $dataToShow[] = [
            'nik' => $nik,
            'namakaryawan' => $namakaryawan,
            'payrollcode' => $payrollcode,
            'namaorganisasi' =>   $namaorganisasi,
            'namaposisi' => $namaposisi,
            'gajikaryawan' => $upah,
            
            
           'payroll_id' => $payroll->id,
           'karyawan_id' => $karyawanid,
           'adjusment_salary' => $adjustmentsalary,
           'tunjangan' => $tunjanganjabatan,
           'uangsaku' => $uangsaku,
           'insentif' => $insentif,
           'overtime' => $overtime,
           'total_allowance' => $totalallowance,
           'kompensasi' => $totalkompensasi,
           'total' => $total,
        ];

       
        }
       return view ('payroll.tampilpayroll',[
        'dataToShow' => $dataToShow,
        'payroll' => $payroll,
       ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
            
    
        $organisasiid = $request->organisasi_id;
        $bulan = $request->bulan;
        $tahun = $request->tahun;



        if ($bulan == 1) {
            $bulanSebelumnya = 12;
            $tahunSebelumnya = $tahun - 1;
        } else {
            $bulanSebelumnya = $bulan - 1;
            $tahunSebelumnya = $tahun;
        }

        $checkclosing = Payroll::where('bulan', $bulanSebelumnya)
        ->where('tahun', $tahunSebelumnya)
        ->where('organisasi_id', $organisasiid)
        ->first();


if($checkclosing && $checkclosing->status_payroll =="Created"){
 $request->session()->flash('error', "Laporan Payroll pada bulan $bulanSebelumnya belum closing");
 return redirect(route('payroll'));
}

        $kompensasiexsist = Kompensasi::where('bulan', $bulan)
        ->where('tahun', $tahun)
        ->where('status_kompensasi', 'Closing')
        -> first();


        $lemburexsist = Lembur::where('bulan', $bulanSebelumnya)
        ->where('tahun', $tahunSebelumnya)
        ->where('organisasi_id', $organisasiid)
        ->where('status_lembur', 'Closing')
        -> first();

       
        $dataorganisasi = Organisasi::find($organisasiid);
        $namaorganisasi =$dataorganisasi->organisasi;
        $loggedInUser = auth()->user(); 
        $loggedInUserName = $loggedInUser->nama_user;
    
        $dateObj = DateTime::createFromFormat('!m', $bulan);
        $bulanNama = $dateObj->format('F'); // This will give the full month name
        
    $startDate = "$tahun-$bulan-01";
    $endDate = date("Y-m-t", strtotime($startDate)); // Last day of the month

    // Query to get employees based on the conditions

    $karyawan = Karyawan::whereHas('penempatan', function ($query) use ($organisasiid) {
        $query->where('organisasi_id', $organisasiid);
    })->whereHas('kontrakkaryawan', function ($query) use ($startDate, $endDate) {
        $query->where(function ($query) use ($startDate, $endDate) {
            $query->where('tanggal_awal_kontrak', '<=', $endDate)
                  ->where('tanggal_akhir_kontrak', '>=', $startDate);
        })->orWhere(function ($query) use ($startDate, $endDate) {
            $query->where('tanggal_awal_kontrak', '>=', $startDate)
                  ->where('tanggal_awal_kontrak', '<=', $endDate);
        })->orWhere(function ($query) use ($startDate, $endDate) {
            $query->where('tanggal_akhir_kontrak', '>=', $startDate)
                  ->where('tanggal_akhir_kontrak', '<=', $endDate);
        });
    })->whereHas('kontrakkaryawan', function ($query) use ($bulan, $tahun) {
        $query->where(function ($query) use ($bulan, $tahun) {
            $query->whereMonth('tanggal_awal_kontrak', '!=', $bulan)
                  ->orWhereYear('tanggal_awal_kontrak', '!=', $tahun);
        })->orWhere(function ($query) use ($bulan, $tahun) {
            $query->whereMonth('tanggal_awal_kontrak', $bulan)
                  ->whereYear('tanggal_awal_kontrak', $tahun)
                  ->whereDay('tanggal_awal_kontrak', '<', 15);
        });
    })->get();

    



  
        $errors = [];

        
        if ($karyawan->isEmpty()) {
            $errors[] = "Tidak ada karyawan terdaftar.";
        }
        
        if (!$kompensasiexsist) {
            $errors[] = "Silakan buat laporan kompensasi terlebih dahulu sebelum membuat payroll dan pastikan sudah closing.";
        }
        
        if (!$lemburexsist) {
            $errors[] = "Silakan buat laporan lembur terlebih dahulu sebelum membuat payroll dan pastikan sudah closing.";
        }
        
        if (!empty($errors)) {
            $request->session()->flash('error', implode('<br>', $errors));
            return redirect()->route('payroll');
        }



   
    

        foreach ($karyawan as $item){

          
          
            $karyawanid = $item->id;
            $penempatanid = $item->penempatan_id;
            $tanggalresign = $item->tanggal_resign;
            $tanggal_awal = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
            $tanggal_akhir = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();

            $kontrak = KontrakKaryawan::where('karyawan_id', $karyawanid)
            ->where(function ($query) use ($tanggal_awal, $tanggal_akhir) {
                $query->whereBetween('tanggal_awal_kontrak', [$tanggal_awal, $tanggal_akhir])
                    ->orWhereBetween('tanggal_akhir_kontrak', [$tanggal_awal, $tanggal_akhir])
                    ->orWhere(function ($query) use ($tanggal_awal, $tanggal_akhir) {
                        $query->where('tanggal_awal_kontrak', '<=', $tanggal_awal)
                            ->where('tanggal_akhir_kontrak', '>=', $tanggal_akhir);
                    });
            })
            ->orderBy('tanggal_awal_kontrak', 'desc') // Mengurutkan berdasarkan tgl_mulai secara descending
            ->first(); // Mengambil satu data pertama (yang paling terbaru)
        

            if(!$kontrak){
                $karyawan = Karyawan::find($karyawanid);
                $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
              
                $request->session()->flash('error', "Kontrak untuk karyawan $namaKaryawan belum ditambahkan.");
        
                return redirect(route('payroll'));
            }

            $kontrakberakhir = $kontrak->tanggal_akhir_kontrak;
            $kontrakberakhirnew = new DateTime($kontrak->tanggal_akhir_kontrak);


           

            $bulanabis= $kontrakberakhirnew->format('n'); // bulan tanpa leading zero
            $tahunabis= $kontrakberakhirnew->format('Y'); // tahun dengan 4 digit
            $hariabis= $kontrakberakhirnew->format('j'); // hari tanpa leading zero


            $tanggalresignnew = new DateTime($item->tanggal_resign);
            $bulanresign= $tanggalresignnew->format('n'); // bulan tanpa leading zero
            $tahunresign= $tanggalresignnew->format('Y'); // tahun dengan 4 digit
            $hariresign= $tanggalresignnew->format('j'); // hari tanpa leading zero


           


            if($kontrakberakhir && $bulan == $bulanabis && $tahun == $tahunabis && $tanggalresign == null){

                

                $awalkontrak = $item->tanggal_awal_kontrak;
                $akhirkontrak = $item->tanggal_akhir_kontrak;
    
                $konfigurasi = DetailKonfigurasi::where('penempatan_id', $penempatanid)->first();
    
                $datapenempatan = Penempatan::find($penempatanid);
                $namapenempatan = $datapenempatan->nama_unit_kerja;

            if (!$konfigurasi) {
                
                if (!$konfigurasi) {
                    $request->session()->flash('error', "Konfigurasi untuk organisasi $namapenempatan belum terdaftar");
                    return redirect(route('payroll'));
                }
            }
        
                $hitungpayroll = $konfigurasi->perhitungan_payroll;
    
                $hitungtujangan = $konfigurasi ->hitung_tunjangan;
              
    
                if($hitungpayroll == "kalender"){
    
                    $startDateNow = "$tahun-$bulan-01";
                        $endDateNow = date("Y-m-t", strtotime($startDateNow)); // Last day of the month
    
                    $akhir = new DateTime($kontrakberakhir);
                    $awal = new DateTime($startDateNow);
                    $interval = $akhir->diff($awal);
                    
                    $hari = $interval->days + 1; 

                     if($hitungtujangan=="Yes"){
    
                        $tglmulai = "$tahun-$bulan-01";
                        $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
        
                        $gaji = Gaji::where('karyawan_id', $karyawanid)
                        ->where(function($query) use ($tglmulai, $tglselesai) {
                            $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                                  ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                                  ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                      $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                            ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                                  });
                        })
                        ->first();
                    
                    $tunjangandata = Gaji::where('karyawan_id', $karyawanid)
                        ->where(function($query) use ($tglmulai, $tglselesai) {
                            $query->whereBetween('tanggal_mulai_tunjangan', [$tglmulai, $tglselesai])
                                  ->orWhereBetween('tanggal_selesai_tunjangan', [$tglmulai, $tglselesai])
                                  ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                      $query->where('tanggal_mulai_tunjangan', '<=', $tglmulai)
                                            ->where('tanggal_selesai_tunjangan', '>=', $tglselesai);
                                  });
                        })
                        ->first();
                            
        
                            if (is_null($gaji)) {
                                $karyawan = Karyawan::find($karyawanid);
                                $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                              
                                $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                        
                                return redirect(route('payroll'));
                            }

                            if (is_null($tunjangandata)) {
                                $karyawan = Karyawan::find($karyawanid);
                                $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                              
                                $request->session()->flash('error', "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");
                        
                                return redirect(route('payroll'));
                            }
                        
        
                           $upah = $gaji->gaji;
                           $tunjangan = $tunjangandata ->tunjangan;
                           $gajipokok = $upah + $tunjangan;
        
                        }else  if($hitungtujangan=="No"){
                            $tglmulai = "$tahun-$bulan-01";
                            $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
            
                            $gaji = Gaji::where('karyawan_id', $karyawanid)
                            ->where(function($query) use ($tglmulai, $tglselesai) {
                                $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                                      ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                                      ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                          $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                                ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                                      });
                            })
                            ->first();
                        
                      
    
                           
        
                            if (is_null($gaji)) {
                                $karyawan = Karyawan::find($karyawanid);
                                $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                              
                                $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                        
                                return redirect(route('payroll'));
                            }
                            $gajipokok = $gaji->gaji;
        
                        }
    
                        $divide = ((strtotime($endDateNow) - strtotime($startDateNow)) / (60 * 60 * 24))+1;
    
                        
    
                    }else if ($hitungpayroll =="harikerja"){
    
                        $startDateNow = "$tahun-$bulan-01";
                        $endDateNow = date("Y-m-t", strtotime($startDateNow)); // Last day of the month
    
                        $holidays = Holiday::whereBetween('date', [$startDateNow, $kontrakberakhir])->get();

                        $hari = 0;

                
                        
                        foreach ($holidays as $holiday) {
                            $description = $holiday->description;

                           
                            // Cek apakah organisasiid berada dalam organisasi_pengecualian
                            if ($holiday->pengecualian_organisasi && in_array($organisasiid, json_decode($holiday->pengecualian_organisasi))) {

                               
                                // Ubah deskripsi berdasarkan kebalikan dari kondisi aslinya
                                if ($description === 'Kerja') {
                                    $description = 'Libur';
                                } else {
                                    $description = 'Kerja';
                                }

                                
                            }
                        
                            // Hitung hanya jika deskripsi tetap 'Kerja'
                            if ($description === 'Kerja') {
                                $hari++;
                            }
                        }

                        
    
                    if($hitungtujangan=="Yes"){
    
                        $tglmulai = "$tahun-$bulan-01";
                        $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
        
                        $gaji = Gaji::where('karyawan_id', $karyawanid)
                        ->where(function($query) use ($tglmulai, $tglselesai) {
                            $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                                  ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                                  ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                      $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                            ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                                  });
                        })
                        ->first();
                    
                    $tunjangandata = Gaji::where('karyawan_id', $karyawanid)
                        ->where(function($query) use ($tglmulai, $tglselesai) {
                            $query->whereBetween('tanggal_mulai_tunjangan', [$tglmulai, $tglselesai])
                                  ->orWhereBetween('tanggal_selesai_tunjangan', [$tglmulai, $tglselesai])
                                  ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                      $query->where('tanggal_mulai_tunjangan', '<=', $tglmulai)
                                            ->where('tanggal_selesai_tunjangan', '>=', $tglselesai);
                                  });
                        })
                        ->first();
        
                            
        
                            if (is_null($gaji)) {
                                $karyawan = Karyawan::find($karyawanid);
                                $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                              
                                $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                        
                                return redirect(route('payroll'));
                            }

                            if (is_null($tunjangandata)) {
                                $karyawan = Karyawan::find($karyawanid);
                                $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                              
                                $request->session()->flash('error', "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");
                        
                                return redirect(route('payroll'));
                            }
                        
        
                           $upah = $gaji->gaji;
                           $tunjangan = $tunjangandata ->tunjangan;
                           $gajipokok = $upah + $tunjangan;
                         
                    }else  if($hitungtujangan=="No"){
    
                        $tglmulai = "$tahun-$bulan-01";
                        $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
        
                        $gaji = Gaji::where('karyawan_id', $karyawanid)
                        ->where(function($query) use ($tglmulai, $tglselesai) {
                            $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                                  ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                                  ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                      $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                            ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                                  });
                        })
                        ->first();
                    
                  

                       
    
                            if (is_null($gaji)) {
                                $karyawan = Karyawan::find($karyawanid);
                                $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                              
                                $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                        
                                return redirect(route('payroll'));
                            }

                         
                            
                            $gajipokok = $gaji->gaji;
        
                    }

                        $mulaidate = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
                        $akhirdate = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();
                
                    
                        $holidays = Holiday::whereBetween('date', [$mulaidate, $akhirdate])->get();

                        $divide = 0;

                
                        
                        foreach ($holidays as $holiday) {
                            $description = $holiday->description;

                           
                            // Cek apakah organisasiid berada dalam organisasi_pengecualian
                            if ($holiday->pengecualian_organisasi && in_array($organisasiid, json_decode($holiday->pengecualian_organisasi))) {

                               
                                // Ubah deskripsi berdasarkan kebalikan dari kondisi aslinya
                                if ($description === 'Kerja') {
                                    $description = 'Libur';
                                } else {
                                    $description = 'Kerja';
                                }

                                
                            }
                        
                            // Hitung hanya jika deskripsi tetap 'Kerja'
                            if ($description === 'Kerja') {
                                $divide++;
                            }
                        }
    
    
    
                    }
                
                  $gajiabis=  intval(round((($hari / $divide) *$gajipokok)));
    

            }

           else if ($tanggalresign && (($bulanresign < $bulan && $tahun == $tahunresign) || ($bulan == 1 && $bulanresign == 12 && $tahun == $tahunresign + 1))) {

                $gajiresignafter = 0;
            }
                       
            else if($tanggalresign && $bulan == $bulanresign && $tahun == $tahunresign){

                $awalkontrak = $item->tanggal_awal_kontrak;
                $akhirkontrak = $item->tanggal_akhir_kontrak;
    
                $konfigurasi = DetailKonfigurasi::where('penempatan_id', $penempatanid)->first();
    
                $datapenempatan = Penempatan::find($penempatanid);
                $namapenempatan = $datapenempatan->nama_unit_kerja;

            if (!$konfigurasi) {
                
                if (!$konfigurasi) {
                    $request->session()->flash('error', "Konfigurasi untuk organisasi $namapenempatan belum terdaftar");
                    return redirect(route('payroll'));
                }
            }
        
                $hitungpayroll = $konfigurasi->perhitungan_payroll;
    
                $hitungtujangan = $konfigurasi ->hitung_tunjangan;
    
                if($hitungpayroll == "kalender"){
    
                    $startDateNow = "$tahun-$bulan-01";
                        $endDateNow = date("Y-m-t", strtotime($startDateNow)); // Last day of the month
    
                    $resign = new DateTime($tanggalresign);
                    $awal = new DateTime($startDateNow);
                    $interval = $resign->diff($awal);
                    
                    $hari = $interval->days + 1; 

                  
                     if($hitungtujangan=="Yes"){
    
                        $tglmulai = "$tahun-$bulan-01";
                        $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
                        $gaji = Gaji::where('karyawan_id', $karyawanid)
                        ->where(function($query) use ($tglmulai, $tglselesai) {
                            $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                                  ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                                  ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                      $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                            ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                                  });
                        })
                        ->first();
                    
                    $tunjangandata = Gaji::where('karyawan_id', $karyawanid)
                        ->where(function($query) use ($tglmulai, $tglselesai) {
                            $query->whereBetween('tanggal_mulai_tunjangan', [$tglmulai, $tglselesai])
                                  ->orWhereBetween('tanggal_selesai_tunjangan', [$tglmulai, $tglselesai])
                                  ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                      $query->where('tanggal_mulai_tunjangan', '<=', $tglmulai)
                                            ->where('tanggal_selesai_tunjangan', '>=', $tglselesai);
                                  });
                        })
                        ->first();
        
                            
        
                            if (is_null($gaji)) {
                                $karyawan = Karyawan::find($karyawanid);
                                $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                              
                                $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                        
                                return redirect(route('payroll'));
                            }

                            if (is_null($tunjangandata)) {
                                $karyawan = Karyawan::find($karyawanid);
                                $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                              
                                $request->session()->flash('error', "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");
                        
                                return redirect(route('payroll'));
                            }
                        
        
                           $upah = $gaji->gaji;
                           $tunjangan = $tunjangandata ->tunjangan;
                           $gajipokok = $upah + $tunjangan;
                         
                        }else  if($hitungtujangan=="No"){
                            $tglmulai = "$tahun-$bulan-01";
                            $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
            
                            $gaji = Gaji::where('karyawan_id', $karyawanid)
                            ->where(function($query) use ($tglmulai, $tglselesai) {
                                $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                                      ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                                      ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                          $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                                ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                                      });
                            })
                            ->first();
                        
                       
    
                        
            
                                
            
                                if (is_null($gaji)) {
                                    $karyawan = Karyawan::find($karyawanid);
                                    $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                                  
                                    $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                            
                                    return redirect(route('payroll'));
                                }
    
                                
                            $gajipokok = $gaji->gaji;
        
                        }
    
                        $divide = ((strtotime($endDateNow) - strtotime($startDateNow)) / (60 * 60 * 24))+1;
    
                        
    
                    }else if ($hitungpayroll =="harikerja"){
    
                        $startDateNow = "$tahun-$bulan-01";
                        $endDateNow = date("Y-m-t", strtotime($startDateNow)); // Last day of the month
    
                        $holidays = Holiday::whereBetween('date', [$startDateNow, $tanggalresign])->get();

                        $hari = 0;

                
                        
                        foreach ($holidays as $holiday) {
                            $description = $holiday->description;

                           
                            // Cek apakah organisasiid berada dalam organisasi_pengecualian
                            if ($holiday->pengecualian_organisasi && in_array($organisasiid, json_decode($holiday->pengecualian_organisasi))) {

                               
                                // Ubah deskripsi berdasarkan kebalikan dari kondisi aslinya
                                if ($description === 'Kerja') {
                                    $description = 'Libur';
                                } else {
                                    $description = 'Kerja';
                                }

                                
                            }
                        
                            // Hitung hanya jika deskripsi tetap 'Kerja'
                            if ($description === 'Kerja') {
                                $hari++;
                            }
                        }
    
    if($hitungtujangan=="Yes"){
    
        $tglmulai = "$tahun-$bulan-01";
        $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month

        $gaji = Gaji::where('karyawan_id', $karyawanid)
        ->where(function($query) use ($tglmulai, $tglselesai) {
            $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                  ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                  ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                      $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                            ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                  });
        })
        ->first();
    
    $tunjangandata = Gaji::where('karyawan_id', $karyawanid)
        ->where(function($query) use ($tglmulai, $tglselesai) {
            $query->whereBetween('tanggal_mulai_tunjangan', [$tglmulai, $tglselesai])
                  ->orWhereBetween('tanggal_selesai_tunjangan', [$tglmulai, $tglselesai])
                  ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                      $query->where('tanggal_mulai_tunjangan', '<=', $tglmulai)
                            ->where('tanggal_selesai_tunjangan', '>=', $tglselesai);
                  });
        })
        ->first();

            

            if (is_null($gaji)) {
                $karyawan = Karyawan::find($karyawanid);
                $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
              
                $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
        
                return redirect(route('payroll'));
            }

            if (is_null($tunjangandata)) {
                $karyawan = Karyawan::find($karyawanid);
                $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
              
                $request->session()->flash('error', "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");
        
                return redirect(route('payroll'));
            }
        

           $upah = $gaji->gaji;
           $tunjangan = $tunjangandata ->tunjangan;
           $gajipokok = $upah + $tunjangan;
        
                         
                        }else  if($hitungtujangan=="No"){
    
    
                            $tglmulai = "$tahun-$bulan-01";
                        $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
                        $gaji = Gaji::where('karyawan_id', $karyawanid)
                        ->where(function($query) use ($tglmulai, $tglselesai) {
                            $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                                  ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                                  ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                      $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                            ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                                  });
                        })
                        ->first();
                    
                  

                       
                            
        
                            if (is_null($gaji)) {
                                $karyawan = Karyawan::find($karyawanid);
                                $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                              
                                $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                        
                                return redirect(route('payroll'));
                            }

                           
                        
        
                          
                            $gajipokok = $gaji->gaji;
        
                        }
                        $mulaidate = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
                        $akhirdate = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();
                
                    
                        $holidays = Holiday::whereBetween('date', [$mulaidate, $akhirdate])->get();

                        $divide = 0;

                
                        
                        foreach ($holidays as $holiday) {
                            $description = $holiday->description;

                           
                            // Cek apakah organisasiid berada dalam organisasi_pengecualian
                            if ($holiday->pengecualian_organisasi && in_array($organisasiid, json_decode($holiday->pengecualian_organisasi))) {

                               
                                // Ubah deskripsi berdasarkan kebalikan dari kondisi aslinya
                                if ($description === 'Kerja') {
                                    $description = 'Libur';
                                } else {
                                    $description = 'Kerja';
                                }

                                
                            }
                        
                            // Hitung hanya jika deskripsi tetap 'Kerja'
                            if ($description === 'Kerja') {
                                $divide++;
                            }
                        }
    
    
                    }
                
                  $gajiresign=  intval(round((($hari / $divide) *$gajipokok)));
    
                 
    
            }

            
           

                $tanggalAwalKontrak = new DateTime($item->tanggal_bergabung);
                $bulanKontrak = $tanggalAwalKontrak->format('n'); // bulan tanpa leading zero
                $tahunKontrak = $tanggalAwalKontrak->format('Y'); // tahun dengan 4 digit
                $hariKontrak = $tanggalAwalKontrak->format('j'); // hari tanpa leading zero
                $awalkontrak = $item->tanggal_bergabung;
                $akhirkontrak = $item->tanggal_akhir_kontrak;
    
          

                $tglmulai = "$tahun-$bulan-01";
                $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month

                $tglmulai = "$tahun-$bulan-01";
                $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month

                $gaji = Gaji::where('karyawan_id', $karyawanid)
                ->where(function($query) use ($tglmulai, $tglselesai) {
                    $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                          ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                          ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                              $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                    ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                          });
                })
                ->first();
            
            $tunjangandata = Gaji::where('karyawan_id', $karyawanid)
                ->where(function($query) use ($tglmulai, $tglselesai) {
                    $query->whereBetween('tanggal_mulai_tunjangan', [$tglmulai, $tglselesai])
                          ->orWhereBetween('tanggal_selesai_tunjangan', [$tglmulai, $tglselesai])
                          ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                              $query->where('tanggal_mulai_tunjangan', '<=', $tglmulai)
                                    ->where('tanggal_selesai_tunjangan', '>=', $tglselesai);
                          });
                })
                ->first();
                    

                    if (is_null($gaji)) {
                        $karyawan = Karyawan::find($karyawanid);
                        $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                      
                        $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                
                        return redirect(route('payroll'));
                    }

                    if (is_null($tunjangandata)) {
                        $karyawan = Karyawan::find($karyawanid);
                        $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                      
                        $request->session()->flash('error', "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");
                
                        return redirect(route('payroll'));
                    }
                

                  
                
    
                if (is_null($gaji)) {
                    $karyawan = Karyawan::find($karyawanid);
                    $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                  
                    $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
            
                    return redirect(route('payroll'));
                }

                // Adjusment Awal Join
            
                if ($bulanKontrak == $bulanSebelumnya && $tahunKontrak == $tahunSebelumnya && $hariKontrak > 15) {
                

                    $konfigurasi = DetailKonfigurasi::where('penempatan_id', $penempatanid)->first();
    
                    $datapenempatan = Penempatan::find($penempatanid);
                    $namapenempatan = $datapenempatan->nama_unit_kerja;
    
                if (!$konfigurasi) {
                    
                    if (!$konfigurasi) {
                        $request->session()->flash('error', "Konfigurasi untuk organisasi $namapenempatan belum terdaftar");
                        return redirect(route('payroll'));
                    }
                }
            
                    $hitungpayroll = $konfigurasi->perhitungan_payroll;
        
                    $hitungtujangan = $konfigurasi ->hitung_tunjangan;

                  

                    if($hitungpayroll == "kalender"){

                        $startDatebefore = "$tahunSebelumnya-$bulanSebelumnya-01";
                        $endDatebefore = date("Y-m-t", strtotime($startDatebefore)); // Last day of the month

                        $awalkontraktgl = $kontrak->tanggal_awal_kontrak;

                        $end = new DateTime($endDatebefore);
                        $awalKontrak = new DateTime($awalkontraktgl);
                        $interval = $end->diff($awalKontrak);
                        
                        $days = $interval->days + 1; ;

                        if($hitungtujangan=="Yes"){

                            $tglmulai = "$tahun-$bulan-01";
                            $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
            
                            $gaji = Gaji::where('karyawan_id', $karyawanid)
                            ->where(function($query) use ($tglmulai, $tglselesai) {
                                $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                                      ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                                      ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                          $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                                ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                                      });
                            })
                            ->first();
                        
                        $tunjangandata = Gaji::where('karyawan_id', $karyawanid)
                            ->where(function($query) use ($tglmulai, $tglselesai) {
                                $query->whereBetween('tanggal_mulai_tunjangan', [$tglmulai, $tglselesai])
                                      ->orWhereBetween('tanggal_selesai_tunjangan', [$tglmulai, $tglselesai])
                                      ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                          $query->where('tanggal_mulai_tunjangan', '<=', $tglmulai)
                                                ->where('tanggal_selesai_tunjangan', '>=', $tglselesai);
                                      });
                            })
                            ->first();
            
                                
            
                                if (is_null($gaji)) {
                                    $karyawan = Karyawan::find($karyawanid);
                                    $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                                  
                                    $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                            
                                    return redirect(route('payroll'));
                                }
    
                                if (is_null($tunjangandata)) {
                                    $karyawan = Karyawan::find($karyawanid);
                                    $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                                  
                                    $request->session()->flash('error', "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");
                            
                                    return redirect(route('payroll'));
                                }
                            
            
                               $upah = $gaji->gaji;
                               $tunjangan = $tunjangandata ->tunjangan;
                               $gajipokok = $upah + $tunjangan;
                         
                        }else  if($hitungtujangan=="No"){
                            $tglmulai = "$tahun-$bulan-01";
                            $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
            
                            $gaji = Gaji::where('karyawan_id', $karyawanid)
                            ->where(function($query) use ($tglmulai, $tglselesai) {
                                $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                                      ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                                      ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                          $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                                ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                                      });
                            })
                            ->first();
                    
    
                       
                                
            
                                if (is_null($gaji)) {
                                    $karyawan = Karyawan::find($karyawanid);
                                    $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                                  
                                    $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                            
                                    return redirect(route('payroll'));
                                }
    
                               
                            
            
                              
                            $gajipokok = $gaji->gaji;
        
                        }

                        $pembagi = ((strtotime($endDatebefore) - strtotime($startDatebefore)) / (60 * 60 * 24))+1;


                      
                      
                      
                        
                    }else if ($hitungpayroll =="harikerja"){

                        $startDate = Carbon::createFromDate($kontrak->tanggal_awal_kontrak);
                        $endDate = Carbon::createFromDate($tahunSebelumnya, $bulanSebelumnya, 1)->endOfMonth();
                    
                      

                        $holidays = Holiday::whereBetween('date', [$startDate, $endDate])->get();

                        $days = 0;

                
                        
                        foreach ($holidays as $holiday) {
                            $description = $holiday->description;

                           
                            // Cek apakah organisasiid berada dalam organisasi_pengecualian
                            if ($holiday->pengecualian_organisasi && in_array($organisasiid, json_decode($holiday->pengecualian_organisasi))) {

                               
                                // Ubah deskripsi berdasarkan kebalikan dari kondisi aslinya
                                if ($description === 'Kerja') {
                                    $description = 'Libur';
                                } else {
                                    $description = 'Kerja';
                                }

                                
                            }
                        
                            // Hitung hanya jika deskripsi tetap 'Kerja'
                            if ($description === 'Kerja') {
                                $days++;
                            }
                        }

                        if($hitungtujangan=="Yes"){

                            $tglmulai = "$tahun-$bulan-01";
                            $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
            
                            $gaji = Gaji::where('karyawan_id', $karyawanid)
                            ->where(function($query) use ($tglmulai, $tglselesai) {
                                $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                                      ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                                      ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                          $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                                ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                                      });
                            })
                            ->first();
                        
                        $tunjangandata = Gaji::where('karyawan_id', $karyawanid)
                            ->where(function($query) use ($tglmulai, $tglselesai) {
                                $query->whereBetween('tanggal_mulai_tunjangan', [$tglmulai, $tglselesai])
                                      ->orWhereBetween('tanggal_selesai_tunjangan', [$tglmulai, $tglselesai])
                                      ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                          $query->where('tanggal_mulai_tunjangan', '<=', $tglmulai)
                                                ->where('tanggal_selesai_tunjangan', '>=', $tglselesai);
                                      });
                            })
                            ->first();
            
                                
            
                                if (is_null($gaji)) {
                                    $karyawan = Karyawan::find($karyawanid);
                                    $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                                  
                                    $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                            
                                    return redirect(route('payroll'));
                                }
    
                                if (is_null($tunjangandata)) {
                                    $karyawan = Karyawan::find($karyawanid);
                                    $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                                  
                                    $request->session()->flash('error', "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");
                            
                                    return redirect(route('payroll'));
                                }
                            
            
                               $upah = $gaji->gaji;
                               $tunjangan = $tunjangandata ->tunjangan;
                               $gajipokok = $upah + $tunjangan;
        
                         
                        }else  if($hitungtujangan=="No"){
                            $tglmulai = "$tahun-$bulan-01";
                            $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
            
                            $gaji = Gaji::where('karyawan_id', $karyawanid)
                            ->where(function($query) use ($tglmulai, $tglselesai) {
                                $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                                      ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                                      ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                          $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                                ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                                      });
                            })
                            ->first();
                        
                       
            
                                
            
                                if (is_null($gaji)) {
                                    $karyawan = Karyawan::find($karyawanid);
                                    $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                                  
                                    $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                            
                                    return redirect(route('payroll'));
                                }
    
                             
            
                              
                            $gajipokok = $gaji->gaji;
        
                        }

                        $mulaidate = Carbon::createFromDate($tahunSebelumnya, $bulanSebelumnya, 1)->startOfMonth();
                        $akhirdate = Carbon::createFromDate($tahunSebelumnya, $bulanSebelumnya, 1)->endOfMonth();
                    
                    
                        $holidays = Holiday::whereBetween('date', [$mulaidate, $akhirdate])->get();

                        $pembagi = 0;

                
                        
                        foreach ($holidays as $holiday) {
                            $description = $holiday->description;

                           
                            // Cek apakah organisasiid berada dalam organisasi_pengecualian
                            if ($holiday->pengecualian_organisasi && in_array($organisasiid, json_decode($holiday->pengecualian_organisasi))) {

                               
                                // Ubah deskripsi berdasarkan kebalikan dari kondisi aslinya
                                if ($description === 'Kerja') {
                                    $description = 'Libur';
                                } else {
                                    $description = 'Kerja';
                                }

                                
                            }
                        
                            // Hitung hanya jika deskripsi tetap 'Kerja'
                            if ($description === 'Kerja') {
                                $pembagi++;
                            }
                        }
                                   
                                    
                             
                    }

                    $adjusmentawaljoin = intval(round((($days / $pembagi) *$gajipokok)));
                 

            
                } elseif ($bulanKontrak == $bulan && $tahunKontrak == $tahun && $hariKontrak <=15){

                    $konfigurasi = DetailKonfigurasi::where('penempatan_id', $penempatanid)->first();
    
                    $datapenempatan = Penempatan::find($penempatanid);
                    $namapenempatan = $datapenempatan->nama_unit_kerja;
    
                if (!$konfigurasi) {
                    
                    if (!$konfigurasi) {
                        $request->session()->flash('error', "Konfigurasi untuk organisasi $namapenempatan belum terdaftar");
                        return redirect(route('payroll'));
                    }
                }
            
                    $hitungpayroll = $konfigurasi->perhitungan_payroll;
        
                    $hitungtujangan = $konfigurasi ->hitung_tunjangan;


                    if($hitungpayroll == "kalender") {

                        $startDateNow = "$tahun-$bulan-01";
                        $endDateNow = date("Y-m-t", strtotime($startDateNow)); // Last day of the month

                        $awalkontraktgl = $kontrak->tanggal_awal_kontrak;

                        $end = new DateTime($endDateNow);
                        $awalKontrak = new DateTime($awalkontraktgl);
                        $interval = $end->diff($awalKontrak);
                        
                        $days = $interval->days + 1; 

                        if($hitungtujangan=="Yes"){

                            $tglmulai = "$tahun-$bulan-01";
                            $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
                            $gaji = Gaji::where('karyawan_id', $karyawanid)
                            ->where(function($query) use ($tglmulai, $tglselesai) {
                                $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                                      ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                                      ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                          $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                                ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                                      });
                            })
                            ->first();
                        
                        $tunjangandata = Gaji::where('karyawan_id', $karyawanid)
                            ->where(function($query) use ($tglmulai, $tglselesai) {
                                $query->whereBetween('tanggal_mulai_tunjangan', [$tglmulai, $tglselesai])
                                      ->orWhereBetween('tanggal_selesai_tunjangan', [$tglmulai, $tglselesai])
                                      ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                          $query->where('tanggal_mulai_tunjangan', '<=', $tglmulai)
                                                ->where('tanggal_selesai_tunjangan', '>=', $tglselesai);
                                      });
                            })
                            ->first();
            
                                
            
                                if (is_null($gaji)) {
                                    $karyawan = Karyawan::find($karyawanid);
                                    $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                                  
                                    $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                            
                                    return redirect(route('payroll'));
                                }
    
                                if (is_null($tunjangandata)) {
                                    $karyawan = Karyawan::find($karyawanid);
                                    $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                                  
                                    $request->session()->flash('error', "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");
                            
                                    return redirect(route('payroll'));
                                }
                            
            
                               $upah = $gaji->gaji;
                               $tunjangan = $tunjangandata ->tunjangan;
                               $gajipokok = $upah + $tunjangan;
        
                         
                        }else  if($hitungtujangan=="No"){
                            $tglmulai = "$tahun-$bulan-01";
                            $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
            
                            $gaji = Gaji::where('karyawan_id', $karyawanid)
                            ->where(function($query) use ($tglmulai, $tglselesai) {
                                $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                                      ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                                      ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                          $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                                ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                                      });
                            })
                            ->first();
                        
                     
            
                                
            
                                if (is_null($gaji)) {
                                    $karyawan = Karyawan::find($karyawanid);
                                    $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                                  
                                    $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                            
                                    return redirect(route('payroll'));
                                }
    
                              
                            
            
                             
                            $gajipokok = $gaji->gaji;
        
                        }

                        $pembagi = ((strtotime($endDateNow) - strtotime($startDateNow)) / (60 * 60 * 24))+1;





                    }else if ($hitungpayroll =="harikerja"){

                        $startDate = Carbon::createFromDate($kontrak->tanggal_awal_kontrak);
                        $endDate = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();
                    
                        $holidays = Holiday::whereBetween('date', [$startDate, $endDate])->get();

                        $days = 0;

                
                        
                        foreach ($holidays as $holiday) {
                            $description = $holiday->description;

                           
                            // Cek apakah organisasiid berada dalam organisasi_pengecualian
                            if ($holiday->pengecualian_organisasi && in_array($organisasiid, json_decode($holiday->pengecualian_organisasi))) {

                               
                                // Ubah deskripsi berdasarkan kebalikan dari kondisi aslinya
                                if ($description === 'Kerja') {
                                    $description = 'Libur';
                                } else {
                                    $description = 'Kerja';
                                }

                                
                            }
                        
                            // Hitung hanya jika deskripsi tetap 'Kerja'
                            if ($description === 'Kerja') {
                                $days++;
                            }
                        }
                        if($hitungtujangan=="Yes"){

                            $tglmulai = "$tahun-$bulan-01";
                            $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
            
                            $gaji = Gaji::where('karyawan_id', $karyawanid)
                            ->where(function($query) use ($tglmulai, $tglselesai) {
                                $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                                      ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                                      ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                          $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                                ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                                      });
                            })
                            ->first();
                        
                        $tunjangandata = Gaji::where('karyawan_id', $karyawanid)
                            ->where(function($query) use ($tglmulai, $tglselesai) {
                                $query->whereBetween('tanggal_mulai_tunjangan', [$tglmulai, $tglselesai])
                                      ->orWhereBetween('tanggal_selesai_tunjangan', [$tglmulai, $tglselesai])
                                      ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                          $query->where('tanggal_mulai_tunjangan', '<=', $tglmulai)
                                                ->where('tanggal_selesai_tunjangan', '>=', $tglselesai);
                                      });
                            })
                            ->first();
            
                                
            
                                if (is_null($gaji)) {
                                    $karyawan = Karyawan::find($karyawanid);
                                    $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                                  
                                    $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                            
                                    return redirect(route('payroll'));
                                }
    
                                if (is_null($tunjangandata)) {
                                    $karyawan = Karyawan::find($karyawanid);
                                    $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                                  
                                    $request->session()->flash('error', "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");
                            
                                    return redirect(route('payroll'));
                                }
                            
            
                               $upah = $gaji->gaji;
                               $tunjangan = $tunjangandata ->tunjangan;
                               $gajipokok = $upah + $tunjangan;
        
                         
                        }else  if($hitungtujangan=="No"){
                            $tglmulai = "$tahun-$bulan-01";
                            $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
            
                            $gaji = Gaji::where('karyawan_id', $karyawanid)
                            ->where(function($query) use ($tglmulai, $tglselesai) {
                                $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                                      ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                                      ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                          $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                                ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                                      });
                            })
                            ->first();
                       
                                
            
                                if (is_null($gaji)) {
                                    $karyawan = Karyawan::find($karyawanid);
                                    $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                                  
                                    $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                            
                                    return redirect(route('payroll'));
                                }
    
            
                              
                            $gajipokok = $gaji->gaji;
        
                        }

                        $mulaidate = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
                        $akhirdate = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();
                    
                    
                        $holidays = Holiday::whereBetween('date', [$mulaidate, $akhirdate])->get();

                        $pembagi = 0;

                
                        
                        foreach ($holidays as $holiday) {
                            $description = $holiday->description;

                           
                            // Cek apakah organisasiid berada dalam organisasi_pengecualian
                            if ($holiday->pengecualian_organisasi && in_array($organisasiid, json_decode($holiday->pengecualian_organisasi))) {

                               
                                // Ubah deskripsi berdasarkan kebalikan dari kondisi aslinya
                                if ($description === 'Kerja') {
                                    $description = 'Libur';
                                } else {
                                    $description = 'Kerja';
                                }

                                
                            }
                        
                            // Hitung hanya jika deskripsi tetap 'Kerja'
                            if ($description === 'Kerja') {
                                $pembagi++;
                            }
                        }
                        
                    }

                    $adjusmentawaljoin = intval(round((($days / $pembagi) *$gajipokok)));


                } else {
                    $adjusmentawaljoin=0;
                }
            
                //AdjustmentAbsensi

                $attendanceCount = Attendance::where('karyawan_id', $karyawanid)
                ->whereIn('attendance_code', ['I', 'STD', 'UL'])
                ->whereMonth('date', $bulan)
                ->whereYear('date', $tahun)
                ->count();
            
                $konfigurasi = DetailKonfigurasi::where('penempatan_id', $penempatanid)->first();
    
                $datapenempatan = Penempatan::find($penempatanid);
                $namapenempatan = $datapenempatan->nama_unit_kerja;

            if (!$konfigurasi) {
                
                if (!$konfigurasi) {
                    $request->session()->flash('error', "Konfigurasi untuk organisasi $namapenempatan belum terdaftar");
                    return redirect(route('payroll'));
                }
            }
        
                $hitungpayroll = $konfigurasi->perhitungan_payroll;
    
                $hitungtujangan = $konfigurasi ->hitung_tunjangan;
           

            $hitungpayroll = $konfigurasi->perhitungan_payroll;

            $hitungtujangan = $konfigurasi -> hitung_tunjangan;

          
           if($attendanceCount >0){
            if($hitungpayroll =="harikerja"){

                if($hitungtujangan=="Yes"){

                    $tglmulai = "$tahun-$bulan-01";
                    $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
    
                    $gaji = Gaji::where('karyawan_id', $karyawanid)
                    ->where(function($query) use ($tglmulai, $tglselesai) {
                        $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                              ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                              ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                  $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                        ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                              });
                    })
                    ->first();
                
                $tunjangandata = Gaji::where('karyawan_id', $karyawanid)
                    ->where(function($query) use ($tglmulai, $tglselesai) {
                        $query->whereBetween('tanggal_mulai_tunjangan', [$tglmulai, $tglselesai])
                              ->orWhereBetween('tanggal_selesai_tunjangan', [$tglmulai, $tglselesai])
                              ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                  $query->where('tanggal_mulai_tunjangan', '<=', $tglmulai)
                                        ->where('tanggal_selesai_tunjangan', '>=', $tglselesai);
                              });
                    })
                    ->first();
    
                        
    
                        if (is_null($gaji)) {
                            $karyawan = Karyawan::find($karyawanid);
                            $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                          
                            $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                    
                            return redirect(route('payroll'));
                        }

                        if (is_null($tunjangandata)) {
                            $karyawan = Karyawan::find($karyawanid);
                            $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                          
                            $request->session()->flash('error', "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");
                    
                            return redirect(route('payroll'));
                        }
                    
    
                       $upah = $gaji->gaji;
                       $tunjangan = $tunjangandata ->tunjangan;
                       $gajipokok = $upah + $tunjangan;

                 
                }else  if($hitungtujangan=="No"){
                    $tglmulai = "$tahun-$bulan-01";
                    $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
    
                    $gaji = Gaji::where('karyawan_id', $karyawanid)
                    ->where(function($query) use ($tglmulai, $tglselesai) {
                        $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                              ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                              ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                  $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                        ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                              });
                    })
                    ->first();
                
                

    
                        
    
                        if (is_null($gaji)) {
                            $karyawan = Karyawan::find($karyawanid);
                            $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                          
                            $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                    
                            return redirect(route('payroll'));
                        }

                       
                    
    
                      
                    $gajipokok = $gaji->gaji;

                }

                $startDate = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
    $endDate = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();


              
                
                $holidays = Holiday::whereBetween('date', [$startDate, $endDate])->get();

                $harikerjacount = 0;

        
                
                foreach ($holidays as $holiday) {
                    $description = $holiday->description;

                   
                    // Cek apakah organisasiid berada dalam organisasi_pengecualian
                    if ($holiday->pengecualian_organisasi && in_array($organisasiid, json_decode($holiday->pengecualian_organisasi))) {

                       
                        // Ubah deskripsi berdasarkan kebalikan dari kondisi aslinya
                        if ($description === 'Kerja') {
                            $description = 'Libur';
                        } else {
                            $description = 'Kerja';
                        }

                        
                    }
                
                    // Hitung hanya jika deskripsi tetap 'Kerja'
                    if ($description === 'Kerja') {
                        $harikerjacount++;
                    }
                }

                $adjustmentsalaryattendance = intval(round((($attendanceCount / $harikerjacount) *$gajipokok)));

               


            }else if ($hitungpayroll=="kalender"){

                if($hitungtujangan=="Yes"){

                    $tglmulai = "$tahun-$bulan-01";
                    $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
    
                    $gaji = Gaji::where('karyawan_id', $karyawanid)
                    ->where(function($query) use ($tglmulai, $tglselesai) {
                        $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                              ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                              ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                  $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                        ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                              });
                    })
                    ->first();
                
                $tunjangandata = Gaji::where('karyawan_id', $karyawanid)
                    ->where(function($query) use ($tglmulai, $tglselesai) {
                        $query->whereBetween('tanggal_mulai_tunjangan', [$tglmulai, $tglselesai])
                              ->orWhereBetween('tanggal_selesai_tunjangan', [$tglmulai, $tglselesai])
                              ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                  $query->where('tanggal_mulai_tunjangan', '<=', $tglmulai)
                                        ->where('tanggal_selesai_tunjangan', '>=', $tglselesai);
                              });
                    })
                    ->first();
                        
    
                        if (is_null($gaji)) {
                            $karyawan = Karyawan::find($karyawanid);
                            $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                          
                            $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                    
                            return redirect(route('payroll'));
                        }

                        if (is_null($tunjangandata)) {
                            $karyawan = Karyawan::find($karyawanid);
                            $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                          
                            $request->session()->flash('error', "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");
                    
                            return redirect(route('payroll'));
                        }
                    
    
                       $upah = $gaji->gaji;
                       $tunjangan = $tunjangandata ->tunjangan;
                       $gajipokok = $upah + $tunjangan;

                 
                }else  if($hitungtujangan=="No"){
                    $tglmulai = "$tahun-$bulan-01";
                    $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month
    
                    $gaji = Gaji::where('karyawan_id', $karyawanid)
                    ->where(function($query) use ($tglmulai, $tglselesai) {
                        $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                              ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                              ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                                  $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                        ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                              });
                    })
                    ->first();
                
                

                    
                        
    
                        if (is_null($gaji)) {
                            $karyawan = Karyawan::find($karyawanid);
                            $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                          
                            $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                    
                            return redirect(route('payroll'));
                        }

                       
                    
    
                     
                    $gajipokok = $gaji->gaji;

                }

                $jumlahHari = $startDate->diffInDays($endDate) + 1;
                $adjustmentsalaryattendance = intval(round((($attendanceCount / $jumlahHari) *$gajipokok)));

            }
            
        } elseif ($attendanceCount <=0){
            $adjustmentsalaryattendance = 0;

            $tglmulai = "$tahun-$bulan-01";
            $tglselesai = date("Y-m-t", strtotime($tglmulai)); // Last day of the month

            $gaji = Gaji::where('karyawan_id', $karyawanid)
            ->where(function($query) use ($tglmulai, $tglselesai) {
                $query->whereBetween('tanggal_mulai_gaji', [$tglmulai, $tglselesai])
                      ->orWhereBetween('tanggal_selesai_gaji', [$tglmulai, $tglselesai])
                      ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                          $query->where('tanggal_mulai_gaji', '<=', $tglmulai)
                                ->where('tanggal_selesai_gaji', '>=', $tglselesai);
                      });
            })
            ->first();
        
        $tunjangandata = Gaji::where('karyawan_id', $karyawanid)
            ->where(function($query) use ($tglmulai, $tglselesai) {
                $query->whereBetween('tanggal_mulai_tunjangan', [$tglmulai, $tglselesai])
                      ->orWhereBetween('tanggal_selesai_tunjangan', [$tglmulai, $tglselesai])
                      ->orWhere(function($query) use ($tglmulai, $tglselesai) {
                          $query->where('tanggal_mulai_tunjangan', '<=', $tglmulai)
                                ->where('tanggal_selesai_tunjangan', '>=', $tglselesai);
                      });
            })
            ->first();
                

                if (is_null($gaji)) {
                    $karyawan = Karyawan::find($karyawanid);
                    $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                  
                    $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
            
                    return redirect(route('payroll'));
                }

                if (is_null($tunjangandata)) {
                    $karyawan = Karyawan::find($karyawanid);
                    $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                  
                    $request->session()->flash('error', "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");
            
                    return redirect(route('payroll'));
                }
            

            $tunjanganjabatan = $tunjangandata->tunjangan;
            $upah = $gaji -> gaji;

        }
        
        

    
        if ($tanggalresign && (($bulanresign < $bulan && $tahun == $tahunresign) || ($bulan == 1 && $bulanresign == 12 && $tahun == $tahunresign + 1))) {
            $tunjanganjabatan = 0;
            } else {
                $tunjanganjabatan = $gaji->tunjangan;
            }
        
            $allowance = Allowance::where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->first();

            if(!$allowance){
                $request->session()->flash('error', "Silakan buat uang saku & insentif pada bulan $bulan tahun $tahun ");
                
                return redirect(route('payroll'));
            }

            $allowanceid = $allowance->id;

            $detailallowance = DetailAllowance::where('allowance_id', $allowanceid)
            ->where ('karyawan_id', $karyawanid)
            ->first();

            if ($detailallowance) {

                $uangsaku = $detailallowance->uang_saku;
                $insentif = $detailallowance->insentif;
            } else {

                $uangsaku = 0;
                $insentif =0;
            }




        $lembur = Lembur::where('bulan', $bulanSebelumnya)
        ->where('tahun', $tahunSebelumnya)
        ->where('organisasi_id', $organisasiid)
        ->first();


        $lemburid = $lembur->id;
        $detailembur = DetailLembur::where('lembur_id', $lemburid)->where('karyawan_id', $karyawanid)->first();

        
        if(!$detailembur){
            $overtime=0;
        } else{
            $overtime = $detailembur->total_biaya_lembur;
        }


        
       
          
        $kompensasi = Kompensasi::where('bulan', $bulan)
        ->where('tahun', $tahun)
        ->first();

        $kompensasiid = $kompensasi->id;

        $detailkompensasi = DetailKompensasi::where('kompensasi_id', $kompensasiid)->where('karyawan_id', $karyawanid)->first();

       

        if($hitungtujangan == "Yes"){

            $totalallowance = $tunjanganjabatan + $uangsaku + $insentif + $overtime;

        }else if ($hitungtujangan =="No"){
        
            $totalallowance =  $uangsaku + $insentif + $overtime;

        }


        if(!$detailkompensasi){
            $totalkompensasi = 0;
        }else{
          $totalkompensasi = $detailkompensasi->total_kompensasi;
        }

       
        if ($bulanKontrak == $bulan && $tahunKontrak == $tahun && $hariKontrak <=15){
            $upah = $adjusmentawaljoin;
        } else if($tanggalresign && $bulan == $bulanresign && $tahun == $tahunresign){
            $upah = $gajiresign;
        } else  if ($tanggalresign && (($bulanresign < $bulan && $tahun == $tahunresign) || ($bulan == 1 && $bulanresign == 12 && $tahun == $tahunresign + 1))) {
            $upah = $gajiresignafter;
        } else  if($kontrakberakhir && $bulan == $bulanabis && $tahun == $tahunabis && $tanggalresign == null) {
            $upah = $gajiabis;
        }
      
    

        if ($bulanKontrak == $bulan && $tahunKontrak == $tahun && $hariKontrak <=15){
            $adjusmentjoinhitung = 0;
        } else {
            $adjusmentjoinhitung = $adjusmentawaljoin;
        }
        
        $adjustmentsalary = $adjusmentjoinhitung - $adjustmentsalaryattendance;

       
      
        $total = $upah + $adjustmentsalary + $totalallowance + $totalkompensasi;

       
       
        // DetailPayroll::create([
        //     'payroll_id' => $payroll->id,
        //     'karyawan_id' => $karyawanid,
        //     'adjusment_salary' => $adjustmentsalary,
        //     'tunjangan' => $tunjanganjabatan,
        //     'uangsaku' => $uangsaku,
        //     'insentif' => $insentif,
        //     'overtime' => $overtime,
        //     'total_allowance' => $totalallowance,
        //     'kompensasi' => $totalkompensasi,
        //     'total' => $total,
        //     'gajipokok' => $upah,
        // ]);

        }

        $payroll = Payroll::create([
            'judul_payroll' => "Laporan Payroll  $namaorganisasi $bulanNama $tahun",
            'bulan' => $bulan,
            'tahun' => $tahun,
            'created_by' => $loggedInUser->nama_user,
            'organisasi_id' => $organisasiid,
            'status_payroll' => "Created",
        ]);
        $request->session()->flash('success', "Laporan payroll berhasil dibuat.");

        return redirect()->route('payroll');
    }
    
    public function closepayroll(Request $request, $id)
    {
        
        $payroll = Payroll::find($id);
        $payroll -> status_payroll = "Closing";
        $payroll->save();


       

        $datapayroll = json_decode($request->input('dataToShow'), true);
      
foreach ($datapayroll as $data) {
  

    $detailpayroll = new DetailPayroll();
    $detailpayroll->payroll_id = $payroll->id;
    $detailpayroll->karyawan_id = $data['karyawan_id'];

    $detailpayroll->adjusment_salary = $data['adjusment_salary'];
    $detailpayroll->gajipokok = $data['gajikaryawan'];
    $detailpayroll->tunjangan = $data['tunjangan'];
    $detailpayroll->uangsaku = $data['uangsaku'];
    $detailpayroll->overtime = $data['overtime'];
    $detailpayroll->insentif = $data['insentif'];
    $detailpayroll->total_allowance = $data['total_allowance'];
    $detailpayroll->kompensasi = $data['kompensasi'];
    $detailpayroll->total = $data['total'];
   

    $detailpayroll->save();

}



 
        return redirect()->back()->with('success', 'Payroll berhasil closing dan tersimpan.');
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    public function batalkanClosingpayroll($id, Request $request)
    {
        $payroll = Payroll::find($id);
        $bulan = $payroll->bulan;

        $timestamp = strtotime($bulan); // Ubah string tanggal ke timestamp
        $bulannama = \Carbon\Carbon::createFromFormat('m', $bulan)->translatedFormat('F');


        $tahun = $payroll->tahun;

        $organisasiid = $payroll->organisasi_id;
        $dataorg = Organisasi::find($organisasiid);

        $namaorg = $dataorg->organisasi;

        $payrollid = $payroll->id;

        DetailPayroll::where('payroll_id', $payrollid)->delete();


        if ($payroll) {
            $payroll->status_payroll = 'Created';
            $payroll->save();
        }
        return redirect()->route('payroll')->with('success', "Closing laporan payroll organisasi $namaorg bulan $bulannama $tahun berhasil dibatalkan");

       
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
