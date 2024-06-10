<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::orderBy('created_at','desc')->get();
        
        return view('user.index',[
            'users' => $users,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('user.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       
        $nama = $request->nama_user;
        $email = $request->email;


$existingdata = User::where('email', $email)->first();

        if ($existingdata){
            $request->session()->flash('error', "Email sudah terdaftar.");

            return redirect()->route('holiday');
        }

        User::create([

            'nama_user' => $nama,
            'email' => $email,
            'password' => Hash::make('12345678'),

        ]);

        $request->session()->flash('success', 'User berhasil ditambahkan.');

        return redirect(route('user'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = User::find($id);

        return view('user.edit',[
            'data' => $data,
        ]);
        
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
        $data = User::find($id);

        
       
        $nama = $request -> nama_user;
        $email = $request -> email;

        $existingdata = User::where('email', $email)->where('id', '<>', $id)->first();

if ($existingdata) {
    $request->session()->flash('error', "Email sudah terdaftar.");

    return redirect()->route('holiday');
}


        $data -> nama_user = $nama;
        $data -> email = $email;
        $data -> save();
        $request->session()->flash('success', 'User berhasil diubah.');

        return redirect(route('user'));

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $useraccount = User::find($id);

        if ($useraccount->id === Auth::id()) {
            return redirect()->route('user')->with('error', 'Tidak dapat menghapus akun anda sendiri.');
        

    }

    $useraccount->delete();

    $request->session()->flash('success', "User berhasil dihapus.");

    return redirect()->route('user');
    }


    public function resetPassword(User $user, Request $request)
    {
        $user->update([
            'password' => Hash::make('12345678'), // Ganti 'password_awal' dengan password yang Anda inginkan
        ]);
    
        $request->session()->flash('success', 'Password berhasil direset.');
    
        return redirect()->route('user');
    }
    
}
