<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('profile.edit', ['user' => auth()->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'address' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:100'],
            'seller_bio' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 100 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah dipakai akun lain.',
            'phone.regex' => 'Nomor telepon hanya boleh angka, +, - dan spasi (8-20 karakter).',
            'address.max' => 'Alamat maksimal 255 karakter.',
            'location.max' => 'Lokasi maksimal 100 karakter.',
            'seller_bio.max' => 'Deskripsi toko maksimal 1000 karakter.',
        ]);

        // Diisi satu per satu (tanpa mass assignment) supaya tidak bergantung pada $fillable.
        // role dan is_active sengaja tidak bisa diubah dari form ini.
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'] ?? null;
        $user->address = $data['address'] ?? null;
        $user->location = $data['location'] ?? null;
        $user->seller_bio = $data['seller_bio'] ?? null;
        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }
}
