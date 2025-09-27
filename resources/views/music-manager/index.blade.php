@extends('layouts.app')

@section('title', 'Music Manager')

@push('styles')
<style>
    /* Custom styles for a professional table */
    .table-wrapper {
        @apply bg-white rounded-xl shadow-lg border border-gray-200/80 overflow-hidden;
    }
    
    .table-header {
        @apply bg-gray-50;
    }

    .table-header th {
        @apply px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider;
    }

    .table-header .search-input {
        @apply mt-2 block w-full px-3 py-1.5 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 
               focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150;
    }

    .sortable-link {
        @apply flex items-center group cursor-pointer;
    }

    .sort-icon {
        @apply ml-2 opacity-30 group-hover:opacity-100 transition-opacity duration-200;
        width: 1rem;
        height: 1rem;
    }

    .sorted .sort-icon.active {
        @apply opacity-100 text-indigo-600;
    }

    .data-table tbody tr:nth-child(even) {
        @apply bg-white;
    }
    
    .data-table tbody tr:nth-child(odd) {
        @apply bg-gray-50/50;
    }

    .data-table tbody tr:hover {
        @apply bg-indigo-50;
    }
    
    .data-table td {
        @apply px-6 py-4 whitespace-nowrap text-sm;
    }
</style>
@endpush

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header Section --}}
        <div class="bg-white rounded-xl shadow-lg border border-gray-200/80 p-6 mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Music Data Manager</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Total {{ $stats['total_tracks'] }} tracks from {{ $stats['total_artists'] }} artists.
                    </p>
            <div class="flex items-center space-x-3">
            <button id="import-btn" class="btn-primary px-5 py-2.5">
                <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
        Import From Excel
            </button>
            <input type="file" id="excel-file-input" class="hidden" accept=".xlsx, .xls, .csv">
        </div>
        </div>
        
        {{-- Table Section --}}
        <div class="table-wrapper">
            <form action="{{ route('music-manager.index') }}" method="GET">
                <div class="overflow-x-auto">
                    <table class="w-full data-table">
                        <thead class="table-header">
                            <tr>
                                @php
                                    $columns = [
                                        'trackName' => 'Track Name',
                                        'artistName' => 'Artist Name',
                                        'primaryGenreName' => 'Genre',
                                        'releaseDate' => 'Release Date',
                                        'trackPrice' => 'Price',
                                        'country' => 'Country',
                                    ];
                                @endphp

                                @foreach ($columns as $key => $label)
                                <th class="{{ $loop->first ? 'pl-6' : '' }} {{ $loop->last ? 'pr-6' : '' }}">
                                    <a href="{{ route('music-manager.index', array_merge(request()->query(), ['sort_by' => $key, 'sort_direction' => request('sort_by') == $key && request('sort_direction') == 'asc' ? 'desc' : 'asc'])) }}" 
                                       class="sortable-link {{ request('sort_by') == $key ? 'sorted' : '' }}">
                                        {{ $label }}
                                        <div class="sort-icons flex flex-col">
                                            <svg class="sort-icon asc {{ request('sort_by') == $key && request('sort_direction') == 'asc' ? 'active' : '' }}" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M7.247 4.86l-4.796 5.481c-.566.647-.106 1.659.753 1.659h9.592a1 1 0 0 0 .753-1.659l-4.796-5.48a1 1 0 0 0-1.506 0z"/></svg>
                                            <svg class="sort-icon desc {{ request('sort_by') == $key && request('sort_direction') == 'desc' ? 'active' : '' }}" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z"/></svg>
                                        </div>
                                    </a>
                                </th>
                                @endforeach
                            </tr>
                            {{-- Search Row --}}
                            <tr>
                                @foreach ($columns as $key => $label)
                                <th class="p-2 {{ $loop->first ? 'pl-6' : '' }} {{ $loop->last ? 'pr-6' : '' }}">
                                    <input type="text" name="search[{{ $key }}]" placeholder="Search..." value="{{ request('search.'.$key) }}" class="search-input">
                                </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($musicData as $track)
                                <tr>
                                    <td class="font-medium text-gray-900">{{ $track->trackName }}</td>
                                    <td class="text-gray-600">{{ $track->artistName }}</td>
                                    <td class="text-gray-600">{{ $track->primaryGenreName }}</td>
                                    <td class="text-gray-600">{{ $track->releaseDate->format('d M Y') }}</td>
                                    <td class="text-gray-600">${{ number_format($track->trackPrice, 2) }}</td>
                                    <td class="text-gray-600">{{ $track->country }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($columns) }}" class="text-center py-12 text-gray-500">
                                        <p class="font-semibold">No data found</p>
                                        <p class="mt-1 text-sm">Try adjusting your search or filter.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Search and Clear Buttons outside the scrolling area --}}
                <div class="p-4 bg-gray-50/50 border-t border-gray-200/80 flex justify-end space-x-3">
                    <a href="{{ route('music-manager.index') }}" class="btn-secondary px-4 py-2">Clear Filters</a>
                    <button type="submit" class="btn-primary px-4 py-2">Search</button>
                </div>
            </form>
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $musicData->withQueryString()->links() }}
        </div>
    </div>
</div>
{{-- Modal untuk Preview Import --}}
<div id="preview-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-5xl shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Data Preview</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500 mb-4">
                    Ini adalah pratinjau dari 50 baris pertama data Anda. Pastikan semua kolom sudah benar sebelum melanjutkan.
                </p>
                <div id="preview-table-container" class="max-h-[60vh] overflow-auto border rounded-lg">
                    {{-- Tabel preview akan dimasukkan di sini oleh JavaScript --}}
                </div>
                <div id="modal-spinner" class="hidden my-8">
                    <p>Loading preview...</p>
                </div>
            </div>
            <div class="items-center px-4 py-3">
                <form id="import-form" action="{{ route('music-manager.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    {{-- Input file akan ditambahkan di sini oleh JavaScript --}}
                    <button id="confirm-import-btn" type="submit" class="btn-primary w-full md:w-auto px-6 py-2">
                        Confirm & Save to Database
                    </button>
                    <button id="cancel-btn" type="button" class="btn-secondary w-full md:w-auto mt-2 md:mt-0 px-6 py-2">
                        Cancel
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const importBtn = document.getElementById('import-btn');
    const fileInput = document.getElementById('excel-file-input');
    const modal = document.getElementById('preview-modal');
    const cancelBtn = document.getElementById('cancel-btn');
    const importForm = document.getElementById('import-form');
    const tableContainer = document.getElementById('preview-table-container');
    const modalSpinner = document.getElementById('modal-spinner');
    
    let selectedFile = null;

    // Buka file dialog saat tombol import diklik
    importBtn.addEventListener('click', () => fileInput.click());

    // Handle saat file dipilih
    fileInput.addEventListener('change', function(event) {
        selectedFile = event.target.files[0];
        if (selectedFile) {
            showPreview(selectedFile);
        }
    });

    // Fungsi untuk menampilkan preview
    async function showPreview(file) {
        modal.classList.remove('hidden');
        tableContainer.innerHTML = '';
        modalSpinner.classList.remove('hidden');

        const formData = new FormData();
        formData.append('file', file);

        try {
            const response = await fetch("{{ route('music-manager.preview') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || 'Failed to fetch preview.');
            }

            const data = await response.json();
            renderTable(data.header, data.rows);
            
        } catch (error) {
            tableContainer.innerHTML = `<p class="text-red-500 p-4">${error.message}</p>`;
        } finally {
            modalSpinner.classList.add('hidden');
        }
    }

    // Fungsi untuk merender tabel preview
    function renderTable(header, rows) {
        let table = '<table class="min-w-full divide-y divide-gray-200">';
        table += '<thead class="bg-gray-50"><tr>';
        header.forEach(h => table += `<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">${h}</th>`);
        table += '</tr></thead><tbody class="bg-white divide-y divide-gray-200">';
        rows.forEach(row => {
            table += '<tr>';
            row.forEach(cell => table += `<td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">${cell || ''}</td>`);
            table += '</tr>';
        });
        table += '</tbody></table>';
        tableContainer.innerHTML = table;
    }

    // Sembunyikan modal saat tombol cancel diklik
    cancelBtn.addEventListener('click', () => {
        modal.classList.add('hidden');
        fileInput.value = ''; // Reset input file
        selectedFile = null;
    });

    // Handle saat form import disubmit
    importForm.addEventListener('submit', function(event) {
        if (!selectedFile) {
            event.preventDefault();
            alert('Please select a file first.');
            return;
        }
        // Tambahkan file ke form sebelum submit
        const fileInputClone = fileInput.cloneNode(true);
        importForm.appendChild(fileInputClone);
    });
});
</script>
@endpush
@endsection