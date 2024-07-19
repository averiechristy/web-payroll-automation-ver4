<?php

namespace App\Http\Controllers;

use App\Models\Allowance;
use App\Models\Attendance;
use App\Models\DetailAllowance;
use App\Models\DetailInvoice;
use App\Models\DetailKonfigurasi;
use App\Models\DetailPayroll;
use App\Models\Gaji;
use App\Models\Holiday;
use App\Models\Invoice;
use App\Models\Karyawan;
use App\Models\Konfigurasi;
use App\Models\KontrakKaryawan;
use App\Models\Organisasi;
use App\Models\Overtime;
use App\Models\Payroll;
use App\Models\Penempatan;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $organisasi = Organisasi::all();
        $penempatan = Penempatan::all();
        $invoice = Invoice::orderBy('created_at','desc')->get();

        return view ('invoice.index',[
            'organisasi' => $organisasi,
            'penempatan' => $penempatan,
            'invoice' => $invoice,
        ]);

    }

    // Add this method to your controller



public function getPenempatanedit($organisasi_id)
{
    // Fetch penempatan where buat_invoice is Yes
    $organisasiId = $organisasi_id;
    $penempatan = Penempatan::where('organisasi_id', $organisasiId)
                ->whereHas('detailkonfigurasi', function($query) {
                    $query->where('buat_invoice', 'Yes');
                })
                ->get();

    return response()->json($penempatan);
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $organisasi = Organisasi::all();
        return view ('invoice.create',[
            'organisasi' => $organisasi,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    
    public function store(Request $request)
    {

        $loggedInUser = auth()->user(); 
        $loggedInUserName = $loggedInUser->nama_user;
        $kodeinvoice = $request->kode_invoice;
        $organisasiid = $request->organisasi_id;
        $currentmonth = $request->bulan;
        $currentyear = $request->tahun;

    if ($currentmonth == 1) {
        $bulan = 12;
        $tahun = $currentyear - 1;
    } else {
        $bulan = $currentmonth - 1;
        $tahun = $currentyear;
    }
        $biayajasareq = $request->biaya_jasa;
        $managementfeereq = $request->management_fee; 

        $kodetemplate = $request->kode_invoice;

        $dateObj = DateTime::createFromFormat('!m', $currentmonth);
        $bulanNama = $dateObj->format('F'); // This will give the full month name

    $dataorg= Organisasi::find($organisasiid);

    $namaorg= $dataorg->organisasi;
     
    $startDate = "$tahun-$bulan-01";
    $endDate = date("Y-m-t", strtotime($startDate)); // Last day of the month

    $penempatanreq = $request->penempatan_id;


    $datautkorg = Organisasi::find($organisasiid);
    $namautkorg = $datautkorg->organisasi;

    if($penempatanreq =="all"){
        $namautkpenempatan ="Seluruh Penempatan";
    }else{
    $datautkpenempatan = Penempatan::find($penempatanreq);
    $namautkpenempatan = $datautkpenempatan->nama_unit_kerja;
    }

    $existingdata = Invoice::where('organisasi_id', $organisasiid)
    ->where('penempatan_id', $penempatanreq)
    ->first();

    if($existingdata){
        $request->session()->flash('error', "Invoice $namautkorg ($namautkpenempatan) pada bulan $currentmonth tahun $currentyear sudah terdaftar.");
                    return redirect(route('invoice'));
    }

    $karyawan = Karyawan::whereHas('penempatan', function ($query) use ($organisasiid, $penempatanreq) {
        $query->where('organisasi_id', $organisasiid);
    
        // If penempatanreq is not 'all', filter by penempatan_id
        if ($penempatanreq !== 'all') {
            $query->where('penempatan_id', $penempatanreq);
        } else {
            // If penempatanreq is 'all', join with konfigurasi to check buat_invoice
            $query->whereHas('detailkonfigurasi', function ($query) {
                $query->where('buat_invoice', 'Yes');
            });
        }
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
    })->get();

    if ($karyawan->isEmpty()) {
        $request->session()->flash('error', "Tidak ada karyawan terdaftar.");
        
        return redirect(route('payroll'));
    }
  
        foreach ($karyawan as $item) {

            $karyawanid = $item->id;
            $namakaryawan = $item->nama_karyawan;

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
        
                return redirect(route('invoice'));
            }

            $awalkontrak = $kontrak -> tanggal_awal_kontrak;
            $akhirkontrak = $kontrak -> tanggal_akhir_kontrak;
            $tanggalresign = $item->tanggal_resign;

            $tanggalawalkontrakformat = new DateTime($item->tanggal_bergabung);
            $bulanKontrak = $tanggalawalkontrakformat->format('n'); // bulan tanpa leading zero
            $tahunKontrak = $tanggalawalkontrakformat->format('Y'); // tahun dengan 4 digit
            $hariKontrak = $tanggalawalkontrakformat->format('j'); // hari tanpa leading zero

            $kontrakberakhir = $kontrak->tanggal_akhir_kontrak;
            $kontrakberakhirnew = new DateTime($kontrak->tanggal_akhir_kontrak);
            $bulanabis= $kontrakberakhirnew->format('n'); // bulan tanpa leading zero
            $tahunabis= $kontrakberakhirnew->format('Y'); // tahun dengan 4 digit
            $hariabis= $kontrakberakhirnew->format('j'); // hari tanpa leading zero

            $tanggalresignnew = new DateTime($item->tanggal_resign);
            $bulanresign= $tanggalresignnew->format('n'); // bulan tanpa leading zero
            $tahunresign= $tanggalresignnew->format('Y'); // tahun dengan 4 digit
            $hariresign= $tanggalresignnew->format('j'); // hari tanpa leading zero

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
        
                return redirect(route('invoice'));
            }

            $penempatanid = $item -> penempatan_id;
            $datapenempatan = Penempatan::find($penempatanid);
            $namapenempatan = $datapenempatan->nama_unit_kerja;

            
            $konfigurasi = DetailKonfigurasi::where('penempatan_id', $penempatanid)->first();
    
            if (!$konfigurasi) {

                if (!$konfigurasi) {
                    $request->session()->flash('error', "Konfigurasi untuk $namapenempatan belum terdaftar");
                    return redirect(route('invoice'));
                }

            }
    
            $hitungpayroll = $konfigurasi->perhitungan_payroll;

            $hitungtujangan = $konfigurasi ->hitung_tunjangan;

            

              if ($bulanKontrak == $bulan && $tahunKontrak == $tahun) {

               
              
                if($hitungpayroll =="kalender"){


                    $startDatebefore = "$tahun-$bulan-01";
                    $endDatebefore = date("Y-m-t", strtotime($startDatebefore)); // Last day of the month
    
                    $awalkontraktgl = $item->tanggal_bergabung;
    
                    $end = new DateTime($endDatebefore);
                    $awalKontrak = new DateTime($awalkontraktgl);
                    $interval = $end->diff($awalKontrak);
                    
                    $days = $interval->days + 1; ;
                   
                   
    
                    $pembagi = ((strtotime($endDatebefore) - strtotime($startDatebefore)) / (60 * 60 * 24))+1;


                    if($hitungtujangan=="Yes"){

                        $gp = $gaji->gaji;
                        $tj = $tunjangandata->tunjangan;

                        $up = $gp + $tj;


                        $adjustgaji = intval(round((($days / $pembagi) *$up)));

                    }else if ($hitungtujangan=="No"){
                        
                        $gp = $gaji -> gaji;
                        $up = $gp;

                        $adjustgaji = intval(round((($days / $pembagi) *$up)));

                    }

                } else if($hitungpayroll == "harikerja"){

                    $startDate = Carbon::createFromDate($item->tanggal_bergabung);
                    $endDate = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();
                
                    $holidayss = Holiday::whereBetween('date', [$startDate, $endDate])->get();

                        $days = 0;

                
                        
                        foreach ($holidayss as $holiday) {
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


                    if(!$days){
                        $request->session()->flash('error', "Data libur belum terdaftar.");
                        return redirect(route('invoice'));
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
    

                                if($hitungtujangan=="Yes"){

                                    $gp = $gaji->gaji;
                                    $tj = $tunjangandata->tunjangan;
            
                                    $up = $gp + $tj;
                        
                                    $adjustgaji = intval(round((($days / $pembagi) *$up)));
            
                                }else if ($hitungtujangan=="No"){
                                    
                                    $gp = $gaji -> gaji;
                                    $up = $gp;
            
                                    $adjustgaji = intval(round((($days / $pembagi) *$up)));
            
                                }
                }

                $gajipokok = $adjustgaji;

            } 
              
              else if ($kontrakberakhir && $bulan == $bulanabis && $tahun == $tahunabis && $tanggalresign == null){

                if($hitungpayroll =="kalender"){
                    
                    $startDateNow = "$tahun-$bulan-01";
                    $endDateNow = date("Y-m-t", strtotime($startDateNow)); // Last day of the month

                $akhir = new DateTime($kontrakberakhir);
                $awal = new DateTime($startDateNow);
                $interval = $akhir->diff($awal);
                
                $days = $interval->days + 1; 

                $pembagi = ((strtotime($endDateNow) - strtotime($startDateNow)) / (60 * 60 * 24))+1;

                if($hitungtujangan=="Yes"){

                    $gp = $gaji->gaji;
                    $tj = $tunjangandata->tunjangan;

                    $up = $gp + $tj;
        
                    $adjustgaji = intval(round((($days / $pembagi) *$up)));

                }else if ($hitungtujangan=="No"){
                    
                    $gp = $gaji -> gaji;
                    $up = $gp;

                    $adjustgaji = intval(round((($days / $pembagi) *$up)));

                }

                }else if($hitungpayroll == "harikerja"){
                    
                    $startDateNow = "$tahun-$bulan-01";
                    $endDateNow = date("Y-m-t", strtotime($startDateNow)); // Last day of the month

                    $holidayss = Holiday::whereBetween('date', [$startDateNow, $kontrakberakhir])->get();

                    $days = 0;

            
                    
                    foreach ($holidayss as $holiday) {
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


                if(!$days){
                    $request->session()->flash('error', "Data libur belum terdaftar.");
                    return redirect(route('invoice'));
                }

                    if(!$days){
                        $request->session()->flash('error', "Data libur belum terdaftar.");
                        return redirect(route('invoice'));
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
    


                    if($hitungtujangan=="Yes"){

                        $gp = $gaji->gaji;
                        $tj = $tunjangandata->tunjangan;
    
                        $up = $gp + $tj;
            
                        $adjustgaji = intval(round((($days / $pembagi) *$up)));
    
                    }else if ($hitungtujangan=="No"){
                        
                        $gp = $gaji -> gaji;
                        $up = $gp;
    
                        $adjustgaji = intval(round((($days / $pembagi) *$up)));
    
                    }

                }

              }   else if($tanggalresign && $bulan == $bulanresign && $tahun == $tahunresign) {

                    if($hitungpayroll =="kalender"){

                        $startDateNow = "$tahun-$bulan-01";
                        $endDateNow = date("Y-m-t", strtotime($startDateNow)); // Last day of the month
    
                    $resign = new DateTime($tanggalresign);
                    $awal = new DateTime($startDateNow);
                    $interval = $resign->diff($awal);
                    
                    $days = $interval->days + 1; 
                    $pembagi = ((strtotime($endDateNow) - strtotime($startDateNow)) / (60 * 60 * 24))+1;
    
                    if($hitungtujangan=="Yes"){

                        $gp = $gaji->gaji;
                        $tj = $tunjangandata->tunjangan;
    
                        $up = $gp + $tj;
            
                        $adjustgaji = intval(round((($days / $pembagi) *$up)));
    
                    }else if ($hitungtujangan=="No"){
                        
                        $gp = $gaji -> gaji;
                        $up = $gp;
    
                        $adjustgaji = intval(round((($days / $pembagi) *$up)));
    
                    }

                    } else if ($hitungpayroll =="harikerja"){

                        $startDateNow = "$tahun-$bulan-01";
                        $endDateNow = date("Y-m-t", strtotime($startDateNow)); // Last day of the month
    
                        $holidayss = Holiday::whereBetween('date', [$startDateNow, $tanggalresign])->get();

                        $days = 0;

                
                        
                        foreach ($holidayss as $holiday) {
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


                    if(!$days){
                        $request->session()->flash('error', "Data libur belum terdaftar.");
                        return redirect(route('invoice'));
                    }

                        if(!$days){
                            $request->session()->flash('error', "Data libur belum terdaftar.");
                            return redirect(route('invoice'));
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
    




                                    if($hitungtujangan=="Yes"){

                                        $gp = $gaji->gaji;
                                        $tj = $tunjangandata->tunjangan;
                    
                                        $up = $gp + $tj;
                            
                                        $adjustgaji = intval(round((($days / $pembagi) *$up)));
                    
                                    }else if ($hitungtujangan=="No"){
                                        
                                        $gp = $gaji -> gaji;
                                        $up = $gp;
                    
                                        $adjustgaji = intval(round((($days / $pembagi) *$up)));
                    
                                    }

                    }
                    
              } else if ($tanggalresign && (($bulanresign < $bulan && $tahun == $tahunresign) || ($bulan == 1 && $bulanresign == 12 && $tahun == $tahunresign + 1))) {

                $adjustgaji = 0;

              }

              else {

                $gajipokok = $gaji->gaji;

              }
              
     
          
              $tunjangan = $tunjangandata->tunjangan;


            $allowance = Allowance::where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->first();

          

            if(!$allowance){
                $request->session()->flash('error', "Silakan buat uang saku & insentif pada bulan $bulan tahun $tahun ");
                
                return redirect(route('invoice'));
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

            $penempatanid = $item->penempatan_id;

            $datapenempatan = Penempatan::find($penempatanid);

            $namapenempatan = $datapenempatan->nama_unit_kerja;

            $organisasiid = $datapenempatan->organisasi_id;

            $dataorganisasi = Organisasi::find($organisasiid);

            $namaorganisasi = $dataorganisasi->organisasi;

            if($hitungtujangan == "Yes"){
                $upah = $gajipokok + $tunjangan;
            }else if ($hitungtujangan == "No"){
                $upah = $gajipokok;
            }

            $bpjstk = intval(round(((4.24 / 100) *$upah)));
            $bpjskesehatan = intval(round(((4 / 100) *$upah)));
            $jaminanpensiun =  intval(round(((2 / 100) *$upah)));

            $thr = intval(round(((1 / 12) *$upah)));

            $kompensasi =  intval(round(((1 / 12) *$upah)));


            if($kodetemplate == 1){
                $subtotalbiayajasa = $upah + $insentif + $bpjstk + $bpjskesehatan + $jaminanpensiun;

            } else if ($kodetemplate == 2) {

                $subtotalbiayajasa = $upah + $thr + $kompensasi + $bpjstk + $bpjskesehatan + $jaminanpensiun;

            } else if ($kodetemplate == 3){
               $subtotalbiayajasa =0;
            }

            $managementfee = intval(round((($managementfeereq / 100) * $subtotalbiayajasa)));

            $totalbiayajasa = $subtotalbiayajasa + $managementfee;

        }

        $penempatan = $request->penempatan_id;

        if($penempatan == "all"){
            $penempatanstore = "Seluruh Penempatan";
        } else {

            $datapenempatan = Penempatan::find($penempatan);
            $penempatanstore = $datapenempatan->nama_unit_kerja;
        }

        Invoice::create([
            'judul_invoice' => "Invoice $namaorg ($penempatanstore) $bulanNama $currentyear",
            'bulan' => $currentmonth,
            'tahun' => $currentyear,
            'organisasi_id' => $organisasiid,
            'penempatan_id' => $penempatanreq,
            'created_by' => $loggedInUserName,
            'kode_invoice' => $kodeinvoice,
            'status_invoice' => "Created",
            'management_fee' => $request->management_fee,
        ]);


        $request->session()->flash('success', "Invoice berhasil ditambahkan.");

        return redirect()->route('invoice');

    }

    public function tampilinvoice($id){

        $invoice = Invoice::find($id);
        $loggedInUser = auth()->user(); 
        $loggedInUserName = $loggedInUser->nama_user;
        $kodeinvoice = $invoice->kode_invoice;
        $organisasiid = $invoice->organisasi_id;
        $currentmonth = $invoice->bulan;
    $currentyear = $invoice->tahun;

    if ($currentmonth == 1) {
        $bulan = 12;
        $tahun = $currentyear - 1;
    } else {
        $bulan = $currentmonth - 1;
        $tahun = $currentyear;
    }
 
        $managementfeereq = $invoice->management_fee; 

        $kodetemplate = $invoice->kode_invoice;

        $dateObj = DateTime::createFromFormat('!m', $currentmonth);
        $bulanNama = $dateObj->format('F'); // This will give the full month name

    $dataorg= Organisasi::find($organisasiid);

    $namaorg= $dataorg->organisasi;
     
    $startDate = "$tahun-$bulan-01";
    $endDate = date("Y-m-t", strtotime($startDate)); // Last day of the month

    $penempatanreq = $invoice->penempatan_id;

    $karyawan = Karyawan::whereHas('penempatan', function ($query) use ($organisasiid, $penempatanreq) {
        $query->where('organisasi_id', $organisasiid);
    
        // If penempatanreq is not 'all', filter by penempatan_id
        if ($penempatanreq !== 'all') {
            $query->where('penempatan_id', $penempatanreq);
        } else {
            // If penempatanreq is 'all', join with konfigurasi to check buat_invoice
            $query->whereHas('detailkonfigurasi', function ($query) {
                $query->where('buat_invoice', 'Yes');
            });
        }
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
    })->get();


$dataInvoice = [];
        foreach ($karyawan as $item) {

            $karyawanid = $item->id;
            $namakaryawan = $item->nama_karyawan;
            $awalkontrak = $item -> tanggal_awal_kontrak;
            $akhirkontrak = $item -> tanggal_akhir_kontrak;
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
        


            $tanggalawalkontrakformat = new DateTime($item->tanggal_bergabung);
            $bulanKontrak = $tanggalawalkontrakformat->format('n'); // bulan tanpa leading zero
            $tahunKontrak = $tanggalawalkontrakformat->format('Y'); // tahun dengan 4 digit
            $hariKontrak = $tanggalawalkontrakformat->format('j'); // hari tanpa leading zero

            $kontrakberakhir = $kontrak->tanggal_akhir_kontrak;
            $kontrakberakhirnew = new DateTime($kontrak->tanggal_akhir_kontrak);
            $bulanabis= $kontrakberakhirnew->format('n'); // bulan tanpa leading zero
            $tahunabis= $kontrakberakhirnew->format('Y'); // tahun dengan 4 digit
            $hariabis= $kontrakberakhirnew->format('j'); // hari tanpa leading zero

            $tanggalresignnew = new DateTime($item->tanggal_resign);
            $bulanresign= $tanggalresignnew->format('n'); // bulan tanpa leading zero
            $tahunresign= $tanggalresignnew->format('Y'); // tahun dengan 4 digit
            $hariresign= $tanggalresignnew->format('j'); // hari tanpa leading zero

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
        
                            
        
                          


            $penempatanid = $item -> penempatan_id;
            $datapenempatan = Penempatan::find($penempatanid);
            $namapenempatan = $datapenempatan->nama_unit_kerja;

            
            $konfigurasi = DetailKonfigurasi::where('penempatan_id', $penempatanid)->first();
    
           
    
            $hitungpayroll = $konfigurasi->perhitungan_payroll;

            $hitungtujangan = $konfigurasi ->hitung_tunjangan;

              if ($bulanKontrak == $bulan && $tahunKontrak == $tahun) {
               
               
                if($hitungpayroll =="kalender"){


                    $startDatebefore = "$tahun-$bulan-01";
                    $endDatebefore = date("Y-m-t", strtotime($startDatebefore)); // Last day of the month
    
                    $awalkontraktgl = $item->tanggal_bergabung;
    
                    $end = new DateTime($endDatebefore);
                    $awalKontrak = new DateTime($awalkontraktgl);
                    $interval = $end->diff($awalKontrak);
                    
                    $days = $interval->days + 1; ;
                   
                   
    
                    $pembagi = ((strtotime($endDatebefore) - strtotime($startDatebefore)) / (60 * 60 * 24))+1;


                    if($hitungtujangan=="Yes"){

                        $gp = $gaji->gaji;
                        $tj = $tunjangandata->tunjangan;

                        $up = $gp + $tj;


                        $adjustgaji = intval(round((($days / $pembagi) *$up)));

                    }else if ($hitungtujangan=="No"){
                        
                        $gp = $gaji -> gaji;
                        $up = $gp;

                        $adjustgaji = intval(round((($days / $pembagi) *$up)));

                    }

                } else if($hitungpayroll == "harikerja"){

                    $startDate = Carbon::createFromDate($item->tanggal_bergabung);
                    $endDate = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();
                
                    $holidayss = Holiday::whereBetween('date', [$startDate, $endDate])->get();

                    $days = 0;

            
                    
                    foreach ($holidayss as $holiday) {
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


                if(!$days){
                    $request->session()->flash('error', "Data libur belum terdaftar.");
                    return redirect(route('invoice'));
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
    



                                if($hitungtujangan=="Yes"){

                                    $gp = $gaji->gaji;
                                    $tj = $tunjangandata->tunjangan;
            
                                    $up = $gp + $tj;
                        
                                    $adjustgaji = intval(round((($days / $pembagi) *$up)));
            
                                }else if ($hitungtujangan=="No"){
                                    
                                    $gp = $gaji -> gaji;
                                    $up = $gp;
            
                                    $adjustgaji = intval(round((($days / $pembagi) *$up)));
            
                                }
                }

                $gajipokok = $adjustgaji;

            } 
              
              else if ($kontrakberakhir && $bulan == $bulanabis && $tahun == $tahunabis && $tanggalresign == null){

                if($hitungpayroll =="kalender"){
                    
                    $startDateNow = "$tahun-$bulan-01";
                    $endDateNow = date("Y-m-t", strtotime($startDateNow)); // Last day of the month

                $akhir = new DateTime($kontrakberakhir);
                $awal = new DateTime($startDateNow);
                $interval = $akhir->diff($awal);
                
                $days = $interval->days + 1; 

                $pembagi = ((strtotime($endDateNow) - strtotime($startDateNow)) / (60 * 60 * 24))+1;

                if($hitungtujangan=="Yes"){

                    $gp = $gaji->gaji;
                    $tj = $tunjangandata->tunjangan;

                    $up = $gp + $tj;
        
                    $adjustgaji = intval(round((($days / $pembagi) *$up)));

                }else if ($hitungtujangan=="No"){
                    
                    $gp = $gaji -> gaji;
                    $up = $gp;

                    $adjustgaji = intval(round((($days / $pembagi) *$up)));

                }

                }else if($hitungpayroll == "harikerja"){
                    
                    $startDateNow = "$tahun-$bulan-01";
                    $endDateNow = date("Y-m-t", strtotime($startDateNow)); // Last day of the month

                    $holidayss = Holiday::whereBetween('date', [$startDate, $kontrakberakhir])->get();

                    $days = 0;

            
                    
                    foreach ($holidayss as $holiday) {
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


                if(!$days){
                    $request->session()->flash('error', "Data libur belum terdaftar.");
                    return redirect(route('invoice'));
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



                    if($hitungtujangan=="Yes"){

                        $gp = $gaji->gaji;
                        $tj = $tunjangandata->tunjangan;
    
                        $up = $gp + $tj;
            
                        $adjustgaji = intval(round((($days / $pembagi) *$up)));
    
                    }else if ($hitungtujangan=="No"){
                        
                        $gp = $gaji -> gaji;
                        $up = $gp;
    
                        $adjustgaji = intval(round((($days / $pembagi) *$up)));
    
                    }

                }

              }   else if($tanggalresign && $bulan == $bulanresign && $tahun == $tahunresign) {

                    if($hitungpayroll =="kalender"){

                        $startDateNow = "$tahun-$bulan-01";
                        $endDateNow = date("Y-m-t", strtotime($startDateNow)); // Last day of the month
    
                    $resign = new DateTime($tanggalresign);
                    $awal = new DateTime($startDateNow);
                    $interval = $resign->diff($awal);
                    
                    $days = $interval->days + 1; 
                    $pembagi = ((strtotime($endDateNow) - strtotime($startDateNow)) / (60 * 60 * 24))+1;
    
                    if($hitungtujangan=="Yes"){

                        $gp = $gaji->gaji;
                        $tj = $tunjangandata->tunjangan;
    
                        $up = $gp + $tj;
            
                        $adjustgaji = intval(round((($days / $pembagi) *$up)));
    
                    }else if ($hitungtujangan=="No"){
                        
                        $gp = $gaji -> gaji;
                        $up = $gp;
    
                        $adjustgaji = intval(round((($days / $pembagi) *$up)));
    
                    }

                    } else if ($hitungpayroll =="harikerja"){

                        $startDateNow = "$tahun-$bulan-01";
                        $endDateNow = date("Y-m-t", strtotime($startDateNow)); // Last day of the month
    
                        $holidayss = Holiday::whereBetween('date', [$startDateNow, $tanggalresign])->get();

                        $days = 0;

                
                        
                        foreach ($holidayss as $holiday) {
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


                    if(!$days){
                        $request->session()->flash('error', "Data libur belum terdaftar.");
                        return redirect(route('invoice'));
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
    
                                    if($hitungtujangan=="Yes"){

                                        $gp = $gaji->gaji;
                                        $tj = $tunjangandata->tunjangan;
                    
                                        $up = $gp + $tj;
                            
                                        $adjustgaji = intval(round((($days / $pembagi) *$up)));
                    
                                    }else if ($hitungtujangan=="No"){
                                        
                                        $gp = $gaji -> gaji;
                                        $up = $gp;
                    
                                        $adjustgaji = intval(round((($days / $pembagi) *$up)));
                    
                                    }

                    }
                    
              } else if ($tanggalresign && (($bulanresign < $bulan && $tahun == $tahunresign) || ($bulan == 1 && $bulanresign == 12 && $tahun == $tahunresign + 1))) {

                $adjustgaji = 0;

              }

              else {

                $gajipokok = $gaji->gaji;

              }
              
       
           
              $tunjangan = $tunjangandata->tunjangan;


            $allowance = Allowance::where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->first();

          

            if(!$allowance){
                $request->session()->flash('error', "Silakan buat uang saku & insentif pada bulan $bulan tahun $tahun ");
                
                return redirect(route('invoice'));
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

            $penempatanid = $item->penempatan_id;

            $datapenempatan = Penempatan::find($penempatanid);

            $namapenempatan = $datapenempatan->nama_unit_kerja;

            $organisasiid = $datapenempatan->organisasi_id;

            $dataorganisasi = Organisasi::find($organisasiid);

            $namaorganisasi = $dataorganisasi->organisasi;

            if($hitungtujangan == "Yes"){
                $upah = $gajipokok + $tunjangan;
            }else if ($hitungtujangan == "No"){
                $upah = $gajipokok;
            }

            $bpjstk = intval(round(((4.24 / 100) *$upah)));
            $bpjskesehatan = intval(round(((4 / 100) *$upah)));
            $jaminanpensiun =  intval(round(((2 / 100) *$upah)));

            $thr = intval(round(((1 / 12) *$upah)));

            $kompensasi =  intval(round(((1 / 12) *$upah)));


          
                $subtotalbiayajasa = $upah + $insentif + $bpjstk + $bpjskesehatan + $jaminanpensiun;

          

            $managementfee = intval(round((($managementfeereq / 100) * $subtotalbiayajasa)));

            $totalbiayajasa = $subtotalbiayajasa + $managementfee;

            $joindate = $item -> tanggal_bergabung;
            
         
        
            $dataInvoice[] = [
                'karyawanid' => $karyawanid,
                'nama' => $namakaryawan,
                'joindate' => $joindate,
                'insentif' => $insentif,
                'bpjstk' => $bpjstk,
                'bpjskesehatan' => $bpjskesehatan,
                'jaminanpensiun' => $jaminanpensiun,
                'subtotalbiayajasa' => $subtotalbiayajasa,
                'managementfee' => $managementfee,
                'totalbiayajasa' => $totalbiayajasa,
                'gajipokok' => $gajipokok,
                'tunjangan' => $tunjangan,
            ];

        }

   

        return view('invoice.tampilinvoice',[
            'dataInvoice' => $dataInvoice,
            'invoice' => $invoice,
        ]);

    }

    public function tampilinvoice2( $id){

        $invoice = Invoice::find($id);
        $loggedInUser = auth()->user(); 
        $loggedInUserName = $loggedInUser->nama_user;
        $kodeinvoice = $invoice->kode_invoice;
        $organisasiid = $invoice->organisasi_id;
        $currentmonth = $invoice->bulan;
    $currentyear = $invoice->tahun;

    if ($currentmonth == 1) {
        $bulan = 12;
        $tahun = $currentyear - 1;
    } else {
        $bulan = $currentmonth - 1;
        $tahun = $currentyear;
    }
 
        $managementfeereq = $invoice->management_fee; 

        $kodetemplate = $invoice->kode_invoice;

        $dateObj = DateTime::createFromFormat('!m', $currentmonth);
        $bulanNama = $dateObj->format('F'); // This will give the full month name

    $dataorg= Organisasi::find($organisasiid);

    $namaorg= $dataorg->organisasi;
     
    $startDate = "$tahun-$bulan-01";
    $endDate = date("Y-m-t", strtotime($startDate)); // Last day of the month

    $penempatanreq = $invoice->penempatan_id;

    $karyawan = Karyawan::whereHas('penempatan', function ($query) use ($organisasiid, $penempatanreq) {
        $query->where('organisasi_id', $organisasiid);
    
        // If penempatanreq is not 'all', filter by penempatan_id
        if ($penempatanreq !== 'all') {
            $query->where('penempatan_id', $penempatanreq);
        } else {
            // If penempatanreq is 'all', join with konfigurasi to check buat_invoice
            $query->whereHas('detailkonfigurasi', function ($query) {
                $query->where('buat_invoice', 'Yes');
            });
        }
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
    })->get();



$dataInvoicedua = [];
        foreach ($karyawan as $item) {

            $karyawanid = $item->id;
            $namakaryawan = $item->nama_karyawan;
            $awalkontrak = $item -> tanggal_awal_kontrak;
            $akhirkontrak = $item -> tanggal_akhir_kontrak;
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
        

           
            $tanggalawalkontrakformat = new DateTime($item->tanggal_bergabung);
            $bulanKontrak = $tanggalawalkontrakformat->format('n'); // bulan tanpa leading zero
            $tahunKontrak = $tanggalawalkontrakformat->format('Y'); // tahun dengan 4 digit
            $hariKontrak = $tanggalawalkontrakformat->format('j'); // hari tanpa leading zero

            $kontrakberakhir = $kontrak->tanggal_akhir_kontrak;
            $kontrakberakhirnew = new DateTime($kontrak->tanggal_akhir_kontrak);
            $bulanabis= $kontrakberakhirnew->format('n'); // bulan tanpa leading zero
            $tahunabis= $kontrakberakhirnew->format('Y'); // tahun dengan 4 digit
            $hariabis= $kontrakberakhirnew->format('j'); // hari tanpa leading zero

            $tanggalresignnew = new DateTime($item->tanggal_resign);
            $bulanresign= $tanggalresignnew->format('n'); // bulan tanpa leading zero
            $tahunresign= $tanggalresignnew->format('Y'); // tahun dengan 4 digit
            $hariresign= $tanggalresignnew->format('j'); // hari tanpa leading zero
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


                


            $penempatanid = $item -> penempatan_id;
            $datapenempatan = Penempatan::find($penempatanid);
            $namapenempatan = $datapenempatan->nama_unit_kerja;

            
            $konfigurasi = DetailKonfigurasi::where('penempatan_id', $penempatanid)->first();
    
           
    
            $hitungpayroll = $konfigurasi->perhitungan_payroll;

            $hitungtujangan = $konfigurasi ->hitung_tunjangan;

              if ($bulanKontrak == $bulan && $tahunKontrak == $tahun) {
               
               
                if($hitungpayroll =="kalender"){


                    $startDatebefore = "$tahun-$bulan-01";
                    $endDatebefore = date("Y-m-t", strtotime($startDatebefore)); // Last day of the month
    
                    $awalkontraktgl = $item->tanggal_bergabung;
    
                    $end = new DateTime($endDatebefore);
                    $awalKontrak = new DateTime($awalkontraktgl);
                    $interval = $end->diff($awalKontrak);
                    
                    $days = $interval->days + 1; ;
                   
                   
    
                    $pembagi = ((strtotime($endDatebefore) - strtotime($startDatebefore)) / (60 * 60 * 24))+1;

                 

                    if($hitungtujangan=="Yes"){

                        $gp = $gaji->gaji;
                        $tj = $tunjangandata->tunjangan;

                        $up = $gp + $tj;


                        $adjustgaji = intval(round((($days / $pembagi) *$up)));

                    }else if ($hitungtujangan=="No"){
                        
                        $gp = $gaji -> gaji;
                        $up = $gp;

                        $adjustgaji = intval(round((($days / $pembagi) *$up)));

                    }

                    

                } else if($hitungpayroll == "harikerja"){

                    $startDate = Carbon::createFromDate($item->tanggal_bergabung);
                    $endDate = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();
                
                    $holidayss = Holiday::whereBetween('date', [$startDate, $endDate])->get();

                    $days = 0;

            
                    
                    foreach ($holidayss as $holiday) {
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


                if(!$days){
                    $request->session()->flash('error', "Data libur belum terdaftar.");
                    return redirect(route('invoice'));
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




                                if($hitungtujangan=="Yes"){

                                    $gp = $gaji->gaji;
                                    $tj = $tunjangandata->tunjangan;
            
                                    $up = $gp + $tj;
                        
                                    $adjustgaji = intval(round((($days / $pembagi) *$up)));
            
                                }else if ($hitungtujangan=="No"){
                                    
                                    $gp = $gaji -> gaji;
                                    $up = $gp;
            
                                    $adjustgaji = intval(round((($days / $pembagi) *$up)));
            
                                }
                }

                $gajipokok = $adjustgaji;

            } 
              
              else if ($kontrakberakhir && $bulan == $bulanabis && $tahun == $tahunabis && $tanggalresign == null){

                if($hitungpayroll =="kalender"){
                    
                    $startDateNow = "$tahun-$bulan-01";
                    $endDateNow = date("Y-m-t", strtotime($startDateNow)); // Last day of the month

                $akhir = new DateTime($kontrakberakhir);
                $awal = new DateTime($startDateNow);
                $interval = $akhir->diff($awal);
                
                $days = $interval->days + 1; 

                $pembagi = ((strtotime($endDateNow) - strtotime($startDateNow)) / (60 * 60 * 24))+1;

                if($hitungtujangan=="Yes"){

                    $gp = $gaji->gaji;
                    $tj = $tunjangandata->tunjangan;

                    $up = $gp + $tj;
        
                    $adjustgaji = intval(round((($days / $pembagi) *$up)));

                }else if ($hitungtujangan=="No"){
                    
                    $gp = $gaji -> gaji;
                    $up = $gp;

                    $adjustgaji = intval(round((($days / $pembagi) *$up)));

                }

                }else if($hitungpayroll == "harikerja"){
                    
                    $startDateNow = "$tahun-$bulan-01";
                    $endDateNow = date("Y-m-t", strtotime($startDateNow)); // Last day of the month

                    $holidayss = Holiday::whereBetween('date', [$startDateNow, $kontrakberakhir])->get();

                    $days = 0;

            
                    
                    foreach ($holidayss as $holiday) {
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


                if(!$days){
                    $request->session()->flash('error', "Data libur belum terdaftar.");
                    return redirect(route('invoice'));
                }


                    if(!$days){
                        $request->session()->flash('error', "Data libur belum terdaftar.");
                        return redirect(route('invoice'));
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


                    if($hitungtujangan=="Yes"){

                        $gp = $gaji->gaji;
                        $tj = $tunjangandata->tunjangan;
    
                        $up = $gp + $tj;
            
                        $adjustgaji = intval(round((($days / $pembagi) *$up)));
    
                    }else if ($hitungtujangan=="No"){
                        
                        $gp = $gaji -> gaji;
                        $up = $gp;
    
                        $adjustgaji = intval(round((($days / $pembagi) *$up)));
    
                    }

                }

              }   else if($tanggalresign && $bulan == $bulanresign && $tahun == $tahunresign) {

                    if($hitungpayroll =="kalender"){

                        $startDateNow = "$tahun-$bulan-01";
                        $endDateNow = date("Y-m-t", strtotime($startDateNow)); // Last day of the month
    
                    $resign = new DateTime($tanggalresign);
                    $awal = new DateTime($startDateNow);
                    $interval = $resign->diff($awal);
                    
                    $days = $interval->days + 1; 
                    $pembagi = ((strtotime($endDateNow) - strtotime($startDateNow)) / (60 * 60 * 24))+1;
    
                    if($hitungtujangan=="Yes"){

                        $gp = $gaji->gaji;
                        $tj = $tunjangandata->tunjangan;
    
                        $up = $gp + $tj;
            
                        $adjustgaji = intval(round((($days / $pembagi) *$up)));
    
                    }else if ($hitungtujangan=="No"){
                        
                        $gp = $gaji -> gaji;
                        $up = $gp;
    
                        $adjustgaji = intval(round((($days / $pembagi) *$up)));
    
                    }

                    } else if ($hitungpayroll =="harikerja"){

                        $startDateNow = "$tahun-$bulan-01";
                        $endDateNow = date("Y-m-t", strtotime($startDateNow)); // Last day of the month
    
                        $holidayss = Holiday::whereBetween('date', [$startDateNow, $tanggalresign])->get();

                        $days = 0;

                
                        
                        foreach ($holidayss as $holiday) {
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


                    if(!$days){
                        $request->session()->flash('error', "Data libur belum terdaftar.");
                        return redirect(route('invoice'));
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
                                    if($hitungtujangan=="Yes"){

                                        $gp = $gaji->gaji;
                                        $tj = $tunjangandata->tunjangan;
                    
                                        $up = $gp + $tj;
                            
                                        $adjustgaji = intval(round((($days / $pembagi) *$up)));
                    
                                    }else if ($hitungtujangan=="No"){
                                        
                                        $gp = $gaji -> gaji;
                                        $up = $gp;
                    
                                        $adjustgaji = intval(round((($days / $pembagi) *$up)));
                    
                                    }

                    }
                    
              } else if ($tanggalresign && (($bulanresign < $bulan && $tahun == $tahunresign) || ($bulan == 1 && $bulanresign == 12 && $tahun == $tahunresign + 1))) {

                $adjustgaji = 0;

              }

              else {

                $gajipokok = $gaji->gaji;

              }
              
       
           
              $tunjangan = $gaji->tunjangan;


            $allowance = Allowance::where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->first();

          

            if(!$allowance){
                $request->session()->flash('error', "Silakan buat uang saku & insentif pada bulan $bulan tahun $tahun ");
                
                return redirect(route('invoice'));
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

            $penempatanid = $item->penempatan_id;

            $datapenempatan = Penempatan::find($penempatanid);

            $namapenempatan = $datapenempatan->nama_unit_kerja;

            $organisasiid = $datapenempatan->organisasi_id;

            $dataorganisasi = Organisasi::find($organisasiid);

            $namaorganisasi = $dataorganisasi->organisasi;

            if($hitungtujangan == "Yes"){
                $upah = $gajipokok + $tunjangan;
            }else if ($hitungtujangan == "No"){
                $upah = $gajipokok;
            }

            $bpjstk = intval(round(((4.24 / 100) *$upah)));
            $bpjskesehatan = intval(round(((4 / 100) *$upah)));
            $jaminanpensiun =  intval(round(((2 / 100) *$upah)));

            $thr = intval(round(((1 / 12) *$upah)));

            $kompensasi =  intval(round(((1 / 12) *$upah)));


          
                $subtotalbiayajasa = $upah + $thr + $kompensasi + $bpjstk + $bpjskesehatan + $jaminanpensiun;

          

            $managementfee = intval(round((($managementfeereq / 100) * $subtotalbiayajasa)));

            $totalbiayajasa = $subtotalbiayajasa + $managementfee;

            $joindate = $item -> tanggal_bergabung;
            
         
            $karyawanid = $item->id;
            $dataInvoicedua[] = [
                'nama' => $namakaryawan,
                'karyawanid' => $karyawanid,
                'joindate' => $joindate,
                'insentif' => $insentif,
                'bpjstk' => $bpjstk,
                'bpjskesehatan' => $bpjskesehatan,
                'jaminanpensiun' => $jaminanpensiun,
                'subtotalbiayajasa' => $subtotalbiayajasa,
                'managementfee' => $managementfee,
                'totalbiayajasa' => $totalbiayajasa,
                'gajipokok' => $gajipokok,
            ];

        }

        
        return view('invoice.tampilinvoice2',[
            'dataInvoicedua' => $dataInvoicedua,
            'invoice' => $invoice,
        ]);

    }

    


    
    
    


    public function closeinvoice(Request $request, $id){
        $invoice = Invoice::find($id);
        $invoice -> status_invoice = "Closing";
      
        $invoice->save();

       

        $datainvoice = json_decode($request->input('dataInvoice'), true);

      

        foreach ($datainvoice as $data) {
  
            $detailinvoice = new DetailInvoice();
            $detailinvoice->invoice_id = $invoice->id;
            $detailinvoice->karyawan_id = $data['karyawanid'];
            $detailinvoice->gajipokok = $data['gajipokok'];
            $detailinvoice->biayatransport = $data['insentif'];
            $detailinvoice->bpjs_tk = $data['bpjstk'];
            $detailinvoice->bpjs_kesehatan = $data['bpjskesehatan'];
            $detailinvoice->jaminan_pensiun = $data['jaminanpensiun'];
            $detailinvoice->subtotal_biaya_jasa = $data['subtotalbiayajasa'];
            $detailinvoice->management_fee=$data['managementfee'];
            $detailinvoice->total_biaya_jasa= $data['totalbiayajasa'];
            $detailinvoice->tunjangan = $data['tunjangan'];

            $detailinvoice->save();
        }

        return redirect()->back()->with('success', 'Invoice berhasil closing dan tersimpan.');
    }


    public function closeinvoicekedua(Request $request, $id){
        $invoice = Invoice::find($id);
        $invoice -> status_invoice = "Closing";
      
        $invoice->save();

       

        $datainvoice = json_decode($request->input('dataInvoicedua'), true);

      
      

        foreach ($datainvoice as $data) {
  
            $detailinvoice = new DetailInvoice();
            $detailinvoice->invoice_id = $invoice->id;
            $detailinvoice->karyawan_id = $data['karyawanid'];
            $detailinvoice->gajipokok = $data['gajipokok'];
            $detailinvoice->biayatransport = $data['insentif'];
            $detailinvoice->bpjs_tk = $data['bpjstk'];
            $detailinvoice->bpjs_kesehatan = $data['bpjskesehatan'];
            $detailinvoice->jaminan_pensiun = $data['jaminanpensiun'];
            $detailinvoice->subtotal_biaya_jasa = $data['subtotalbiayajasa'];
            $detailinvoice->management_fee=$data['managementfee'];
            $detailinvoice->total_biaya_jasa= $data['totalbiayajasa'];

            $detailinvoice->save();
        }

        return redirect()->back()->with('success', 'Invoice berhasil closing dan tersimpan.');
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


    public function batalkanClosinginvoice($id, Request $request)
    {
        $invoice = Invoice::find($id);
        $bulan = $invoice->bulan;

        $timestamp = strtotime($bulan); // Ubah string tanggal ke timestamp
        $bulannama = \Carbon\Carbon::createFromFormat('m', $bulan)->translatedFormat('F');


       


        $tahun = $invoice->tahun;

        
      
        $invoiceid = $invoice->id;

        DetailInvoice::where('invoice_id', $invoiceid)->delete();


        if ($invoice) {
            $invoice->status_invoice = 'Created';
            $invoice->save();
        }
        return redirect()->route('invoice')->with('success', "Closing invoice bulan $bulannama $tahun berhasil dibatalkan");

       
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
