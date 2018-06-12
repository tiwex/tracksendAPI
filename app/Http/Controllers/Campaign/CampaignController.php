<?php

namespace App\Http\Controllers\Campaign;

use App\Campaign;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CampaignController extends Controller
{

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
        ]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
     $this->validator($request->all())->validate();
   /* $url=$request->input('url');
     $name=
     $token= return substr(md5(uniqid(mt_rand(), true)), 0, 7);
     $array= array("name"=>$name, "user_id"=>$user_id,"provider_id"=>$provider_id,"sender_id"=>$sender_id,"channel_id"=>$channel_id
        ,"status"=>$status,"url"=>$url,"token"=>$token);*/
     $campaign = Campaign::create($request->all());

 

     //confirm if messgae will be schedule,saved or sent now
     //if meesage statu is set to sent put message in a mesage queue for sending
     //if message status is et to scheudle put in a message queueu
     return response()->json($campaign,201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\campaign  $campaign
     * @return \Illuminate\Http\Response
     */
    public function show($userid)
    {
        //getsentcampaign,get contacts/recepient message status , 
        //getdraftandschedule campaign
      /*$campaign=DB::table('campaigns')->
      where('user_id', $userid)->get();*/
      $campaign=DB::table('campaigns')
      //->leftjoin('messages', 'messages.campaign_id', '=', 'campaigns.id')
      ->select('*',DB::raw('(select count(*) from messages m where m.campaign_id = campaigns.id) as cnt'))
      ->where('campaigns.user_id',$userid)
      ->get();
      return response()->json($campaign,201);
    }
    public function showbyid($userid,$campaign_id)
    {
        //getsentcampaign,get contacts/recepient message status , 
        //getdraftandschedule campaign
      /*$campaign=DB::table('campaigns')->
      where('user_id', $userid)->get();*/
      $campaign=DB::table('campaigns')
      //->leftjoin('messages', 'messages.campaign_id', '=', 'campaigns.id')
      ->select('*',DB::raw('(select count(*) from messages m where m.campaign_id = campaigns.id) as cnt'))
      ->where([['campaigns.user_id',$userid],['campaigns.id',$campaign_id]])
      ->first();
      return response()->json($campaign,201);
    }
    public function pricecalculator(campaign $campaign)
    {
        //getsentcampaign,get contacts/recepient message status , 
        //getdraftandschedule campaign
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\campaign  $campaign
     * @return \Illuminate\Http\Response
     */
    public function edit(campaign $campaign)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\campaign  $campaign
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, campaign $campaign)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\campaign  $campaign
     * @return \Illuminate\Http\Response
     */
    public function destroy(campaign $campaign)
    {
        //
    }
}
