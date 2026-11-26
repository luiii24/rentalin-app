<?php

namespace App\Http\Controllers;

class AuthController extends Controller
{
    // Login dengan hardcode untuk admin/kasir (di real app pakai model user)
    public function login()
    {
        if (session('authAdminKasir')) {
            return redirect()->route('portal.dashboard');
        }
        // Simple hardcoded login check (dihidupkan jika session authAdminKasir belum diset)
        // Dalam praktik nyata gunakan model User dengan role
        $username = request('username');
        $password = request('password');

        // Hardcoded untuk admin dan kasir
        $users = [
            ['name' => 'Admin Rentalin', 'email' => 'admin@rentalin.id', 'role' => 'admin'],
            ['name' => 'Kasir Rentalin', 'email' => 'kasir@rentalin.id', 'role' => 'kasir'],
        ];

        if (str_contains($username, '@') && in_array($username, ['admin@rentalin.id', 'kasir@rentalin.id']) 
            && $password === env('ADMIN_PASS', 'rentalin2026') 
            && str_contains($username, 'admin')) {
            session(['authAdminKasir' => true]);
        } elseif (str_contains($username, '@') && in_array($username, ['admin@rentalin.id', 'kasir@rentalin.id'])
            && $password === env('ADMIN_PASS', 'rentalin2026') 
            && str_contains($username, 'kasir')) {
            session(['authAdminKasir' => true]);
        }

        $isLoggedIn = session('authAdminKasir');
        
        if (!$isLoggedIn) {
            return view('auth.login');
        }

        return redirect()->route('portal.dashboard');
    }

    public function logout()
    {
        session()->forget('authAdminKasir');
        return redirect()->route('portal.dashboard');
    }
}