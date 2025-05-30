@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-xl">
            <div class="card">
                <div class="card-header text-center fw-bold">{{ __('Latest data received') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    
                    <!-- Search Form -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <input type="text" name="search" class="form-control" placeholder="Search by extension or number" value="{{ request('search') }}">
                        </div>
                    </div>

                    <div class="row">
                        <table class="table table-hover table-responsive table-striped">
                        <tr>
                            <th>Direction</th>
                            <th>Extension</th>
                            <th>Initiator</th>
                            <th>Date Time</th>
                            <th>Duration</th>
                            <th>Destination</th>
                            <th>External Number</th>
                        </tr>

                        @if(isset($records))
                        @foreach ($records as  $r )
                            <tr>
                                <td>{{ $r->getCallDirection()}}</td>
                                <td>{{ $r->extension }}</td>
                                <td>{{ $r->initiator }} </td>
                                <td>{{ $r->datetime }}</td>
                                <td>{{ $r->getDuration() }}</td>
                                <td>{{ $r->destination_number }}</td>
                                <td>{{ $r->external_number }} </td>
                            </tr>                               
                        @endforeach
                        @endif
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    

function getData() {
    if (document.querySelector('input[name="search"]').value.trim() !== '') {
        searchData(); // If there's a search term, use the search function
        return;
    }
    fetch('api/latest-voip-records')
        .then(response => response.json())
        .then(data => {
            addRows(data);
        })
        .catch(error => console.error('Error fetching VoIP records:', error));
}
getData(); // Initial call to populate the table on page load
setInterval(getData, 5000); // Update every 5 seconds

document.addEventListener('change', function(event) {
    if (event.target.name === 'search') {
        searchData();
    }
});
document.addEventListener('keyup', function(event) {
    if (event.target.name === 'search') {
        searchData();
    }
});

function searchData() {
    const searchInput = document.querySelector('input[name="search"]');
    const searchValue = searchInput.value.trim();
    fetch(`api/filter-voip-records?search=${encodeURIComponent(searchValue)}`)
        .then(response => response.json())
        .then(data => {
            addRows(data);
        })
        .catch(error => console.error('Error searching VoIP records:', error));
}

function addRows (data) {
    const tableBody = document.querySelector('table tbody');
    //clear all rows except the header
    for( let i = tableBody.rows.length - 1; i > 0; i--) {
        tableBody.deleteRow(i);
    }
    data.records.forEach(record => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${record.call_direction}</td>
            <td>${record.extension}</td>
            <td>${record.initiator}</td>
            <td>${record.datetime}</td>
            <td>${record.duration}</td>
            <td>${record.destination}</td>
            <td>${record.external_number}</td>
        `;
        tableBody.appendChild(row);
    });
}
</script>
@endsection
