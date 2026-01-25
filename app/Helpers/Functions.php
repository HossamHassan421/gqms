<?php

// Get path
function get_path($path){
    return base_path() . '/' . config('vars.public') . '/' . $path;
}

// Get path
function nice_time($time){
    return \Carbon\Carbon::createFromTimeStamp(strtotime($time))->diffForHumans();
}

// Default language
function lang(){
    return app()->getLocale();
}

// Make string format
function str_well($value){
    return ucfirst(str_replace('_', ' ', $value));
}

// Store Log User Login
function storeLogUserLogin($user = null){
    return \App\UserLoginHistory::store($user);
}

// Store Log User Login
function getDeskReport($user, $status, $all = null, $date = null){
    $report = \App\DeskQueueStatus::getDeskQueues($user->id, $status, $all, $date);
    return $report;
}

// Resource translation
function translate($resource, $field){
    if(lang() == 'ar'){
        if($field == 'name'){ $data = $resource->name_ar;}
    }elseif (lang() == 'en'){
        if($field == 'name'){ $data = $resource->name_en;}
    }

    return $data;
}

// Store Log User Login
function getCurrentDeskReport($user){

    $desk = ($user->desk)? $user->desk : null;

    if($desk){
        $report = \App\DeskQueue::where('desk_id', $desk->id)
            ->where('status', config('vars.desk_queue_status.called'))
            ->where('created_at', 'like', "%".date('Y-m-d')."%")
            ->orderBy('id', 'DESC')
            ->first();
    }else{
        $report = null;
    }

    return $report;
}

// Store Log User Login
function getDoctorReport($user, $status, $all = null, $date_from = null, $date_to = null){
    $report = \App\RoomQueueStatus::getRoomQueues($user->id, $status, $all, $date_from, $date_to);
    return $report;
}

// Store Log User Login
function getCurrentDoctorReport($user){
    $room = ($user->room)? $user->room : null;

    if($room){
        $report = \App\RoomQueue::where('room_id', $user->room->id)
            ->where('status', config('vars.room_queue_status.called'))
            ->where('created_at', 'like', "%".date('Y-m-d')."%")
            ->orderBy('id', 'DESC')
            ->first();
    }else{
        $report = null;
    }

    return $report;
}

// Store Log User Actions
function storeLogUserAction($action, $method, $url = null, $user = null){
    $theUrl = (!is_null($url))? $url : '';
    $theUser = (is_null($user))? auth()->user()->id : $user;

    $log = \App\Log::store([
        'user_id' => $theUser,
        'method' => $method,
        'action' => $action,
        'url' => $theUrl,
    ]);

    if ($log){
        $result = true;
    }else{
        $result = false;
    }

    return $result;
}

// Upload files
function upload_file($type, $file, $path){

    $results = [
        'status' => false,
        'filename' => null,
        'mime' => null,
        'message' => null,
    ];

    $extention = strtolower($file->getClientOriginalExtension());

    $validExtentions = [];
    $file_mimes = lookups('file_mimes');

    $results['mime'] = $type . '/' . $extention;

    if ($type == "image") {
        foreach($file_mimes as $file_mime){
            $ext = strtolower(str_replace('image/', '', $file_mime->name));
            $validExtentions[] = $ext;
        }
    }
    elseif ($type == "text") {
        foreach($file_mimes as $file_mime){
            $ext = strtolower(str_replace('text/', '', $file_mime->name));
            $validExtentions[] = $ext;
        }
    }

    if (in_array($extention, $validExtentions)) {

        $filename = time().rand(1000,9999).'.'.$extention;
        $destinationPath = get_path($path);

        $upload = $file->move($destinationPath, $filename);

        if ($upload) {
            // File Uploaded
            $results['status'] = true;
            $results['filename'] = $filename;
            $results['message'] = 'Uploaded Successfully';

            return $results;
        }else{
            // Error Uploading
            $results['message'] = 'Error Uploading';

            return $results;
        }

    }else{
        // File not valid
        $results['message'] = 'File not valid';

        return $results;
    }
}

// Function to get the client IP address
function getenv_get_client_ip() {
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
        $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

// Function to get the client IP address
function server_get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

// Desk Queue Number Format
function deskQueueNumberFormat($area, $scheme){
    $lastNumber = \App\DeskQueue::getDeskQueues($area->id)->count() + 1;

    $lastNumberZeros = '';

    if($lastNumber < $scheme){
        $schemeLength = strlen((string)$scheme);
        $lastNumberLength = strlen((string)$lastNumber);

        $zerosLength = $schemeLength - $lastNumberLength;

        for ($i=0; $i < $zerosLength; $i++) {
            $lastNumberZeros .= 0;
        }

        $lastNumber = $lastNumberZeros . $lastNumber;
    }

//    return $area->floor->name_en . '-' . $area->name_en . '-' . $lastNumber;
    return $area->name_en . '-' . $lastNumber;
}

// Room Queue Number Format
function roomQueueNumberFormat($floor_id, $room_id, $scheme){
    $lastNumber = \App\RoomQueue::getRoomQueues($floor_id, $room_id)->count() + 1;
//    $lastNumber = 101; // Testing

    $lastNumberZeros = '';

    if($lastNumber < $scheme){
        $schemeLength = strlen((string)$scheme);
        $lastNumberLength = strlen((string)$lastNumber);

        $zerosLength = $schemeLength - $lastNumberLength;

        for ($i=0; $i < $zerosLength; $i++) {
            $lastNumberZeros .= 0;
        }

        $lastNumber = $lastNumberZeros . $lastNumber;
    }

    return \App\Floor::getBy('id', $floor_id)->name_en . '-' . \App\Room::getBy('id', $room_id)->name_en .'-' . $lastNumber;
}

function getPatientAverageTime($user, $date_from, $date_to){
    if($date_from == null){
        return '-';
    }

    // Get all patient_in records for the user in the date range
    $patientIns = \App\RoomQueueStatus::where('user_id', $user->id)
        ->where('queue_status_id', config('vars.room_queue_status.patient_in'))
        ->whereBetween('created_at', [
            date($date_from) . ' 00:00:00', 
            date($date_to) . ' 23:59:59'
        ])
        ->get();

    if($patientIns->isEmpty()){
        return '-';
    }

    // Get all corresponding patient_out records in a single query
    $roomQueueIds = $patientIns->pluck('room_queue_id')->toArray();
    $patientOuts = \App\RoomQueueStatus::where('user_id', $user->id)
        ->where('queue_status_id', config('vars.room_queue_status.patient_out'))
        ->whereIn('room_queue_id', $roomQueueIds)
        ->get()
        ->keyBy('room_queue_id');

    $allDiffTime = 0;
    $validCount = 0;

    foreach ($patientIns as $patientIn){
        $patientOut = $patientOuts->get($patientIn->room_queue_id);

        if ($patientOut){
            $diffTime = $patientOut->created_at->diffInSeconds($patientIn->created_at);
            $allDiffTime += $diffTime;
            $validCount++;
        }
    }

    // Average
    if($allDiffTime == 0 || $validCount == 0){
        return '-';
    }

    $diff = ($allDiffTime / $validCount);
    return gmdate('H:i:s', $diff);
}

function getQueuePatientTime($queueOpj, $toType, $timeType){

    if($toType == 'desk'){
        if ($timeType == 'waiting'){
            $called = $queueOpj->deskQueueStatusHistories()->where('queue_status_id', config('vars.desk_queue_status.called'))->first()->created_at;
            $waiting = $queueOpj->created_at;
            $diffTime = $called->diffInSeconds($waiting);
        }
        elseif ($timeType == 'serve'){
            $done = $queueOpj->deskQueueStatusHistories()->where('queue_status_id', config('vars.desk_queue_status.done'))->first()->created_at;
            $called = $queueOpj->deskQueueStatusHistories()->where('queue_status_id', config('vars.desk_queue_status.called'))->first()->created_at;
            $diffTime = $done->diffInSeconds($called);
        }
    }
    elseif ($toType == 'room'){
        if ($timeType == 'waiting'){
            $called = $queueOpj->roomQueueStatusHistories()->where('queue_status_id', config('vars.room_queue_status.called'))->first()->created_at;
            $waiting = $queueOpj->created_at;
            $diffTime = $called->diffInSeconds($waiting);
        }
        elseif ($timeType == 'serve'){
            $patientOut = $queueOpj->roomQueueStatusHistories()->where('queue_status_id', config('vars.room_queue_status.patient_out'))->first()->created_at;
            $patientIn = $queueOpj->roomQueueStatusHistories()->where('queue_status_id', config('vars.room_queue_status.patient_in'))->first()->created_at;
            $diffTime = $patientOut->diffInSeconds($patientIn);
        }
    }

    return gmdate('H:i:s', $diffTime);
}

function getQueuePatientActionTime($queueOpj, $toType, $timeType){

    $time = '';

    if($toType == 'desk'){
        if ($timeType == 'call'){
            $time = $queueOpj->deskQueueStatusHistories()->where('queue_status_id', config('vars.desk_queue_status.called'))->first();
        }
        elseif ($timeType == 'done'){
            $time = $queueOpj->deskQueueStatusHistories()->where('queue_status_id', config('vars.desk_queue_status.done'))->first();
        }
    }
    elseif ($toType == 'room'){
        if ($timeType == 'call'){
            $time = $queueOpj->roomQueueStatusHistories()->where('queue_status_id', config('vars.room_queue_status.called'))->first();
        }
        elseif ($timeType == 'in'){
            $time = $queueOpj->roomQueueStatusHistories()->where('queue_status_id', config('vars.room_queue_status.patient_in'))->first();
        }
        elseif ($timeType == 'done'){
            $time = $queueOpj->roomQueueStatusHistories()->where('queue_status_id', config('vars.room_queue_status.patient_out'))->first();
        }
    }

    return $time = ($time)? $time->created_at->addHour(2) : '-';
}

function getQueueActionTime($queue_id, $toType, $timeType){

    $time = '';

    if($toType == 'desk'){
        $time = \App\DeskQueueStatus::where('desk_queue_id', $queue_id);
        if ($timeType == 'call'){
            $time = $time->where('queue_status_id', config('vars.desk_queue_status.called'));
        }
        elseif ($timeType == 'done'){
            $time = $time->where('queue_status_id', config('vars.desk_queue_status.done'));
        }
        $time = $time->first();
    }
    elseif ($toType == 'room'){
        $time = \App\RoomQueueStatus::where('room_queue_id', $queue_id);
        if ($timeType == 'call'){
            $time = $time->where('queue_status_id', config('vars.room_queue_status.called'));
        }
        elseif ($timeType == 'in'){
            $time = $time->where('queue_status_id', config('vars.room_queue_status.patient_in'));
        }
        elseif ($timeType == 'done'){
            $time = $time->where('queue_status_id', config('vars.room_queue_status.patient_out'));
        }
        $time = $time->first();
    }

    return $time = ($time)? $time->created_at->addHour(2) : '-';
}

function getQueueServeTime($queue_id, $toType){
    if($toType == 'desk'){
        $get_done = \App\DeskQueueStatus::where('desk_queue_id', $queue_id)->where('queue_status_id', config('vars.desk_queue_status.done'))->first();
        $done = ($get_done)? $get_done->created_at : null;
        $get_called = \App\DeskQueueStatus::where('desk_queue_id', $queue_id)->where('queue_status_id', config('vars.desk_queue_status.called'))->first();
        $called = ($get_called)? $get_called->created_at : null;

        if(is_null($done) || is_null($called)){
            $diffTime = 0;
        }else{
            $diffTime = $done->diffInSeconds($called);
        }
    }

    if($toType == 'room'){
        $get_patientOut = \App\RoomQueueStatus::where('room_queue_id', $queue_id)->where('queue_status_id', config('vars.room_queue_status.patient_out'))->first();
        $patientOut = ($get_patientOut)? $get_patientOut->created_at : null;
        $get_patientIn = \App\RoomQueueStatus::where('room_queue_id', $queue_id)->where('queue_status_id', config('vars.room_queue_status.patient_in'))->first();
        $patientIn = ($get_patientIn)? $get_patientIn->created_at : null;

        if(is_null($patientOut) || is_null($patientIn)){
            $diffTime = 0;
        }else{
            $diffTime = $patientOut->diffInSeconds($patientIn);
        }
    }

    return gmdate('H:i:s', $diffTime);
}

function getDeskQueuePatientServeTime($queueOpj){
    $done = $queueOpj->deskQueueStatusHistories()->where('queue_status_id', config('vars.desk_queue_status.done'))->first()->created_at;
    $called = $queueOpj->deskQueueStatusHistories()->where('queue_status_id', config('vars.desk_queue_status.called'))->first()->created_at;
    $diffTime = $done->diffInSeconds($called);

    return gmdate('H:i:s', $diffTime);
}

function getRoomQueuePatientServeTime($queueOpj){
    $patientOut = $queueOpj->roomQueueStatusHistories()->where('queue_status_id', config('vars.room_queue_status.patient_out'))->first()->created_at;
    $patientIn = $queueOpj->roomQueueStatusHistories()->where('queue_status_id', config('vars.room_queue_status.patient_in'))->first()->created_at;
    $diffTime = $patientOut->diffInSeconds($patientIn);

    return gmdate('H:i:s', $diffTime);
}

/**
 * Get patient call time (when patient was called to doctor's room)
 */
function getPatientCallTime($roomQueue){
    $called = \App\RoomQueueStatus::where('room_queue_id', $roomQueue->id)
        ->where('queue_status_id', config('vars.queue_statuses.called'))
        ->first();
    
    return $called ? $called->created_at : null;
}

/**
 * Get first skip time for patient
 */
function getPatientFirstSkipTime($roomQueue){
    $skipped = \App\RoomQueueStatus::where('room_queue_id', $roomQueue->id)
        ->where('queue_status_id', config('vars.queue_statuses.skipped'))
        ->orderBy('created_at', 'ASC')
        ->first();
    
    return $skipped ? $skipped->created_at : null;
}

/**
 * Get patient check-in time (when patient entered doctor's room)
 */
function getPatientCheckInTime($roomQueue){
    $patientIn = \App\RoomQueueStatus::where('room_queue_id', $roomQueue->id)
        ->where('queue_status_id', config('vars.queue_statuses.patient_in'))
        ->first();
    
    return $patientIn ? $patientIn->created_at : null;
}

/**
 * Get patient check-out time (when consultation ended)
 */
function getPatientCheckOutTime($roomQueue){
    $patientOut = \App\RoomQueueStatus::where('room_queue_id', $roomQueue->id)
        ->where('queue_status_id', config('vars.queue_statuses.patient_out'))
        ->first();
    
    return $patientOut ? $patientOut->created_at : null;
}

/**
 * Calculate total waiting duration (Check-in Time - Payment Time)
 * Priority: Use skip time if available, otherwise use check-in time
 */
function calculateWaitingDuration($roomQueue){
    $paymentTime = $roomQueue->created_at;
    $skipTime = getPatientFirstSkipTime($roomQueue);
    $checkInTime = getPatientCheckInTime($roomQueue);
    
    // Use skip time if found, otherwise use check-in time
    $endTime = $skipTime ?: $checkInTime;
    
    if (!$endTime) {
        return null;
    }
    
    $diffInSeconds = $paymentTime->diffInSeconds($endTime);
    return gmdate('H:i', $diffInSeconds);
}

/**
 * Calculate total visit duration (Check-out Time - Check-in Time)
 */
function calculateVisitDuration($roomQueue){
    $checkInTime = getPatientCheckInTime($roomQueue);
    $checkOutTime = getPatientCheckOutTime($roomQueue);
    
    if (!$checkInTime || !$checkOutTime) {
        return null;
    }
    
    $diffInSeconds = $checkInTime->diffInSeconds($checkOutTime);
    return gmdate('H:i', $diffInSeconds);
}

/**
 * Calculate average waiting time from collection
 */
function calculateAverageWaitingTime($roomQueues){
    $totalSeconds = 0;
    $count = 0;
    
    foreach ($roomQueues as $roomQueue) {
        $paymentTime = $roomQueue->created_at;
        $skipTime = getPatientFirstSkipTime($roomQueue);
        $checkInTime = getPatientCheckInTime($roomQueue);
        
        $endTime = $skipTime ?: $checkInTime;
        
        if ($endTime) {
            $totalSeconds += $paymentTime->diffInSeconds($endTime);
            $count++;
        }
    }
    
    if ($count == 0) {
        return 'N/A';
    }
    
    $avgSeconds = $totalSeconds / $count;
    return gmdate('H:i', $avgSeconds);
}

/**
 * Calculate average visit duration from collection
 */
function calculateAverageVisitDuration($roomQueues){
    $totalSeconds = 0;
    $count = 0;
    
    foreach ($roomQueues as $roomQueue) {
        $checkInTime = getPatientCheckInTime($roomQueue);
        $checkOutTime = getPatientCheckOutTime($roomQueue);
        
        if ($checkInTime && $checkOutTime) {
            $totalSeconds += $checkInTime->diffInSeconds($checkOutTime);
            $count++;
        }
    }
    
    if ($count == 0) {
        return 'N/A';
    }
    
    $avgSeconds = $totalSeconds / $count;
    return gmdate('H:i', $avgSeconds);
}
