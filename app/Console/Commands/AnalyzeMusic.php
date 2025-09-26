<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyzeMusic extends Command
{
    protected $signature = 'app:analyze-music {type : Tipe analisis: \'artists\' atau \'genres\'} {--from=} {--to=} {--limit=10}';
    protected $description = 'Menganalisis data musik untuk menemukan artis atau genre teratas.';

    public function handle()
    {
        $analysisType = $this->argument('type');
        $limit = $this->option('limit');
        $fromDate = $this->option('from') ? Carbon::parse($this->option('from'))->startOfDay() : Carbon::now()->subYears(100);
        $toDate = $this->option('to') ? Carbon::parse($this->option('to'))->endOfDay() : Carbon::now();

        $this->info("Menganalisis data dari {$fromDate->toFormattedDateString()} hingga {$toDate->toFormattedDateString()}...");

        match ($analysisType) {
            'artists' => $this->analyzeTopArtists($fromDate, $toDate, $limit),
            'genres' => $this->analyzeTopGenres($fromDate, $toDate, $limit),
            default => $this->error("Tipe analisis tidak valid. Gunakan 'artists' atau 'genres'."),
        };
        return self::SUCCESS;
    }

    private function analyzeTopArtists(Carbon $fromDate, Carbon $toDate, int $limit)
    {
        $this->line("Mengambil {$limit} artis teratas...");
        $artists = DB::table('music_tracks')
            ->select('artistName', DB::raw('COUNT(*) as track_count'))
            ->whereBetween('releaseDate', [$fromDate, $toDate])->groupBy('artistName')
            ->orderByDesc('track_count')->limit($limit)->get()->map(fn($item) => (array)$item);

        if ($artists->isEmpty()) {
            $this->warn('Tidak ada data artis ditemukan.'); return;
        }
        $this->info('Artis Teratas:');
        $this->table(['Nama Artis', 'Jumlah Lagu'], $artists->toArray());
    }

    private function analyzeTopGenres(Carbon $fromDate, Carbon $toDate, int $limit)
    {
        $this->line("Mengambil {$limit} genre teratas...");
        $genres = DB::table('music_tracks')
            ->select('primaryGenreName', DB::raw('COUNT(*) as genre_count'))
            ->whereBetween('releaseDate', [$fromDate, $toDate])->groupBy('primaryGenreName')
            ->orderByDesc('genre_count')->limit($limit)->get()->map(fn($item) => (array)$item);

        if ($genres->isEmpty()) {
            $this->warn('Tidak ada data genre ditemukan.'); return;
        }
        $this->info('Genre Teratas:');
        $this->table(['Genre', 'Jumlah Lagu'], $genres->toArray());
    }
}