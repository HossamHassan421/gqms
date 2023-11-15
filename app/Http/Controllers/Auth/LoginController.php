<?php

namespace App\Http\Controllers\Auth;

use App\Desk;
use App\Doctor;
use App\Events\DeskStatus;
use App\Events\RoomStatus;
use App\Http\Controllers\Controller;
use App\Room;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');

    }

    // After Login
    protected function authenticated(\Illuminate\Http\Request $request, $user)
    {
        // Check user status (account is not activated)
//        dd($request, $user);
        if ($user->status == 0 && !in_array($user->id, config('vars.authorized_users'))) {
            auth()->logout();
            $data['message'] = [
                'type' => 'danger',
                'text' => 'Sorry your account is not activated',
            ];
            return redirect(route('login'))->with('message', $data['message']);
        }
        // check the relation if the doctor
        if ($user->type == 1) { // Doctor
            $message = '';
            $checkDoctorData = Doctor::where('user_id', $user->id)->first();
            $login = false;
            if ($checkDoctorData) {
                if (empty($checkDoctorData['source_doctor_id']) || empty($checkDoctorData['source_speciality_id'])) {
                    if (empty($checkDoctorData['source_doctor_id'])) {
                        $message = 'Sorry, this doctor didn\'t linked with Ganzory doctors table, contact with Administrator!';
                    } elseif (empty($checkDoctorData['source_speciality_id'])) {
                        $message = 'Sorry, this doctor didn\'t linked with Ganzory specialities table, contact with Administrator!';
                    }
                } else {
                    $login = true;
                }
            } else {
                $message = 'Sorry, this doctor dont have relation with doctor table, contact with Administrator!';
            }
            if (!$login) {
                auth()->logout();
                $data['message'] = [
                    'type' => 'danger',
                    'text' => $message,
                ];
                return redirect(route('login'))->with('message', $data['message']);
            }
        }
        // Check if user and ip exists in desks or in rooms
        if ($user->type == 1) { // Doctor
            $data['resource'] = Room::getBy('ip', $request->login_ip);
        } elseif ($user->type == 2) { // Desk
            $data['resource'] = Desk::getBy('ip', $request->login_ip);
        }

        // Update user
        if (isset($data['resource'])) {

            // Check in any user already logged in from this Desk or Room
            $userToLogOut = User::getBy('login_ip', $data['resource']->ip);
            if ($userToLogOut) {
                // Broadcast event
                if ($user->type == 1) { // Doctor
                    if (isset($userToLogOut->room_id)) {
                        event(new RoomStatus(Room::getBy('id', $userToLogOut->room_id)->uuid, 0));
                    }
                } elseif ($user->type == 2) { // Desk
                    if ($userToLogOut->desk_id) {
                        event(new DeskStatus(Desk::getBy('id', $userToLogOut->desk_id)->uuid, 0));
                    }
                }

                // Logout this user
                User::edit([
                    'desk_id' => null,
                    'room_id' => null,
                    'login_ip' => null,
                    'available' => 0,
                ], $userToLogOut->id);
            }

            // Login current user and update data
            User::edit([
                'room_id' => ($user->type == 1) ? $data['resource']->id : null,
                'desk_id' => ($user->type == 2) ? $data['resource']->id : null,
                'login_ip' => $data['resource']->ip,
                'available' => 1,
            ], $user->id);


            // Broadcast event
            if ($user->type == 1) { // Doctor
                event(new RoomStatus($data['resource']->uuid, 1));
            } elseif ($user->type == 2) { // Desk
                event(new DeskStatus($data['resource']->uuid, 1));
            }

//            if(!User::getBy('room_id', $data['resource']->id)){
//            }
        }

        // Store Log User Login
        storeLogUserLogin();
    }

    // Custom Logout
    public function logout(\Illuminate\Http\Request $request)
    {

        if (auth()->user()->desk_id) {
            // Broadcast event
            event(new DeskStatus(Desk::getBy('id', auth()->user()->desk_id)->uuid, 0));

            // Update user
            User::edit([
                'desk_id' => null,
                'login_ip' => null,
                'available' => 0,
            ], auth()->user()->id);
        } elseif (auth()->user()->room_id) {
            // Broadcast event
            event(new RoomStatus(Room::getBy('id', auth()->user()->room_id)->uuid, 0));

            // Update user
            User::edit([
                'room_id' => null,
                'login_ip' => null,
                'available' => 0,
            ], auth()->user()->id);
        }

        auth()->logout();

        session()->forget('permissions');
        return redirect(route('login'));
    }
}
