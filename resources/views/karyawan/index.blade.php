@extends('layouts.app')

@section('content')

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Karyawan</h3>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="py-3">
                        <h5 class="description ml-3">Unggah file untuk menambahkan data karyawan baru, atau tambahkan ke kumpulan data karyawan yang sudah ada. <br> Anda dapat mengunggah file Excel</h5>
                        <div class="button-group ml-3">
                            <a href="{{ route('karyawan.create') }}">
                                <button type="button" class="btn btn-primary">Tambah Karyawan</button>
                            </a>
                            <a href="{{ route('downloadkaryawan') }}" id="download-template-link">
                                <button type="button" class="btn btn-warning" download>Unduh Template</button>
                            </a>
                        </div>
                        <div class="importdata ml-3">
                            <form id="upload-form" action="{{ route('importkaryawan') }}" method="post" enctype="multipart/form-data" style="display: flex; align-items: center;">
                                @csrf
                                <input type="file" name="file" accept=".xlsx, .xls" style="margin-right: 10px;" required>
                                <button class="btn btn-info" type="submit">Unggah File</button>
                            </form>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="dataTables_length mb-3" id="myDataTable_length">
                            <label for="entries"> Show
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
                                        <th>NIK</th>
                                        <th>Payroll Code</th>
                                        <th>Nama Karyawan</th>
                                        <th>No Amandemen</th>
                                        <th>NIK (sesuai KTP)</th>
                                        <th>Penempatan</th>
                                        <th>Posisi</th>
                                        <th>Jabatan</th>
                                        <th>Management Fee (%)</th>
                                        <th>Leader</th>
                                        <th>Tanggal Bergabung</th>
                                        <th>Status Karyawan</th>
                                     
                                        <!-- <th>Status Kerja</th> -->
                                        <th>Tanggal Resign</th>
                                        <th>Created At</th>
                                        <th>Created By</th>
                                        <th>Updated At</th>
                                        <th>Updated By</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
@php
    use Carbon\Carbon;
@endphp
                                    @foreach ($karyawan as $item)
                                    <tr>
                                        <td>{{ $item->nik }}</td>
                                        <td>{{ $item->payroll_code }}</td>
                                        <td>{{ $item->nama_karyawan }}</td>
                                        <td>{{ $item->no_amandemen }}</td>
                                        <td>{{ $item->nik_ktp }}</td>
                                        <td>{{ $item->penempatan->nama_unit_kerja }}</td>
                                        <td>{{ $item->posisi->posisi }}</td>
                                        <td>{{ $item->jabatan }}</td>
                                        <td>{{ $item->management_fee * 100 }}</td>
                                        <td>{{ $item->leader }}</td>
                                        <td>
                                        @if ($item->tanggal_bergabung)
                                        {{ Carbon::parse($item->tanggal_bergabung)->format('d-m-Y') }}</td>
                                        @endif
                                        <td>{{ $item->status_karyawan }}</td>
                                     
<!-- <td>
    @if($item->status_kerja == 'Aktif')
        <span class="badge badge-success">Aktif</span>
    @elseif ($item->status_kerja == 'Tidak Aktif')
        <span class="badge badge-danger">Tidak Aktif</span>
    @endif
</td> -->

<td>@if ($item->tanggal_resign != null)
{{ Carbon::parse($item->tanggal_resign)->format('d-m-Y') }}
@endif

</td>

                                        <td>{{ $item->created_at }}</td>
                                        <td>{{ $item->created_by }}</td>
                                        <td>{{ $item->updated_at }}</td>
                                        <td>{{ $item->updated_by }}</td>
                                        <td>
                                            <a href="{{ route('showkaryawan', $item->id) }}">
                                                <button type="button" class="btn btn-rounded btn-icon" data-toggle="tooltip" title="Ubah">
                                                    <i class="ti-pencil text-warning" style="font-weight: bold;"></i>
                                                </button>
                                            </a>
                                            <form id="deleteForm-{{ $item->id }}" method="POST" action="{{ route('deletekaryawan', $item->id) }}">
                                                @csrf
                                                <input name="_method" type="hidden" value="DELETE">
                                                <button type="button" class="btn btn-rounded btn-icon delete-btn" data-id="{{ $item->id }}" data-toggle="modal" data-target="#confirmModal" title="Hapus">
                                                    <i class="ti-trash text-danger" style="font-weight: bold;"></i>
                                                </button>
                                            </form>


                                            <a href="javascript:void(0)">
    <button type="button" class="btn btn-rounded btn-icon resign-btn" data-id="{{ $item->id }}" data-toggle="tooltip" title="Non Aktifkan Karyawan">
        <i class="ti-close text-info" style="font-weight: bold;"></i>
    </button>
</a>

<div class="modal fade" id="resignModal" tabindex="-1" role="dialog" aria-labelledby="resignModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resignModalLabel">Non Aktifkan Karyawan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="resignForm" method="POST" action="{{ route('nonaktifkaryawan') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="karyawan_id" id="karyawanId">
                    <div class="form-group">
                        <label for="tanggalResign">Tanggal Resign</label>
                        <input type="date" class="form-control" id="tanggalResign" name="tanggal_resign" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Konfirmasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

                                        </td>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <!-- partial:partials/_footer.html -->
    <!-- partial -->
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

<!-- Loading Screen -->
<div id="loading-screen" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.8); z-index:9999; text-align:center; padding-top:200px;">
    <div class="spinner-border text-primary" role="status">
        <span class="sr-only">Loading...</span>
    </div>
    <h2>Loading...</h2>
</div>
<script>
    document.querySelectorAll('.resign-btn').forEach(button => {
    button.addEventListener('click', function() {
        const karyawanId = this.getAttribute('data-id');
        document.getElementById('karyawanId').value = karyawanId;
        $('#resignModal').modal('show');
    });
});

</script>
<script>
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.getAttribute('data-id');
            document.getElementById('confirmDelete').setAttribute('data-id', itemId);
        });
    });

    document.getElementById('confirmDelete').addEventListener('click', function() {
        const itemId = this.getAttribute('data-id');
        document.getElementById('deleteForm-' + itemId).submit();
    });

    document.getElementById('download-template-link').addEventListener('click', function(event) {
        document.getElementById('loading-screen').style.display = 'block';
        setTimeout(function() {
            document.getElementById('loading-screen').style.display = 'none';
        }, 3000); // Adjust the timeout value as needed
    });

    document.getElementById('upload-form').addEventListener('submit', function() {
        document.getElementById('loading-screen').style.display = 'block';
    });

    // Hide the loading screen after form submission completes
    window.addEventListener('load', function() {
        if (document.getElementById('loading-screen').style.display === 'block') {
            document.getElementById('loading-screen').style.display = 'none';
        }
    });

    var itemsPerPage = 10; // Ubah nilai ini sesuai dengan jumlah item per halaman
    var currentPage = 1;
    var filteredData = [];
    
    function initializeData() {
        var tableRows = document.querySelectorAll("table tbody tr");
        filteredData = Array.from(tableRows); // Konversi NodeList ke array
        updatePagination();
    }

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

.dataTables_paginate{float:right;text-align:right;padding-top:.25em}
.paginate_button {box-sizing:border-box;
    display:inline-block;
    min-width:1.5em;
    padding:.5em 1em;
    margin-left:2px;
    text-align:center;
    text-decoration:none !important;
    cursor:pointer;color:inherit !important;
    border:1px solid transparent;
    border-radius:2px;
    background:transparent}

.dataTables_length{float:left}.dataTables_wrapper .dataTables_length select{border:1px solid #aaa;border-radius:3px;padding:5px;background-color:transparent;color:inherit;padding:4px}
.dataTables_info{clear:both;float:left;padding-top:.755em}    
.dataTables_filter{float:right;text-align:right}
.dataTables_filter input{border:1px solid #aaa;border-radius:3px;padding:5px;background-color:transparent;color:inherit;margin-left:3px}


.btn-active {
    background-color: #007bff;
    color: #fff;
}

/* Styling for paginasi container */
.dataTables_paginate {
        text-align: center;
    }

    /* Styling for each paginasi button */
 
        /* Styling for paginasi container */
    .dataTables_paginate {
        text-align: center;
    }

    /* Styling for each paginasi button */
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
