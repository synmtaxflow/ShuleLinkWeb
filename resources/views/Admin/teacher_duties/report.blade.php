@extends('layouts.vali')

@section('content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Teacher Duties Report</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="#">Dashboard</a></li>
                    <li><a href="#">Duties Book</a></li>
                    <li class="active">Report</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <strong class="card-title">Duties Roster Report</strong>
                        <button onclick="window.print()" class="btn btn-secondary float-right">
                            <i class="fa fa-print"></i> Print Report
                        </button>
                    </div>
                    <div class="card-body">
                        <table id="bootstrap-data-table" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Teacher Name</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Duration</th>
                                    <th>Term / Year</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($duties as $key => $duty)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            @if($duty->teacher)
                                                {{ $duty->teacher->first_name }} {{ $duty->teacher->last_name }}
                                                <br>
                                                <small class="text-muted">{{ $duty->teacher->employee_number }}</small>
                                            @else
                                                <span class="text-danger">Unknown Teacher</span>
                                            @endif
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($duty->start_date)->format('d M Y, l') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($duty->end_date)->format('d M Y, l') }}</td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($duty->start_date)->diffInDays(\Carbon\Carbon::parse($duty->end_date)) + 1 }} Days
                                        </td>
                                        <td>
                                            {{ $duty->term ? $duty->term->term_name : '-' }} 
                                            <small>({{ $duty->academicYear ? $duty->academicYear->year : '-' }})</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .breadcrumbs, .btn-secondary, #left-panel, #header {
            display: none !important;
        }
        .content {
            margin: 0 !important;
            padding: 0 !important;
        }
        .card {
            border: none !important;
        }
    }
</style>
@endsection
