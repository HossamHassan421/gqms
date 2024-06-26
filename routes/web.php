<?php

use App\Reservation;
use App\RoomQueue;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

Route::group(
    ['middleware' => ['cors']
],function (){

});

// Site Languages
Route::get('language/{language}', 'LanguagesController@setLanguage')->name('language');

Route::get('test_schedule', function (){
    DB::table('test_schedule')->insert(['name' => str_random('10')]);
    return 'Done';
});

Route::get('dev-test', function (){
    $currentDateTime = Carbon::now('Africa/Cairo');
    echo $currentDateTime->toDateTimeString();
});

Route::get('dev-fix-today-reservations', function (){
    $reservations = Reservation::where('reservation_date_time', 'like', date('Y-m-d') . '%')->get();
    foreach ($reservations as $key => $val) {
        $queue = RoomQueue::where('doctor_id', $val->doctor_id)
            ->where('reservation_source_serial', $val->source_reservation_serial)->first();
        if($queue) {
            $queue->created_at = date('Y-m-d H:i:s');
            $queue->save();
        }
    }
});

// Logout user after close browser
Route::get('auth/close-browser/logout', 'Auth\CustomAuthController@authCloseBrowserLogout')->name('authCloseBrowserLogout');

//Route::get('test', function (){
//    // 1 - Get Reservation
//    $reservation = \App\Reservation::getBy('source_reservation_serial', 73504);
//    $doctor = $reservation->doctor;
//});

//// Integration And Sync Section ////////
Route::get('/integration/get-clinics', 'SyncVendorDataController@getClientClinics');
Route::get('/integration/get-specialities', 'SyncVendorDataController@getClientSpecialities');
Route::get('/integration/get-patients', 'SyncVendorDataController@getClientPatients');
Route::get('/integration/get-doctors', 'SyncVendorDataController@getClientDoctors');
Route::get('/integration/get-reservations', 'SyncVendorDataController@getClientReservations');

Route::get('/integration/sync/{what}', 'SyncButtonsController@syncClient')->name('integration.syncClient');
//// END Integration And Sync Section ////

// Auth modifications
Route::get('auth/users/logout', 'Auth\CustomAuthController@logoutUsers')->name('auth.logoutUsers');

// Globals
Route::get('/', 'HomeController@index')->name('home');
Route::get('home', 'HomeController@index')->name('home');
Route::get('get-my-ip', function(){ return view('get_my_ip'); })->name('ip.get');
Route::post('queues/{screen_uuid}/{area_uuid}', 'DeskQueuesController@storeNewQueue')->name('desks.queues.storeNewQueue');
Route::get('/reset', 'HomeController@resetQueues')->name('resetQueues');

// Screens
Route::get('screens/unified/url', 'ScreensController@showByUnifiedUrl')->name('screens.showByUnifiedUrl');
Route::get('screens/{screen}', 'ScreensController@show')->name('screens.show');
Route::get('screens/ajax/{screen}/get-contents', 'ScreensController@getScreensAjaxContents')->name('screens.getScreensAjaxContents');
Route::get('screens/search-by-letter/{type}/{letter}', 'ScreensController@searchByLetter')->name('screens.searchByLetter')->where('type', 'doctor|speciality');
Route::get('doctors/get/floors/{doctor_uuid}', 'DoctorToFloorsController@getDoctorFloor')->name('doctor-to-floor.getDoctorFloor');

// Auth
Auth::routes(['verify' => true]);
Route::get('register', function (){return redirect('login');});
Route::get('logout', 'Auth\LoginController@logout');

// Admin
Route::group([
    'prefix'=>'dashboard',
    'middleware' => ['auth']
],function (){
    // System Resources
    Route::get('/', 'DashboardController@index')->name('dashboard.index');
    Route::resource('permission-groups', 'PermissionGroupsController');
    Route::resource('permissions', 'PermissionsController');
    Route::resource('roles', 'RolesController');
    Route::resource('users', 'UsersController');
    Route::resource('floors', 'FloorsController');
    Route::resource('areas', 'AreasController');
    Route::resource('desks', 'DesksController');
    Route::resource('rooms', 'RoomsController');
    Route::resource('printers', 'PrintersController');
    Route::resource('screens', 'ScreensController')->except(['show']);

    // Reload screen
    Route::get('screen/reload/{screen_uuid}', 'ScreensController@reload')->name('screens.reload');

    // User update data
    Route::get('user/profile', 'UsersController@showUserProfile')->name('users.showUserProfile');

    // User logs
    Route::get('logs/user/logins', 'LogsController@index_logs_user_logins')->name('logs_user_logins.index');
    Route::get('logs/user/actions', 'LogsController@index_logs_user_actions')->name('logs_user_actions.index');

    // Reports
    Route::get('reports/desks', 'ReportsController@desksIndex')->name('reports.desks.index');
    Route::get('reports/doctors', 'ReportsController@doctorsIndex')->name('reports.doctors.index');

    // Reset password
    Route::get('users/{user}/reset_password', 'UsersController@resetPassword')->name('users.reset_password');

    // Update password
    Route::put('users/{user}/update_password', 'UsersController@updatePassword')->name('users.update_password');

    // Settings
    Route::get('settings', 'SettingsController@index')->name('settings.index');

    // Doctor modifications
    Route::get('doctors/{doctor_uuid}/{nickname}/update-nickName', 'DoctorsController@updateNickName')->name('screens.updateNickName');

    // Screen modifications
    Route::get('screens/filter/areas/{area_uuid}', 'ScreensController@filterByArea')->name('screens.filterByArea');

    // Doctor to floor
    Route::get('doctor-to-floor', 'DoctorToFloorsController@index')->name('doctor-to-floor.index');
    Route::post('doctor-to-floor/{floor_uuid}/update', 'DoctorToFloorsController@update')->name('doctor-to-floor.update');

    // Speciality to area
    Route::get('speciality-to-area', 'AreasController@getSpecialityToArea')->name('areas.getSpecialityToArea');
    Route::post('speciality-to-area/{area_uuid}/update', 'AreasController@updateSpecialityToArea')->name('areas.updateSpecialityToArea');

    // Ganzory Resources
    Route::get('clinics', 'ClinicsController@index')->name('clinics.index');
    Route::get('specialities', 'SpecialitiesController@index')->name('specialities.index');
    Route::get('doctors', 'DoctorsController@index')->name('doctors.index');
    Route::get('patients', 'PatientsController@index')->name('patients.index');
    Route::get('reservations', 'ReservationsController@index')->name('reservations.index');
    Route::get('schedules', 'DoctorScheduleController@index')->name('schedules.index');

    // Desks Actions
    Route::get('desk/{desk_uuid}/{desk_queue_uuid}/skip', 'DeskQueuesController@skipQueueNumber')->name('desks.queues.skipQueueNumber');
    Route::get('desk/{desk_uuid}/{desk_queue_uuid}/skip-and-next', 'DeskQueuesController@skipAndNextQueueNumber')->name('desks.queues.skipAndNextQueueNumber');
    Route::get('desk/{desk_uuid}/{queue_uuid}/call-skipped', 'DeskQueuesController@callSkippedAgain')->name('desks.queues.callSkippedAgain');

    Route::get('desk/{desk_uuid}/next', 'DeskQueuesController@callNextQueueNumber')->name('desks.queues.callNextQueueNumber');
    Route::get('desk/{desk_uuid}/next-again', 'DeskQueuesController@callNextQueueNumberAgain')->name('desks.queues.callNextQueueNumberAgain');

    Route::get('desk/reservation/{reservation_resource}/check', 'DeskQueuesController@checkReservationExists')->name('desks.queues.checkReservationExists');

    Route::get('desk/{desk_uuid}/{desk_queue_uuid}/{reservation_serial}/done', 'DeskQueuesController@doneQueueNumber')->name('desks.queues.doneQueueNumber');
    Route::get('desk/{desk_uuid}/{desk_queue_uuid}/{reservation_serial}/done-and-next', 'DeskQueuesController@doneAndNextQueueNumber')->name('desks.queues.doneAndNextQueueNumber');

    // Rooms Actions
    Route::get('room/{room_queue_uuid}/skip', 'RoomQueuesController@skipQueueNumber')->name('rooms.queues.skipQueueNumber');
    Route::get('room/{room_uuid}/{room_queue_uuid}/skip-and-next', 'RoomQueuesController@skipAndNextQueueNumber')->name('rooms.queues.skipAndNextQueueNumber');
    Route::get('room/{skipped_queue_uuid}/call-skipped', 'RoomQueuesController@callSkippedAgain')->name('rooms.queues.callSkippedAgain');

    Route::get('room/next', 'RoomQueuesController@callNextQueueNumber')->name('rooms.queues.callNextQueueNumber');
    Route::get('room/{room_queue_uuid}/next-again', 'RoomQueuesController@callNextQueueNumberAgain')->name('rooms.queues.callNextQueueNumberAgain');

    Route::get('room/{room_queue_uuid}/in', 'RoomQueuesController@inQueueNumber')->name('rooms.queues.inQueueNumber');

    Route::get('room/{room_queue_uuid}/out', 'RoomQueuesController@outQueueNumber')->name('rooms.queues.outQueueNumber');
    Route::get('room/{room_uuid}/{room_queue_uuid}/out-and-next', 'RoomQueuesController@outAndNextQueueNumber')->name('rooms.queues.outAndNextQueueNumber');


    // Desk History
    Route::get('desk/queues/history', 'DeskQueuesController@deskQueueHistory')->name('desks.queues.deskQueueHistory');
    Route::get('desk/queues/{queue_uuid}/history', 'DeskQueuesController@deskQueueSingleHistory')->name('desks.queues.deskQueueSingleHistory');

    // Room History
    Route::get('rooms/queues/history', 'RoomQueuesController@roomQueueHistory')->name('rooms.queues.roomQueueHistory');
    Route::get('rooms/queues/{queue_uuid}/history', 'RoomQueuesController@roomQueueSingleHistory')->name('rooms.queues.roomQueueSingleHistory');

    // All Queues History
    Route::get('queues/history', 'QueuesController@queuesHistory')->name('queues.queuesHistory');
    Route::get('all/queues/history', 'QueuesController@allQueuesHistory')->name('queues.allQueuesHistory');
    Route::get('queues/{queue_uuid}/history', 'QueuesController@queuesSingleHistory')->name('queues.queuesSingleHistory');

    // Go available
    Route::get('user/availability', 'UsersController@availability')->name('users.availability');
});
