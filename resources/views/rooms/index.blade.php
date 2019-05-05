@extends('_layouts.dashboard')

@section('title') Rooms @endsection

@section('content')

    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">

            <h4 class="page-title">Room</h4>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">{{ config('app.name') }}</a></li>
                <li class="breadcrumb-item"><a href="#">Rooms</a></li>
                <li class="breadcrumb-item active">Index</li>
            </ol>

        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <ul class="nav nav-tabs navtab-bg nav-justified">
                <li class="nav-item">
                    <a href="#searchResource" data-toggle="tab" aria-expanded="false" class="nav-link active">Search & filter</a>
                </li>
                <li class="nav-item">
                    <a href="#createResource" data-toggle="tab" aria-expanded="true" class="nav-link">Create new</a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="searchResource">
                    <h4 class="header-title m-t-0">Search</h4>
                    <p class="text-muted font-14 m-b-20">
                        Parsley is a javascript form validation library. It helps you provide your users with feedback on their form submission before sending it to your server.
                    </p>
                </div>
                <div class="tab-pane" id="createResource">
                    <h4 class="m-t-0 header-title">Create new Room</h4>
                    <p class="text-muted font-14 m-b-30">
                        Create new resource from here.
                    </p>

                    @include('rooms.create')
                </div>
            </div>
        </div>
        <!-- end card-box -->
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card-box table-responsive">
                <h4 class="m-t-0 header-title">All Rooms</h4>
                <p class="text-muted font-14 m-b-30">
                    Here you will find all the resources to make actions on them.
                </p>

                <table id="datatable" class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Name ar</th>
                            <th>Name en</th>
                            <th>Room IP</th>
                            <th>Floor</th>
                            <th>Status</th>
                            <th>Created by</th>
                            {{--<th>Updated by</th>--}}
                            <th>Created at</th>
                            {{--<th>Updated at</th>--}}
                            <th>Control</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($rooms as $desk)
                            <tr>
                                <td>{{ $desk->id }}</td>
                                <td>{{ $desk->name_ar }}</td>
                                <td>{{ $desk->name_en }}</td>
                                <td>{{ $desk->ip }}</td>
                                <td>{{ $desk->floor->name_en }}</td>
                                <td>{{ App\Enums\RoomStatuses::$statuses[$desk->status]['en'] }}</td>
                                <td>{{ $desk->createdBy->name }}</td>
{{--                                <td>{{ $desk->updatedBy->name }}</td>--}}
                                <td>{{ $desk->created_at }}</td>
{{--                                <td>{{ $desk->updated_at }}</td>--}}
                                <td>
                                    <a href="{{ route('rooms.show', [$desk->uuid]) }}" target="_blank" class="btn btn-sm btn-primary">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="{{ route('rooms.edit', [$desk->uuid]) }}" class="update-modal btn btn-sm btn-success">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a href="{{ route('rooms.destroy', [$desk->uuid]) }}" class="confirm-delete btn btn-sm btn-danger">
                                        <i class="fa fa-times"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- end row -->

@endsection