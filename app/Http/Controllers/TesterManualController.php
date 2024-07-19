<?php

namespace App\Http\Controllers;

use App\Models\Allowance;
use App\Models\Attendance;
use App\Models\DetailAllowance;
use App\Models\DetailGajiTMdanKnowledge;
use App\Models\DetailKonfigurasi;
use App\Models\DetailTesterManual;
use App\Models\GajiTMdanKnowledge;
use App\Models\Holiday;
use App\Models\Karyawan;
use App\Models\Overtime;
use App\Models\Penempatan;
use App\Models\TesterManual;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;

class TesterManualController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $testermanual = TesterManual::orderBy('created_at','desc')->get();
        return view('testermanual.index',[
            'testermanual' => $testermanual,
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
    $currentmonth = $request->bulan;
    $currentyear = $request->tahun;

    if ($currentmonth == 1) {
        $bulan = 12;
        $tahun = $currentyear - 1;
    } else {
        $bulan = $currentmonth - 1;
        $tahun = $currentyear;
    }

    $existingdata = TesterManual::where('bulan', $bulan)
    ->where('tahun', $tahun)
    ->first();

    if($existingdata){
        $request->session()->flash('error', "Invoice tester manual pada bulan $bulan dan tahun $tahun sudah terdaftar.");

        return redirect()->route('testermanual');
    }

    $managementfeereq = $request->management_fee;
    $startDate = "$tahun-$bulan-01";
    $endDate = date("Y-m-t", strtotime($startDate)); // Last day of the month

    $loggedInUser = auth()->user(); 
    $loggedInUserName = $loggedInUser->nama_user;

    $dateObj = DateTime::createFromFormat('!m', $currentmonth);
    $bulanNama = $dateObj->format('F'); // This will give the full month name
    
    $karyawan = Karyawan::leftJoin('penempatans', 'karyawans.penempatan_id', '=', 'penempatans.id')
    ->leftJoin('organisasis', 'penempatans.organisasi_id', '=', 'organisasis.id')
    ->where('organisasis.organisasi', 'BCA Finance Tester Manual')
    ->where(function ($query) use ($startDate, $endDate) {
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
    })
    ->select('karyawans.*')
    ->get();

    $datainvoicetm =[];
    foreach ($karyawan as $item) {

        $karyawanid = $item->id;
        $namakaryawan = $item->nama_karyawan;
    
        $datagajitm = GajiTMdanKnowledge::where('karyawan_id', $karyawanid)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->first();
    
        if(!$datagajitm){
            $request->session()->flash('error', "Silahkan buat Gaji dan Cadangan Transfer Knowledge untuk $namakaryawan pada bulan $bulan tahun $tahun.");
            return redirect(route('testermanual'));
        }
    
        $gaji = $datagajitm->gaji_tm;
        $tfknowledge = $datagajitm->cadangan_tfknowledge;
    
        $bpjstk = 4.24 / 100 * $gaji;
        $bpjskesehatan = 4/100 * $gaji;
        $jaminanpensiun = 2 / 100 * $gaji;
    
        $thr = intval(round(1/12 * $gaji));
        $kompensasi = intval(round(1/12 * $gaji));
    
        $benefit = intval(round($bpjstk + $bpjskesehatan + $jaminanpensiun));
    
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
            $insentif = 0;
        }
    
        $subtotalbiayajasa = $gaji + $benefit + $thr + $kompensasi + $insentif + $tfknowledge;
        $managementfeeamount = intval(round($managementfeereq/100 * $subtotalbiayajasa));
        $totalbiayajasa = $subtotalbiayajasa + $managementfeeamount;
    
        $overtimeData = Overtime::whereYear('date', $tahun)
            ->whereMonth('date', $bulan)
            ->where('karyawan_id', $karyawanid)
            ->whereHas('karyawan', function ($query) {
                $query->whereHas('penempatan', function ($query) {
                    $query->whereHas('organisasi', function ($query) {
                        $query->where('organisasi', 'BCA Finance Tester Manual');
                    });
                });
            })
            ->get();

            $penempatanid = $item->penempatan_id;
            $datapenempatan = Penempatan::find($penempatanid);
            $namapenempatan = $datapenempatan->nama_unit_kerja;
    
        $tanggallembur = null;
        $totalJamHrKerja = 0;
        $totalJamHrLibur = 0;
        $biayaLemburRekap = 0;
        $tanggalLemburArray = []; // Reset the array here
    
        foreach ($overtimeData as $data) {
            $tanggallembur = $data->date;
            $tanggalLembur = new DateTime($tanggallembur);
            $hariDalamMinggu = $tanggalLembur->format('N');
            $tanggalLemburArray[] = $tanggallembur; // Add lembur date to array
            $dataholiday = Holiday::where('date', $tanggallembur)->first();
            $statusHari = $dataholiday ? $dataholiday->description : 'Kerja';
    
            $hoursWorked = $data->overtime_payment;
            if ($statusHari == "Kerja") {
                if ($hoursWorked <= 1) {
                    $jampertama = $hoursWorked;
                    $jamkedua = 0;
                    $jamketiga = 0;
                    $jamkeempat = 0;
                } else if ($hoursWorked > 1) {
                    $jampertama = 1;
                    $jamkedua = $hoursWorked - 1;
                    $jamketiga = 0;
                    $jamkeempat = 0;
                }
    
                $totalJamHrKerja += ($jampertama + $jamkedua + $jamketiga + $jamkeempat);
    
            } else if ($statusHari == "Libur") {
                if ($hoursWorked <= 7) {
                    $jampertama = 0;
                    $jamkedua = $hoursWorked;
                    $jamketiga = 0;
                    $jamkeempat = 0;
                } else if ($hoursWorked > 7) {
                    $jampertama = 0;
                    $jamkedua = 7;
                    $jamketiga = 1;
                    $jamkeempat = $hoursWorked - 8;
                }
    
                $totalJamHrLibur += ($jamkedua + $jamketiga + $jamkeempat);
            }
        }
    
        $biayalembur = (($totalJamHrKerja * 73500) + ($totalJamHrLibur * 85000));
               
        $konfigurasi = DetailKonfigurasi::where('penempatan_id', $penempatanid)->first();
    
        if (!$konfigurasi) {
            $request->session()->flash('error', "Konfigurasi untuk $namapenempatan belum terdaftar.");
            return redirect(route('testermanual'));
        }
    
        $hitungpayroll = $konfigurasi->perhitungan_payroll;


        if($hitungpayroll =="harikerja"){
            $mulaitgl = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
            $akhirtgl = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();
    
        
                        $totalhari = Holiday::where('description', 'Kerja')
                        ->whereBetween('date', [$mulaitgl, $akhirtgl])
                        ->count();

        }else if ($hitungpayroll=="kalender"){
            $mulaitglnow = "$tahun-$bulan-01";
            $selesaitglnow = date("Y-m-t", strtotime($mulaitglnow)); // Last day of the month

            $totalhari = ((strtotime($selesaitglnow) - strtotime($mulaitglnow)) / (60 * 60 * 24))+1;

        }


        $attendanceCount = Attendance::where('karyawan_id', $karyawanid)
        ->whereNotIn('attendance_code', ['H'])
        ->whereNotNull('attendance_code')
        ->whereMonth('date', $bulan)
        ->whereYear('date', $tahun)
        ->count();
    
       $absen = $attendanceCount;
       $realisasiharikerja = $totalhari - $absen;
       $presentasekehadiran = intval(round(( ($realisasiharikerja/$totalhari) * 100)));

       $realisasiinvoice = intval(round(( (($realisasiharikerja/$totalhari) * $totalbiayajasa)) + $biayalembur));
    
        $datainvoicetm[] = [
            'karyawanid' => $karyawanid,
            'nama' => $namakaryawan,
            'tanggal_lembur' => implode(', ', $tanggalLemburArray),
            'totaljamharikerja' => $totalJamHrKerja,
            'totaljamharilibur' => $totalJamHrLibur,
            'biayalembur' => $biayalembur,
            'totalhari' => $totalhari,
            'absen' => $absen,
            'realisasiharikerja' => $realisasiharikerja,
            'presentasekehadiran' => $presentasekehadiran,
            'biayajasaperbulan' => $totalbiayajasa,
            'realisasiinvoice' => $realisasiinvoice,
            'management_fee' => $managementfeereq,
        ];

    }

    TesterManual::create([
        'bulan' => $currentmonth,
        'tahun' => $currentyear,
        'judul_invoicetm' => "Invoice Tester Manual $bulanNama $currentyear",
        'created_by' => $loggedInUserName,
        'status_invoicetm' => "Created",
        'management_fee' => $managementfeereq,
    ]);

    $request->session()->flash('success', "Invoice tester manual berhasil dibuat.");

    return redirect()->route('testermanual');
    
    }

    public function tampiltestermanual($id){

        $datatm = TesterManual::find($id);
       
      
        $currentmonth = $datatm->bulan;
        $currentyear = $datatm->tahun;
    
        if ($currentmonth == 1) {
            $bulan = 12;
            $tahun = $currentyear - 1;
        } else {
            $bulan = $currentmonth - 1;
            $tahun = $currentyear;
        }

       
    
        $managementfeereq = $datatm->management_fee;
        $startDate = "$tahun-$bulan-01";
        $endDate = date("Y-m-t", strtotime($startDate)); // Last day of the month
    
        $loggedInUser = auth()->user(); 
        $loggedInUserName = $loggedInUser->nama_user;
    
        $dateObj = DateTime::createFromFormat('!m', $currentmonth);
        $bulanNama = $dateObj->format('F'); // This will give the full month name
        
        $karyawan = Karyawan::leftJoin('penempatans', 'karyawans.penempatan_id', '=', 'penempatans.id')
        ->leftJoin('organisasis', 'penempatans.organisasi_id', '=', 'organisasis.id')
        ->where('organisasis.organisasi', 'BCA Finance Tester Manual')
        ->where(function ($query) use ($startDate, $endDate) {
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
        })
        ->select('karyawans.*')
        ->get();
    
        $datainvoicetm =[];
        foreach ($karyawan as $item) {
    
            $karyawanid = $item->id;
            $namakaryawan = $item->nama_karyawan;
        
            $datagajitm = GajiTMdanKnowledge::where('karyawan_id', $karyawanid)
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->first();
        
        
        
            $gaji = $datagajitm->gaji_tm;
            $tfknowledge = $datagajitm->cadangan_tfknowledge;
        
            $bpjstk = 4.24 / 100 * $gaji;
            $bpjskesehatan = 4/100 * $gaji;
            $jaminanpensiun = 2 / 100 * $gaji;
        
            $thr = intval(round(1/12 * $gaji));
            $kompensasi = intval(round(1/12 * $gaji));
        
            $benefit = intval(round($bpjstk + $bpjskesehatan + $jaminanpensiun));
        
            $allowance = Allowance::where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->first();
        
           
        
            $allowanceid = $allowance->id;
        
            $detailallowance = DetailAllowance::where('allowance_id', $allowanceid)
                ->where ('karyawan_id', $karyawanid)
                ->first();
        
            if ($detailallowance) {
                $uangsaku = $detailallowance->uang_saku;
                $insentif = $detailallowance->insentif;
            } else {
                $uangsaku = 0;
                $insentif = 0;
            }
        
            $subtotalbiayajasa = $gaji + $benefit + $thr + $kompensasi + $insentif + $tfknowledge;
            $managementfeeamount = intval(round($managementfeereq/100 * $subtotalbiayajasa));
            $totalbiayajasa = $subtotalbiayajasa + $managementfeeamount;
        
            $overtimeData = Overtime::whereYear('date', $tahun)
                ->whereMonth('date', $bulan)
                ->where('karyawan_id', $karyawanid)
                ->whereHas('karyawan', function ($query) {
                    $query->whereHas('penempatan', function ($query) {
                        $query->whereHas('organisasi', function ($query) {
                            $query->where('organisasi', 'BCA Finance Tester Manual');
                        });
                    });
                })
                ->get();
    
                $penempatanid = $item->penempatan_id;
                $datapenempatan = Penempatan::find($penempatanid);
                $namapenempatan = $datapenempatan->nama_unit_kerja;
        
            $tanggallembur = null;
            $totalJamHrKerja = 0;
            $totalJamHrLibur = 0;
            $biayaLemburRekap = 0;
            $tanggalLemburArray = []; // Reset the array here
        
            foreach ($overtimeData as $data) {
                $tanggallembur = $data->date;
                $tanggalLembur = new DateTime($tanggallembur);
                $hariDalamMinggu = $tanggalLembur->format('N');
                $tanggalLemburArray[] = $tanggallembur; // Add lembur date to array
                $dataholiday = Holiday::where('date', $tanggallembur)->first();
                $statusHari = $dataholiday ? $dataholiday->description : 'Kerja';
        
                $hoursWorked = $data->overtime_payment;
                if ($statusHari == "Kerja") {
                    if ($hoursWorked <= 1) {
                        $jampertama = $hoursWorked;
                        $jamkedua = 0;
                        $jamketiga = 0;
                        $jamkeempat = 0;
                    } else if ($hoursWorked > 1) {
                        $jampertama = 1;
                        $jamkedua = $hoursWorked - 1;
                        $jamketiga = 0;
                        $jamkeempat = 0;
                    }
        
                    $totalJamHrKerja += ($jampertama + $jamkedua + $jamketiga + $jamkeempat);
        
                } else if ($statusHari == "Libur") {
                    if ($hoursWorked <= 7) {
                        $jampertama = 0;
                        $jamkedua = $hoursWorked;
                        $jamketiga = 0;
                        $jamkeempat = 0;
                    } else if ($hoursWorked > 7) {
                        $jampertama = 0;
                        $jamkedua = 7;
                        $jamketiga = 1;
                        $jamkeempat = $hoursWorked - 8;
                    }
        
                    $totalJamHrLibur += ($jamkedua + $jamketiga + $jamkeempat);
                }
            }
        
            $biayalembur = (($totalJamHrKerja * 73500) + ($totalJamHrLibur * 85000));
    
    
                   
            $konfigurasi = DetailKonfigurasi::where('penempatan_id', $penempatanid)->first();
        
           
        
            $hitungpayroll = $konfigurasi->perhitungan_payroll;
    
    
            if($hitungpayroll =="harikerja"){
                $mulaitgl = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
                $akhirtgl = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();
        
            
                            $totalhari = Holiday::where('description', 'Kerja')
                            ->whereBetween('date', [$mulaitgl, $akhirtgl])
                            ->count();
    
            }else if ($hitungpayroll=="kalender"){
                $mulaitglnow = "$tahun-$bulan-01";
                $selesaitglnow = date("Y-m-t", strtotime($mulaitglnow)); // Last day of the month
    
                $totalhari = ((strtotime($selesaitglnow) - strtotime($mulaitglnow)) / (60 * 60 * 24))+1;
    
    
            }
    
            $attendanceCount = Attendance::where('karyawan_id', $karyawanid)
            ->whereNotIn('attendance_code', ['H'])
            ->whereNotNull('attendance_code')
            ->whereMonth('date', $bulan)
            ->whereYear('date', $tahun)
            ->count();
        
    
           $absen = $attendanceCount;
           $realisasiharikerja = $totalhari - $absen;
           $presentasekehadiran = intval(round(( ($realisasiharikerja/$totalhari) * 100)));
    
           $realisasiinvoice = intval(round(( (($realisasiharikerja/$totalhari) * $totalbiayajasa)) + $biayalembur));
        
            $datainvoicetm[] = [
                'karyawanid' => $karyawanid,
                'nama' => $namakaryawan,
                'tanggal_lembur' => implode(', ', $tanggalLemburArray),
                'totaljamharikerja' => $totalJamHrKerja,
                'totaljamharilibur' => $totalJamHrLibur,
                'biayalembur' => $biayalembur,
                'totalhari' => $totalhari,
                'absen' => $absen,
                'realisasiharikerja' => $realisasiharikerja,
                'presentasekehadiran' => $presentasekehadiran,
                'biayajasaperbulan' => $totalbiayajasa,
                'realisasiinvoice' => $realisasiinvoice,
                'management_fee' => $managementfeereq,
            ];
    
        }

       
        return view ('testermanual.tampiltestermanual',[
            'datatm' => $datatm,
            'datainvoicetm'=> $datainvoicetm,
        ]);
    }

    public function closeinvoicetm(Request $request, $id){
        $invoicetm = TesterManual::find($id);
        $invoicetm -> status_invoicetm = "Closing";
        $invoicetm ->save();
        $datainvoicetm = json_decode($request->input('datainvoicetm'), true);
      

        foreach ($datainvoicetm as $data) {
  
            $detail = new DetailTesterManual();
            $detail -> testermanual_id = $invoicetm->id;
            $detail -> karyawan_id =  $data['karyawanid'];
            $detail->tanggallembur = $data['tanggal_lembur'];
            $detail -> totaljamlemburharikerja =   $data['totaljamharikerja'];
            $detail -> totaljamlemburharilibur = $data['totaljamharilibur'];
            $detail -> biayalembur = $data['biayalembur'];
            $detail -> totalharikerja = $data['totalhari'];
            $detail -> realisasiharikerja = $data['realisasiharikerja'];
            $detail -> absen =  $data['absen'];
            $detail -> presentase_kehadiran = $data['presentasekehadiran'];
            $detail -> biayajasaperbulan = $data['biayajasaperbulan'];
            $detail -> realisasiinvoice = $data['realisasiinvoice'];
            $detail -> save();
         
         
            
        }

        return redirect()->back()->with('success', 'Invoice Tester Manual berhasil closing dan tersimpan.');
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


    public function batalkanClosinginvoicetm($id, Request $request)
    {
        $invoice = TesterManual::find($id);
        $bulan = $invoice->bulan;

        $timestamp = strtotime($bulan); // Ubah string tanggal ke timestamp
        $bulannama = Carbon::createFromFormat('m', $bulan)->translatedFormat('F');

        $tahun = $invoice->tahun;
      
        $invoiceid = $invoice->id;

        DetailTesterManual::where('testermanual_id', $invoiceid)->delete();

        if ($invoice) {
            $invoice->status_invoicetm= 'Created';
            $invoice->save();
        }

        return redirect()->route('testermanual')->with('success', "Closing invoice tester manual bulan $bulannama $tahun berhasil dibatalkan");

       
    }
}
