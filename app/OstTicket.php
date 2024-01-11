<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OstTicket extends Model
{
    //
    protected $table = 'ost_ticket';

    public function user()
    {
        return $this->belongsTo(OstUser::class);
    }
    public function ticket_data()
    {
        return $this->hasOne(OstData::class,'ticket_id','ticket_id');
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class,'staff_id','staff_id');
    }
    public function reply()
    {
        return $this->hasOne(OstThread::class,'object_id','ticket_id');
    }

    public function status_data()
    {
        return $this->belongsTo(OstStatus::class,'status_id','id');
    }
}
