<?php


namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User; 
use App\ActivityLog;
class LoginController extends Controller
{


    // public function __construct()
	// {
	// 	$this->middleware('guest:admin')->except('logout');
	// }
    private $folder = "admin.";
	private $routeprefix = "admin.";

    public function login(Request $request){

        // dd($request);
        $user = User::where('email',$request->email)->first();
        // dd($user);
        $request->session()->put('user', $user);
        
        if($request != ''){
            return redirect()->route('admin.dashboard');
        }
        else{
            return Redirect()->back()->withErrors(['errors'=>"Password doesn't match,Please try again."]);
        }

    //    return view('test');

    // $validate = Validator::make($request->all(), [
    //     'email' => 'required|exists:admins',
    //     'password' => 'required'
    // ],
    // [
    //     'email.exists' => "Email is not exists.",
    //     'email.required' => 'The Email is required.',
    //     'email.email' => 'Please enter valid email.',
    // ]);
    
    // if($validate->fails()){
    //     return Redirect()->back()->with([
    //                         'status'=>false,
    //                         'errors'=>$validate->errors()
    //                     ]);
    // }

    // if (Auth::guard()->attempt(['email' => $request->email, 'password' => $request->password], $request->get('remember_token'))) {

  
    // }
    // return Redirect()->back()->withErrors(['errors'=>"Password doesn't match,Please try again."]);


    }

    public function logout(Request $request)
	{
        // dd('hiii..');
		Auth::guard()->logout();
		return redirect()->route('base_login')->withErrors(['msg'=>'Logout Successfuly.']);
	}
}
