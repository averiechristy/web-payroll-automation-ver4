@extends('layouts.app')
@section('content')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Detail {{$datatm->judul_invoicetm}}</h3>
                    </div>
                </div>


                <div class="row mb-4">
    <div class="col-md-12">
    <a href="{{ route('download.invoicetm', [
    'bulan' => $datatm->bulan,
    'tahun' => $datatm->tahun,
    'status_invoicetm' => $datatm->status_invoicetm,
    'management_fee' => $datatm->management_fee,
    'datainvoicetm' => json_encode($datainvoicetm),
]) }}" class="btn btn-success">Download Laporan Invoice Tester Manual</a>
    </div>



</div>


<form id="closeinvoiceForm" action="{{ route('close.invoicetm', $datatm->id)}}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="datainvoicetm" id="datainvoice">
</form>

<script>
  function closepayroll() {
    var datainvoicetm = @json($datainvoicetm); // Assuming $datainvoice is passed from your backend
    document.getElementById('datainvoice').value = JSON.stringify(datainvoicetm);
    document.getElementById('closeinvoiceForm').submit();
}

</script>
<div class="row mb-4">
    <div class="col-md-12">
        @if ($datatm->status_invoicetm == 'Closing')
        <button type="button" class="btn btn-primary" disabled>Closing Invoice</button>
        @else
        <button type="button" class="btn btn-primary" onclick="closepayroll()">Closing Invoice</button>
        @endif
    </div>
</div>
                <!-- Card Rekap Lembur -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h4>Rekap Lembur</h4>
                    </div>
                    <div class="card-body">
                        <div class="dataTables_length mb-3" id="lemburTable_length">
                            <label for="lemburEntries">Show
                                <select id="lemburEntries" name="lemburTable_length" aria-controls="lemburTable" onchange="changeLemburEntries()">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                entries
                            </label>
                        </div>

                        <div id="lemburTable_filter" class="dataTables_filter">
                            <label for="lemburSearch">search
                                <input id="lemburSearch" placeholder>
                            </label>
                        </div>                            

                        <div class="table-responsive">
                            @include('components.alert')

                            <table class="table table-bordered" id="lemburTable">
                                <thead>
                                    <tr>
                                       <th>No</th>
                                       <th>Nama</th>
                                       <th>Tanggal Lembur</th>
                                       <th>Total Jam Lembur (Hari Kerja)</th>
                                       <th>Total Jam Lembur (Hari Libur)</th>
                                       <th>Biaya Lembur</th>
                                       <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @php
                            $no = 1;
                            @endphp
     @foreach($datainvoicetm as $index => $data)     
                            <tr>
                                <td>{{$no++}}</td>
                            <td>{{$data['nama']}}</td>
                            <td>{{$data['tanggal_lembur']}}</td>
                            <td>{{$data['totaljamharikerja']}}</td>
                            <td>{{$data['totaljamharilibur']}}</td>
                            <td>{{ 'Rp ' . number_format($data['biayalembur'], 0, ',', '.') }}</td>
                            <td></td>
                            </tr>

                            @endforeach
                                </tbody>
                            </table>

                           
                        </div>
                        <div class="dataTables_info" id="lemburTableInfo" role="status" aria-live="polite">
                                Showing <span id="lemburShowingStart">1</span> to <span id="lemburShowingEnd">10</span> of <span id="lemburTotalEntries">0</span> entries
                            </div>

                            <div class="dataTables_paginate paging_simple_numbers" id="lemburTable_paginate">
                                <a href="#" class="paginate_button" id="lemburDoublePrevButton" onclick="doublePreviousPage('lembur')"><i class="ti-angle-double-left" aria-hidden="true"></i></a>
                                <a href="#" class="paginate_button" id="lemburPrevButton" onclick="previousPage('lembur')"><i class="ti-angle-left" aria-hidden="true"></i></a>
                                <span id="lemburPageNumbers">
                                    <!-- Page numbers will be generated here -->
                                </span>
                                <a href="#" class="paginate_button" id="lemburNextButton" onclick="nextPage('lembur')"><i class="ti-angle-right" aria-hidden="true"></i></a>
                                <a href="#" class="paginate_button" id="lemburDoubleNextButton" onclick="doubleNextPage('lembur')"><i class="ti-angle-double-right" aria-hidden="true"></i></a>
                            </div>
                    </div>
                </div>

                <!-- Card Rekap Absensi -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h4>Rekap Absensi</h4>
                    </div>
                    <div class="card-body">
                        <div class="dataTables_length mb-3" id="absensiTable_length">
                            <label for="absensiEntries">Show
                                <select id="absensiEntries" name="absensiTable_length" aria-controls="absensiTable" onchange="changeAbsensiEntries()">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                entries
                            </label>
                        </div>

                        <div id="absensiTable_filter" class="dataTables_filter">
                            <label for="absensiSearch">search
                                <input id="absensiSearch" placeholder>
                            </label>
                        </div>                            

                        <div class="table-responsive">
                            @include('components.alert')

                            <table class="table table-bordered" id="absensiTable">
                                <thead>
                                    <tr>
                                      <th>No</th>
                                      <th>Nama</th>
                                      <th>Total Hari Kerja</th>
                                      <th>Realisasi Hari Kerja</th>
                                      <th>Absen</th>
                                      <th>Persentase Kehadiran</th>
                                      <th>Biaya Lembur</th>
                                      <th>Biaya Jasa Per Bulan</th>
                                      <th>Realisasi Invoice</th>
                                      <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                            @php
                            $nomor = 1;
                            @endphp
     @foreach($datainvoicetm as $index => $data)     
                            <tr>
                            <td>{{$nomor++}}</td>
                            <td>{{$data['nama']}}</td>
                            <td>{{$data['totalhari']}}</td>
                            <td>{{$data['realisasiharikerja']}}</td>
                            <td>{{$data['absen']}}</td>
                            <td>{{$data['presentasekehadiran']}} %</td>
                            <td>{{ 'Rp ' . number_format($data['biayalembur'], 0, ',', '.') }}</td>
                            <td>{{ 'Rp ' . number_format($data['biayajasaperbulan'], 0, ',', '.') }}</td>
                            <td>{{ 'Rp ' . number_format($data['realisasiinvoice'], 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                            @endforeach
                                </tbody>
                            </table>

                        </div>
                        <div class="dataTables_info" id="absensiTableInfo" role="status" aria-live="polite">
                                Showing <span id="absensiShowingStart">1</span> to <span id="absensiShowingEnd">10</span> of <span id="absensiTotalEntries">0</span> entries
                            </div>

                            <div class="dataTables_paginate paging_simple_numbers" id="absensiTable_paginate">
                                <a href="#" class="paginate_button" id="absensiDoublePrevButton" onclick="doublePreviousPage('absensi')"><i class="ti-angle-double-left" aria-hidden="true"></i></a>
                                <a href="#" class="paginate_button" id="absensiPrevButton" onclick="previousPage('absensi')"><i class="ti-angle-left" aria-hidden="true"></i></a>
                                <span id="absensiPageNumbers">
                                    <!-- Page numbers will be generated here -->
                                </span>
                                <a href="#" class="paginate_button" id="absensiNextButton" onclick="nextPage('absensi')"><i class="ti-angle-right" aria-hidden="true"></i></a>
                                <a href="#" class="paginate_button" id="absensiDoubleNextButton" onclick="doubleNextPage('absensi')"><i class="ti-angle-double-right" aria-hidden="true"></i></a>
                            </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Styles -->
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
    .dataTables_paginate {
        text-align: center;
    }
    .paginate_button {
        display: inline-block;
        margin: 5px;
        text-align: center;
        border: 1px solid #000; 
        padding: 5px 10px;
    }
    @media (max-width: 768px) {
        .paginate_button {
            padding: 3px 6px;
        }
    }
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

<!-- Scripts -->
<script>
    var lemburItemsPerPage = 10;
    var lemburCurrentPage = 1;
    var lemburFilteredData = [];
    
    function initializeLemburData() {
        var tableRows = document.querySelectorAll("#lemburTable tbody tr");
        lemburFilteredData = Array.from(tableRows);
        updateLemburPagination();
    }
    initializeLemburData();
    
    function doublePreviousPage(type) {
        if (type === 'lembur') {
            if (lemburCurrentPage > 1) {
                lemburCurrentPage = 1;
                updateLemburPagination();
            }
        } else if (type === 'absensi') {
            if (absensiCurrentPage > 1) {
                absensiCurrentPage = 1;
                updateAbsensiPagination();
            }
        }
    }
    
    function nextPage(type) {
        if (type === 'lembur') {
            var totalPages = Math.ceil(lemburFilteredData.length / lemburItemsPerPage);
            if (lemburCurrentPage < totalPages) {
                lemburCurrentPage++;
                updateLemburPagination();
            }
        } else if (type === 'absensi') {
            var totalPages = Math.ceil(absensiFilteredData.length / absensiItemsPerPage);
            if (absensiCurrentPage < totalPages) {
                absensiCurrentPage++;
                updateAbsensiPagination();
            }
        }
    }
    
    function doubleNextPage(type) {
        if (type === 'lembur') {
            var totalPages = Math.ceil(lemburFilteredData.length / lemburItemsPerPage);
            if (lemburCurrentPage < totalPages) {
                lemburCurrentPage = totalPages;
                updateLemburPagination();
            }
        } else if (type === 'absensi') {
            var totalPages = Math.ceil(absensiFilteredData.length / absensiItemsPerPage);
            if (absensiCurrentPage < totalPages) {
                absensiCurrentPage = totalPages;
                updateAbsensiPagination();
            }
        }
    }
    
    function previousPage(type) {
        if (type === 'lembur') {
            if (lemburCurrentPage > 1) {
                lemburCurrentPage--;
                updateLemburPagination();
            }
        } else if (type === 'absensi') {
            if (absensiCurrentPage > 1) {
                absensiCurrentPage--;
                updateAbsensiPagination();
            }
        }
    }
    
    function updateLemburPagination() {
        var startIndex = (lemburCurrentPage - 1) * lemburItemsPerPage;
        var endIndex = startIndex + lemburItemsPerPage;
        var tableRows = document.querySelectorAll("#lemburTable tbody tr");
        tableRows.forEach(function (row) {
            row.style.display = 'none';
        });
        for (var i = startIndex; i < endIndex && i < lemburFilteredData.length; i++) {
            lemburFilteredData[i].style.display = 'table-row';
        }
        var totalPages = Math.ceil(lemburFilteredData.length / lemburItemsPerPage);
        var pageNumbers = document.getElementById('lemburPageNumbers');
        pageNumbers.innerHTML = '';
        var totalEntries = lemburFilteredData.length;
        document.getElementById('lemburShowingStart').textContent = startIndex + 1;
        document.getElementById('lemburShowingEnd').textContent = Math.min(endIndex, totalEntries);
        document.getElementById('lemburTotalEntries').textContent = totalEntries;
        var pageRange = 3;
        var startPage = Math.max(1, lemburCurrentPage - Math.floor(pageRange / 2));
        var endPage = Math.min(totalPages, startPage + pageRange - 1);
        for (var i = startPage; i <= endPage; i++) {
            var pageButton = document.createElement('button');
            pageButton.className = 'btn btn-primary btn-sm mr-1 ml-1';
            pageButton.textContent = i;
            if (i === lemburCurrentPage) {
                pageButton.classList.add('btn-active');
            }
            pageButton.onclick = function () {
                lemburCurrentPage = parseInt(this.textContent);
                updateLemburPagination();
            };
            pageNumbers.appendChild(pageButton);
        }
    }

    function changeLemburEntries() {
        var entriesSelect = document.getElementById('lemburEntries');
        var selectedEntries = parseInt(entriesSelect.value);
        lemburItemsPerPage = selectedEntries;
        lemburCurrentPage = 1;
        updateLemburPagination();
    }

    function applyLemburSearchFilter() {
        var searchInput = document.getElementById('lemburSearch');
        var filter = searchInput.value.toLowerCase();
        lemburFilteredData = Array.from(document.querySelectorAll("#lemburTable tbody tr")).filter(function (row) {
            var rowText = row.textContent.toLowerCase();
            return rowText.includes(filter);
        });
        lemburCurrentPage = 1;
        updateLemburPagination();
    }

    document.getElementById('lemburSearch').addEventListener('input', applyLemburSearchFilter);

    // Absensi functions
    var absensiItemsPerPage = 10;
    var absensiCurrentPage = 1;
    var absensiFilteredData = [];
    
    function initializeAbsensiData() {
        var tableRows = document.querySelectorAll("#absensiTable tbody tr");
        absensiFilteredData = Array.from(tableRows);
        updateAbsensiPagination();
    }
    initializeAbsensiData();
    
    function updateAbsensiPagination() {
        var startIndex = (absensiCurrentPage - 1) * absensiItemsPerPage;
        var endIndex = startIndex + absensiItemsPerPage;
        var tableRows = document.querySelectorAll("#absensiTable tbody tr");
        tableRows.forEach(function (row) {
            row.style.display = 'none';
        });
        for (var i = startIndex; i < endIndex && i < absensiFilteredData.length; i++) {
            absensiFilteredData[i].style.display = 'table-row';
        }
        var totalPages = Math.ceil(absensiFilteredData.length / absensiItemsPerPage);
        var pageNumbers = document.getElementById('absensiPageNumbers');
        pageNumbers.innerHTML = '';
        var totalEntries = absensiFilteredData.length;
        document.getElementById('absensiShowingStart').textContent = startIndex + 1;
        document.getElementById('absensiShowingEnd').textContent = Math.min(endIndex, totalEntries);
        document.getElementById('absensiTotalEntries').textContent = totalEntries;
        var pageRange = 3;
        var startPage = Math.max(1, absensiCurrentPage - Math.floor(pageRange / 2));
        var endPage = Math.min(totalPages, startPage + pageRange - 1);
        for (var i = startPage; i <= endPage; i++) {
            var pageButton = document.createElement('button');
            pageButton.className = 'btn btn-primary btn-sm mr-1 ml-1';
            pageButton.textContent = i;
            if (i === absensiCurrentPage) {
                pageButton.classList.add('btn-active');
            }
            pageButton.onclick = function () {
                absensiCurrentPage = parseInt(this.textContent);
                updateAbsensiPagination();
            };
            pageNumbers.appendChild(pageButton);
        }
    }

    function changeAbsensiEntries() {
        var entriesSelect = document.getElementById('absensiEntries');
        var selectedEntries = parseInt(entriesSelect.value);
        absensiItemsPerPage = selectedEntries;
        absensiCurrentPage = 1;
        updateAbsensiPagination();
    }

    function applyAbsensiSearchFilter() {
        var searchInput = document.getElementById('absensiSearch');
        var filter = searchInput.value.toLowerCase();
        absensiFilteredData = Array.from(document.querySelectorAll("#absensiTable tbody tr")).filter(function (row) {
            var rowText = row.textContent.toLowerCase();
            return rowText.includes(filter);
        });
        absensiCurrentPage = 1;
        updateAbsensiPagination();
    }

    document.getElementById('absensiSearch').addEventListener('input', applyAbsensiSearchFilter);
</script>
@endsection
