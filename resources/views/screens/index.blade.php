@extends('_layouts.dashboard')

@section('title') Screens @endsection

@section('content')

    <!-- Page-Title -->
    <div class="row">
        <div class="col-sm-12">

            <h4 class="page-title">Screens</h4>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">{{ config('app.name') }}</a></li>
                <li class="breadcrumb-item"><a href="#">Screens</a></li>
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
                    <h4 class="m-t-0 header-title">Create new screen</h4>
                    <p class="text-muted font-14 m-b-30">
                        Create new resource from here.
                    </p>

                    @include('screens.create')
                </div>
            </div>
        </div>
        <!-- end card-box -->
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card-box table-responsive">
                <h4 class="m-t-0 header-title">All Screens</h4>
                <p class="text-muted font-14 m-b-30">
                    Here you will find all the resources to make actions on them.
                </p>

                <table id="datatable" class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Name ar</th>
                            <th>Name en</th>
                            <th>Screen IP</th>
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
                        @foreach($screens as $screen)
                            <tr>
                                <td>{{ $screen->id }}</td>
                                <td>{{ $screen->name_ar }}</td>
                                <td>{{ $screen->name_en }}</td>
                                <td>{{ $screen->ip }}</td>
                                <td>{{ $screen->floor->name_en }}</td>
                                <td>{{ App\Enums\ScreenStatuses::$statuses[$screen->status]['en'] }}</td>
                                <td>{{ $screen->createdBy->name }}</td>
{{--                                <td>{{ $screen->updatedBy->name }}</td>--}}
                                <td>{{ $screen->created_at }}</td>
{{--                                <td>{{ $screen->updated_at }}</td>--}}
                                <td>
                                    <a href="{{ route('screens.show', [$screen->slug]) }}" target="_blank" class="btn btn-sm btn-primary">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="{{ route('screens.edit', [$screen->uuid]) }}" class="update-modal btn btn-sm btn-success">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <a href="{{ route('screens.destroy', [$screen->uuid]) }}" class="confirm-delete btn btn-sm btn-danger">
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

@section('scripts')
    <script>
        $(document).ready(function () {
            $('body').on('change', '#type', function () {
                var type = $(this).val();
               if (type == {{ config('vars.screen_types.kiosk') }}){
                    $('#floor-div').show(0);
                    $('#rooms-div').hide(0);
               }else if (type == {{ config('vars.screen_types.reception') }}) {
                   $('#floor-div').hide(0);
                   $('#rooms-div').show(0);
               }
            });
        });
    </script>
@endsection