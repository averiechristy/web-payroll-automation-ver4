<?php

namespace App\Http\Controllers;

use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('auth.login');
    }
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

      

        if (Auth::attempt($credentials)) {
            
                return redirect()->route('dashboard');
            
        }


        $request->session()->flash('error', "Email atau password tidak sesuai, silakan coba lagi.");
        return redirect()->route('login');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }


    public function showChangePasswordForm()
    {
        return view('changepassword');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!Hash::check($value, auth()->user()->password)) {
                        return $fail(__('Password lama salah.'));
                    }
                },
            ],         
            'new_password' => 'required|min:8|different:current_password',
             'new_password_confirmation' => 'required|same:new_password'
        ], [
            'current_password.required' => 'Password lama harus diisi.', 
            'new_password.required' => 'Password baru harus diisi.', 
            'new_password.min' => 'Password baru minimal 8 karakter', 
            'new_password.different' => 'Password baru tidak boleh sama dengan password lama.',
            
            'new_password_confirmation.required' => 'Konfirmasi password baru harus diisi.',
                'new_password_confirmation.same' => 'Konfirmasi password baru tidak sesuai.',

        ]);
    

        // dd($request);

        $user = Auth::user();

        if (Hash::check($request->current_password, $user->password)) {
            $user->update([
                'password' => Hash::make($request->new_password),
            ]);
            return redirect()->route('password')->with('success', 'Password berhasil diubah.');
        } else {
            return redirect()->route('password');
        }
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
        //
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
