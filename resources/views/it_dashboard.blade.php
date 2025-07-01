@extends('layouts.header')
@section('css')
<link href="{{ asset('/inside/login_css/css/plugins/chosen/bootstrap-chosen.css') }}" rel="stylesheet">
<link href="{{ asset('/inside/login_css/css/plugins/c3/c3.min.css') }}" rel="stylesheet">
<link href="{{ asset('/inside/login_css/css/plugins/morris/morris-0.4.3.min.css') }}" rel="stylesheet">
@endsection
@section('content')
<div class="wrapper wrapper-content ">
    <div class="row">
        <div class="col-lg-4">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <span class="label label-warning pull-right">as of {{date('M. d, Y')}}</span>
                    <h5>Tickets</h5>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-xs-4">
                            <small class="stats-label">Total Open Tickets</small>
                            <h4>{{count($tickets)}}</h4>
                        </div>

                        <div class="col-xs-4">
                            <small class="stats-label">Delayed Tickets</small>
                            <h4><span id='due_tickets'>0</span></h4>
                        </div>
                        <div class="col-xs-4">
                            <small class="stats-label">Total Ticket ({{date('M. Y')}})</small>
                            <h4><span id='total_ticket'>0</span></h4>
                        </div>
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-xs-4">
                            <small class="stats-label">New Tickets</small>
                            <h4>{{count($tickets_today)}}</h4>
                        </div>

                        <div class="col-xs-4">
                            <small class="stats-label">Closed Today</small>
                            <h4>{{count($closed_date)}}</h4>
                        </div>
                        <div class="col-xs-4">
                            <small class="stats-label">Closed Ticket ({{date('M. Y')}})</small>
                            <h4><span id='total_closed_ticket'>0</span></h4>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Percent of Ticket this {{date('M Y')}}</h5>
                </div>
                <div class="ibox-content">
                    <div>
                        <div id="pie"></div>
                    </div>
                </div>
            </div>
            <div class="ibox float-e-margins">
        
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table id='table' class="table table-striped table-bordered table-hover tables">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Backlogs Tickets (OPEN)</th>
                                    <th>Ticket this ({{date('M Y')}})</th>
                                    <th>Closed this Month</th>
                                    <th>Open Tickets</th>
                                    <th>Closed Today</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $total_ticket_this_month = 0;
                                    $total_closed_this_month = 0;
                                    $backlogs_open = 0;
                                    $employees_data = [];
                                    $employees_data_data = [];
                                @endphp
                                @foreach($employees->whereNotIn('staff_id',[5,10,8]) as $employee)
                                <tr>
                                    <td>{{$employee->firstname}} {{$employee->lastname}}</td>
                                    <td>{{(count($tickets_this_month->where('staff_id',$employee->staff_id))-count($tickets->where('closed',null)->where('staff_id',$employee->staff_id))-count($tickets_this_month->where('closed','!=',null)->where('staff_id',$employee->staff_id)))*-1}}</td>
                                    <td>{{count($tickets_this_month->where('staff_id',$employee->staff_id))}}</td>
                                    <td>{{count($tickets_this_month->where('closed','!=',null)->where('staff_id',$employee->staff_id))}}</td>
                                    <td>{{count($tickets->where('closed',null)->where('staff_id',$employee->staff_id))}}</td>
                                    <td>{{count($closed_date->where('staff_id',$employee->staff_id))}}</td>
                                </tr>
                                @php
                                    $object = [];
                                    $object_data = new stdClass();
                                    $object[0] = $employee->firstname." ".$employee->lastname;
                                    $object[1] = count($tickets_this_month->where('staff_id',$employee->staff_id));
                                    $object_data->label = $employee->firstname." ".$employee->lastname;
                                    $object_data->value = count($tickets_this_month->where('closed','!=',null)->where('staff_id',$employee->staff_id));
                                    $employees_data[] = $object;
                                    $employees_data_data[] = $object_data;
                                    $backlogs_open = $backlogs_open + (count($tickets_this_month->where('staff_id',$employee->staff_id))-count($tickets->where('closed',null)->where('staff_id',$employee->staff_id))-count($tickets_this_month->where('closed','!=',null)->where('staff_id',$employee->staff_id)))*-1;
                                    $total_ticket_this_month = $total_ticket_this_month + count($tickets_this_month->where('staff_id',$employee->staff_id));
                                    $total_closed_this_month =  $total_closed_this_month + count($tickets_this_month->where('closed','!=',null)->where('staff_id',$employee->staff_id));
                                @endphp
                                @endforeach

                            </tbody>
                        </table>
                    </div>

                </div>
            </div> 
      
        </div> 
        <div class="col-lg-8">
            <div class='row'>
                <div class='col-lg-6 text-center stretch'>
                    
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5>Biometrics</h5>
                            <div class="ibox-tools">
                                <a class="collapse-link">
                                    <i class="fa fa-chevron-up"></i>
                                </a>
                            </div>
                        </div>
                        <div class="ibox-content " style="overflow-y: scroll; height:400px;">
                            <table class="table table-hover no-margins">
                                <thead>
                                <tr>
                                    <th>Location</th>
                                    <th>Last Log</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <tbody >
                                    @foreach($biometrics as $biometric)
                                    <tr>
                                        <td>{{$biometric->location}}</td>
                                        <td><i class="fa fa-clock-o"></i> {{$biometric->datetime}}</td>
                                        <td>@if(date('Y-m-d',strtotime($biometric->datetime)) != date('Y-m-d')) <span class='label label-danger pull-right'>Need to re-sync</span>@else <span class='label label-info pull-right'>No Error</span> @endif</td>
                                    </tr>
                                    @endforeach
                            
                                </tbody>
                            </table>
                        </div>
                    </div>
        
                </div>
                <div class="col-lg-3 text-center ">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5>Tickets <span class="label label-danger pull-right">as of {{date('M. Y')}}</span></h5>
        
                        </div>
                        <div class="ibox-content">
                            <div id="morris-donut-chart" ></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 text-center ">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5>Tickets Closed<span class="label label-danger pull-right">as of {{date('M. Y')}}</span></h5>
        
                        </div>
                        <div class="ibox-content">
                            <div id="morris-donut-chart-employees" ></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class='row'>
                <div class="col-lg-12">
                    <div class="ibox float-e-margins">
                        <div class="ibox-title">
                            <h5>Ticket per IT Personnel</h5>
                        </div>
                        <div class="ibox-content">
                            <div id="morris-bar-chart"></div>
                        </div>
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
                            <div class="col-lg-4"> <small>Total : <span id="total_generated_ticket">{{count($tickets_this_month_request->whereNotIn('staff_id',[5,10,8]))}}</span> | Open : <span id="open_tickets_generated">{{count($tickets_this_month_request->whereNotIn('staff_id',[5,10,8])->where('closed',null))}}</span> | Delayed : <span id="delayed_tickets_generated">0</span></small><h1 class="no-margins text-danger text-right"><span id="avg_ticket">0.00</span> %</h1>
                            </div>
                        </div>
                    </form>
                    
                </div>
            </div>    
        </div>
    </div>
    <div class='row'>
        <div class='col-lg-12'>
            <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>Tickets </h5> <span class="label label-info pull-right">as of {{date('M Y')}}</span>
                    </div>
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table id='table' class="table tablesa table-striped table-bordered table-hover">
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
                                        $due = 0;
                                        $avg_percent_total=0;
                                        $percent_count=0;
                                        $open=0;
                                        $delayed=0;
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
                                            @php
                                                $delayed++;
                                            @endphp
                                                Delayed
                                            @endif
                                        </td>
                                        <td>{{$ticket->status_data->name}}</td>
                                        @php
                                            if(($ticket->status_data->name == "Open") && ($datetime<0))
                                            {
                                                $due++;
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
        <div class='col-lg-12'>
            <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>SCRF </h5> <span class="label label-info pull-right">as of {{date('M Y')}}</span>
                    </div>
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table  class="table tablesa table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Developer</th>
                                        <th>Title</th>
                                        {{-- <th>Project</th> --}}
                                        <th>Type</th>
                                        <th>Date Received</th>
                                        <th>Due Date</th>
                                        <th>Date Finished</th>
                                        <th>Status</th>
                                        {{-- <th>Attachments</th>
                                        <th>Remarks</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- @foreach($issues as $issue)
                                    <tr>
                                        <td><a href='https://wgroup-projects.atlassian.net/jira/software/projects/SYSDEV/issues/{{$issue['key']}}' target='_blank'>{{$issue['key']}}</a></td>
                                        <td>{{$issue['fields']['assignee']['displayName']}}</td>
                                        <td>{{$issue['fields']['summary']}}</td>
                                        <td>{{$issue['fields']['customfield_10032']['value']}}</td>
                                        <td>@isset($issue['fields']['customfield_10051']['value'])
                                            {{ $issue['fields']['customfield_10051']['value'] }}
                                        @endisset</td>
                                        <td>{{$issue['fields']['customfield_10050']}}</td>
                                        <td>{{$issue['fields']['duedate']}}</td>
                                        <td>{{$issue['fields']['customfield_10075']}}</td>
                                        <td>{{$issue['fields']['status']['name']}}</td>
                                        <td>
                                            @foreach($issue['attachments'] as $attachment)
                                            <a href='{{url("/images/".$attachment["filename"])}}' target='_blank'><i class="fa fa-file-pdf-o"></i></a> <br>
                                            @endforeach
                                        </td>
                                        <td>
                                            @if($issue['fields']['customfield_10075'])
                                                @if($issue['fields']['customfield_10075'] > $issue['fields']['duedate']) 
                                                Delayed 
                                                @else Not Delay 
                                                @endif
                                            @else
                                                @if(date('Y-m-d') > $issue['fields']['duedate']) 
                                                Delayed 
                                                @else Not Delay 
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach --}}
                                    @foreach ($task_lists as $task)
                                        @php
                                            $assigns = collect($task->assignees)->first();
                                        @endphp
                                        <tr>
                                            <td>{{ $task->name }}</td>
                                            <td>
                                                @if ($assigns)
                                                    {{ $assigns->username }}
                                                @endif
                                            </td>
                                            <td>{{ $task->text_content }}</td>
                                            {{-- <td></td> --}}
                                            <td>
                                                @if($task->priority)
                                                    {{ $task->priority->priority }}
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $dateCreated = $task->date_created / 1000;
                                                @endphp
                                                @if ($task->date_created)
                                                    {{ date('Y-m-d', $dateCreated) }}
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $dueDate = $task->due_date / 1000;
                                                @endphp
                                                @if($task->due_date)
                                                    {{ date('Y-m-d', $dueDate) }}
                                                @endif
                                            </td>   
                                            <td>
                                                @php
                                                    $dateDone = $task->date_done / 1000;
                                                @endphp
                                                @if($task->date_done)
                                                    {{ date('Y-m-d', $dateDone) }}
                                                @endif
                                            </td>
                                            <td>
                                                {{ $task->status->status }}
                                            </td>
                                            {{-- <td></td>
                                            <td></td> --}}
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('footer')
<script src="{{ asset('/inside/login_css/js/plugins/dataTables/datatables.min.js')}}"></script>
<script src="{{ asset('/inside/login_css/js/plugins/chosen/chosen.jquery.js') }}"></script>


<script src="{{ asset('/inside/login_css/js/plugins/morris/raphael-2.1.0.min.js') }}"></script>
<script src="{{ asset('/inside/login_css/js/plugins/morris/morris.js') }}"></script>


<script src="{{ asset('/inside/login_css/js/plugins/d3/d3.min.js') }}"></script>
<script src="{{ asset('/inside/login_css/js/plugins/c3/c3.min.js') }}"></script>
<script>
 var due = {!! json_encode($due) !!};
 var total_ticket_this_month = {!! json_encode($total_ticket_this_month) !!};
 var open_tickets = {!! json_encode(($tickets->count())) !!};
 var total_closed_this_month = {!! json_encode($total_closed_this_month) !!};
 var backlogs_open = {!! json_encode($backlogs_open) !!};
 var delayed = {!! json_encode($delayed) !!};
 var avg_percent_total = {!! json_encode(number_format($avg_percent_total/$percent_count,2)) !!}
 var employees = {!! json_encode($employees_data) !!};
 var employees_data_data = {!! json_encode($employees_data_data) !!};
 var months = {!! json_encode(($months)) !!};
 document.getElementById("due_tickets").innerHTML = due;
 document.getElementById("avg_ticket").innerHTML = avg_percent_total;
 document.getElementById("delayed_tickets_generated").innerHTML = delayed;
 document.getElementById("total_ticket").innerHTML = total_ticket_this_month;
 document.getElementById("total_closed_ticket").innerHTML = total_closed_this_month;
$(document).ready(function(){

$('.cat').chosen({width: "100%"});
$('.tables').DataTable({
    pageLength: -1,
    paginate: false,
    responsive: true,
    dom: '<"html5buttons"B>lTfgitp',
    buttons: [
        {extend: 'csv', title: 'Ticketing'},
        {extend: 'excel', title: 'Ticketing'}
    ]

});
$('.tablesa').DataTable({
    pageLength: 10, // Set pagination to 10 rows per page
    paginate: true, // Enable pagination
    responsive: true,
    dom: '<"html5buttons"B>lTfgitp',
    buttons: [
        { extend: 'csv', title: 'Ticketing' },
        { extend: 'excel', title: 'Ticketing' }
    ]
});

});

$(function() {
            Morris.Donut({
            element: 'morris-donut-chart',
            data:[
                
                { label: "Open - Not delayed", value: open_tickets-due-backlogs_open },
                { label: "Open - Delayed", value: due },
                { label: "Closed", value: total_closed_this_month }, 
                { label: "Backlogs Open", value: backlogs_open }, 
            ],
            resize: true,
            colors: ['#FFA500','#f44336', '#54cdb4','#f44336'],
        });
            Morris.Donut({
            element: 'morris-donut-chart-employees',
            data:employees_data_data,
            resize: true,
        });
        Morris.Bar({
        element: 'morris-bar-chart',
        data: months,
        xkey: 'y',
        ykeys: ['a'],
        labels: ['Tickets'],
        hideHover: 'auto',
        resize: true,
        barColors: ['#54cdb4'],
    });

    c3.generate({
                bindto: '#pie',
                data:{
                    columns: employees,
                    type : 'pie'
                }
            });
        
    });
</script>

{{-- <script src="{{ asset('/inside/login_css/js/demo/chartjs-demo.js') }}"></script> --}}

@endsection

