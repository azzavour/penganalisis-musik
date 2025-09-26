<?php

namespace App\Console\Commands;

use App\Imports\AppleMusicImport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ImportMusicData extends Command
{
    /**
     * Signature untuk memanggil command.
     * @var string
     */
    protected $signature = 'app:import-apple-music {file : Nama file CSV di dalam folder storage/app folder.}';

    /**
     * Deskripsi dari perintah konsol.
     * @var string
     */
    protected $description = 'Impor data musik dari file CSV di folder public';

    /**
     * Menjalankan perintah konsol.
     */
    public function handle()
    {

        $file = $this->argument('file');
        
        $filePath = storage_path('app/' . $file);

        if (!file_exists($filePath)) {
            $this->error("File not found at: {$filePath}");
            return 1; 
        }

        $this->info("Starting import for file: {$file}");

        try {
            $import = new SpotifyUserImport;
            $import->withOutput($this->output);
            $import->import($filePath);

            $this->output->success('Import completed successfully!');

        } catch (\Exception $e) {
            $this->error('An error occurred during the import: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}