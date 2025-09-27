<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MusicTrack;
use Illuminate\Http\Request;

class MusicManagerController extends Controller
{
    /**
     * Tampilkan halaman utama music manager dengan data.
     */
    public function index(Request $request)
    {
        // Ambil data statistik
        $stats = [
            'total_tracks' => MusicTrack::count(),
            'total_artists' => MusicTrack::distinct('artistName')->count(),
            'total_genres' => MusicTrack::distinct('primaryGenreName')->count(),
        ];

        // Ambil data untuk tabel dengan paginasi
        $musicData = MusicTrack::orderBy('id', 'desc')->paginate(10);

        // Kirim kedua data (stats dan musicData) ke view
        return view('music-manager.index', compact('stats', 'musicData'));
    }

    // Fungsi-fungsi lain seperti upload, preview, dll. bisa tetap ada
    // atau disederhanakan nanti jika diperlukan.
}