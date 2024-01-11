@extends('layouts.header')
@section('css')
<link href="{{ asset('/inside/login_css/css/plugins/chosen/bootstrap-chosen.css') }}" rel="stylesheet">
@endsection
@section('content')
<div class="wrapper wrapper-content ">
<div class="row">
    <div class="col-lg-2 text-center">
        <div class="ibox text-center">
            <div class="ibox-title">
                <div class="ibox-tools text-center">
                    <span class="label label-info pull-right">as of {{date('M. d, Y')}}</span>
                </div>
            </div>
            <div class="ibox-content">
                <h1 class="no-margins text-danger">{{count($tickets->where('closed',null))}}</h1>
                <small>Total Open Tickets</small>
            </div>
            <div class="ibox-content">
                <h1 class="no-margins">{{count($tickets_today)}}</h1>
                {{-- <div class="stat-percent font-bold text-navy">20% <i class="fa fa-level-up"></i></div> --}}
                <small>Created Today</small>
            </div>
            <div class="ibox-content">
                <h1 class="no-margins">{{count($closed_date)}}</h1>
                {{-- <div class="stat-percent font-bold text-navy">20% <i class="fa fa-level-up"></i></div> --}}
                <small>Closed Today</small>
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
@endsection
@section('footer')
<script src="{{ asset('/inside/login_css/js/plugins/dataTables/datatables.min.js')}}"></script>
<script src="{{ asset('/inside/login_css/js/plugins/chosen/chosen.jquery.js') }}"></script>
<script>

     
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

