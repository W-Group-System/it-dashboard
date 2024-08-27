<?php

namespace App\Http\Controllers;
use App\OstTicket;
use App\Employee;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request) {

        // JIRA configuration
        $jiraUrl = 'https://wgroup-projects.atlassian.net'; // Replace with your JIRA instance URL
        $apiEndpoint = '/rest/api/2/search'; // Endpoint for searching issues
        $projectKey = 'SYSDEV'; // Replace with the issue key you want to fetch
        $username = 'renz.cabato+1@wgroup.com.ph'; // Replace with your JIRA username (email)
        $apiToken = env('JIRA_API_TOKEN');
        // dd($apiToken);

       
        // Function to get all issues from a project
        function getIssuesFromProject($jiraUrl, $apiEndpoint, $projectKey, $username, $apiToken,$request): Collection
        {
            $date_to = date('Y-m-t',strtotime($request->month."-01"));
            $date = date('Y-m-d',strtotime($request->month."-01"));
            if($request->month == null)
            {
                $date_to = date('Y-m-t');
                $date = date('Y-m-01');
            }
            // Updated JQL query to filter issues by date and status
            // dd($date);
            $jql = "project={$projectKey} AND (created >= '{$date}' AND created <= '{$date_to}')";
            $startAt = 0; // Start from the beginning
            $maxResults = 1000; // Maximum results per request
            $allIssues = collect(); // Initialize a Laravel Collection
        
            do {
                // Create the URL for the API request
                $url = $jiraUrl . $apiEndpoint;
        
                // Initialize cURL session
                $ch = curl_init($url);
        
                // Set cURL options
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $apiToken);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                    'jql' => $jql,
                    'startAt' => $startAt,
                    'maxResults' => $maxResults
                ]));
        
                // Execute the request and fetch the response
                $response = curl_exec($ch);
        
                // Check for errors
                if (curl_errno($ch)) {
                    echo 'Error:' . curl_error($ch);
                    curl_close($ch);
                    return collect(); // Return an empty collection on error
                }
        
                // Decode the JSON response
                $data = json_decode($response, true);
                curl_close($ch);
        
                // Add issues to the allIssues collection
                $allIssues = $allIssues->merge($data['issues']);
        
                // Update startAt for pagination
                $startAt += $maxResults;
        
            } while ($startAt < $data['total']); // Continue until all issues are fetched
        
            return $allIssues;
        }

        $issues = getIssuesFromProject($jiraUrl, $apiEndpoint, $projectKey, $username, $apiToken,$request);
        // dd($issues[0]);
        $months = [];
        
       
        for ($m=1; $m<=12; $m++) {
            $object = new \stdClass();
            $object->y =date('M-Y', mktime(0,0,0,$m, 1, date('Y')));
            $tick = OstTicket::whereYear('created',date('Y'))->whereMonth('created',date('m',mktime(0,0,0,$m, 1, date('Y'))))->count();
            $object->a =$tick;
            $months[$m-1]=  $object;
        }
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
            'months' => $months,
            'issues' => $issues,
        )
    );
    }
}
