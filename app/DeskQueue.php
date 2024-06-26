<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeskQueue extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['floor_id', 'area_id', 'desk_id', 'queue_number', 'status', 'reminder'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    /**
     *  Setup model event hooks
     */
    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = (string) \Webpatser\Uuid\Uuid::generate(config('vars.uuid_ver'));
        });
    }

    /**
     *  Create new resource
     */
    public static function store($inputs)
    {
        return self::create($inputs);
    }

    /**
     *  Update existing resource
     */
    public static function edit($inputs, $resource)
    {
        return self::where('id', $resource)->update($inputs);
    }

    /**
     *  Delete existing resource
     */
    public static function remove($resource)
    {
        return self::where('id', $resource)->delete();
    }

    /**
     *  Get a specific resource
     */
    public static function getBy($by, $resource)
    {
        return self::where($by, $resource)->first();
    }

    /**
     *  Get a specific resource
     */
    public static function getAll($status = 1)
    {
        return self::where('status', $status)->get();
    }

    /**
     *  Get Available Desk Queue
     */
    public static function getAvailableDeskQueueView($area_id)
    {
        $data['deskQueues'] = self::getDeskQueues($area_id);
        $data['deskQueueStatues'] = QueueStatus::getQueueStatuses('desk');
        $availableDeskQueue = view('desks._available_desk_queue', $data)->render();

        return $availableDeskQueue;
    }

    /**
     *  Get All Desk Queues
     */
    public static function getDeskQueues($area_id)
    {
        $deskQueues = self::where('area_id', $area_id)
            ->where('created_at', 'like', "%".date('Y-m-d')."%")
            ->orderBy('id', 'DESC')
            ->get();

        return $deskQueues;
    }


    /**
     *  Get Current Desk Queue
     */
    public static function getCurrentDeskQueues($desk_id)
    {
        $deskQueues = self::where('status', config('vars.desk_queue_status.call_from_skip'))
        ->where('created_at', 'like', "%".date('Y-m-d')."%")
        ->where('desk_id', $desk_id)
        ->first();

        if(empty($deskQueues)){
            $deskQueues = self::where('status', config('vars.desk_queue_status.called'))
                ->where('created_at', 'like', "%".date('Y-m-d')."%")
                ->where('desk_id', $desk_id)
                ->first();
        }

        return $deskQueues;
    }

    /**
     *  Get Count Desk Queue
     */
    public static function getCountDeskQueues($status, $all = null, $date_from = null, $date_to = null){
        $queues = self::where('status', $status);

        if (is_null($all)){
            if(is_null($date_from) && is_null($date_to)){
                $queues = $queues->where('created_at', 'like', "%".date('Y-m-d')."%")->count();
            }
            elseif (!is_null($date_from) && is_null($date_to)){
                $queues = $queues->where('created_at', 'like', "%".date($date_from)."%")->count();
            }
            else{
                $queues = $queues->where('created_at', '>=', $date_from)->where('created_at', '<=', $date_to)->count();
//                $queues = $queues->whereBetween('created_at', [$date_from, $date_to])->count();
            }
        }else{
            if(is_null($date_from) && is_null($date_to)){
                $queues = $queues->count();
            }else{
                $queues = $queues->where('created_at', '>=', $date_from)->where('created_at', '<=', $date_to)->count();
//                $queues = $queues->whereBetween('created_at', [$date_from, $date_to])->count();
            }
        }

        return $queues;
    }


    // Floor Relation
    public function floor(){
        return $this->belongsTo('App\Floor');
    }

    // Floor Relation
    public function desk(){
        return $this->belongsTo('App\Desk');
    }

    // Queue Status Relation
    public function queueStatus(){
        return $this->belongsTo('App\QueueStatus', 'status');
    }

    // Queue Status Histories Relation
    public function deskQueueStatusHistories(){
        return $this->hasMany('App\DeskQueueStatus', 'desk_queue_id');
    }

    // Reservation Relation
    public function reservation(){
        return $this->belongsTo('App\Reservation', 'id','desk_queue_id');
    }
}
