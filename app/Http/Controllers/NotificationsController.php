<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Notifications;
use App\AttendanceRequest;
use App\Attendance;
use App\Users;
use App\IncidenceDetails;
use App\SiteAssign;
use App\ActivityLog;
use Log;
use App\FCMNotify;
use App\SiteDetails;
use App\CompanyDetails;
use App\IncidenceComment;
use Carbon\Carbon;


class NotificationsController extends Controller
{
    // notification list
    public function index()
    {
        $user = session("user");
        Log::info($user->name . ' view notification list, User_id: ' . $user->id);
        $past12Hours = date("Y-m-d H:i:s", strtotime('-12 hours', time()));
        $currentDateTime = date("Y-m-d H:i:s");
        $date = date('Y-m-d');
        if ($user->role_id == '1') {
            $attendance = Notifications::where('dateFormat', $date)->where([['type', '=', 'Attendance Request'], ['readStatusForAdmin', '=', '0'], ['readStatusForSupervisor', '=', '0'], ['company_id', '=', $user->company_id]])->pluck('id')->toArray();
            //print_r($attendance);exit;
            $attendances = Notifications::where('dateFormat', $date)->where([['type', '=', 'Attendance Request'], ['readStatusForAdmin', '=', '0'], ['readStatusForSupervisor', '=', '0'], ['company_id', '=', $user->company_id]])->get();
            $incidence = Notifications::where([['type', '=', 'Incidence'], ['readStatusForAdmin', '=', '0'], ['readStatusForSupervisor', '=', '0'], ['company_id', '=', $user->company_id]])->pluck('id')->toArray();
            $incidences = Notifications::where([['type', '=', 'Incidence'], ['readStatusForAdmin', '=', '0'], ['readStatusForSupervisor', '=', '0'], ['company_id', '=', $user->company_id]])->orderBy('id', 'desc')->get();
            $complaint = Notifications::where('dateFormat', $date)->where([['type', '=', 'Complaint'], ['readStatusForAdmin', '=', '0'], ['readStatusForSupervisor', '=', '0'], ['company_id', '=', $user->company_id]])->pluck('id')->toArray();
            $complaints = Notifications::where('dateFormat', $date)->where([['type', '=', 'Complaint'], ['readStatusForAdmin', '=', '0'], ['readStatusForSupervisor', '=', '0'], ['company_id', '=', $user->company_id]])->get();
            $output = array_merge($incidence, $attendance, $complaint);
            // $a = json_decode($output,true);
            $notifications = Notifications::whereIn('id', $output)->orderBy('id', 'desc')->get();
            // dd($notifications);
        } else if ($user->role_id == '2') {

            $sites = SiteAssign::where('user_id', $user->id)->first();
            $siteArray = json_decode($sites['site_id'], true);
            $attendance = Notifications::where('dateFormat', $date)->where([['type', '=', 'Attendance Request'], ['readStatusForSupervisor', '=', '0'], ['readStatusForAdmin', '=', '0']])->whereIn('site_id', $siteArray)->pluck('id')->toArray();
            $attendances = Notifications::where('dateFormat', $date)->where([['type', '=', 'Attendance Request'], ['readStatusForSupervisor', '=', '0'], ['readStatusForAdmin', '=', '0']])->whereIn('site_id', $siteArray)->get();

            $incidence = Notifications::where([['type', '=', 'Incidence'], ['readStatusForAdmin', '=', '0'], ['readStatusForSupervisor', '=', '0']])->whereIn('site_id', $siteArray)->pluck('id')->toArray();
            $incidences = Notifications::where([['type', '=', 'Incidence'], ['readStatusForAdmin', '=', '0'], ['readStatusForSupervisor', '=', '0']])->whereIn('site_id', $siteArray)->orderBy('id', 'desc')->get();

            $output = array_merge($incidence, $attendance);

            //print_r($a);exit;

            $notifications = Notifications::whereIn('id', $output)->orderBy('id', 'DESC')->get();
        } else if ($user->role_id == '4') {
            $site = SiteDetails::where('client_id', $user->client_id)->pluck('id')->toArray();
            $attendance = Notifications::where('dateFormat', $date)->where([['type', '=', 'Attendance Request'], ['readStatusForSupervisor', '=', '0'], ['readStatusForAdmin', '=', '0']])->whereIn('site_id', $site)->pluck('id')->toArray();
            $attendances = Notifications::where('dateFormat', $date)->where([['type', '=', 'Attendance Request'], ['readStatusForSupervisor', '=', '0'], ['readStatusForAdmin', '=', '0']])->whereIn('site_id', $site)->get();

            $incidence = Notifications::where([['type', '=', 'Incidence'], ['readStatusForAdmin', '=', '0'], ['readStatusForSupervisor', '=', '0']])->whereIn('site_id', $site)->pluck('id')->toArray();
            $incidences = Notifications::where([['type', '=', 'Incidence'], ['readStatusForAdmin', '=', '0'], ['readStatusForSupervisor', '=', '0']])->whereIn('site_id', $site)->orderBy('id', 'desc')->get();
            $complaints = Notifications::where('dateFormat', $date)->where([['type', '=', 'Complaint'], ['readStatusForAdmin', '=', '0'], ['readStatusForSupervisor', '=', '0'], ['client_id', '=', $user->client_id]])->get();

            $output = array_merge($incidence, $attendance);
            $notifications = Notifications::whereIn('id', $output)->orderBy('id', 'DESC')->get();
        }
        return view("notificationlist")->with('notifications', $notifications)->with('attendances', $attendances)->with('incidences', $incidences)->with('complaints', $complaints);

        // print_r($incidence);exit;

        // print_r($attendance);exit;

    }

    // attendance approved
    public function notificationAdd($id)
    {
        $user = session('user');

        if ($user->role_id == '1') {
            Notifications::where('notification_id', $id)->where('type', 'Attendance Request')->update(array('readStatusForAdmin' => '1'));
        } else {
            Notifications::where('notification_id', $id)->where('type', 'Attendance Request')->update(array('readStatusForSupervisor' => '1'));
        }
        $date = date('Y-m-d');
        //DB::enableQueryLog();
        $records = AttendanceRequest::where('id', $id)->first();
        //print_r(\DB::getQueryLog());exit;
        //print_r($records);exit;

        //print_r($records);exit;
        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Attendance Request Approved",
            'message' => "Attendance request from '" . $records->guard_name . "' approved by " . $user->name,
            'date_time' => date('Y-m-d H:i:s'),
        ]);
        if ($records != '') {
            $attendance_new = new Attendance();

            $attendance_new->name = $records->guard_name;
            $attendance_new->geo_id = $records->geo_id;
            $attendance_new->geo_name = $records->geo_name;
            $attendance_new->site_id = $records->site_id;
            $attendance_new->site_name = $records->site_name;
            $attendance_new->supervisor_id = $records->supervisor_id;
            $attendance_new->company_id = $user->company_id;
            $attendance_new->user_id = $records->guard_id;
            $attendance_new->client_id = $records->client_id;
            $attendance_new->photo = $records->photo;
            $attendance_new->date = $records->date;
            $attendance_new->dateFormat = $records->dateFormat;
            $attendance_new->entry_time = $records->time;
            $attendance_new->entry_date_time = $records->dateTime;
            $attendance_new->entryDateTime = date("Y-m-d H:i:s ", strtotime($records->dateTime));
            $attendance_new->role_id = $records->role_id;
            $attendance_new->location = $records->location;
            // if ($attendance_new->guard_id != $user->id) {
            $attendance_new->approvedBy = $user->name;
            $attendance_new->approvedById = $user->id;
            // } else {
            //     $attendance->approvedBy = "Auto Approved";
            // }
            $attendance_new->inOutStatus = 'Inside';
            $attendance_new->emergency_attend = '1';

            $attendance_new->save();

            AttendanceRequest::where('id', $id)->update(array('status' => 'Approved', 'attendance_id' => $attendance_new->id, 'action_by' => $user->id));

            if ($user->role_id == '1') {
                $guardonsite = DB::table('attendance')
                    ->where('company_id', $user->company_id)
                    ->where('role_id', '=', 3)
                    ->where('dateFormat', '=', $date)
                    ->select('count(*) as allcount')
                    ->count();

                $attendance = DB::table('attendance')
                    ->where('company_id', $user->company_id)
                    ->where('dateFormat', $date)
                    ->where('role_id', 3)
                    ->pluck('user_id')
                    ->toArray();

                $noshow = DB::table('users')
                    ->where('company_id', $user->company_id)
                    ->where('role_id', 3)
                    ->whereNotIn('id', $attendance)
                    ->select('count(*) as allcount')
                    ->count();
                //print_r($guardonsite);exit;
            } else if ($user->role_id == '2') {
                $guardonsite = DB::table('attendance')
                    ->where('supervisor_id', $user->supervisor_id)->where('role_id', '=', 3)
                    ->where('dateFormat', '=', $date)
                    ->select('count(*) as allcount')
                    ->count();
                $attendance = DB::table('attendance')
                    ->where('supervisor_id', $user->supervisor_id)
                    ->where('dateFormat', $date)
                    ->where('role_id', 3)
                    ->pluck('user_id')
                    ->toArray();

                $noshow = DB::table('users')
                    ->where('supervisor_id', $user->supervisor_id)
                    ->where('role_id', 3)
                    ->whereNotIn('id', $attendance)
                    ->select('count(*) as allcount')
                    ->count();
            }
            $response = array(
                "guardonsite" => $guardonsite,
                "noshow" => $noshow,
            );

            return $response;
        } else {
            return "No record Found";
        }
    }

    //reject attendance
    public function notificationReject(Request $request, $id)
    {
        $user = session('user');

        if ($user->role_id == '1') {
            Notifications::where('notification_id', $id)->where('type', 'Attendance Request')->update(array('readStatusForAdmin' => '1'));
        } else {
            Notifications::where('notification_id', $id)->where('type', 'Attendance Request')->update(array('readStatusForSupervisor' => '1'));
        }
        $date = date("Y-m-d");
        $today = Carbon::now()->format('Y-m-d');
        $yesterday = Carbon::yesterday()->format('Y-m-d');
        $req = AttendanceRequest::where('id', $id)->update(array('status' => 'Rejected'));
        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Attendance Request Rejected",
            'message' => "Attendance request from '" . $req->guard_name . "' rejected by " . $user->name,
            'date_time' => date('Y-m-d H:i:s'),
        ]);
        $attendance = DB::table('attendance')->whereBetween('dateFormat', [$yesterday, $today])->pluck('user_id')->toArray();
        $noshow = DB::table('users')->where('role_id', 3)->whereNotIn('id', $attendance)->select('count(*) as allcount')->count();
        return  $noshow;
    }

    // incidence escalate
    public function notificationEscalate(Request $request, $incident_id)
    {
        $user = session('user');
        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Incidence Escalated",
            'message' => "Incidence escalated by " . $user->name,
            'date_time' => date('Y-m-d H:i:s'),
        ]);
        if ($request->incidence == 'incidence') {

            $incidence =   IncidenceDetails::where('id', '=', $incident_id)->first();
            //  dd($incidence);
            $user = session('user');

            $comment = new IncidenceComment();
            $comment->user_id = $incidence->guard_id;
            $comment->user_name = $user->name;
            $comment->company_id = $user->company_id;
            $comment->client_id = $incidence->client_id;
            $comment->site_id = $incidence->site_id;
            $comment->incidence_id = $incident_id;

            $comment->date_time = date('Y-m-d H:i:s');
            $comment->comment  = $request->remark;
            //dd($comment);
            $comment->save();

            if ($user->role_id == '1') {
                $webToken = Users::where('company_id', $user->company_id)->where('role_id', 1)->where('web_token', '!=', NULL)->pluck('web_token')->toArray();
                $adminFcm = Users::where('company_id', $user->company_id)->where('role_id', 1)->where('fcm_token', '!=', NULL)->pluck('fcm_token')->toArray();

                IncidenceDetails::where('id', '=', $incident_id)->update(['adminRemark' => $request->remark, 'statusFlag' => '5', 'status' => 'Escalated to client by ' . $user->name, 'adminRemark' => $request->remark, 'actionRemark' => $request->remark, 'adminActionDateTime' => date("Y-m-d H:i:s")]);
                Notifications::where('notification_id', '=', $incident_id)->update(['readStatusForAdmin' => '1', 'action' => 'Escalate']);
            } else {
                $webToken = Users::where('company_id', $user->company_id)->where('role_id', 2)->where('web_token', '!=', NULL)->pluck('web_token')->toArray();
                $adminFcm = Users::where('company_id', $user->company_id)->where('role_id', 1)->where('fcm_token', '!=', NULL)->pluck('fcm_token')->toArray();
                IncidenceDetails::where('id', '=', $incident_id)->update(['supervisorRemark' => $request->remark, 'statusFlag' => '3', 'status' => 'Escalated to Admin by ' . $user->name]);
                Notifications::where('notification_id', '=', $incident_id)->update(['action' => 'Escalate', 'readStatusForSupervisor' => '1']);
            }
            $title = "Incidence Alert";
            $message = $user->name . " has resolved incidence";
            $SERVER_API_KEY = 'AAAAbGxbLJ4:APA91bGSRsYTyZaW45QQQ8tgLtV5KtGJvin732rxMZaZsN_4gl6xE7lmvqhicPfy-HsbfmJs0w-4t6DxJ25ozPCj4xJHA3eEYY2B_ajCYNg07h4X7qk_xJRv4TVwPqy7jCmdYR0-rerQ';
            $webData = [
                "registration_ids" => $webToken,
                "notification" => [
                    "title" => $title,
                    "body" => $message,
                ]
            ];
            $dataString = json_encode($webData);
            $headers = [
                'Authorization: key=' . $SERVER_API_KEY,
                'Content-Type: application/json',
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

            $response = curl_exec($ch);
            if (count($adminFcm) > 0) {
                $fcm = new FCMNotify;
                $fcm->sendNotification($title, $message, $adminFcm);
            }
            return 'success';
        } else {
            $notificationID = Notifications::where('id', '=', $_GET['notification_id'])->first();
            if ($user->role_id == '1') {
                IncidenceDetails::where('id', '=', $notificationID->notification_id)->update(['actionRemark' => $request->remark, 'statusFlag' => '5', 'status' => 'Escalated to client by ' . $user->name]);
                Notifications::where('id', '=', $_GET['notification_id'])->update(['action' => 'Escalate', 'readStatusForAdmin' => '1']);
            } else {
                IncidenceDetails::where('id', '=', $notificationID->notification_id)->update(['actionRemark' => $request->remark, 'statusFlag' => '3', 'status' => 'Escalated to Admin by ' . $user->name]);
                Notifications::where('id', '=', $_GET['notification_id'])->update(['action' => 'Escalate', 'readStatusForSupervisor' => '1']);
            }
        }
    }

    // incidence resolved
    public function notificationResolve(Request $request, $incident_id)
    {
        $user = session('user');
        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Incidence Resolved",
            'message' => "Incidence resolved by " . $user->name,
            'date_time' => date('Y-m-d H:i:s'),
        ]);


        if ($request->incidence == 'incidence') {

            $incidence =   IncidenceDetails::where('id', '=', $incident_id)->first();
            //  dd($incidence);
            $user = session('user');

            $comment = new IncidenceComment();
            $comment->user_id = $incidence->guard_id;
            $comment->user_name = $user->name;
            $comment->company_id = $user->company_id;
            $comment->client_id = $incidence->client_id;
            $comment->site_id = $incidence->site_id;
            $comment->incidence_id = $incident_id;
            $comment->status = "Incidence resolved by " . $user->name;

            $comment->date_time = date('Y-m-d H:i:s');
            $comment->comment  = $request->remark;
            //dd($comment);
            $comment->save();


            if ($user->role_id == '1') {
                IncidenceDetails::where('id', '=', $incident_id)->update(['adminRemark' => $request->remark, 'statusFlag' => '1', 'status' => 'Resolved By Admin', 'adminRemark' => $request->remark, 'adminActionDateTime' => date("Y-m-d H:i:s"), 'actionRemark' => $request->remark]);
                Notifications::where('notification_id', '=', $incident_id)->where('type', 'Incidence')->update(['readStatusForAdmin' => '1']);
                $webToken = Users::where('company_id', $user->company_id)->where('role_id', 1)->where('web_token', '!=', NULL)->pluck('web_token')->toArray();

                $adminFcm = Users::where('company_id', $user->company_id)->where('role_id', 1)->where('fcm_token', '!=', NULL)->pluck('fcm_token')->toArray();
            } else {
                $webToken = Users::where('company_id', $user->company_id)->where('role_id', 2)->where('web_token', '!=', NULL)->pluck('web_token')->toArray();

                $adminFcm = Users::where('company_id', $user->company_id)->where('role_id', 2)->where('fcm_token', '!=', NULL)->pluck('fcm_token')->toArray();
                IncidenceDetails::where('id', '=', $incident_id)->update(['actionRemark' => $request->remark, 'statusFlag' => '1', 'status' => 'Resolved By Supervisor', 'supervisorRemark' => $request->remark, 'supervisorActionDateTime' => date("Y-m-d H:i:s")]);
                Notifications::where('notification_id', '=', $incident_id)->where('type', 'Incidence')->update(['action' => 'Resolve', 'readStatusForSupervisor' => '1']);
            }

            $title = "Incidence Alert";
            $message = $user->name . " has resolved incidence";
            $SERVER_API_KEY = 'AAAAbGxbLJ4:APA91bGSRsYTyZaW45QQQ8tgLtV5KtGJvin732rxMZaZsN_4gl6xE7lmvqhicPfy-HsbfmJs0w-4t6DxJ25ozPCj4xJHA3eEYY2B_ajCYNg07h4X7qk_xJRv4TVwPqy7jCmdYR0-rerQ';
            $webData = [
                "registration_ids" => $webToken,
                "notification" => [
                    "title" => $title,
                    "body" => $message,
                ]
            ];
            $dataString = json_encode($webData);
            $headers = [
                'Authorization: key=' . $SERVER_API_KEY,
                'Content-Type: application/json',
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

            $response = curl_exec($ch);
            if (count($adminFcm) > 0) {
                $fcm = new FCMNotify;
                $fcm->sendNotification($title, $message, $adminFcm);
            }
            return 'success';
        } else {
            $notificationID = Notifications::where('id', '=', $_GET['notification_id'])->first();
            if ($user->role_id == '1') {
                IncidenceDetails::where('id', '=', $notificationID->notification_id)->update(['actionRemark' => $request->remark, 'statusFlag' => '1', 'status' => 'Resolved By Admin']);
                Notifications::where('id', '=', $_GET['notification_id'])->update(['readStatusForAdmin' => '1']);
            } else {
                IncidenceDetails::where('id', '=', $notificationID->notification_id)->update(['actionRemark' => $request->remark, 'statusFlag' => '1', 'status' => 'Resolved By Supervisor']);
                Notifications::where('id', '=', $_GET['notification_id'])->update(['action' => 'Resolve', 'readStatusForSupervisor' => '1']);
            }
        }
    }

    //incidence ignored
    public function notificationIgnore(Request $request, $incident_id)
    {
        $user = session('user');
        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Incidence Ignored",
            'message' => "Incidence ignored by " . $user->name,
            'date_time' => date('Y-m-d H:i:s'),
        ]);
        if ($request->incidence == 'incidence') {

            $incidence =   IncidenceDetails::where('id', '=', $incident_id)->first();
            //  dd($incidence);
            $user = session('user');

            $comment = new IncidenceComment();
            $comment->user_id = $incidence->guard_id;
            $comment->user_name = $user->name;
            $comment->company_id = $user->company_id;
            $comment->client_id = $incidence->client_id;
            $comment->site_id = $incidence->site_id;
            $comment->incidence_id = $incident_id;

            $comment->date_time = date('Y-m-d H:i:s');
            $comment->comment  = $request->remark;
            $comment->status  = "Incidence ignored by " . $user->name;
            // dd($comment);
            $comment->save();

            if ($user->role_id == '1') {
                $webToken = Users::where('company_id', $user->company_id)->where('role_id', 2)->where('web_token', '!=', NULL)->pluck('web_token')->toArray();

                $adminFcm = Users::where('company_id', $user->company_id)->where('role_id', 2)->where('fcm_token', '!=', NULL)->pluck('fcm_token')->toArray();
                IncidenceDetails::where('id', '=', $incident_id)->update(['adminRemark' => $request->remark, 'statusFlag' => '2', 'status' => 'Ignored by Admin ' . $user->name, 'adminActionDateTime' => date("Y-m-d H:i:s")]);
                Notifications::where('notification_id', '=', $incident_id)->where('type', 'Incidence')->update(['readStatusForAdmin' => '1']);
            } else {
                $adminFcm = Users::where('company_id', $user->company_id)->where('role_id', 1)->where('fcm_token', '!=', NULL)->pluck('fcm_token')->toArray();
                $webToken = Users::where('company_id', $user->company_id)->where('role_id', 1)->where('web_token', '!=', NULL)->pluck('web_token')->toArray();
                IncidenceDetails::where('id', '=', $incident_id)->update(['actionRemark' => $request->remark, 'statusFlag' => '2', 'status' => 'Ignored by Supervisor ' . $user->name]);
                Notifications::where('notification_id', '=', $incident_id)->where('type', 'Incidence')->update(['readStatusForSupervisor' => '1']);
            }
            $title = "Incidence Alert";
            $message = $user->name . " has resolved incidence";
            $SERVER_API_KEY = 'AAAAbGxbLJ4:APA91bGSRsYTyZaW45QQQ8tgLtV5KtGJvin732rxMZaZsN_4gl6xE7lmvqhicPfy-HsbfmJs0w-4t6DxJ25ozPCj4xJHA3eEYY2B_ajCYNg07h4X7qk_xJRv4TVwPqy7jCmdYR0-rerQ';
            $webData = [
                "registration_ids" => $webToken,
                "notification" => [
                    "title" => $title,
                    "body" => $message,
                ]
            ];
            $dataString = json_encode($webData);
            $headers = [
                'Authorization: key=' . $SERVER_API_KEY,
                'Content-Type: application/json',
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

            $response = curl_exec($ch);
            if (count($adminFcm) > 0) {
                $fcm = new FCMNotify;
                $fcm->sendNotification($title, $message, $adminFcm);
            }
        } else {
            $notificationID = Notifications::where('id', '=', $_GET['notification_id'])->first();
            if ($user->role_id == '1') {
                IncidenceDetails::where('id', '=', $notificationID->notification_id)->update(['actionRemark' => $request->remark, 'statusFlag' => '2', 'status' => 'Ignored by Admin ' . $user->name]);
                Notifications::where('id', '=', $_GET['notification_id'])->update(['action' => 'Ignore'], ['readStatusForAdmin' => '1']);
            } else {
                IncidenceDetails::where('id', '=', $notificationID->notification_id)->update(['actionRemark' => $request->remark, 'statusFlag' => '2', 'status' => 'Ignored by Supervisor ' . $user->name]);
                Notifications::where('id', '=', $_GET['notification_id'])->update(['action' => 'Ignore'], ['readStatusForSupervisor' => '1']);
            }
        }
    }





    // incidence revert
    public function notificationRevert(Request $request, $incident_id)
    {
        // dd('request',$request,$incident_id);
        $user = session('user');
        ActivityLog::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Incidence Revert",
            'message' => "Incidence reverted by " . $user->name,
            'date_time' => date('Y-m-d H:i:s'),
        ]);
        if ($request->incidence == 'incidence') {

            $incidence =   IncidenceDetails::where('id', '=', $incident_id)->first();
            //  dd($incidence);
            $user = session('user');

            $comment = new IncidenceComment();
            $comment->user_id = $incidence->guard_id;
            $comment->user_name = $user->name;
            $comment->company_id = $user->company_id;
            $comment->client_id = $incidence->client_id;
            $comment->site_id = $incidence->site_id;
            $comment->incidence_id = $incident_id;

            $comment->date_time = date('Y-m-d H:i:s');
            $comment->comment  = $request->remark;
            $comment->status  = "Incidence reverted by " . $user->name;
            //dd($comment);
            $comment->save();

            if ($user->role_id == '1') {
                $webToken = Users::where('company_id', $user->company_id)->where('role_id', 2)->where('web_token', '!=', NULL)->pluck('web_token')->toArray();

                $adminFcm = Users::where('company_id', $user->company_id)->where('role_id', 2)->where('fcm_token', '!=', NULL)->pluck('fcm_token')->toArray();
                IncidenceDetails::where('id', '=', $incident_id)->update(['adminRemark' => $request->remark, 'statusFlag' => '6', 'status' => 'Revert By admin ' . $user->name, 'adminActionDateTime' => date("Y-m-d H:i:s")]);
                Notifications::where('notification_id', '=', $incident_id)->where('type', 'Incidence')->update(['readStatusForAdmin' => '1']);
            } else {
                $adminFcm = Users::where('company_id', $user->company_id)->where('role_id', 1)->where('fcm_token', '!=', NULL)->pluck('fcm_token')->toArray();
                $webToken = Users::where('company_id', $user->company_id)->where('role_id', 1)->where('web_token', '!=', NULL)->pluck('web_token')->toArray();
                IncidenceDetails::where('id', '=', $incident_id)->update(['actionRemark' => $request->remark, 'statusFlag' => '6', 'status' => 'Revert By supervisor ' . $user->name]);
                Notifications::where('notification_id', '=', $incident_id)->where('type', 'Incidence')->update(['readStatusForSupervisor' => '1']);
            }
            $title = "Incidence Alert";
            $message = $user->name . " has reverted the incidence";
            $SERVER_API_KEY = 'AAAAbGxbLJ4:APA91bGSRsYTyZaW45QQQ8tgLtV5KtGJvin732rxMZaZsN_4gl6xE7lmvqhicPfy-HsbfmJs0w-4t6DxJ25ozPCj4xJHA3eEYY2B_ajCYNg07h4X7qk_xJRv4TVwPqy7jCmdYR0-rerQ';
            $webData = [
                "registration_ids" => $webToken,
                "notification" => [
                    "title" => $title,
                    "body" => $message,
                ]
            ];
            $dataString = json_encode($webData);
            $headers = [
                'Authorization: key=' . $SERVER_API_KEY,
                'Content-Type: application/json',
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

            $response = curl_exec($ch);
            if (count($adminFcm) > 0) {
                $fcm = new FCMNotify;
                $fcm->sendNotification($title, $message, $adminFcm);
            }
        } else {
            $notificationID = Notifications::where('id', '=', $_GET['notification_id'])->first();
            if ($user->role_id == '1') {
                IncidenceDetails::where('id', '=', $notificationID->notification_id)->update(['actionRemark' => $request->remark, 'statusFlag' => '2', 'status' => 'Ignored By admin ' . $user->name]);
                Notifications::where('id', '=', $_GET['notification_id'])->update(['action' => 'Ignore'], ['readStatusForAdmin' => '1']);
            } else {
                IncidenceDetails::where('id', '=', $notificationID->notification_id)->update(['actionRemark' => $request->remark, 'statusFlag' => '2', 'status' => 'Ignored By supervisor ' . $user->name]);
                Notifications::where('id', '=', $_GET['notification_id'])->update(['action' => 'Ignore'], ['readStatusForSupervisor' => '1']);
            }
        }
    }

    // session expired
    public function sessionExpired()
    {
        $user = session('user');

        session()->forget('sessionExpire');
        Log::info($user->name . ' destroy expire button session, User_id: ' . $user->id);
        return  response()->json([
            'success' => '200',
        ]);
    }
}
