@extends('layouts.app')

@section('title', 'Music Manager')

@push('styles')
<style>
    .search-input {
        @apply mt-1 block w-full px-2 py-1.5 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 
               focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-xs;
    }
</style>
@endpush

@section('content')
<div class="p-6 sm:p-8">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Music Orders</h1>
            <p class="mt-1 text-sm text-gray-500">
                Displaying {{ $musicData->firstItem() }}-{{ $musicData->lastItem() }} of {{ $musicData->total() }} results
            </p>
        </div>
        <div class="flex items-center space-x-2 mt-4 sm:mt-0">
            {{-- Tombol Download --}}
            <a href="{{ route('music-manager.export') }}" class="btn-secondary inline-flex items-center">
                <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                Export
            </a>
            {{-- Tombol Tambah Data --}}
            <a href="{{ route('music-manager.create') }}" class="btn-primary inline-flex items-center">
                <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                New Music Order
            </a>
        </div>
    </div>
    
    {{-- Table Section --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <form action="{{ route('music-manager.index') }}" method="GET">
            <div class="overflow-x-auto">
                <table class="w-full data-table">
                    <thead class="bg-gray-50">
                        {{-- Baris Header untuk Judul Kolom & Sorting --}}
                        <tr>
                            <th class="pl-4 pr-2 py-3 w-4">
                                <input type="checkbox" id="select-all-checkbox" class="form-checkbox h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            </th>
                            
                            @php
                                $columns = [
                                    'trackName' => 'Track Name',
                                    'artistName' => 'Artist Name',
                                    'collectionName' => 'Album',
                                    'primaryGenreName' => 'Genre',
                                    'releaseDate' => 'Release Date',
                                    'trackPrice' => 'Price',
                                ];
                            @endphp

                            @foreach ($columns as $key => $label)
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider align-top">
                                    <a href="{{ route('music-manager.index', array_merge(request()->query(), ['sort_by' => $key, 'sort_direction' => request('sort_by') == $key && request('sort_direction') == 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center space-x-1 group">
                                        <span>{{ $label }}</span>
                                    </a>
                                </th>
                            @endforeach
                        </tr>
                        
                        {{-- Baris Header untuk Search Bar --}}
                        <tr>
                            <th class="px-4 pb-3"></th> {{-- Sel Kosong untuk menggeser --}}

                            @foreach ($columns as $key => $label)
                                <th class="px-4 pb-3 align-top">
                                    @if ($key == 'primaryGenreName')
                                        <select name="search[primaryGenreName]" class="search-input">
                                            <option value="">All Genres</option>
                                            @foreach ($genres as $genre)
                                                <option value="{{ $genre }}" {{ request('search.primaryGenreName') == $genre ? 'selected' : '' }}>
                                                    {{ $genre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @elseif ($key == 'releaseDate')
                                        <input type="date" name="search[releaseDate]" value="{{ request('search.releaseDate') }}" class="search-input">
                                    @elseif ($key == 'trackPrice')
                                         <div class="flex space-x-2">
											<input type="number" name="search[price_min]" placeholder="Min" value="{{ request('search.price_min') }}" class="search-input w-1/2" step="0.01">
											<input type="number" name="search[price_max]" placeholder="Max" value="{{ request('search.price_max') }}" class="search-input w-1/2" step="0.01">
										</div>
                                    @else
                                        <input type="text" name="search[{{ $key }}]" placeholder="Search {{ $label }}..." value="{{ request('search.'.$key) }}" class="search-input">
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($musicData as $track)
                            <tr>
                                <td class="pl-4 pr-2 py-4">
                                    <input type="checkbox" class="row-checkbox form-checkbox h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" value="{{ $track->id }}">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ \Illuminate\Support\Str::limit($track->trackName, 40) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ \Illuminate\Support\Str::limit($track->artistName, 30) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ \Illuminate\Support\Str::limit($track->collectionName, 35) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $track->primaryGenreName }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $track->releaseDate->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${{ number_format($track->trackPrice, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columns) + 1 }}" class="text-center py-12 text-gray-500">
                                    No data found matching your criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 bg-gray-50/50 border-t border-gray-200/80 flex justify-end space-x-3">
                <a href="{{ route('music-manager.index') }}" class="btn-secondary px-4 py-2">Clear</a>
                <button type="submit" class="btn-primary px-4 py-2">Search</button>
            </div>
        </form>
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $musicData->withQueryString()->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const exportSelectedBtn = document.getElementById('export-selected-btn');
    const exportBaseUrl = "{{ route('music-manager.export') }}";

    function updateExportButtonState() {
        const selectedIds = getSelectedIds();
        if (selectedIds.length > 0) {
            exportSelectedBtn.disabled = false;
            exportSelectedBtn.classList.remove('btn-secondary');
            exportSelectedBtn.classList.add('btn-primary');
        } else {
            exportSelectedBtn.disabled = true;
            exportSelectedBtn.classList.add('btn-secondary');
            exportSelectedBtn.classList.remove('btn-primary');
        }
    }

    function getSelectedIds() {
        const selectedIds = [];
        rowCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                selectedIds.push(checkbox.value);
            }
        });
        return selectedIds;
    }

    selectAllCheckbox.addEventListener('change', function () {
        rowCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateExportButtonState();
    });

    rowCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            if (!this.checked) {
                selectAllCheckbox.checked = false;
            } else {
                if (document.querySelectorAll('.row-checkbox:checked').length === rowCheckboxes.length) {
                    selectAllCheckbox.checked = true;
                }
            }
            updateExportButtonState();
        });
    });

    exportSelectedBtn.addEventListener('click', function () {
        const selectedIds = getSelectedIds();
        if (selectedIds.length > 0) {
            const queryParams = new URLSearchParams();
            selectedIds.forEach(id => queryParams.append('selected_ids[]', id));
            window.location.href = `${exportBaseUrl}?${queryParams.toString()}`;
        }
    });

    // Initial state check
    updateExportButtonState();
});
</script>
@endpush