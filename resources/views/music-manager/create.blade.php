@extends('layouts.app')

@section('title', 'New Music Order')

@section('content')
<div class="p-6 sm:p-8">
    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">New Music Order</h1>
        <p class="mt-1 text-sm text-gray-500">Upload your Excel file to add new music tracks to the database.</p>
    </div>

    {{-- Upload Section --}}
    <div id="upload-container" class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-8 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Upload an Excel File</h3>
            <p class="mt-1 text-sm text-gray-500">Click the button below to select a .xlsx or .csv file.</p>
            <div class="mt-6">
                <button id="upload-btn" type="button" class="btn-primary inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l-3.75 3.75M12 9.75l3.75 3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                    </svg>
                    Select File
                </button>
                <input type="file" id="excel-file-input" class="hidden" accept=".xlsx, .xls, .csv">
            </div>
            <p id="file-name" class="mt-2 text-sm text-gray-600"></p>
        </div>
    </div>
</div>

{{-- Modal Preview (Sama seperti sebelumnya) --}}
<div id="preview-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-6xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900 text-center">Data Preview</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500 mb-4 text-center">
                    This is a preview of the first 50 rows. Ensure all columns are correct before proceeding.
                </p>
                <div id="preview-table-container" class="max-h-[65vh] overflow-auto border rounded-lg">
                    {{-- Tabel preview akan dimasukkan di sini oleh JavaScript --}}
                </div>
                <div id="modal-spinner" class="hidden my-8 text-center">
                    <div role="status">
                        <svg aria-hidden="true" class="inline w-8 h-8 text-gray-200 animate-spin fill-blue-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                            <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0492C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5424 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                        </svg>
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="items-center px-4 py-3 bg-gray-50 rounded-b-md">
                <form id="import-form" action="{{ route('music-manager.import') }}" method="POST" enctype="multipart/form-data" class="flex justify-end space-x-3">
                    @csrf
                    {{-- Input file akan ditambahkan di sini oleh JavaScript --}}
                    <button id="cancel-btn" type="button" class="btn-secondary px-6 py-2">
                        Cancel
                    </button>
                    <button id="confirm-import-btn" type="submit" class="btn-primary px-6 py-2">
                        Confirm & Save to Database
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- JavaScript untuk halaman ini sama persis dengan yang sebelumnya --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const uploadBtn = document.getElementById('upload-btn');
    const fileInput = document.getElementById('excel-file-input');
    const modal = document.getElementById('preview-modal');
    const cancelBtn = document.getElementById('cancel-btn');
    const importForm = document.getElementById('import-form');
    const tableContainer = document.getElementById('preview-table-container');
    const modalSpinner = document.getElementById('modal-spinner');
    const fileNameDisplay = document.getElementById('file-name');
    
    let selectedFile = null;

    uploadBtn.addEventListener('click', () => fileInput.click());

    fileInput.addEventListener('change', function(event) {
        selectedFile = event.target.files[0];
        if (selectedFile) {
            fileNameDisplay.textContent = `File selected: ${selectedFile.name}`;
            showPreview(selectedFile);
        }
    });

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

function renderTable(header, rows) {
    if (!header || !rows) {
        tableContainer.innerHTML = `<p class="text-red-500 p-4">Invalid data format received from server.</p>`;
        return;
    }

    let table = '<table class="min-w-full divide-y divide-gray-200 text-left text-sm">';
    table += '<thead class="bg-gray-50"><tr>';
    header.forEach(h => table += `<th class="px-4 py-2 font-medium text-gray-500 uppercase">${h}</th>`);
    table += '</tr></thead><tbody class="bg-white divide-y divide-gray-200">';
    
    // PERBAIKAN LOGIKA ADA DI SINI
    rows.forEach(row => {
        table += '<tr>';
        row.forEach(cell => {
            // Langsung gunakan nilai sel dari array
            table += `<td class="px-4 py-2 whitespace-nowrap text-gray-700">${cell || ''}</td>`;
        });
        table += '</tr>';
    });

    table += '</tbody></table>';
    tableContainer.innerHTML = table;
}

    cancelBtn.addEventListener('click', () => {
        modal.classList.add('hidden');
        fileInput.value = '';
        fileNameDisplay.textContent = '';
        selectedFile = null;
    });

    importForm.addEventListener('submit', function(event) {
        if (!selectedFile) {
            event.preventDefault();
            alert('Please select a file first.');
            return;
        }
        const fileInputClone = document.createElement('input');
        fileInputClone.type = 'file';
        fileInputClone.name = 'file';
        fileInputClone.files = fileInput.files;
        fileInputClone.style.display = 'none';

        importForm.appendChild(fileInputClone);
    });
});
</script>
@endpush