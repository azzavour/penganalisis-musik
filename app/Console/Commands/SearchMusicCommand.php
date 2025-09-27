<?php

namespace App\Console\Commands;

use App\Models\MusicTrack;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class SearchMusicCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'music:search {kolom : Kolom yang ingin dicari (misal: artist_name, track_name)} {nilai : Nilai yang ingin dicari}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mencari data musik di database berdasarkan kolom dan nilai';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $kolom = $this->argument('kolom');
        $nilai = $this->argument('nilai');

        // Validasi apakah kolom yang dimasukkan ada di tabel
        $kolomTabel = Schema::getColumnListing('music_tracks');
        if (!in_array($kolom, $kolomTabel)) {
            $this->error("Error: Kolom '{$kolom}' tidak ditemukan di database.");
            $this->line('Kolom yang tersedia: ' . implode(', ', $kolomTabel));
            return 1;
        }

        $this->info("Mencari musik dengan '{$kolom}' yang mirip dengan '{$nilai}'...");

        // Lakukan pencarian dengan query LIKE untuk hasil yang lebih fleksibel
        $hasil = MusicTrack::where($kolom, 'LIKE', "%{$nilai}%")->get();

        if ($hasil->isEmpty()) {
            $this->line('Tidak ada data yang cocok dengan pencarian Anda.');
            return 0;
        }

        // Tampilkan hasil dalam bentuk tabel
        $this->line("Ditemukan " . $hasil->count() . " data:");
        
        // Ambil semua atribut dari model untuk dijadikan header
        $headers = array_keys($hasil->first()->toArray());
        
        $this->table($headers, $hasil->toArray());

        return 0;
    }
}