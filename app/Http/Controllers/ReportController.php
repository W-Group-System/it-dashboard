<?php

namespace App\Http\Controllers;
use App\OstTicket;
use App\Employee;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request) {
        $tickets = OstTicket::get();
        $tickets_today = OstTicket::whereDate('created',date('Y-m-d'))->get();
        $tickets_this_month = OstTicket::whereYear('created',date('Y'))->whereMonth('created',date('m'))->get();
        $closed_this_month = OstTicket::whereYear('closed',date('Y'))->whereMonth('closed',date('m'))->get();
        $closed_date = OstTicket::whereDate('closed',date('Y-m-d'))->get();
        $employees = Employee::where('isactive',1)->get();
        return view('it_dashboard',
        array(
            'tickets' => $tickets,
            'tickets_today' => $tickets_today,
            'closed_date' => $closed_date,
            'employees' => $employees,
            'tickets_this_month' => $tickets_this_month,
            'closed_this_month' => $closed_this_month,
        )
    );
    }
}
