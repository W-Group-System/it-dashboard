<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OstThread extends Model
{
    //
    protected $table = 'ost_thread';

    public function reply_thread()
    {
        return $this->hasMany(OstThreadEntry::class,'thread_id','id')->where('type','R');
    }
}
