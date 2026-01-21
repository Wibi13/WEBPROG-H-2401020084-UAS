<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;
use Illuminate\Support\Facades\Auth;

class AbsensiController extends Controller
{
    /**
     * DASHBOARD
     */
    public function index()
{
    $today = now()->toDateString();

    $absensi = Absensi::where('tanggal', $today)->first();

    $checkedIn = $absensi && $absensi->cek_in !== null;
    $checkedOut = $absensi && $absensi->cek_out !== null;

    $totalIn = Absensi::whereNotNull('cek_in')->count();
    $totalOut = Absensi::whereNotNull('cek_out')->count();

    return view('absensi', compact(
        'absensi',
        'checkedIn',
        'checkedOut',
        'totalIn',
        'totalOut'
    ));
}

    /**
     * CHECK IN
     */
    public function checkIn()
    {
        $today = now()->toDateString();

        $absensi = Absensi::where('tanggal', $today)->first();

        // sudah check-in
        if ($absensi && $absensi->cek_in) {
            return back()->with('error', 'Sudah Check In hari ini.');
        }

        Absensi::create([
            'user_id' => Auth::id(),
            'karyawan_id' => session('karyawan_id'),
            'tanggal' => $today,
            'cek_in' => now()->toTimeString(),
        ]);

        return back()->with('success', 'Berhasil Check In.');
    }

    /**
     * CHECK OUT
     */
    public function checkOut()
    {
        $today = now()->toDateString();

        $absensi = Absensi::where('tanggal', $today)->first();

        if (!$absensi || !$absensi->cek_in) {
            return back()->with('error', 'Belum Check In hari ini.');
        }

        if ($absensi->cek_out) {
            return back()->with('error', 'Sudah Check Out hari ini.');
        }

        $absensi->update([
            'cek_out' => now()->toTimeString(),
        ]);

        return back()->with('success', 'Berhasil Check Out.');
    }
    public function resetToday()
    {
        $today = now()->toDateString();

        Absensi::where('tanggal', $today)->delete();

        return back()->with('success', 'Absensi hari ini berhasil direset.');
    }
}