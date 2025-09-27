<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MusicTrack;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel; // <-- Import Facade

class MusicImportController extends Controller
{
    /**
     * Tampilkan pratinjau data dari file Excel yang diunggah.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            // Ambil data dari sheet pertama, konversi ke array
            $data = Excel::toCollection(null, $request->file('file'))->first();

            // Ambil 50 baris pertama untuk preview
            $previewData = $data->slice(0, 50); 
            $header = $previewData->pull(0); // Ambil baris pertama sebagai header

            return response()->json([
                'header' => $header,
                'rows' => $previewData->values(), // values() untuk reset keys
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal membaca file. Pastikan formatnya benar.'], 422);
        }
    }

    /**
     * Simpan data dari file Excel yang diunggah ke database.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $data = Excel::toCollection(null, $request->file('file'))->first();
            
            // Hapus baris header
            $rows = $data->slice(1);
            $header = $data->first()->map(fn($item) => strtolower(str_replace(' ', '', $item)))->all();
            
            $insertData = [];
            foreach ($rows as $row) {
                // Gabungkan header dengan baris menjadi array asosiatif
                $rowData = array_combine($header, $row->all());

                $insertData[] = [
                    'trackId'         => $rowData['trackid'],
                    'trackName'       => $rowData['trackname'],
                    'artistId'        => $rowData['artistid'],
                    'artistName'      => $rowData['artistname'],
                    'collectionName'  => $rowData['collectionname'],
                    'primaryGenreName'=> $rowData['primarygenrename'],
                    'releaseDate'     => \Carbon\Carbon::parse($rowData['releasedate']),
                    'trackPrice'      => !empty($rowData['trackprice']) ? $rowData['trackprice'] : null,
                    'collectionPrice' => !empty($rowData['collectionprice']) ? $rowData['collectionprice'] : null,
                    'country'         => $rowData['country'],
                    'currency'        => $rowData['currency'],
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            }

            // Gunakan insert untuk performa yang lebih baik
            if (!empty($insertData)) {
                MusicTrack::insert($insertData);
            }

            return back()->with('success', 'Data berhasil diimpor!');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat mengimpor data. ' . $e->getMessage());
        }
    }
}