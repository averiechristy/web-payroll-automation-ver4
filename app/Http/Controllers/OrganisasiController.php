<?php

namespace App\Http\Controllers;

use App\Models\Organisasi;
use App\Models\Penempatan;
use Illuminate\Http\Request;

class OrganisasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $organisasi = Organisasi::orderBy('created_at','desc')->get();
        return view('organisasi.index',[
            'organisasi' => $organisasi,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('organisasi.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $loggedInUser = auth()->user();
        $loggedInUsername = $loggedInUser->nama_user; 

        
    
        $organisasi = $request->organisasi;
        
        $existingOrganisasi = Organisasi::where('organisasi', $organisasi)->first();

        if ($existingOrganisasi) {
            // Jika organisasi sudah ada, tampilkan pesan error
            $request->session()->flash('error', "Organisasi sudah terdaftar.");
            return redirect()->route('organisasi');

        }
        Organisasi::create([

            'organisasi' => $organisasi,
            'created_by' => $loggedInUsername,

        ]);

        $request->session()->flash('success', "Organisasi berhasil ditambahkan.");

        return redirect()->route('organisasi');

    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = Organisasi::find($id);
       
        return view('organisasi.edit',[
            'data' => $data,
        ]);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
       
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = Organisasi::find($id);

        $loggedInUser = auth()->user();
        $loggedInUsername = $loggedInUser->nama_user; 

        $data->organisasi = $request->organisasi;
        $organisasi = $request->organisasi;
        

        $existingOrganisasi = Organisasi::where('organisasi', $organisasi)
        ->where('id', '!=', $id)
        ->first();

        if ($existingOrganisasi) {
            // Jika organisasi dengan nama yang sama sudah ada, tampilkan pesan error
            $request->session()->flash('error', "Organisasi sudah terdaftar.");
            return redirect()->route('organisasi');
        }
        $data->updated_by = $loggedInUsername;
        $data->save();

        $request->session()->flash('success', 'Organisasi berhasil diubah.');

        return redirect(route('organisasi'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $organisasi = Organisasi::find($id);
        if (Penempatan::where('organisasi_id', $organisasi->id)->exists()) {
            $request->session()->flash('error', "Tidak dapat menghapus organisasi, karena masih ada data penempatan yang berhubungan.");
            return redirect()->route('organisasi');
        }
        $organisasi->delete();

        $request->session()->flash('success', "Organisasi berhasil dihapus.");
    
        return redirect()->route('organisasi');

    }
}
