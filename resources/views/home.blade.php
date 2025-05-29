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
@endsection
