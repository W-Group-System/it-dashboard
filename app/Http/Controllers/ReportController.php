<?php

namespace App\Http\Controllers;
use App\OstTicket;
use App\Employee;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request) {
        $date = date('Y-m-t',strtotime($request->month."-01"));
        if($request->month == null)
        {
            $date = date('Y-m-d');
        }
        $tickets = OstTicket::where('closed',null)->get();
        $tickets_today = OstTicket::whereDate('created',date('Y-m-d'))->get();
        $tickets_this_month = OstTicket::whereYear('created',date('Y'))->whereMonth('created',date('m'))->get();

        if($request->staff)
        {
            $tickets_this_month_request = OstTicket::whereYear('created', date('Y', strtotime($date)))
            ->whereMonth('created', date('m', strtotime($date)))
            ->where('staff_id',$request->staff)
            ->where('closed','!=',null)
            // ->orWhere(function ($query) use ($date,$request) {
            //     $query->where('closed', null)
            //         ->where('staff_id',$request->staff)
            //         ->whereDate('created', '<', $date);
            // })
            ->get(); 
        }
        else
        {
            $tickets_this_month_request = OstTicket::whereYear('created', date('Y', strtotime($date)))
            ->whereMonth('created', date('m', strtotime($date)))
            // ->orWhere(function ($query) use ($date) {
            //     $query->where('closed', null)
            //         ->whereDate('created', '<', $date);
            // })
            ->get(); 
        }
        $url = 'https://hris.wsystem.online/get-devices';

        $response = file_get_contents($url);
        $biometrics = json_decode($response);
    

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
            'tickets_this_month_request' => $tickets_this_month_request,
            'staff' => $date,
            'month' => $request->month,
            'biometrics' => $biometrics,
        )
    );
    }
}
