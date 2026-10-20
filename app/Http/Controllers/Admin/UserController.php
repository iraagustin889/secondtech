<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $users = User::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search'));
    }

    public function toggle(User $user)
    {
      // Admin tidak boleh menonaktifkan dirinya sendiri
     if ($user->id === auth()->id()) {
        return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
     }

     $user->update(['is_active' => ! $user->is_active]);

     return back()->with(
        'status',
        $user->is_active ? "Akun {$user->name} diaktifkan." : "Akun {$user->name} dinonaktifkan."
      );
    }

   
}