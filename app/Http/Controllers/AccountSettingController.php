<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Users;
use Illuminate\Support\Facades\Hash;
use App\SMS;
use App\CompanyDetails;
use App\ActivityLog;
use Log;
use Redirect;


class AccountSettingController extends Controller
{
    // account setting profile page
    public function index($id)
    {
        $users = session('user');
        if ($users) {
            Log::info($users->name . ' view profile page, User_id: '. $users->id);
            $user = Users::where('id', $id)->first();
            return view('profile')->with('user', $user);
        }
    }

    // account setting profile Page updation
    public function profileUpdateAction(Request $request, $id)
    {
        $user = session('user');
        if ($request->photo != null) {

            $file = $request->file('photo');
            $extension = $file->getClientOriginalExtension(); // you can also use file name
            $fileName = time() . '.' . $extension;
            $path = public_path() . '/uploads';
            $file_name = 'image_' . time() . '.png';
            $uplaod = $file->move($path, $file_name);
            $actual_path = 'http://dev.pugmarg.in/uploads/' . $file_name;
        }

        if ($user) {
            ActivityLog::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'type' => "Profile updated",
                'message' => "Self profile updated by " . $user->name,
                'date_time' => date('Y-m-d H:i:s'),
            ]);
            $users = Users::find($id);

            $users->name = $request->name;
            $users->email = $request->email;
            $users->contact = $request->mobile;
            if ($request->photo != null) {
                $users->profile_pic = $actual_path;
                $users->profile_file_name = $file_name;
            }
            $users->save();
            return redirect()->route('home');
        }
    }

    // password updation
    public function changePassword(Request $request, $id)
    {
        $user = session('user');
        if ($user) {
            ActivityLog::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'type' => "Password changed",
                'message' => "Password changed by " . $user->name,
                'date_time' => date('Y-m-d H:i:s'),
            ]);
            $users = Users::find($id);

            $currentPassword = $request->currentPassword;
            $newPassword = $request->newPassword;
            $confirmPassword = $request->confirmPassword;
            if (Hash::check($currentPassword, $users->password)) {
                // $user = Users:-:find($id);
                $users->password = bcrypt($confirmPassword);
                $users->save();
                return redirect()->back()->with('alert', 'Password change successfully');
            } else {
                return redirect()->back()->with('alert', 'Invalid password');
            }
        }
    }

    // view forgot password page
    public function forgotPassword()
    {
        return view('forgotpassword');
    }

    // forgot password otp for user
    public function getOTP(Request $request)
    {
        $user = Users::where('contact', $request->mobile)->first();

        if ($user) {
            $email = $user->email;
            $mobile = $request->mobile;
            $password = rand(100000, 999999);

            $user->password = bcrypt($password);

            $result = $user->save();
            if ($result) {

                $title = 'Password reset confirmation';
                $content = "Dear User";
                $cred = "Your OTP for password reset is " . $password . ". Please do not share this OTP.";
                $tp = "Password - " . $password;
                $ps = "Regards,";
                $pt = "Team Guard Konnect";

                $smsMessage = $content . "\n" . $cred . "\n" . $ps . "\n" . $pt;

                $sms = new SMS;
                $msg91Response = $sms->SendSms2($smsMessage, $mobile);

                // \Mail::send('emails.sendss', ['title' => $title, 'content' => $content, 'cred' => $cred, 'tp' => $tp, 'ps' => $ps, 'pt' => $pt], function ($message) use ($email) {
                //     $message->to($email)->subject('PugMarg EMS Password Reseted');
                //     $message->from('pugmarg.airavat@gmail.com');
                // });

                return 'success';
            } else {
                return 'error';
            }
        } else {
            return 'error';
        }
    }

    //Verify OTP
    public function verifyOTP(Request $request)
    {
        $mobile = $request->mobile;

        if ($mobile) {
            $user = Users::where('contact', $mobile)->first();
            if ($user) {
                $OTP = $request->OTP;
                if (Hash::check($OTP, $user->password)) {
                    return 'success';
                } else {
                    return 'error';
                }
            } else {
                return 'error';
            }
        } else {
            return 'error';
        }
    }

    public function forgotPasswordAction($contact)
    {
        return view("forgotpasswordaction")->with("contact", $contact);
    }


    public function forgotPasswordSave(Request $request)
    {
      
        $users = Users::where('contact', $request->contact)->first();
        $id = Users::find($users->id);
        $id->password = bcrypt($request->confirmPassword);
        $id->save();
        return redirect('/');
    }

    public function userDetails(Request $request)
    {
        $output = '';
        $users = Users::where('id', $request->id)->get();

        foreach ($users as $row) {
            $output = '  
                <p><img src="' . $row["profile_pic"] . '" class="image-style" /></p>  
                <p><label>Name : </label><br />' . $row['name'] . '</p>  
                <p><label>Address : </label><br />' . $row['address'] . '</p>  
                <p><label>Gender : </label>' . $row['gender'] . '</p>  
           ';
        }
        echo $output;
    }
}
