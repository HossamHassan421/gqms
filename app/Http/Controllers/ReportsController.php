<?php

namespace App\Http\Controllers;

use App\Enums\UserTypes;
use App\User;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    // Index Desks Reports
    public function desksIndex(Request $request)
    {
        $data['allUsers'] = User::where('type', UserTypes::$typesReverse['Desk'])->where('status', 1)
            ->groupBy('name')
            ->get();
        
        $data['date'] = null;
        $data['all'] = null;

        // Build the query
        $usersQuery = User::where('type', UserTypes::$typesReverse['Desk'])->where('status', 1);

        // Filter by specific user if requested
        if ($request->has('user') && $request->user) {
            $selectedUser = User::getBy('uuid', $request->get('user'));
            if ($selectedUser) {
                $usersQuery->where('id', $selectedUser->id);
            }
        }

        // Handle date filtering and "Only Performed" option
        if ($request->has('date') && $request->date != null) {
            $data['date'] = $request->date;
            $data['all'] = null;

            // If "Only Performed" is selected, filter users who have desk queue statuses on that date
            if ($request->has('show') && $request->show == 0) {
                $usersQuery->whereHas('deskQueueStatuses', function($query) use ($request) {
                    $query->whereDate('created_at', $request->date);
                });
            }
        } else {
            $data['date'] = null;
            $data['all'] = 1;

            // If "Only Performed" is selected without date, filter users who have any desk queue statuses
            if ($request->has('show') && $request->show == 0) {
                $usersQuery->whereHas('deskQueueStatuses');
            }
        }

        // Eager load relationships to reduce queries
        $usersQuery->with('desk');

        // Group by name and paginate
        $data['users'] = $usersQuery->groupBy('name')->paginate(50);

        // Store User Action Log
        storeLogUserAction(\App\Enums\LogUserActions::$name['IndexDeskReport'], 'Get', route('reports.desks.index'));

        return view('reports.desks.index', $data);
    }

    // Index Doctors Reports
    public function doctorsIndex(Request $request)
    {
        $data['allUsers'] = User::where('type', UserTypes::$typesReverse['Doctor'])->where('status', 1)
            ->groupBy('name')
            ->get();
        
        $data['date_from'] = null;
        $data['date_to'] = null;
        $data['all'] = null;

        // Build the query
        $usersQuery = User::where('type', UserTypes::$typesReverse['Doctor'])->where('status', 1);

        // Filter by specific user if requested
        if ($request->has('user') && $request->user) {
            $selectedUser = User::getBy('uuid', $request->get('user'));
            if ($selectedUser) {
                $usersQuery->where('id', $selectedUser->id);
            }
        }

        // Handle date filtering and "Only Performed" option
        if ($request->has('date_from') && $request->date_from != null) {
            $data['date_from'] = $request->date_from;
            $data['date_to'] = $request->date_to;
            $data['all'] = null;

            // If "Only Performed" is selected, filter users who have room queue statuses in the date range
            if ($request->has('show') && $request->show == 0) {
                $usersQuery->whereHas('roomQueueStatuses', function($query) use ($request) {
                    $query->whereBetween('created_at', [
                        $request->date_from . ' 00:00:00',
                        $request->date_to . ' 23:59:59'
                    ]);
                });
            }
        } else {
            $data['date_from'] = null;
            $data['date_to'] = null;
            $data['all'] = 1;

            // If "Only Performed" is selected without date, filter users who have any room queue statuses
            if ($request->has('show') && $request->show == 0) {
                $usersQuery->whereHas('roomQueueStatuses');
            }
        }

        // Eager load relationships to reduce queries
        $usersQuery->with(['doctor.speciality', 'room']);

        // Group by name and paginate
        $data['users'] = $usersQuery->groupBy('name')->paginate(50);

        // Store User Action Log
        storeLogUserAction(\App\Enums\LogUserActions::$name['IndexDoctorReport'], 'Get', route('reports.doctors.index'));

        return view('reports.doctors.index', $data);
    }

    // Index Patient Waiting Time Reports
    public function patientWaitingTimeIndex(Request $request)
    {
        // Validate date range if provided
        if ($request->has('date_from') && $request->has('date_to') && $request->date_from && $request->date_to) {
            $dateFrom = \Carbon\Carbon::parse($request->date_from);
            $dateTo = \Carbon\Carbon::parse($request->date_to);
            
            if ($dateFrom->diffInDays($dateTo) > 7) {
                return redirect()->back()->withErrors(['date_range' => 'Date range cannot exceed 7 days.']);
            }
        }

        // Get all specialities for filter dropdown
        $data['allSpecialities'] = \App\Speciality::orderBy('name_en', 'ASC')->get();
        
        // Get all doctors for filter dropdown
        $data['allDoctors'] = \App\Doctor::where('workstatus', 1)->orderBy('name_en', 'ASC')->get();
        
        // Initialize filter data
        $data['date_from'] = null;
        $data['date_to'] = null;
        $data['patient_name'] = null;
        $data['patient_phone'] = null;
        $data['selected_speciality'] = null;
        $data['selected_doctor'] = null;

        // Build the query
        $roomQueuesQuery = \App\RoomQueue::query()
            ->with([
                'reservation.patient',
                'reservation.doctor.speciality',
                'roomQueueStatusHistories.queueStatus'
            ])
            ->whereHas('reservation', function($query) {
                $query->where('cashier_flag', 1);
            });

        // Apply date range filter (default to last 7 days)
        if ($request->has('date_from') && $request->date_from != null && $request->has('date_to') && $request->date_to != null) {
            $data['date_from'] = $request->date_from;
            $data['date_to'] = $request->date_to;
            
            $roomQueuesQuery->whereHas('reservation', function($query) use ($request) {
                $query->whereBetween('reservation_date_time', [
                    $request->date_from . ' 00:00:00',
                    $request->date_to . ' 23:59:59'
                ]);
            });
        } else {
            // Default to 1 day (today)
            $data['date_from'] = \Carbon\Carbon::now()->format('Y-m-d');
            $data['date_to'] = \Carbon\Carbon::now()->format('Y-m-d');
            
            $roomQueuesQuery->whereHas('reservation', function($query) use ($data) {
                $query->whereBetween('reservation_date_time', [
                    $data['date_from'] . ' 00:00:00',
                    $data['date_to'] . ' 23:59:59'
                ]);
            });
        }

        // Filter by patient name
        if ($request->has('patient_name') && $request->patient_name) {
            $data['patient_name'] = $request->patient_name;
            $roomQueuesQuery->whereHas('reservation.patient', function($query) use ($request) {
                $query->where('name_en', 'like', '%' . $request->patient_name . '%')
                      ->orWhere('name_ar', 'like', '%' . $request->patient_name . '%');
            });
        }

        // Filter by patient phone
        if ($request->has('patient_phone') && $request->patient_phone) {
            $data['patient_phone'] = $request->patient_phone;
            $roomQueuesQuery->whereHas('reservation.patient', function($query) use ($request) {
                $query->where('phone', 'like', '%' . $request->patient_phone . '%');
            });
        }

        // Filter by speciality
        if ($request->has('speciality') && $request->speciality) {
            $selectedSpeciality = \App\Speciality::where('uuid', $request->speciality)->first();
            if ($selectedSpeciality) {
                $data['selected_speciality'] = $selectedSpeciality;
                $roomQueuesQuery->whereHas('reservation', function($query) use ($selectedSpeciality) {
                    $query->where('speciality_id', $selectedSpeciality->source_speciality_id);
                });
            }
        }

        // Filter by doctor
        if ($request->has('doctor') && $request->doctor) {
            $selectedDoctor = \App\Doctor::where('uuid', $request->doctor)->first();
            if ($selectedDoctor) {
                $data['selected_doctor'] = $selectedDoctor;
                $roomQueuesQuery->where('doctor_id', $selectedDoctor->source_doctor_id);
            }
        }

        // Order by creation date (payment time)
        $roomQueuesQuery->orderBy('created_at', 'DESC');

        // Get the results with pagination
        $roomQueues = $roomQueuesQuery->paginate(50);

        // Pre-load all room queue statuses for current page to avoid N+1 queries
        $roomQueueIds = $roomQueues->pluck('id')->toArray();
        
        // Fetch all statuses at once and group by room_queue_id and status type
        $allStatuses = \App\RoomQueueStatus::whereIn('room_queue_id', $roomQueueIds)
            ->whereIn('queue_status_id', [
                config('vars.queue_statuses.called'),
                config('vars.queue_statuses.skipped'),
                config('vars.queue_statuses.patient_in'),
                config('vars.queue_statuses.patient_out')
            ])
            ->orderBy('created_at', 'ASC')
            ->get()
            ->groupBy('room_queue_id');

        // Pre-calculate all timestamps and durations
        $processedData = [];
        $totalWaitingSeconds = 0;
        $totalVisitSeconds = 0;
        $waitingCount = 0;
        $visitCount = 0;

        foreach ($roomQueues as $roomQueue) {
            $statuses = $allStatuses->get($roomQueue->id, collect());
            
            // Extract timestamps
            $callTime = $statuses->where('queue_status_id', config('vars.queue_statuses.called'))->first();
            $skipTime = $statuses->where('queue_status_id', config('vars.queue_statuses.skipped'))->first();
            $checkInTime = $statuses->where('queue_status_id', config('vars.queue_statuses.patient_in'))->first();
            $checkOutTime = $statuses->where('queue_status_id', config('vars.queue_statuses.patient_out'))->first();
            
            // Calculate durations
            $waitingDuration = null;
            $visitDuration = null;
            
            $endTime = $skipTime ? $skipTime->created_at : ($checkInTime ? $checkInTime->created_at : null);
            if ($endTime) {
                $diffInSeconds = $roomQueue->created_at->diffInSeconds($endTime);
                $waitingDuration = gmdate('H:i:s', $diffInSeconds);
                $totalWaitingSeconds += $diffInSeconds;
                $waitingCount++;
            }
            
            if ($checkInTime && $checkOutTime) {
                $diffInSeconds = $checkInTime->created_at->diffInSeconds($checkOutTime->created_at);
                $visitDuration = gmdate('H:i:s', $diffInSeconds);
                $totalVisitSeconds += $diffInSeconds;
                $visitCount++;
            }
            
            $processedData[$roomQueue->id] = [
                'call_time' => $callTime ? $callTime->created_at : null,
                'skip_time' => $skipTime ? $skipTime->created_at : null,
                'check_in_time' => $checkInTime ? $checkInTime->created_at : null,
                'check_out_time' => $checkOutTime ? $checkOutTime->created_at : null,
                'waiting_duration' => $waitingDuration,
                'visit_duration' => $visitDuration,
            ];
        }

        // Calculate averages
        $data['averageWaitingTime'] = $waitingCount > 0 ? gmdate('H:i:s', $totalWaitingSeconds / $waitingCount) : 'N/A';
        $data['averageVisitDuration'] = $visitCount > 0 ? gmdate('H:i:s', $totalVisitSeconds / $visitCount) : 'N/A';
        $data['processedData'] = $processedData;
        $data['roomQueues'] = $roomQueues;

        // Store User Action Log
        storeLogUserAction(\App\Enums\LogUserActions::$name['IndexPatientWaitingTimeReport'] ?? 'Index Patient Waiting Time Report', 'Get', route('reports.patient-waiting-time.index'));

        return view('reports.patient-waiting-time.index', $data);
    }
}
