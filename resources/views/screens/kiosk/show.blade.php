@extends('_layouts.screen')

@section('title') {{ $screen->name_en }} @endsection

@section('head')
    <link href="{{ url('assets/plugins/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css"/>

    <style>
        .select2-container .select2-selection--single {
            height: 80px;
            font-size: 30px;
            font-weight: bold;
            color: #035B88;
        }

        .select2-container .select2-selection--single .select2-selection__rendered {
            font-weight: bold;
            padding: 20px 0;
            color: #035B88;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 80px;
            color: #035B88;
        }
    </style>
@endsection

@section('content')

    <div class="text-center pt-2 pb-2 mb-2" style="direction: rtl !important;background-color: #eeeeee">
        @foreach($arabic_alphas as $arabic_alpha)
            <div style="min-width: 40px;font-size: 30px;font-weight: bold;padding: 3px 3px;display: inline-block;background-color: #0a6aa1;color: #ffffff;cursor: pointer">{{ $arabic_alpha }}</div>
        @endforeach
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 text-center">
                <div style="background-color: #eeeeee;padding: 10px;">
                    <div style="display: inline-block;">
                        @foreach($screen->floors as $floor)
                            <div class="mb-4 floors-item" id="{{ $floor->uuid }}">
                                <div class="qn-{{$floor->uuid}} return-screen-qn">-</div>
                                <button @click.prevent="printQueue('{{$floor->uuid}}')" class="btn-print">
                                    <span style="background-color: rgba(64, 85, 102, .7);padding: 0 10px">{{ $floor->name_en }}</span>
                                    <span> اضغط هنا لطباعة حجز</span>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-md-6" style="height: 100%;">
                <div class="" style="background-color: #eeeeee;overflow: auto;font-weight: bold;text-transform: capitalize;font-size: 20px">
                    @foreach($doctors as $doctor)
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <div style="background-color: #ffffff;padding: 5px;margin: 5px;">{{ 'f1' }}</div>
                            </div>
                            <div class="col-md-9">
                                <div style="background-color: #ffffff;padding: 5px;margin: 5px;text-align: right;">{{ $doctor->name_ar }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{--<div class="kiosk-screen">--}}
    {{--<div class="kiosk-screen-in">--}}
    {{--<div class="mb-4">--}}
    {{--<select class="select2" style="width: 100%;" id="select2">--}}
    {{--<option selected disabled value="">ابحث عن الدكتور</option>--}}
    {{--@foreach($doctors as $doctor)--}}
    {{--<option value="{{ $doctor->uuid }}">د / {{ $doctor->name_ar }}</option>--}}
    {{--@endforeach--}}
    {{--<option v-for="doctor in doctors" :value="doctor.uuid">@{{ doctor.name_ar }}</option>--}}
    {{--</select>--}}
    {{--</div>--}}
    {{--@foreach($screen->floors as $floor)--}}
    {{--<div class="mb-4 floors-item" id="{{ $floor->uuid }}">--}}
    {{--<div class="qn-{{$floor->uuid}} return-screen-qn">-</div>--}}
    {{--<button @click.prevent="printQueue('{{$floor->uuid}}')" class="btn-print">--}}
    {{--<span style="background-color: rgba(64, 85, 102, .7);padding: 0 10px">{{ $floor->name_en }}</span>--}}
    {{--<span> اضغط هنا لطباعة حجز</span>--}}
    {{--</button>--}}
    {{--</div>--}}
    {{--@endforeach--}}
    {{--</div>--}}
    {{--</div>--}}

@endsection

@section('scripts')
    <script>
        $('.kiosk-screen').height($(window).height() - 93);

        const app = new Vue({
            el: '#app',
            data: {},
            methods: {
                printQueue(floor_uuid) {
                    addLoader();
                    var url = '{{ url('/') }}/queues/{{$screen->uuid}}/' + floor_uuid;
                    axios.post(url, {
                        _token: '{{ csrf_token() }}'
                    })
                        .then((response) => {
                            console.log(response);
                            $('.qn-' + floor_uuid).text(response.data.queue_number);
                            removeLoarder();
                        })
                        .catch((response) => {
                            console.log(0, response.data);
                            removeLoarder();
                        });

                    $('.floors-item').show();
                    $('#select2').val('').select2();
                },
                getFloors(doctor) {
                    addLoader();
                    var url = '{{ url('doctors/get/floors') }}/' + doctor;

                    axios.get(url)
                        .then((response) => {
                            console.log(response);
                            $('.floors-item').hide();
                            $('#' + response.data.floor).show();
                            removeLoarder();
                        })
                        .catch((response) => {
                            console.log(0, response.data);
                            removeLoarder();
                        });
                }
            },
            mounted() {
            }
        });

        $('#select2').on('change', function () {
            var select2Val = $(this).val();
            app.getFloors(select2Val);
        });
    </script>


    <script src="{{ url('assets/plugins/select2/js/select2.min.js') }}" type="text/javascript"></script>

    <script>
        // Select2
        $(".select2").select2();
        // $("#select2").select2({ dropdownCssClass: "select2CssClass" });
    </script>
@endsection