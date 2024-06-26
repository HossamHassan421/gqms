@extends('_layouts.dashboard')

@section('title') Doctors Reports @endsection

@section('post_css')
    <style>
        #datatable-history-buttons_wrapper {
            padding: 0;
        }
    </style>
@endsection

@section('content')

    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right m-t-15">
                <a class="btn btn-danger waves-effect waves-light"
                   href="{{ route('reports.doctors.index') }}">Remove search <i class="fa fa-fw fa-close"></i></a>
            </div>

            <h4 class="page-title">Doctors Reports</h4>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">{{ config('app.name') }}</a></li>
                <li class="breadcrumb-item active">Doctors Reports</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card-box">
                <h4 class="m-t-0 header-title">Search and filter</h4>
                <p class="text-muted font-14 m-b-30">
                    Here you can filter and search.
                </p>

                @include('reports.doctors._search')
            </div>
        </div>
        <!-- end card-box -->
    </div>

    <div class="row" id="goToAll">
        <div class="col-lg-12">
            <div class="card-box table-responsive">
                <h4 class="m-t-0 header-title">All Doctors Reports</h4>
                <p class="text-muted font-14 m-b-30">
                    Here you will find all the login users and rooms.
                </p>

                <table data-page-length='50' id="datatable-history-buttons"
                       class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>User</th>
                        <th>Clinic</th>
                        <th>Room</th>
                        <th>Called</th>
                        <th>Skipped</th>
                        <th>Patient in</th>
                        <th>Patient out</th>
                        <th>Average Patient Time</th>
                        <th>Status</th>
                    </tr>
                    </thead>

                    <tbody>
                    @if(request()->has('show') && request()->show == 0)
                        @foreach($users as $user)
                            @if(\App\RoomQueueStatus::where('user_id', $user->id)->where('created_at', '>=', request()->date_from . ' 00:00:00')
                                ->where('created_at', '<=', request()->date_to ." 23:59:59")->first())
                                <tr>
                                    <td>@if(isset($user->doctor->id)) @if(lang() == 'ar') {{$user->doctor->name_ar}} @else {{$user->doctor->name_en}} @endif @endif</td>
                                    <td>{{ isset($user->doctor->speciality->id) ? lang() == 'ar' ? $user->doctor->speciality->name_ar : $user->doctor->speciality->name_en : '-' }}</td>
                                    <td>{{ ($user->room)? $user->room->name_en : '-' }}</td>
                                    <td>{{ getDoctorReport($user, config('vars.room_queue_status.called'), $all, $date_from, $date_to) }}</td>
                                    <td>{{ getDoctorReport($user, config('vars.room_queue_status.skipped'), $all, $date_from, $date_to) }}</td>
                                    <td>{{ getDoctorReport($user, config('vars.room_queue_status.patient_in'), $all, $date_from, $date_to) }}</td>
                                    <td>{{ getDoctorReport($user, config('vars.room_queue_status.patient_out'), $all, $date_from, $date_to) }}</td>
                                    <td>{{ getPatientAverageTime($user, $date_from, $date_to) }}</td>
                                    <td>{{ (getCurrentDoctorReport($user))? 'Serving queue ' . getCurrentDoctorReport($user)->queue_number : '-' }}</td>
                                </tr>
                            @endif
                        @endforeach
                    @else
                        @foreach($users as $user)
                            <tr>
                                <td>@if(isset($user->doctor->id)) @if(lang() == 'ar') {{$user->doctor->name_ar}} @else {{$user->doctor->name_en}} @endif @endif</td>
                                <td>{{ isset($user->doctor->speciality->id) ? lang() == 'ar' ? $user->doctor->speciality->name_ar : $user->doctor->speciality->name_en : '-' }}</td>
                                <td>{{ ($user->room)? $user->room->name_en : '-' }}</td>
                                <td>{{ getDoctorReport($user, config('vars.room_queue_status.called'), $all, $date_from, $date_to) }}</td>
                                <td>{{ getDoctorReport($user, config('vars.room_queue_status.skipped'), $all, $date_from, $date_to) }}</td>
                                <td>{{ getDoctorReport($user, config('vars.room_queue_status.patient_in'), $all, $date_from, $date_to) }}</td>
                                <td>{{ getDoctorReport($user, config('vars.room_queue_status.patient_out'), $all, $date_from, $date_to) }}</td>
                                <td>{{ getPatientAverageTime($user, $date_from, $date_to) }}</td>
                                <td>{{ (getCurrentDoctorReport($user))? 'Serving queue ' . getCurrentDoctorReport($user)->queue_number : '-' }}</td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>

            @if ($users instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="clearfix">
                    <div class="float-left">Pages numbers</div>
                    <div class="float-right">{{ $users->links() }}</div>
                </div>
            @endif
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        var tableDTUsers = $('#datatable-history-buttons').DataTable({
            lengthChange: false,
            buttons: [
                {
                    extend: 'copyHtml5',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                    }
                },
                {
                    extend: 'excelHtml5',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                    }
                },
                {
                    extend: 'pdfHtml5',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                    }
                },
                {
                    extend: 'print',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                    }
                }
            ],
        });
        tableDTUsers.buttons().container().appendTo('#datatable-history-buttons_wrapper .col-md-6:eq(0)');

    </script>
@endsection
