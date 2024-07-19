@extends('layouts.app')
@section('content')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Detail uang saku & insentif {{ DateTime::createFromFormat('!m', $allowance->bulan)->format('F') }} {{$allowance->tahun}}</h3>
                    </div>
                </div>
                <div class="card shadow mb-4">
                    <div class="card-body">
                        @if($detailallowance && count($detailallowance) > 0)
                            <div class="dataTables_length mb-3" id="myDataTable_length">
                                <label for="entries">Show
                                    <select id="entries" name="myDataTable_length" aria-controls="myDataTable" onchange="changeEntries()">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                    entries
                                </label>
                            </div>

                            <div id="myDataTable_filter" class="dataTables_filter">
                                <label for="search">search
                                    <input id="search" placeholder>
                                </label>
                            </div>

                            <div class="table-responsive">
                                @include('components.alert')
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Nama Karyawan</th>
                                            <th>Uang Saku</th>
                                            <th>Insentif</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($detailallowance as $detail)
                                            <tr>
                                                <td>{{$detail->karyawan->nama_karyawan}}</td>
                                                <td>{{$detail->uang_saku}}</td>
                                                <td>{{$detail->insentif}}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                               
                            </div>
                            <div class="dataTables_info" id="dataTableInfo" role="status" aria-live="polite">
                                    Showing <span id="showingStart">1</span> to <span id="showingEnd">10</span> of <span id="totalEntries">0</span> entries
                                </div>

                                <div class="dataTables_paginate paging_simple_numbers" id="myDataTable_paginate">
                                    <a href="#" class="paginate_button" id="doublePrevButton" onclick="doublePreviousPage()"><i class="ti-angle-double-left" aria-hidden="true"></i></a>
                                    <a href="#" class="paginate_button" id="prevButton" onclick="previousPage()"><i class="ti-angle-left" aria-hidden="true"></i></a>
                                    <span>
                                        <a id="pageNumbers" aria-controls="myDataTable" role="link" aria-current="page" data-dt-idx="0" tabindex="0"></a>
                                    </span>
                                    <a href="#" class="paginate_button" id="nextButton" onclick="nextPage()"><i class="ti-angle-right" aria-hidden="true"></i></a>
                                    <a href="#" class="paginate_button" id="doubleNextButton" onclick="doubleNextPage()"><i class="ti-angle-double-right" aria-hidden="true"></i></a>
                                </div>
                        @else
                            <p>Tidak ada uang saku dan insentif untuk karyawan pada bulan {{ DateTime::createFromFormat('!m', $allowance->bulan)->format('F') }} {{$allowance->tahun}}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Konfirmasi Penghapusan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus item ini?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tidak</button>
                <button type="button" class="btn btn-primary" id="confirmDelete">Ya</button>
            </div>
        </div>
    </div>
</div>
<script>
    document.getElementById('confirmDelete').addEventListener('click', function() {
        document.getElementById('deleteForm').submit();
    });

    var itemsPerPage = 10; // Ubah nilai ini sesuai dengan jumlah item per halaman
    var currentPage = 1;
    var filteredData = [];
    
    function initializeData() {
        var tableRows = document.querySelectorAll("table tbody tr");
        filteredData = Array.from(tableRows); // Konversi NodeList ke array
        updatePagination();
    }

    // Panggil fungsi initializeData() untuk menginisialisasi data saat halaman dimuat
    initializeData();
    
    function doublePreviousPage() {
        if (currentPage > 1) {
            currentPage = 1;
            updatePagination();
        }
    }
    
    function nextPage() {
        var totalPages = Math.ceil(document.querySelectorAll("table tbody tr").length / itemsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            updatePagination();
        }
    }
  
    function doubleNextPage() {
        var totalPages = Math.ceil(document.querySelectorAll("table tbody tr").length / itemsPerPage);
        if (currentPage < totalPages) {
            currentPage = totalPages;
            updatePagination();
        }
    }

    function previousPage() {
        if (currentPage > 1) {
            currentPage--;
            updatePagination();
        }
    }
 
    function updatePagination() {
        var startIndex = (currentPage - 1) * itemsPerPage;
        var endIndex = startIndex + itemsPerPage;

        // Sembunyikan semua baris
        var tableRows = document.querySelectorAll("table tbody tr");
        tableRows.forEach(function (row) {
            row.style.display = 'none';
        });

        // Tampilkan baris untuk halaman saat ini
        for (var i = startIndex; i < endIndex && i < filteredData.length; i++) {
            filteredData[i].style.display = 'table-row';
        }

        // Update nomor halaman
        var totalPages = Math.ceil(filteredData.length / itemsPerPage);
        var pageNumbers = document.getElementById('pageNumbers');
        pageNumbers.innerHTML = '';

        var totalEntries = filteredData.length;

        document.getElementById('showingStart').textContent = startIndex + 1;
        document.getElementById('showingEnd').textContent = Math.min(endIndex, totalEntries);
        document.getElementById('totalEntries').textContent = totalEntries;

        var pageRange = 3; // Jumlah nomor halaman yang ditampilkan
        var startPage = Math.max(1, currentPage - Math.floor(pageRange / 2));
        var endPage = Math.min(totalPages, startPage + pageRange - 1);

        for (var i = startPage; i <= endPage; i++) {
            var pageButton = document.createElement('button');
            pageButton.className = 'btn btn-primary btn-sm mr-1 ml-1';
            pageButton.textContent = i;
            if (i === currentPage) {
                pageButton.classList.add('btn-active');
            }
            pageButton.onclick = function () {
                currentPage = parseInt(this.textContent);
                updatePagination();
            };
            pageNumbers.appendChild(pageButton);
        }
    }

    function changeEntries() {
        var entriesSelect = document.getElementById('entries');
        var selectedEntries = parseInt(entriesSelect.value);

        // Update the 'itemsPerPage' variable with the selected number of entries
        itemsPerPage = selectedEntries;

        // Reset the current page to 1 when changing the number of entries
        currentPage = 1;

        // Update pagination based on the new number of entries
        updatePagination();
    }

    function applySearchFilter() {
        var searchInput = document.getElementById('search');
        var filter = searchInput.value.toLowerCase();
        
        // Mencari data yang sesuai dengan filter
        filteredData = Array.from(document.querySelectorAll("table tbody tr")).filter(function (row) {
            var rowText = row.textContent.toLowerCase();
            return rowText.includes(filter);
        });

        // Set currentPage kembali ke 1
        currentPage = 1;

        updatePagination();
    }

    updatePagination();

    // Menangani perubahan pada input pencarian
    document.getElementById('search').addEventListener('input', applySearchFilter);
</script>

<style>
    .dataTables_paginate {
        float: right;
        text-align: right;
        padding-top: .25em;
    }

    .paginate_button {
        box-sizing: border-box;
        display: inline-block;
        min-width: 1.5em;
        padding: .5em 1em;
        margin-left: 2px;
        text-align: center;
        text-decoration: none !important;
        cursor: pointer;
        color: inherit !important;
        border: 1px solid transparent;
        border-radius: 2px;
        background: transparent;
    }

    .dataTables_length {
        float: left;
    }

    .dataTables_wrapper .dataTables_length select {
        border: 1px solid #aaa;
        border-radius: 3px;
        padding: 5px;
        background-color: transparent;
        color: inherit;
        padding: 4px;
    }

    .dataTables_info {
        clear: both;
        float: left;
        padding-top: .755em;
    }

    .dataTables_filter {
        float: right;
        text-align: right;
    }

    .dataTables_filter input {
        border: 1px solid #aaa;
        border-radius: 3px;
        padding: 5px;
        background-color: transparent;
        color: inherit;
        margin-left: 3px;
    }

    .btn-active {
        background-color: #007bff;
        color: #fff;
    }

    /* Styling for pagination container */
    .dataTables_paginate {
        text-align: center;
    }

    /* Styling for each pagination button */
    .paginate_button {
        display: inline-block;
        margin: 5px;
        text-align: center;
        border: 1px solid #000;
        padding: 5px 10px;
    }

    /* Media query for small screens */
    @media (max-width: 768px) {
        .paginate_button {
            padding: 3px 6px;
        }
    }

    /* Media query for extra small screens */
    @media (max-width: 576px) {
        .dataTables_paginate {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        .paginate_button {
            padding: 2px 4px;
            margin: 2px;
        }
    }
</style>

@endsection
