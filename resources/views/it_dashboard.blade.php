@extends('layouts.header')
@section('css')
<link href="{{ asset('/inside/login_css/css/plugins/chosen/bootstrap-chosen.css') }}" rel="stylesheet">
@endsection
@section('content')
<div class="wrapper wrapper-content ">
    <div class="row">
        <div class="col-lg-3 text-center">
            <div class="row">
                <div class="col-lg-6 text-center">
                    <div class="ibox-title">
                        <div class="ibox-tools text-center">
                            <span class="label label-info pull-right">as of {{date('M. d, Y')}}</span>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <h1 class="no-margins text-danger">{{count($tickets->where('closed',null))}}</h1>
                        <small>Total Open Tickets</small>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <div class="ibox-title">
                        <div class="ibox-tools text-center">
                            <span class="label label-info pull-right">as of {{date('M. d, Y')}}</span>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <h1 class="no-margins">{{count($tickets_today)}}</h1>
                        {{-- <div class="stat-percent font-bold text-navy">20% <i class="fa fa-level-up"></i></div> --}}
                        <small>Created Today</small>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    
                    <div class="ibox-content">
                        <h1 class="no-margins text-danger"><span id='due_tickets'>0</span></h1>
                        <small>Due Tickets (Open)</small>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <div class="ibox-content">
                        <h1 class="no-margins">{{count($closed_date)}}</h1>
                        {{-- <div class="stat-percent font-bold text-navy">20% <i class="fa fa-level-up"></i></div> --}}
                        <small>Closed Today</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 text-center">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Tickets </h5> <span class="label label-info pull-right">as of {{date('M Y')}}</span>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table id='table' class="table table-striped table-bordered table-hover tables">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Backlogs Tickets(OPEN)</th>
                                    <th>Ticket this ({{date('M Y')}})</th>
                                    <th>Closed this Month</th>
                                    <th>Open Tickets</th>
                                    <th>Closed Today</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employees->whereNotIn('staff_id',[5,10,8]) as $employee)
                                <tr>
                                    <td>{{$employee->firstname}} {{$employee->lastname}}</td>
                                    <td>{{(count($tickets_this_month->where('staff_id',$employee->staff_id))-count($tickets->where('closed',null)->where('staff_id',$employee->staff_id))-count($tickets_this_month->where('closed','!=',null)->where('staff_id',$employee->staff_id)))*-1}}</td>
                                    <td>{{count($tickets_this_month->where('staff_id',$employee->staff_id))}}</td>
                                    <td>{{count($tickets_this_month->where('closed','!=',null)->where('staff_id',$employee->staff_id))}}</td>
                                    <td>{{count($tickets->where('closed',null)->where('staff_id',$employee->staff_id))}}</td>
                                    <td>{{count($closed_date->where('staff_id',$employee->staff_id))}}</td>
                                </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="ibox float-e-margins">
                <div class="ibox-content">
                    <form  method='GET' onsubmit='show();'  enctype="multipart/form-data" >
                        <div class="row">
                            <div class="col-lg-3">
                                <input name='month' type='month' value='{{$month}}' class='form-control' required>
                            </div>
                            <div class="col-lg-3">
                                <select name='staff' class='form-control select cat'>
                                    <option value=''>All</option>
                                    @foreach($employees->whereNotIn('staff_id',[5,10,8]) as $employee)
                                    <option value='{{$employee->staff_id}}' @if($staff == $employee->staff_id) selected @endif>{{$employee->firstname}} {{$employee->lastname}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2">
                                <button class="btn btn-primary mt-4" type="submit" id='submit'>Generate</button>
                            </div>
                            <div class="col-lg-4"> <small>Total : <span id="total_generated_ticket">0</span>Open : <span id="open_tickets_generated">0</span></small><h1 class="no-margins text-danger text-right"><span id="avg_ticket">0.00</span> %</h1>
                            </div>
                        </div>
                    </form>
                    
                </div>
            </div>    
        </div>
    </div>
    <div class='row'>
        <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Tickets </h5> <span class="label label-info pull-right">as of {{date('M Y')}}</span>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table id='table' class="table table-striped table-bordered table-hover tables">
                            <thead>
                                <tr>
                                    <th>Ticket Number</th>
                                    <th>Requestor</th>
                                    <th>Subject</th>
                                    <th>Priority Level</th>
                                    <th>Staff Assigned</th>
                                    <th>Date Created</th>
                                    <th>First Response</th>
                                    <th>Target</th>
                                    <th>Closed Date</th>
                                    <th>Ticket Duration</th>
                                    <th>%</th>
                                    <th>Remarks</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $delayed = 0;
                                    $avg_percent_total=0;
                                    $percent_count=0;
                                @endphp
                                @foreach($tickets_this_month_request->whereNotIn('staff_id',[5,10,8]) as $ticket)
                                <tr>
                                    <td>{{$ticket->number}}</td>
                                    <td>{{$ticket->user->name}}</td>
                                    <td><small>{{$ticket->ticket_data->subject}}</small></td>
                                    <td>{{$ticket->ticket_data->priority_data->priority_desc}}</td>
                                    <td>@if($ticket->employee){{$ticket->employee->firstname}} {{$ticket->employee->lastname}}@else No IT Assigned @endif</td>
                                    <td>{{$ticket->created}}</td>
                                    <td>@if(count($ticket->reply->reply_thread)>0){{$ticket->reply->reply_thread[0]->created}}@endif</td>
                                    @php
                                        $priority = $ticket->ticket_data->priority_data->priority_desc;
                                        if ($priority == "Low") {
                                            $per =5;
                                            $due_date = date('Y-m-d h:m', strtotime('+5 days',strtotime($ticket->created)));
                                        } elseif ($priority == "Medium") {
                                            $due_date = date('Y-m-d h:m', strtotime('+3 days',strtotime($ticket->created)));
                                            $per =3;
                                        } elseif ($priority == "High") {
                                            $due_date = date('Y-m-d h:m', strtotime('+1 day',strtotime($ticket->created)));
                                            $per =1;
                                        } elseif ($priority == "Critical") {
                                            $due_date = date('Y-m-d h:m', strtotime('+4 hours',strtotime($ticket->created)));
                                            $per =.17;
                                        }
                                        if($ticket->closed != null)
                                        {
                                            $datetime = strtotime($due_date)-strtotime($ticket->closed);
                                            $datediff = strtotime($ticket->closed)-strtotime($ticket->created);
                                        }
                                        else {
                                            $datetime = strtotime($due_date)-strtotime(date('Y-m-d H:i:s'));
                                            $datediff = strtotime(date('Y-m-d H:i:s'))-strtotime($ticket->created);
                                        }
                                    @endphp
                                    <td>{{$due_date}}</td>
                                    <td>@if($ticket->closed != null){{$ticket->closed}}@endif</td>
                                    <td>
                                      
                                        {{number_format($datediff/60/60/24,2)}} Days

                                    </td>
                                    <td >
                                        @php
                                            $percent = (($datediff/60/60/24)/$per)*100;
                                            $avg_percent_total = $avg_percent_total + $percent;
                                            $percent_count++;

                                        @endphp
                                        {{number_format($percent,2)}} %
                                    </td>
                                    <td>
                                        @if($datetime>=0)
                                        Not Delayed
                                        @else
                                            Delayed
                                        @endif
                                    </td>
                                    <td>{{$ticket->status_data->name}}</td>
                                    @php
                                        if(($ticket->status_data->name == "Open") && ($datetime<0))
                                        {
                                            $delayed++;
                                        }
                                    @endphp
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
</div>
</div>
@endsection
@section('footer')
<script src="{{ asset('/inside/login_css/js/plugins/dataTables/datatables.min.js')}}"></script>
<script src="{{ asset('/inside/login_css/js/plugins/chosen/chosen.jquery.js') }}"></script>
<script>
 var delayed = {!! json_encode($delayed) !!};
 var avg_percent_total = {!! json_encode(number_format($avg_percent_total/$percent_count,2)) !!}
 document.getElementById("due_tickets").innerHTML = delayed;
 document.getElementById("avg_ticket").innerHTML = avg_percent_total;
$(document).ready(function(){

$('.cat').chosen({width: "100%"});
$('.tables').DataTable({
    pageLength: -1,
    paginate: false,
    responsive: true,
    dom: '<"html5buttons"B>lTfgitp',
    buttons: [
        {extend: 'csv', title: 'Aging Report'},
        {extend: 'excel', title: 'Aging Report'}
    ]

});

});
</script>

@endsection

