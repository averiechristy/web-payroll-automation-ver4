@extends('layouts.app')
@section('content')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="row">
                    <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                        <h3 class="font-weight-bold">Libur</h3>
                    </div>
                </div>
                <div class="card shadow mb-4">
                    <div class="py-3">
                        <form action="{{ url('/update-holidays') }}" method="GET" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-info ml-3">Update Libur</button>
                        </form>
                    </div>
                    <div class="card-body">
                      <div class="dataTables_filter mb-3">
    <label for="month">Bulan
        <select id="month" name="month" onchange="filterData()" class="form-control custom-select">
            @for ($i = 1; $i <= 12; $i++)
                <option value="{{ $i }}">{{ DateTime::createFromFormat('!m', $i)->format('F') }}</option>
            @endfor
        </select>
    </label>
</div>

<style>
    .custom-select {
        width: 200px; /* Adjust the width as needed */
    }
</style>

                        <div class="dataTables_filter" style="float:right;">
                            <label for="search">Search
                                <input id="search" placeholder="Cari..." class="form-control">
                            </label>
                        </div>
                        <div class="table-responsive">
                            @include('components.alert')
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Jenis Hari</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="holidayTableBody">
                                @foreach ($holiday as $item)
    <tr>
        <td>{{ \Carbon\Carbon::parse($item->date)->format('d-m-Y') }}</td>
        <td>{{ $item->description }}</td>
        <td>
            <a href="{{ route('showholiday', $item->id) }}">
                <button type="button" class="btn btn-rounded btn-icon" data-toggle="tooltip" title="Ubah">
                    <i class="ti-pencil text-warning" style="font-weight: bold;"></i>
                </button>
            </a>
        </td>
    </tr>
@endforeach

                                </tbody>
                            </table>
                        </div>
                        <div class="dataTables_info" id="dataTableInfo" role="status" aria-live="polite">
                            Showing <span id="totalEntries">{{ count($holiday) }}</span> entries
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
<script>
    document.getElementById('confirmDelete').addEventListener('click', function() {
        document.getElementById('deleteForm').submit();
    });

    var holidayData = @json($holiday); // Mengambil data liburan dari PHP ke JavaScript
    var currentMonth = new Date().getMonth() + 1; // Bulan saat ini (1-12)
    var filteredData = holidayData;

    function initializeData() {
        filterData();
    }

    function filterData() {
        var monthSelect = document.getElementById('month');
        var selectedMonth = parseInt(monthSelect.value);
        var searchInput = document.getElementById('search').value.toLowerCase();

        filteredData = holidayData.filter(function(item) {
            var itemMonth = new Date(item.date).getMonth() + 1;
            var itemText = item.date + ' ' + item.description;

            return itemMonth === selectedMonth && itemText.toLowerCase().includes(searchInput);
        });

        updateTable();
    }

    function updateTable() {
        var tableBody = document.getElementById('holidayTableBody');
        tableBody.innerHTML = '';

        filteredData.forEach(function(item) {
            var row = document.createElement('tr');

            var dateCell = document.createElement('td');
        var date = new Date(item.date);
        var day = date.getDate();
        var month = date.toLocaleString('default', { month: 'long' });
        var year = date.getFullYear();
        dateCell.textContent = day + ' ' + month + ' ' + year;
        row.appendChild(dateCell);

            var descriptionCell = document.createElement('td');
            descriptionCell.textContent = item.description;
            row.appendChild(descriptionCell);

            var actionCell = document.createElement('td');
            var editButton = document.createElement('a');
            editButton.href = "{{ url('showholiday') }}/" + item.id;
            editButton.innerHTML = '<button type="button" class="btn btn-rounded btn-icon" data-toggle="tooltip" title="Ubah"><i class="ti-pencil text-warning" style="font-weight: bold;"></i></button>';
            actionCell.appendChild(editButton);
            row.appendChild(actionCell);

            tableBody.appendChild(row);
        });

        document.getElementById('totalEntries').textContent = filteredData.length;
    }

    document.getElementById('search').addEventListener('input', filterData);
    document.getElementById('month').addEventListener('change', filterData);

    initializeData();
</script>

<style>
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
