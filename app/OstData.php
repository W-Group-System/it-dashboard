<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OstData extends Model
{
    //
    protected $table = 'ost_ticket__cdata';

    public function priority_data()
    {

        return $this->belongsTo(OstPriority::class,'priority','priority_id');
    }
}
