@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-xl">
            <div class="card">
                <div class="card-header text-center fw-bold">Search and export</div>

                <div class="card-body">
                   <div class="row mb-2">
                        <div class="col-10">
                            <form action="{{route('search')}}" method="post" class="needs-validation" novalidate id="searchForm">
                                @csrf
                                <div class="row">
                                    <div class="col">
                                        <div class="input-group">
                                            <span class="input-group-text" id="extension1">Extension</span>
                                            <input type="text" name="extension" id="extension" class="form-control" required value="{{ $extension ?? "" }}"  aria-describedby="extension1">
                                        </div>
                                        <div class="invalid-feedback">
                                            Please provide a valid extension.
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="input-group">
                                            <span class="input-group-text" id="fromDate1">From date</span>
                                            <input type="date" name="from" id="from" class="form-control" required value="{{ $from ?? "" }}"  aria-describedby="fromDate1">
                                        </div>
                                        <div class="invalid-feedback">
                                            Please provide a valid to date.
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="input-group">
                                            <span class="input-group-text" id="toDate1">To date</span>
                                            <input type="date" name="to" id="to" class="form-control" required value="{{  $to ?? "" }}"  aria-describedby="toDate1">
                                        </div>
                                        <div class="invalid-feedback">
                                            Please provide a valid to date.
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-2">
                            <button class="btn btn-primary" type="submit" form="searchForm">Search</button>
                            @if(isset($records) && count($records) > 0)
                            <button class="btn btn-secondary" type="submit" form="exportForm">Export</button>
                            @endif
                        </div>
                        @if(isset($records) && count($records) > 0)
                        <div class="col-1">
                            <form action="{{route('export')}}" method="post" id="exportForm">
                                @csrf
                                <input type="hidden" name="extension" value="{{ $extension }}">
                                <input type="hidden" name="from" value="{{ $from }}">
                                <input type="hidden" name="to" value="{{ $to }}">
                            </form>
                        </div>
                        @endif
                        
                   </div>


                   <div class="row mb-2">
                        @if(isset($records))
                        <div class="row mb-2">
                        </div>
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
                                    <td>{{ $r->call_direction}}</td>
                                    <td>{{ $r->extension }}</td>
                                    <td>{{ $r->initiator }} </td>
                                    <td>{{ $r->datetime }}</td>
                                    <td>{{ $r->duration }}</td>
                                    <td>{{ $r->destination_number }}</td>
                                    <td>{{ $r->external_number }} </td>
                                </tr>                               
                            @endforeach
                            @endif
                        </table>
                        @endif
                   </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

@endsection