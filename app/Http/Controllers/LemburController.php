<?php

namespace App\Http\Controllers;

use App\Models\DetailKonfigurasi;
use App\Models\DetailLembur;
use App\Models\Gaji;
use App\Models\Holiday;
use App\Models\Karyawan;
use App\Models\Konfigurasi;
use App\Models\Lembur;
use App\Models\Organisasi;
use App\Models\Overtime;
use App\Models\Payroll;
use App\Models\Penempatan;
use App\Models\ReportLembur;
use DateTime;
use Illuminate\Http\Request;

class LemburController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $organisasi = Organisasi::orderBy('organisasi', 'asc')->get();

        $lembur = Lembur::orderBy('created_at','desc')->get();

        return view('lembur.index',[
            'organisasi' => $organisasi,
            'lembur' => $lembur,
        ]);
    }

    public function batalkanClosinglembur($id, Request $request)
    {
        $lembur = Lembur::find($id);
        $bulan = $lembur->bulan;

        $timestamp = strtotime($bulan); // Ubah string tanggal ke timestamp
        $bulannama = \Carbon\Carbon::createFromFormat('m', $bulan)->translatedFormat('F');



        $tahun = $lembur->tahun;

        $organisasiid = $lembur->organisasi_id;

        $dataorg = Organisasi::find($organisasiid);

        $namaorg = $dataorg->organisasi;

        
        $payroll = Payroll::where('bulan', $bulan)
        ->where('tahun', $tahun)
        ->where('organisasi_id', $organisasiid)
        ->first();

       

        if($payroll && $payroll->status_payroll =="Closing"){
            return redirect()->route('lembur')->with('error', "Lakukan pembatalan closing terlebih dahulu pada laporan payroll bulan $bulannama $tahun untuk organisasi $namaorg");

        }  

        $lemburid = $lembur->id;

        DetailLembur::where('lembur_id', $lemburid)->delete();


        if ($lembur) {
            $lembur->status_lembur = 'Created';
            $lembur->save();
        }
        return redirect()->route('lembur')->with('success', "Closing laporan lembur bulan $bulannama $tahun berhasil dibatalkan");

       
    }

    public function closelembur(Request $request, $id)
    {

        $lembur = Lembur::find($id);
        $lembur -> status_lembur = "Closing";
        $lembur->save();
       

        $result = json_decode($request->input('result'), true);
        
        foreach ($result as $karyawanID => $data) {
            DetailLembur::create([
                'lembur_id' => $lembur->id,
                'karyawan_id' => $karyawanID,
                'total_jam_pertama_hari_kerja' => $data['work_days']['first_hour'],
                'total_jam_kedua_hari_kerja' => $data['work_days']['second_hour'],
                'total_jam_kedua_hari_libur' => $data['holidays']['second_hour'],
                'total_jam_ketiga_hari_libur' => $data['holidays']['third_hour'],
                'total_jam_keempat_hari_libur' => $data['holidays']['fourth_hour'],
                'total_biaya_jam_pertama_hari_kerja' => $data['work_days']['first_hour_cost'],
                'total_biaya_jam_kedua_hari_kerja' => $data['work_days']['second_hour_cost'],
                'total_biaya_jam_kedua_hari_libur' => $data['holidays']['second_hour_cost'],
                'total_biaya_jam_ketiga_hari_libur' => $data['holidays']['third_hour_cost'],
                'total_biaya_jam_keempat_hari_libur' => $data['holidays']['fourth_hour_cost'],
                'total_jam' => $data['total_hours'],
                'total_biaya_lembur' => $data['total_cost'],
            ]);
        }

        return redirect()->back()->with('success', 'Laporan Lembur berhasil closing dan tersimpan.');
      
    }
    public function tampillembur (Request  $request, $id){
        $lembur = Lembur::find($id);
        $bulan = $lembur -> bulan;
        $tahun = $lembur-> tahun;
        $organisasiid = $lembur->organisasi_id;

        $dataorganisasi = Organisasi::find($organisasiid);
        $namaorganisasi =$dataorganisasi->organisasi;
        $loggedInUser = auth()->user(); 
        $loggedInUserName = $loggedInUser->nama_user;
    
        $dateObj = DateTime::createFromFormat('!m', $bulan);
        $bulanNama = $dateObj->format('F'); // This will give the full month name
    
    
     
    
        $overtimeData = Overtime::whereYear('date', $tahun)
            ->whereMonth('date', $bulan)
            ->whereHas('karyawan', function ($query) use ($organisasiid) {
                $query->whereHas('penempatan', function ($query) use ($organisasiid) {
                    $query->whereHas('organisasi', function ($query) use ($organisasiid) {
                        $query->where('id', $organisasiid);
                    });
                });
            })
            ->get()
            ->groupBy('karyawan_id');
            

           

            if ($overtimeData->isEmpty()) { 
                $request->session()->flash('error', "Tidak ada data lembur untuk organisasi $namaorganisasi pada $bulanNama $tahun");
                return redirect(route('lembur'));
            }
            

        
        $result = [];
    
        foreach ($overtimeData as $karyawanID => $items) {
            $karyawan = Karyawan::find($karyawanID);
            $penempatanid = $karyawan->penempatan_id;
            $datapenempatan = Penempatan::find($penempatanid);
            $organisasiid = $datapenempatan->organisasi_id;
            
            $result[$karyawanID] = [
                'nik' => $karyawan->nik,
        'payroll_code' => $karyawan->payroll_code,
        'nama_karyawan' => $karyawan->nama_karyawan,
        'jabatan' => $karyawan->jabatan,
        'leader' => $karyawan->leader,
        'status_karyawan' => $karyawan->status_karyawan,
        'organisasi' => $datapenempatan->organisasi->organisasi,
                'work_days' => [
                    'first_hour' => 0,
                    'second_hour' => 0,
                    'third_hour' => 0,
                    'fourth_hour' => 0,
                    'first_hour_cost' => 0,
                    'second_hour_cost' => 0,
                    'third_hour_cost' => 0,
                    'fourth_hour_cost' => 0,
                ],
                'holidays' => [
                    'first_hour' => 0,
                    'second_hour' => 0,
                    'third_hour' => 0,
                    'fourth_hour' => 0,
                    'first_hour_cost' => 0,
                    'second_hour_cost' => 0,
                    'third_hour_cost' => 0,
                    'fourth_hour_cost' => 0,
                ],
                'total_hours' => 0,
                'total_cost' => 0,
            ];
    
            foreach ($items as $data) {
                $tanggallembur = $data->date;
                $tanggalLembur = new DateTime($tanggallembur);
                $hariDalamMinggu = $tanggalLembur->format('N');
                
                // Cek apakah hari Sabtu (6) atau Minggu (7)
                $isWeekend = ($hariDalamMinggu >= 6);
                $dataholiday = Holiday::where('date', $tanggallembur)->first();
                $idorganisasi = $dataorganisasi->id;

                if ($dataholiday) {
                    $organisasiPengecualian = json_decode($dataholiday->pengecualian_organisasi, true);
    
                 
                    if (is_array($organisasiPengecualian) && in_array($idorganisasi, $organisasiPengecualian)) {
                        $statusHari = $dataholiday->description == 'Kerja' ? 'Libur' : 'Kerja';
                    } else {
                       
                        $statusHari = $dataholiday->description;
                    }
                } 
    
                $hoursWorked = $data->overtime_payment;
                $karyawan = Karyawan::find($karyawanID);
                $penempatanid = $karyawan->penempatan_id;
                $datapenempatan = Penempatan::find($penempatanid);
                $namapenempatan = $datapenempatan->nama_unit_kerja;
                $organisasiid = $datapenempatan->organisasi_id;
                $konfigurasi = DetailKonfigurasi::where('penempatan_id', $penempatanid)->first();
    
            if (!$konfigurasi) {
                
                if (!$konfigurasi) {
                    $request->session()->flash('error', "Konfigurasi untuk organisasi $namapenempatan belum terdaftar");
                    return redirect(route('lembur'));
                }
            }
                
                $hitungTunjangan = $konfigurasi->hitung_tunjangan;
                
                $gaji = Gaji::where('karyawan_id', $karyawanID)
                    ->where('tanggal_mulai_gaji', '<=', $tanggallembur)
                    ->where('tanggal_selesai_gaji', '>=', $tanggallembur)
                    ->first();
    
                if (is_null($gaji)) {
                    $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                    $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                    return redirect(route('lembur'));
                }
                
                $upah = $gaji->gaji;
                $tunjanganamount = 0;
    
                if ($hitungTunjangan == "Yes" || is_null($hitungTunjangan)) {
                    $tunjangan = Gaji::where('karyawan_id', $karyawanID)
                        ->where('tanggal_mulai_tunjangan', '<=', $tanggallembur)
                        ->where('tanggal_selesai_tunjangan', '>=', $tanggallembur)
                        ->first();
    
                    if (is_null($tunjangan)) {
                        $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                        $request->session()->flash('error', "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");
                        return redirect(route('lembur'));
                    }
    
                    $tunjanganamount = $tunjangan->tunjangan;
                }
    
                $gajipokok = $upah + $tunjanganamount;
    
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
    
                    $result[$karyawanID]['work_days']['first_hour'] += $jampertama;
                    $result[$karyawanID]['work_days']['second_hour'] += $jamkedua;
                    $result[$karyawanID]['work_days']['third_hour'] += $jamketiga;
                    $result[$karyawanID]['work_days']['fourth_hour'] += $jamkeempat;
    
                    $result[$karyawanID]['work_days']['first_hour_cost'] += intval(round(($jampertama * 1.5 * $gajipokok) / 173));
                    $result[$karyawanID]['work_days']['second_hour_cost'] += intval(round(($jamkedua * 2 * $gajipokok) / 173));
                    $result[$karyawanID]['work_days']['third_hour_cost'] += intval(round(($jamketiga * 3 * $gajipokok) / 173));
                    $result[$karyawanID]['work_days']['fourth_hour_cost'] += intval(round(($jamkeempat * 4 * $gajipokok) / 173));
    
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
    
                    $result[$karyawanID]['holidays']['first_hour'] += $jampertama;
                    $result[$karyawanID]['holidays']['second_hour'] += $jamkedua;
                    $result[$karyawanID]['holidays']['third_hour'] += $jamketiga;
                    $result[$karyawanID]['holidays']['fourth_hour'] += $jamkeempat;
    
                    $result[$karyawanID]['holidays']['first_hour_cost'] += intval(round(($jampertama * 1.5 * $gajipokok) / 173));
                    $result[$karyawanID]['holidays']['second_hour_cost'] += intval(round(($jamkedua * 2 * $gajipokok) / 173));
                    $result[$karyawanID]['holidays']['third_hour_cost'] += intval(round(($jamketiga * 3 * $gajipokok) / 173));
                    $result[$karyawanID]['holidays']['fourth_hour_cost'] += intval(round(($jamkeempat * 4 * $gajipokok) / 173));
                }
    
                $total_hours_worked = $jampertama + $jamkedua + $jamketiga + $jamkeempat;
                $total_cost = intval(round(($jampertama * 1.5 * $gajipokok) / 173)) +
                              intval(round(($jamkedua * 2 * $gajipokok) / 173)) +
                              intval(round(($jamketiga * 3 * $gajipokok) / 173)) +
                              intval(round(($jamkeempat * 4 * $gajipokok) / 173));
    
                $result[$karyawanID]['total_hours'] += $total_hours_worked;
                $result[$karyawanID]['total_cost'] += $total_cost;
            }

            
        }
        
        return view('lembur.tampillembur',[
            'result' => $result,
            'lembur' => $lembur,

        ]);
    }

    public function tampildetail($id){
        
        $lembur = Lembur::find($id);
        $detaillembur = DetailLembur::with('lembur')->where('lembur_id', $id)->get();

     
        return view('lembur.detail',[
            'lembur' => $lembur,
            'detaillembur' => $detaillembur,
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
    
    $currentmonth = $request->bulan;
    $currentyear = $request->tahun;

    if ($currentmonth == 1) {
        $bulan = 12;
        $tahun = $currentyear - 1;
    } else {
        $bulan = $currentmonth - 1;
        $tahun = $currentyear;
    }

    $dataorganisasi = Organisasi::find($organisasiid);
    $namaorganisasi =$dataorganisasi->organisasi;
    $loggedInUser = auth()->user(); 
    $loggedInUserName = $loggedInUser->nama_user;
    $organisasiid = $dataorganisasi->id;

    $dateObj = DateTime::createFromFormat('!m', $bulan);
    $bulanNama = $dateObj->format('F'); // This will give the full month name

    $existingdata = Lembur::where('organisasi_id', $organisasiid)->where('bulan', $bulan)->where('tahun', $tahun)->first();

    if ($existingdata) {
        $request->session()->flash('error', "Laporan lembur untuk organisasi $namaorganisasi bulan $bulanNama $tahun sudah dibuat.");
        return redirect()->back();
    }


    $overtimeData = Overtime::whereYear('date', $tahun)
        ->whereMonth('date', $bulan)
        ->whereHas('karyawan', function ($query) use ($organisasiid) {
            $query->whereHas('penempatan', function ($query) use ($organisasiid) {
                $query->whereHas('organisasi', function ($query) use ($organisasiid) {
                    $query->where('id', $organisasiid);
                });
            });
        })
        ->get()
        ->groupBy('karyawan_id');

        

     
       
        if ($overtimeData->isEmpty()) { 
            $request->session()->flash('error', "Tidak ada data lembur untuk organisasi $namaorganisasi pada $bulanNama $tahun");
            return redirect(route('lembur'));
        }
        
       
    
    $result = [];

    foreach ($overtimeData as $karyawanID => $items) {
        $result[$karyawanID] = [
            'work_days' => [
                'first_hour' => 0,
                'second_hour' => 0,
                'third_hour' => 0,
                'fourth_hour' => 0,
                'first_hour_cost' => 0,
                'second_hour_cost' => 0,
                'third_hour_cost' => 0,
                'fourth_hour_cost' => 0,
            ],
            'holidays' => [
                'first_hour' => 0,
                'second_hour' => 0,
                'third_hour' => 0,
                'fourth_hour' => 0,
                'first_hour_cost' => 0,
                'second_hour_cost' => 0,
                'third_hour_cost' => 0,
                'fourth_hour_cost' => 0,
            ],
            'total_hours' => 0,
            'total_cost' => 0,
        ];

        foreach ($items as $data) {
            $tanggallembur = $data->date;
            $tanggalLembur = new DateTime($tanggallembur);
            $hariDalamMinggu = $tanggalLembur->format('N');
            
            // Cek apakah hari Sabtu (6) atau Minggu (7)
            $isWeekend = ($hariDalamMinggu >= 6);
            $dataholiday = Holiday::where('date', $tanggallembur)->first();

           

            $idorganisasi = $dataorganisasi->id;

            if ($dataholiday) {
                $organisasiPengecualian = json_decode($dataholiday->pengecualian_organisasi, true);
        
             

                if (is_array($organisasiPengecualian) && in_array($idorganisasi, $organisasiPengecualian)) {
                    $statusHari = $dataholiday->description == 'Kerja' ? 'Libur' : 'Kerja';
                } else {
                   
                    $statusHari = $dataholiday->description;
                }
                
            } 


           

            $hoursWorked = $data->overtime_payment;
            $karyawan = Karyawan::find($karyawanID);
            $penempatanid = $karyawan->penempatan_id;
            $datapenempatan = Penempatan::find($penempatanid);
            $organisasiid = $datapenempatan->organisasi_id;

            $namapenempatan = $datapenempatan->nama_unit_kerja;
            $konfigurasi = DetailKonfigurasi::where('penempatan_id', $penempatanid)->first();
    
            if (!$konfigurasi) {
                
                if (!$konfigurasi) {
                    $request->session()->flash('error', "Konfigurasi untuk organisasi $namapenempatan belum terdaftar");
                    return redirect(route('lembur'));
                }
                
            }
            
            $hitungTunjangan = $konfigurasi->hitung_tunjangan;
            
            $gaji = Gaji::where('karyawan_id', $karyawanID)
                ->where('tanggal_mulai_gaji', '<=', $tanggallembur)
                ->where('tanggal_selesai_gaji', '>=', $tanggallembur)
                ->first();

            if (is_null($gaji)) {
                $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                $request->session()->flash('error', "Gaji untuk karyawan $namaKaryawan belum ditambahkan.");
                return redirect(route('lembur'));
            }
            
            $upah = $gaji->gaji;
            $tunjanganamount = 0;

            if ($hitungTunjangan == "Yes" || is_null($hitungTunjangan)) {
                $tunjangan = Gaji::where('karyawan_id', $karyawanID)
                    ->where('tanggal_mulai_tunjangan', '<=', $tanggallembur)
                    ->where('tanggal_selesai_tunjangan', '>=', $tanggallembur)
                    ->first();

                if (is_null($tunjangan)) {
                    $namaKaryawan = $karyawan ? $karyawan->nama_karyawan : 'tidak diketahui';
                    $request->session()->flash('error', "Tunjangan untuk karyawan $namaKaryawan belum ditambahkan.");
                    return redirect(route('lembur'));
                }

                $tunjanganamount = $tunjangan->tunjangan;
            }

            $gajipokok = $upah + $tunjanganamount;

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

                $result[$karyawanID]['work_days']['first_hour'] += $jampertama;
                $result[$karyawanID]['work_days']['second_hour'] += $jamkedua;
                $result[$karyawanID]['work_days']['third_hour'] += $jamketiga;
                $result[$karyawanID]['work_days']['fourth_hour'] += $jamkeempat;

                $result[$karyawanID]['work_days']['first_hour_cost'] += intval(round(($jampertama * 1.5 * $gajipokok) / 173));
                $result[$karyawanID]['work_days']['second_hour_cost'] += intval(round(($jamkedua * 2 * $gajipokok) / 173));
                $result[$karyawanID]['work_days']['third_hour_cost'] += intval(round(($jamketiga * 3 * $gajipokok) / 173));
                $result[$karyawanID]['work_days']['fourth_hour_cost'] += intval(round(($jamkeempat * 4 * $gajipokok) / 173));

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

                $result[$karyawanID]['holidays']['first_hour'] += $jampertama;
                $result[$karyawanID]['holidays']['second_hour'] += $jamkedua;
                $result[$karyawanID]['holidays']['third_hour'] += $jamketiga;
                $result[$karyawanID]['holidays']['fourth_hour'] += $jamkeempat;

                $result[$karyawanID]['holidays']['first_hour_cost'] += intval(round(($jampertama * 1.5 * $gajipokok) / 173));
                $result[$karyawanID]['holidays']['second_hour_cost'] += intval(round(($jamkedua * 2 * $gajipokok) / 173));
                $result[$karyawanID]['holidays']['third_hour_cost'] += intval(round(($jamketiga * 3 * $gajipokok) / 173));
                $result[$karyawanID]['holidays']['fourth_hour_cost'] += intval(round(($jamkeempat * 4 * $gajipokok) / 173));
            }

            $total_hours_worked = $jampertama + $jamkedua + $jamketiga + $jamkeempat;
            $total_cost = intval(round(($jampertama * 1.5 * $gajipokok) / 173)) +
                          intval(round(($jamkedua * 2 * $gajipokok) / 173)) +
                          intval(round(($jamketiga * 3 * $gajipokok) / 173)) +
                          intval(round(($jamkeempat * 4 * $gajipokok) / 173));

            $result[$karyawanID]['total_hours'] += $total_hours_worked;
            $result[$karyawanID]['total_cost'] += $total_cost;
        }
    }

    $lembur = Lembur::create([
        'judul_lembur' => "Laporan Lembur $namaorganisasi $bulanNama $tahun",
        'organisasi_id' => $organisasiid,
        'bulan' => $bulan,
        'tahun' => $tahun,
        'created_by' => $loggedInUser->nama_user,
        'status_lembur'=> "Created",
        'currentbulan'=> $currentmonth,
        'currenttahun' => $currentyear,
    ]);

    // foreach ($result as $karyawanID => $data) {
    //     DetailLembur::create([
    //         'lembur_id' => $lembur->id,
    //         'karyawan_id' => $karyawanID,
    //         'total_jam_pertama_hari_kerja' => $data['work_days']['first_hour'],
    //         'total_jam_kedua_hari_kerja' => $data['work_days']['second_hour'],
    //         'total_jam_kedua_hari_libur' => $data['holidays']['second_hour'],
    //         'total_jam_ketiga_hari_libur' => $data['holidays']['third_hour'],
    //         'total_jam_keempat_hari_libur' => $data['holidays']['fourth_hour'],
    //         'total_biaya_jam_pertama_hari_kerja' => $data['work_days']['first_hour_cost'],
    //         'total_biaya_jam_kedua_hari_kerja' => $data['work_days']['second_hour_cost'],
    //         'total_biaya_jam_kedua_hari_libur' => $data['holidays']['second_hour_cost'],
    //         'total_biaya_jam_ketiga_hari_libur' => $data['holidays']['third_hour_cost'],
    //         'total_biaya_jam_keempat_hari_libur' => $data['holidays']['fourth_hour_cost'],
    //         'total_jam' => $data['total_hours'],
    //         'total_biaya_lembur' => $data['total_cost'],
    //     ]);
    // }

    $request->session()->flash('success', "Laporan lembur berhasil dibuat.");

    return redirect()->route('lembur');

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
}
