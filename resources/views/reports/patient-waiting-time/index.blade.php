@extends('_layouts.dashboard')

@section('title') Patient Waiting Time Report @endsection

@section('post_css')
    <style>
        #datatable-history-buttons_wrapper {
            padding: 0;
        }
        .summary-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .summary-item {
            display: inline-block;
            margin-right: 30px;
        }
        .summary-label {
            font-weight: bold;
            color: #495057;
        }
        .summary-value {
            color: #007bff;
            font-size: 1.2em;
            margin-left: 10px;
        }
    </style>
@endsection

@section('content')

    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a class="btn btn-danger waves-effect waves-light"
                   href="{{ route('reports.patient-waiting-time.index') }}">Clear Filters <i class="fa fa-fw fa-close"></i></a>
            </div>

            <h4 class="page-title">Patient Waiting Time Report</h4>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">{{ config('app.name') }}</a></li>
                <li class="breadcrumb-item"><a href="#">Reports</a></li>
                <li class="breadcrumb-item active">Patient Waiting Time</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card-box">
                <h4 class="m-t-0 header-title">Search and Filter</h4>
                <p class="text-muted font-14 m-b-30">
                    Filter the report by patient name, phone, specialty, doctor, or date range.
                </p>

                @include('reports.patient-waiting-time._search')
            </div>
        </div>
        <!-- end card-box -->
    </div>

    <!-- Summary Section -->
    <div class="row">
        <div class="col-lg-12">
            <div class="summary-box">
                <h5 class="m-b-15">Report Summary</h5>
                <div class="summary-item">
                    <span class="summary-label">Total Records:</span>
                    <span class="summary-value">{{ $roomQueues->total() }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Average Waiting Time:</span>
                    <span class="summary-value">{{ $averageWaitingTime }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Average Visit Duration:</span>
                    <span class="summary-value">{{ $averageVisitDuration }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row" id="goToAll">
        <div class="col-lg-12">
            <div class="card-box table-responsive">
                <h4 class="m-t-0 header-title">Patient Waiting Time Details</h4>
                <p class="text-muted font-14 m-b-30">
                    Detailed report of patient journey from payment to checkout.
                </p>

                <table data-page-length='50' id="datatable-history-buttons"
                       class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th>Patient Phone</th>
                        <th>Doctor Name</th>
                        <th>Doctor Specialty</th>
                        <th>Payment Time</th>
                        <th>Call Time</th>
                        <th>Skip Time</th>
                        <th>Check-in Time</th>
                        <th>Check-out Time</th>
                        <th>Waiting Duration</th>
                        <th>Visit Duration</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($roomQueues as $roomQueue)
                        @php
                            $reservation = $roomQueue->reservation;
                            $patient = $reservation ? $reservation->patient : null;
                            $doctor = $reservation ? $reservation->doctor : null;
                            $speciality = $doctor ? $doctor->speciality : null;
                            
                            $paymentTime = $roomQueue->created_at;
                            $callTime = getPatientCallTime($roomQueue);
                            $skipTime = getPatientFirstSkipTime($roomQueue);
                            $checkInTime = getPatientCheckInTime($roomQueue);
                            $checkOutTime = getPatientCheckOutTime($roomQueue);
                            $waitingDuration = calculateWaitingDuration($roomQueue);
                            $visitDuration = calculateVisitDuration($roomQueue);
                        @endphp
                        <tr>
                            <td>
                                @if($patient)
                                    @if(lang() == 'ar') {{ $patient->name_ar ?? '-' }} @else {{ $patient->name_en ?? '-' }} @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $patient ? $patient->phone ?? '-' : '-' }}</td>
                            <td>
                                @if($doctor)
                                    @if(lang() == 'ar') {{ $doctor->name_ar ?? '-' }} @else {{ $doctor->name_en ?? '-' }} @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($speciality)
                                    @if(lang() == 'ar') {{ $speciality->name_ar ?? '-' }} @else {{ $speciality->name_en ?? '-' }} @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $paymentTime ? $paymentTime->format('Y-m-d H:i:s') : '-' }}</td>
                            <td>{{ $callTime ? $callTime->format('Y-m-d H:i:s') : '-' }}</td>
                            <td>{{ $skipTime ? $skipTime->format('Y-m-d H:i:s') : '-' }}</td>
                            <td>{{ $checkInTime ? $checkInTime->format('Y-m-d H:i:s') : '-' }}</td>
                            <td>{{ $checkOutTime ? $checkOutTime->format('Y-m-d H:i:s') : '-' }}</td>
                            <td>{{ $waitingDuration ?? 'N/A' }}</td>
                            <td>{{ $visitDuration ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            @if ($roomQueues instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="clearfix">
                    <div class="float-left">Page {{ $roomQueues->currentPage() }} of {{ $roomQueues->lastPage() }}</div>
                    <div class="float-right">{{ $roomQueues->appends(request()->query())->links() }}</div>
                </div>
            @endif
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        var tableDTUsers = $('#datatable-history-buttons').DataTable({
            lengthChange: false,
            searching: false,
            paging: false,
            info: false,
            buttons: [
                {
                    extend: 'copyHtml5',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                    }
                },
                {
                    extend: 'excelHtml5',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                    }
                },
                {
                    extend: 'pdfHtml5',
                    orientation: 'landscape',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                    }
                },
                {
                    extend: 'print',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                    }
                }
            ],
        });
        tableDTUsers.buttons().container().appendTo('#datatable-history-buttons_wrapper .col-md-6:eq(0)');
    </script>
@endsection
