<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MusicTrack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class MusicImportController extends Controller
{
    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $data = Excel::toCollection(null, $request->file('file'))->first();
            if ($data->isEmpty()) {
                return response()->json(['error' => 'File Excel kosong atau tidak dapat dibaca.'], 422);
            }

            // Membersihkan kolom kosong dari preview agar konsisten
            $header = $data->first()->filter()->values();
            $rows = $data->slice(1)->map(function ($row) use ($header) {
                return $row->take($header->count()); // Ambil data sesuai jumlah header
            });

            return response()->json([
                'header' => $header,
                'rows' => $rows->slice(0, 50)->values(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error previewing file: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memproses file. Pastikan formatnya benar.'], 422);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $data = Excel::toCollection(null, $request->file('file'))->first();

            if ($data->isEmpty() || $data->count() < 2) {
                return redirect()->route('music-manager.create')->with('error', 'File Excel harus memiliki setidaknya satu baris header dan satu baris data.');
            }

            // PERBAIKAN: Pastikan header diinisialisasi sebagai Collection dengan benar
            $headerFromFile = collect($data->pull(0))
                ->filter() // Hapus nilai null/kosong
                ->map(fn($item) => strtolower(str_replace(' ', '', trim($item))))
                ->values(); // Reset index array agar menjadi list sederhana
            
            $headerCount = $headerFromFile->count();

            $expectedHeaders = collect(['trackid', 'trackname', 'artistid', 'artistname', 'collectionname', 'primarygenrename', 'releasedate', 'trackprice', 'collectionprice', 'country', 'currency']);
            
            $missingColumns = $expectedHeaders->diff($headerFromFile);
            $extraColumns = $headerFromFile->diff($expectedHeaders);

            if ($missingColumns->isNotEmpty() || $extraColumns->isNotEmpty()) {
                $errorMessage = "Struktur header file Excel tidak sesuai.<ul>";
                if ($missingColumns->isNotEmpty()) {
                    $errorMessage .= "<li>Kolom yang **kurang**: <span style='color: red;'>" . $missingColumns->implode(', ') . "</span></li>";
                }
                if ($extraColumns->isNotEmpty()) {
                    $errorMessage .= "<li>Kolom yang **berlebih**: <span style='color: red;'>" . $extraColumns->implode(', ') . "</span></li>";
                }
                $errorMessage .= "</ul>";
                return redirect()->route('music-manager.create')->with('error', $errorMessage);
            }
            
            $headerMapping = [
                'trackid' => 'trackId',
                'trackname' => 'trackName',
                'artistid' => 'artistId',
                'artistname' => 'artistName',
                'collectionname' => 'collectionName',
                'primarygenrename' => 'primaryGenreName',
                'releasedate' => 'releaseDate',
                'trackprice' => 'trackPrice',
                'collectionprice' => 'collectionPrice',
                'country' => 'country',
                'currency' => 'currency',
            ];

            $insertData = [];
            foreach ($data as $row) {
                $rowData = $row->take($headerCount);
                
                if ($rowData->filter()->isEmpty()) {
                    continue;
                }
                
                $combinedData = array_combine($headerFromFile->all(), $rowData->all());
                
                $trackData = [];
                foreach($headerMapping as $fileHeader => $dbColumn) {
                    $trackData[$dbColumn] = $combinedData[$fileHeader] ?? null;
                }
                
                $trackData['releaseDate'] = Carbon::parse($trackData['releaseDate']);
                $trackData['trackPrice'] = !empty($trackData['trackPrice']) ? $trackData['trackPrice'] : null;
                $trackData['collectionPrice'] = !empty($trackData['collectionPrice']) ? $trackData['collectionPrice'] : null;
                $trackData['created_at'] = now();
                $trackData['updated_at'] = now();

                $insertData[] = $trackData;
            }

            if (!empty($insertData)) {
                MusicTrack::insert($insertData);
            }

            return redirect()->route('music-manager.index')->with('success', count($insertData) . ' data musik berhasil diimpor!');

        } catch (\Exception $e) {
            Log::error('Error importing file: ' . $e->getMessage());
            return redirect()->route('music-manager.create')->with('error', 'Terjadi kesalahan saat mengimpor data. Pesan: ' . $e->getMessage());
        }
    }
}

