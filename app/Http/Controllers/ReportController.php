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
        $attachments = 'rest/api/2/issue/'; // Endpoint for searching issues
        $projectKey = 'SYSDEV'; // Replace with the issue key you want to fetch
        $username = 'renz.cabato+1@wgroup.com.ph'; // Replace with your JIRA username (email)
        $apiToken = env('JIRA_API_TOKEN');
        // dd($apiToken);

       
        // Function to get all issues from a project
        function getIssuesFromProject($jiraUrl, $apiEndpoint, $projectKey, $username, $apiToken, $request): Collection
        {
            $date_to = date('Y-m-t', strtotime($request->month . "-01"));
            $date = date('Y-m-d', strtotime($request->month . "-01"));
            if ($request->month == null) {
                $date_to = date('Y-m-t');
                $date = date('Y-m-01');
            }
        
            $jql = "project={$projectKey} AND (created >= '{$date}' AND created <= '{$date_to}')";
            $startAt = 0; // Start from the beginning
            $maxResults = 1000; // Maximum results per request
            $allIssues = collect(); // Initialize a Laravel Collection
        
            do {
                // Create the URL for the API request
                $url = $jiraUrl . $apiEndpoint;
        
                // Initialize cURL session
                $ch = curl_init($url);
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
                    echo 'Error: ' . curl_error($ch);
                    curl_close($ch);
                    return collect(); // Return an empty collection on error
                }
        
                // Decode the JSON response
                $data = json_decode($response, true);
                curl_close($ch);
        
                // Add issues to the allIssues collection
                $issues = collect($data['issues']);
                $issues_new = [];
                foreach ($issues as $issue) {
                    $issueKey = $issue['key'];
                    $attachments = getAttachmentsForIssue($jiraUrl, $issueKey, $username, $apiToken);
                    // Add attachments to the issue data
                    // dd($attachments);
                    $issue['attachments'] = $attachments;
                    array_push($issues_new,$issue);

                    $attachmentsa = getIssueDetails($jiraUrl, $issueKey, $username, $apiToken);
                    foreach ($attachmentsa as $attachment) {
                        $attachmentUrl = $attachment['content'];
                        $attachmentName = $attachment['filename'];
                        $savePath = public_path().'/images/' . $attachmentName; // Path to save the attachment
                    
                        // Download the attachment
                        if (downloadAttachment($jiraUrl, $attachmentUrl, $username, $apiToken, $savePath)) {
                            // echo 'Downloaded: ' . $attachmentName . PHP_EOL;
                        } else {
                            // echo 'Failed to download: ' . $attachmentName . PHP_EOL;
                        }
                    }   
                }
        
                $allIssues = $allIssues->merge($issues_new);
        
                // Update startAt for pagination
                $startAt += $maxResults;
        
            } while ($startAt < $data['total']); // Continue until all issues are fetched
        
            return $allIssues;
        }
        function getAttachmentsForIssue($jiraUrl, $issueKey, $username, $apiToken) {
            $url = $jiraUrl . '/rest/api/2/issue/' . $issueKey;
        
            // Initialize cURL session
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Basic ' . base64_encode($username . ':' . $apiToken),
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_HTTPGET, true); // Ensure GET method is used
        
            // Execute the request and fetch the response
            $response = curl_exec($ch);
        
            // Check for cURL errors
            if (curl_errno($ch)) {
                echo 'cURL Error: ' . curl_error($ch);
                curl_close($ch);
                return []; // Return an empty array on error
            }
        
            // Check HTTP response code
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode != 200) {
                echo 'HTTP Error Code: ' . $httpCode;
                echo 'Response: ' . $response;
                curl_close($ch);
                return []; // Return an empty array on HTTP error
            }
        
            // Decode the JSON response
            $data = json_decode($response, true);
            curl_close($ch);
        
            // Extract attachments from the issue fields
            return $data['fields']['attachment'] ?? []; // Return attachments or an empty array
        }

        function getIssueDetails($jiraUrl, $issueKey, $username, $apiToken) {
            $url = $jiraUrl . '/rest/api/2/issue/' . $issueKey;
        
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Basic ' . base64_encode($username . ':' . $apiToken),
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        
            $response = curl_exec($ch);
            
            if (curl_errno($ch)) {
                echo 'cURL Error: ' . curl_error($ch);
                curl_close($ch);
                return [];
            }
        
            $data = json_decode($response, true);
            curl_close($ch);
        
            return $data['fields']['attachment'] ?? [];
        }
        
        function downloadAttachment($jiraUrl, $attachmentUrl, $username, $apiToken, $savePath) {
            // Initialize cURL session
            $ch = curl_init($attachmentUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Basic ' . base64_encode($username . ':' . $apiToken)
            ]);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirections
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10); // Max number of redirections
        
            // Download file
            $data = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
            if (curl_errno($ch)) {
                echo 'Download Error: ' . curl_error($ch);
                curl_close($ch);
                return false;
            }
        
            // Check for HTTP errors
            if ($httpCode != 200) {
                echo 'HTTP Error Code: ' . $httpCode;
                curl_close($ch);
                return false;
            }
        
            file_put_contents($savePath, $data);
            curl_close($ch);
        
            return true;
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
