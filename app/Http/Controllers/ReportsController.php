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
}
