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
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Music Manager</h1>
            <p class="mt-1 text-sm text-gray-500">
                Displaying {{ $musicData->firstItem() }}-{{ $musicData->lastItem() }} of {{ $musicData->total() }} results
            </p>
        </div>
        <a href="{{ route('music-manager.create') }}" class="btn-primary inline-flex items-center mt-4 sm:mt-0">
            <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            New Music Order
        </a>
    </div>
    
    {{-- Table Section --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <form action="{{ route('music-manager.index') }}" method="GET">
            <div class="overflow-x-auto">
                <table class="w-full data-table">
                    <thead class="bg-gray-50">
                        {{-- Header untuk Judul & Sorting --}}
                        <tr>
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
                                        <svg class="h-4 w-4 text-gray-400 opacity-50 group-hover:opacity-100" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" /></svg>
                                    </a>
                                </th>
                            @endforeach
                        </tr>
                        {{-- Header untuk Search Bar --}}
                        <tr>
                            @foreach ($columns as $key => $label)
                                <th class="px-4 pb-3">
                                    <input type="text" name="search[{{ $key }}]" placeholder="Search {{ $label }}..." value="{{ request('search.'.$key) }}" class="search-input">
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($musicData as $track)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ \Illuminate\Support\Str::limit($track->trackName, 40) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ \Illuminate\Support\Str::limit($track->artistName, 30) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ \Illuminate\Support\Str::limit($track->collectionName, 35) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $track->primaryGenreName }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $track->releaseDate->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${{ number_format($track->trackPrice, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columns) }}" class="text-center py-12 text-gray-500">
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