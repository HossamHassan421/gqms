<form method="get" action="{{ route('reports.patient-waiting-time.index') }}" enctype="multipart/form-data">
    @csrf

    @if ($errors->has('date_range'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>Error!</strong> {{ $errors->first('date_range') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label>Patient Name</label>
                <input type="text" id="patient_name" autocomplete="off" class="form-control" 
                       name="patient_name" placeholder="Enter patient name" 
                       value="{{ request()->get('patient_name') }}" />
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label>Patient Phone</label>
                <input type="text" id="patient_phone" autocomplete="off" class="form-control" 
                       name="patient_phone" placeholder="Enter phone number" 
                       value="{{ request()->get('patient_phone') }}" />
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label>Clinic Specialty</label>
                <select name="speciality" id="speciality" class="select2" data-placeholder="Choose ..." tabindex="-1" aria-hidden="true">
                    <option value="">All Specialties</option>
                    @foreach($allSpecialities as $speciality)
                        <option @if(request()->get('speciality') == $speciality->uuid) selected @endif value="{{ $speciality->uuid }}">
                            @if(lang() == 'ar') {{ $speciality->name_ar }} @else {{ $speciality->name_en }} @endif
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label>Doctor Name</label>
                <select name="doctor" id="doctor" class="select2" data-placeholder="Choose ..." tabindex="-1" aria-hidden="true">
                    <option value="">All Doctors</option>
                    @foreach($allDoctors as $doctor)
                        <option @if(request()->get('doctor') == $doctor->uuid) selected @endif value="{{ $doctor->uuid }}">
                            @if(lang() == 'ar') {{ $doctor->name_ar }} @else {{ $doctor->name_en }} @endif
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label class="" for="date_from">Date from</label>
                <input type="date" id="date_from" autocomplete="off" class="form-control" 
                       name="date_from" value="{{ $date_from ?? request()->get('date_from') }}" />
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label class="" for="date_to">Date to</label>
                <input type="date" id="date_to" autocomplete="off" class="form-control" 
                       name="date_to" value="{{ $date_to ?? request()->get('date_to') }}" />
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label>&nbsp;</label>
                <div>
                    <button type="submit" class="btn btn-success waves-effect waves-light">
                        <i class="fa fa-fw fa-search"></i> Search
                    </button>
                    <a class="btn btn-secondary waves-effect waves-light" href="{{ route('reports.patient-waiting-time.index') }}">
                        <i class="fa fa-fw fa-refresh"></i> Reset
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <p class="text-muted"><small><i class="fa fa-info-circle"></i> Maximum date range is 7 days. Default is today.</small></p>
            <p class="text-danger" id="date-range-error" style="display: none;"><small><i class="fa fa-exclamation-circle"></i> <strong>Error:</strong> Date range cannot exceed 7 days.</small></p>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');
    const errorMessage = document.getElementById('date-range-error');
    const submitButton = document.querySelector('form button[type="submit"]');

    function validateDateRange() {
        if (dateFromInput.value && dateToInput.value) {
            const dateFrom = new Date(dateFromInput.value);
            const dateTo = new Date(dateToInput.value);
            
            // Calculate difference in days
            const diffTime = Math.abs(dateTo - dateFrom);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (diffDays > 7) {
                errorMessage.style.display = 'block';
                submitButton.disabled = true;
                submitButton.classList.add('disabled');
                return false;
            } else {
                errorMessage.style.display = 'none';
                submitButton.disabled = false;
                submitButton.classList.remove('disabled');
                return true;
            }
        }
        errorMessage.style.display = 'none';
        submitButton.disabled = false;
        submitButton.classList.remove('disabled');
        return true;
    }

    // Validate on date change
    dateFromInput.addEventListener('change', validateDateRange);
    dateToInput.addEventListener('change', validateDateRange);

    // Prevent form submission if validation fails
    document.querySelector('form').addEventListener('submit', function(e) {
        if (!validateDateRange()) {
            e.preventDefault();
            return false;
        }
    });
});
</script>
