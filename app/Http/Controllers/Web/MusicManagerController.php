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

        $query = MusicTrack::query();

        // Handle pencarian
        if ($request->has('search')) {
            foreach ($request->input('search') as $key => $value) {
                if ($value) {
                    $query->where($key, 'like', '%' . $value . '%');
                }
            }
        }

        // Handle pengurutan
        if ($request->has('sort_by')) {
            $direction = $request->input('sort_direction', 'asc');
            $query->orderBy($request->input('sort_by'), $direction);
        } else {
            $query->orderBy('id', 'desc');
        }

        // Ambil data untuk tabel dengan paginasi
        $musicData = $query->paginate(10);

        // Kirim kedua data (stats dan musicData) ke view
        return view('music-manager.index', compact('stats', 'musicData'));
    }
        public function create()
    {
        return view('music-manager.create');
    }
}