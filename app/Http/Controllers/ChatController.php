<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Storage;
use App\User;
use App\Group;
use App\UserGroups;
use App\Conversations;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{

    public function index()
    {
        $user = session('user');
        $chatUsers = Conversations::where('creator_id', '=', $user->id)->orderBy('last_message_date', 'DESC')->get();
        $groupMembers = User::where('id', '!=', $user->id)->where('company_id',$user->company_id)->orderBy('name','asc')->get();
        $creatorName = User::where('contact', $user->contact)->where('company_id',$user->company_id)->first();
        $newUsers = User::where('id', '!=', $user->id)->where('company_id',$user->company_id)->orderBy('name','asc')->get();
        $groups = UserGroups::leftjoin('groups', 'user_groups.group_id', '=', 'groups.id')->where('user_id', $user->id)->get();
        return view('chat')->with('person1', $user->id)->with('creatorName', $creatorName->name)->with('chatUsers', $chatUsers)->with('newUsers', $newUsers)->with('groupMembers',$groupMembers)->with('groups',$groups);
    }

    public function singleChat($person1, $person2, $person1Name)
    {
        $user = session('user');
        $chatUsers = User::where('id', '!=', $person1)->where('company_id',$user->company_id)->get();
        $groups = UserGroups::leftjoin('groups', 'user_groups.group_id', '=', 'groups.id')->where('user_id', $person1)->get();
        
        $messages = DB::table('messages')->where('creator_id', $person1)->where('receiver_id', $person2)->orWhere('creator_id', $person2)->where('receiver_id', $person1)->get();
        $creatorName = User::where('id', $person1)->where('company_id',$user->company_id)->first();
        $recieverName = User::where('id', $person2)->where('company_id',$user->company_id)->first();
        return view('chat')->with('person1', $person1)->with('person2', $person2)->with('name', $person1Name)->with('creatorName', $creatorName->name)->with('messages', $messages)->with('recieverName', $recieverName->name)->with('chatUsers', $chatUsers)->with('groups', $groups);
    }

    public function createGroup(Request $request)
    {

        $groupData = $request->all();
        $groupData['socket_id'] = uniqid();
        $groupData['name'] = $request->name;
        $group = Group::create($groupData);
        $user_id = $request->allValArr;
        $userData = User::whereIn('id', $user_id)->get();
        foreach ($userData as $key => $value) {
            $data = [
                'user_id' => $value['id'],
                'user_name' => $value['name'],
                'group_id' => $group->id

            ];
            $userGroup = UserGroups::create($data);

            $data1 = [
                'name' => $value['name'],
                'creator_id' => $value['id'],
                'receiver_id' => $group->id,
                'last_message' => $group->id,
                'last_message_date' => $group->id,
                'last_message_by' => $group->id,
                'type' => 1,
                'socket_id' => $groupData['socket_id'],


            ];
            $conversation = Conversations::create($data1);
        }
        return response()->json([
            'groupId' => $group->id,
            'socket_id' => $group->socket_id,
            'person1' => $request->person1,
            'name' => $request->name,
        ]);
    }

    public function groupDetails(Request $request)
    {

        $group = Group::where('id', $request->groupId)->first();
        $groupMessages = DB::table('messages')->where('group_id', $request->groupId)->get();
        // dd($groupMessages);
        return response()->json([
            'groupDetails' => $group,
            'groupMessages' => $groupMessages
        ]);
    }

    public function singleChatDetails(Request $request)
    {
        $receiverName = User::where('id', $request->person2)->first();
        $messages = DB::table('messages')->where('creator_id', $request->person1)->where('receiver_id', $request->person2)->orWhere('creator_id', $request->person2)->where('receiver_id', $request->person1)->get();
        return response()->json([
            'messages' => $messages,
            'receiverDetails' => $receiverName
        ]);
    }

    public function uploadFile(Request $request)
    {

        $filesss = $request->file('imgupload');
        if ($request->hasFile('imgupload')) {
            $path = [];
            foreach ($filesss as $file) {
                // $originalName = $file->get('imgupload')->getClientOriginalName();
                $originalName = $file->getClientOriginalName();

                $fileName = preg_replace('/\s+/', '', $originalName);
                $file->storeAs('upload', $fileName);
                $path[] = "http://192.168.0.123/bhagyashri/laravel-rdb/storage/app/upload/" . $fileName;
            }
            return response()->json([
                'success' => 'success',
                'url' => $path,
                'fileName' => $fileName,

            ]);
        }
    }

    public function downloadFile($fileName)
    {
        return Storage::disk('upload')->download($fileName);
    }
}
