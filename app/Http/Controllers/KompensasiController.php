<?php

namespace App\Http\Controllers;

use App\Models\DetailKompensasi;
use App\Models\DetailKonfigurasi;
use App\Models\Karyawan;
use App\Models\Kompensasi;
use App\Models\Organisasi;
use App\Models\Payroll;
use App\Models\Penempatan;
use App\Models\Posisi;
use App\Models\ReportKompensasi;
use Illuminate\Http\Request;

class KompensasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kompensasi = Kompensasi::orderBy('created_at','desc')->get();
      
        return view('kompensasi.index',[
           'kompensasi' => $kompensasi,
        ]);
    }
    

    public function tampildetail($id){

        $kompensasi = Kompensasi::find($id);
        $detailkompensasi = DetailKompensasi::with('kompensasi')->where('kompensasi_id', $id)->get();

     
        return view('kompensasi.detail',[
            'kompensasi' => $kompensasi,
            'detailkompensasi' => $detailkompensasi,
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
        $bulan = $request->bulan;
        $tahun = $request->tahun;

        $bulan = $request->bulan;
        $tahun = $request->tahun;

        if ($bulan == 1) {
            $bulanSebelumnya = 12;
            $tahunSebelumnya = $tahun - 1;
        } else {
            $bulanSebelumnya = $bulan - 1;
            $tahunSebelumnya = $tahun;
        }

        $checkclosing = Kompensasi::where('bulan', $bulanSebelumnya)
        ->where('tahun', $tahunSebelumnya)
        ->first();



        if($checkclosing && $checkclosing->status_kompensasi =="Created"){
        $request->session()->flash('error', "Laporan Kompensasi pada bulan $bulanSebelumnya belum closing");
        return redirect(route('kompensasi'));
        }
    
        // Buat tanggal dari bulan dan tahun yang diterima
        $date = \Carbon\Carbon::createFromDate($tahun, $bulan, 1);
        
        // Mendapatkan awal dan akhir bulan dari tanggal tersebut
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();
        $namaBulan = \Carbon\Carbon::createFromFormat('m', $bulan)->translatedFormat('F');

        $existingKompensasi = Kompensasi::where('bulan', $bulan)->where('tahun', $tahun)->first();
        if ($existingKompensasi) {
            $request->session()->flash('error', "Kompensasi untuk bulan {$namaBulan} {$tahun} sudah dibuat.");
            return redirect()->back();
        }

        $karyawan = Karyawan::whereBetween('kontrak_karyawans.tanggal_akhir_kontrak', [$startOfMonth, $endOfMonth])
        ->whereNull('karyawans.tanggal_resign') // Kondisi tanggal_resign == null
        ->leftJoin('kontrak_karyawans', 'karyawans.id', '=', 'kontrak_karyawans.karyawan_id')
        ->leftJoin('gajis as g1', function($join) use ($startOfMonth, $endOfMonth) {
            $join->on('karyawans.id', '=', 'g1.karyawan_id')
                ->where(function($query) use ($startOfMonth, $endOfMonth) {
                    $query->where('g1.tanggal_mulai_gaji', '<=', $endOfMonth)
                          ->where('g1.tanggal_selesai_gaji', '>=', $startOfMonth);
                });
        })
        ->leftJoin('gajis as g2', function($join) use ($startOfMonth, $endOfMonth) {
            $join->on('karyawans.id', '=', 'g2.karyawan_id')
                ->where(function($query) use ($startOfMonth, $endOfMonth) {
                    $query->where('g2.tanggal_mulai_tunjangan', '<=', $endOfMonth)
                          ->where('g2.tanggal_selesai_tunjangan', '>=', $startOfMonth);
                });
        })
        ->leftJoin('penempatans', 'karyawans.penempatan_id', '=', 'penempatans.id')
        ->select(
            'karyawans.*', 
            'kontrak_karyawans.tanggal_awal_kontrak', 
            'kontrak_karyawans.tanggal_akhir_kontrak', 
            'g1.gaji', 
            'g2.tunjangan', 
            'penempatans.hitung_tunjangan',
            \DB::raw('TIMESTAMPDIFF(MONTH, kontrak_karyawans.tanggal_awal_kontrak, kontrak_karyawans.tanggal_akhir_kontrak) + 1 as masa_kerja')
        )
        ->get();
    

        

        
        $kompensasiArray = [];
        
        foreach ($karyawan as $k) {
                        
            $gaji = $k->gaji;
            $tunjangan = $k->tunjangan;

            $namakaryawan = $k->nama_karyawan;

            if ($gaji === null && $tunjangan === null) {
                $request->session()->flash('error', "Gaji dan tunjangan untuk karyawan $namakaryawan belum ditambahkan.");
                return redirect(route('kompensasi'));
            } elseif ($gaji === null || $tunjangan === null) {
                $request->session()->flash('error', "Gaji dan tunjangan untuk karyawan $namakaryawan belum ditambahkan.");
                    return redirect(route('kompensasi'));
            }
            
            
            $masaKerjaDalamTahun = $k->masa_kerja / 12;
         
        
            $karyawanid = $k->id;
            $datakaryawan = Karyawan::find($karyawanid);
            $penempatanid = $datakaryawan->penempatan_id;
            $datapenempatan = Penempatan::find($penempatanid);
            $organisasiid = $datapenempatan->organisasi_id;
            $dataorganisasi = Organisasi::find($organisasiid);
            $namaorganisasi = $dataorganisasi->organisasi;
            $namapenempatan = $datapenempatan->nama_unit_kerja;
            $posisiid = $datakaryawan->posisi_id;
            $dataposisi = Posisi::find($posisiid);
            $namaposisi = $dataposisi->posisi;
            $konfigurasi = DetailKonfigurasi::where('penempatan_id', $penempatanid)->first();
            $namakaryawan = $k->nama_karyawan;



            if (!$konfigurasi) {
                
                if (!$konfigurasi) {
                    $request->session()->flash('error', "Konfigurasi untuk organisasi $namapenempatan belum terdaftar");
                    return redirect(route('kompensasi'));
                }
            }


            $hitungtunjangan = $konfigurasi->hitung_tunjangan;
            

            if ($hitungtunjangan === 'Yes') {
                $k->kompensasi =intval(round(( $masaKerjaDalamTahun * ($k->gaji + $k->tunjangan))));
              
            } else if ($hitungtunjangan === 'No') {
                $k->kompensasi = intval(round(($masaKerjaDalamTahun * $k->gaji)));
               
            } else if ($hitungtunjangan === null) {
                $k->kompensasi = intval(round(($masaKerjaDalamTahun * $k->gaji)));
               
            } 


        }
        
        
       
        // Konversi bulan ke dalam tulisan
        $namaBulan = \Carbon\Carbon::createFromFormat('m', $bulan)->translatedFormat('F');
    
        $loggedInUser = auth()->user();
        $loggedInUsername = $loggedInUser->nama_user; 
        // Simpan data ke dalam model Kompensasi
        $kompensasi = new Kompensasi();
        $kompensasi->bulan = $bulan;
        $kompensasi->tahun = $tahun;
        $kompensasi->judul = "Kompensasi {$namaBulan} {$tahun}";
        $kompensasi->status_kompensasi = "Created";
        $kompensasi -> created_by = $loggedInUsername;
        $kompensasi->save();
    
        // Simpan data setiap karyawan ke dalam DetailKompensasi
        // foreach ($karyawan as $k) {
        //     $detailKompensasi = new DetailKompensasi();
        //     $detailKompensasi->kompensasi_id = $kompensasi->id;
        //     $detailKompensasi->karyawan_id = $k->id;
        //     $detailKompensasi->masa_kerja = $k->masa_kerja;
        //     $detailKompensasi->total_kompensasi = $k->kompensasi;
        //     $detailKompensasi->gaji = $k->gaji;
        //     $detailKompensasi->tunjangan = $k->tunjangan;
        //     $detailKompensasi->save();
        // }
    
        $request->session()->flash('success', 'Kompensasi berhasil dibuat.');

        return redirect(route('kompensasi'));
    }
    public function batalkanClosingkompensasi($id, Request $request)
    {
        $kompensasi = Kompensasi::find($id);
        $bulan = $kompensasi->bulan;

        $timestamp = strtotime($bulan); // Ubah string tanggal ke timestamp
        $bulannama = \Carbon\Carbon::createFromFormat('m', $bulan)->translatedFormat('F');





        $tahun = $kompensasi->tahun;

        
        $payroll = Payroll::where('bulan', $bulan)
        ->where('tahun', $tahun)
        ->first();


        if($payroll && $payroll->status_payroll=="Closing"){
            return redirect()->route('kompensasi')->with('error', "Lakukan pembatalan closing terlebih dahulu pada laporan payroll bulan $bulannama $tahun untuk seluruh organisasi.");

        }

        $kompensasiid = $kompensasi->id;

        DetailKompensasi::where('kompensasi_id', $kompensasiid)->delete();


        if ($kompensasi) {
            $kompensasi->status_kompensasi = 'Created';
            $kompensasi->save();
        }
        return redirect()->route('kompensasi')->with('success', "Closing laporan kompensasi bulan $bulannama $tahun berhasil dibatalkan");

       
    }
    public function tampilkompensasi ($id){
        $kompensasi = Kompensasi::find($id);
        $bulan = $kompensasi -> bulan;
        $tahun = $kompensasi -> tahun;

      
        // Buat tanggal dari bulan dan tahun yang diterima
        $date = \Carbon\Carbon::createFromDate($tahun, $bulan, 1);
        
        // Mendapatkan awal dan akhir bulan dari tanggal tersebut
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();
        $namaBulan = \Carbon\Carbon::createFromFormat('m', $bulan)->translatedFormat('F');

        $karyawan = Karyawan::whereBetween('kontrak_karyawans.tanggal_akhir_kontrak', [$startOfMonth, $endOfMonth])
        ->whereNull('karyawans.tanggal_resign') // Kondisi tanggal_resign == null
        ->leftJoin('kontrak_karyawans', 'karyawans.id', '=', 'kontrak_karyawans.karyawan_id')
        ->leftJoin('gajis as g1', function($join) use ($startOfMonth, $endOfMonth) {
            $join->on('karyawans.id', '=', 'g1.karyawan_id')
                ->where(function($query) use ($startOfMonth, $endOfMonth) {
                    $query->where('g1.tanggal_mulai_gaji', '<=', $endOfMonth)
                          ->where('g1.tanggal_selesai_gaji', '>=', $startOfMonth);
                });
        })
        ->leftJoin('gajis as g2', function($join) use ($startOfMonth, $endOfMonth) {
            $join->on('karyawans.id', '=', 'g2.karyawan_id')
                ->where(function($query) use ($startOfMonth, $endOfMonth) {
                    $query->where('g2.tanggal_mulai_tunjangan', '<=', $endOfMonth)
                          ->where('g2.tanggal_selesai_tunjangan', '>=', $startOfMonth);
                });
        })
        ->leftJoin('penempatans', 'karyawans.penempatan_id', '=', 'penempatans.id')
        ->select(
            'karyawans.*', 
            'kontrak_karyawans.tanggal_awal_kontrak', 
            'kontrak_karyawans.tanggal_akhir_kontrak', 
            'g1.gaji', 
            'g2.tunjangan', 
            'penempatans.hitung_tunjangan',
            \DB::raw('TIMESTAMPDIFF(MONTH, kontrak_karyawans.tanggal_awal_kontrak, kontrak_karyawans.tanggal_akhir_kontrak) + 1 as masa_kerja')
        )
        ->get();

            $kompensasiArray = [];
        // Menghitung kompensasi berdasarkan kondisi hitung_tunjangan

       
        $dataKompensasi = [];
        foreach ($karyawan as $k) {
         
            $masaKerjaDalamTahun = $k->masa_kerja / 12;
         
        
           

            $karyawanid = $k->id;
            $datakaryawan = Karyawan::find($karyawanid);
            $penempatanid = $datakaryawan->penempatan_id;
            $datapenempatan = Penempatan::find($penempatanid);
            $organisasiid = $datapenempatan->organisasi_id;
            $dataorganisasi = Organisasi::find($organisasiid);
            $namaorganisasi = $dataorganisasi->organisasi;
            $namapenempatan = $datapenempatan->nama_unit_kerja;
            $posisiid = $datakaryawan->posisi_id;
        $dataposisi = Posisi::find($posisiid);
            $namaposisi = $dataposisi->posisi;
            $konfigurasi = DetailKonfigurasi::where('penempatan_id', $penempatanid)->first();

        
            $hitungtunjangan = $konfigurasi->hitung_tunjangan;
            
            if ($hitungtunjangan === 'Yes') {

        
                $k->kompensasi =intval(round(( $masaKerjaDalamTahun * ($k->gaji + $k->tunjangan))));
              
            } else if ($hitungtunjangan === 'No') {
                $k->kompensasi = intval(round(($masaKerjaDalamTahun * $k->gaji)));
               
            } else if ($hitungtunjangan === null) {
                $k->kompensasi = intval(round(($masaKerjaDalamTahun * $k->gaji)));
               
            } 

            
            $nik = $k->nik;
            $nama = $k->nama_karyawan;
            $penempatanid = $k->penempatan_id;
            $datapenempatan = Penempatan::find($penempatanid);
            $namapenempatan = $datapenempatan->nama_unit_kerja;
            $gaji = $k->gaji;
            $tunjangan = $k->tunjangan;
            $tanggalawal = $k->tanggal_awal_kontrak;
            $tanggalakhir = $k->tanggal_akhir_kontrak;
            $masakerja = $k->masa_kerja;
            $totalkompensasi = $k->kompensasi;
            $id = $k->id;
           


            $dataKompensasi[] = [
                'nik' => $nik,
                'nama' => $nama,
                'namapenempatan' => $namapenempatan,
                'gaji' => $gaji,
                'tunjangan' => $tunjangan,
                'tanggalawal' => $tanggalawal,
                'tanggalakhir' => $tanggalakhir,
                'masakerja' => $masakerja,
                'totalkompensasi' => $totalkompensasi,
                'id' => $id,
            ];

       

    }
        

      return view('kompensasi.tampilkompensasi',[
'dataKompensasi' => $dataKompensasi,
'kompensasi' => $kompensasi,
      ]);



    }
    public function closekompensasi(Request $request, $id)
    {
        
        $kompensasi = Kompensasi::find($id);
        $kompensasi -> status_kompensasi = "Closing";

        $dataKompensasi = json_decode($request->input('dataKompensasi'), true);

      

        $kompensasi->save();
       


      

        foreach ($dataKompensasi as $detail) {

            $detailKompensasi = new DetailKompensasi();
            $detailKompensasi->kompensasi_id = $kompensasi->id;
            $detailKompensasi->karyawan_id =  $detail['id'];
            $detailKompensasi->masa_kerja = $detail['masakerja'];
            $detailKompensasi->total_kompensasi = $detail['totalkompensasi'];
            $detailKompensasi->gaji = $detail['gaji'];
            $detailKompensasi->tunjangan =$detail['tunjangan'];
            $detailKompensasi->save();

         

           
            $detailKompensasi->save();
        }

        return redirect()->back()->with('success', 'Kompensasi berhasil closing dan tersimpan.');
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
