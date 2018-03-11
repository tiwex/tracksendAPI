<?php

namespace App\Http\Controllers\Campaign;

use App\Message;
use App\Campaign;
use App\Message_Transaction;
use App\User;
use App\Send_group;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     protected function validator(array $data)
    {
        return Validator::make($data, [
              'type' => 'required|integer',
             'message' => 'required|string'
            
        ]);
    }
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
        //
        // $this->validator($request->all())->validate();
        
         $user_id=$request->input('user_id');
         $campaign_id=$request->input('campaign_id');
         $messages= $request->input('messages');
         $groups =$request->input('group');
         $sender =$request->input('sender_id');
         $recepient =$request->input('recepient');
         $is_sent =$request->input('is_sent');
         $url_id =$request->input('url_id');
        /* foreach ( $recepient as $value)
         {
            $array[]= array('campaign_id'=>$campaign,'type'=>$type,'message'=>$message,'recepient'=>$value);
         }*/
         //'type','message','is_sent','is_clicked','schedule_at
        $campaign = Campaign::where('id',$campaign_id)->first();
        // $campaign_update=Campaign::find($campaign_id);
        $campaign->recepient=implode(",",$recepient);
        $campaign->sender_id=$sender;
        $campaign->save();
        $totalrate=$this->totalcredit($groups,$recepient,$sender);
        $balance=$this->checkbalance($user_id);
         foreach ( $groups as $value)
         {
            $array=array("user_id"=>$user_id,"campaign_id"=>$campaign_id,"group_id"=>$value);
            $send_groups[] = Send_group::create($array);
         }

  if ($campaign->channel_id==2 and $campaign->status==0) 
  {
         if  ($balance > $totalrate )

      {
       
        //put in a queue ,save recors in message transaaction table
        $status =array("sent"=>true);     
      
    }
    elseIf ($balance < $totalrate)
    {
        $status =array("sent"=>"insufficient credit");
   }
    else
    {
       
         $status =array("sent"=>"in_draft");    
    }
    foreach ( $messages as $value)
    {
       
       $array= array('user_id'=>$user_id,'campaign_id'=>$campaign_id,'type'=>$value['type'],'message'=>$value['message']
                    ,'is_clicked'=>$value['is_clicked'],'scheduled_at'=>$value['scheduled_at']);
   
     $message[] = Message::create($array);
   }
  }
  
  
    
     $message=array("message"=>$message,"campaign"=>$campaign,"status"=>$status,"send_group"=>$send_groups);
         
         
        
     
         return response()->json($message,201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\message  $message
     * @return \Illuminate\Http\Response
     */
    public function show(message $message)
    {
        //
    }
    public function sendsms(Message $message)
    {
        //
        $username="thinktech";
        $password="Tjflash8319#";
        $header = "Basic " . base64_encode($username . ":" . $password);
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "http://api.infobip.com/sms/1/text/single",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => "{ \"from\":\"Spaceba\", \"to\":[\"2348089357063,2348022881418\"], \"text\":\"Test SMS.\" }",
  CURLOPT_HTTPHEADER => array(
    "accept: application/json",
    "authorization: ".$header,
    "content-type: application/json"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}
    }
    public function calculaterate($campaign_id,$user_id)
    {
        //
        $rate = array ("user"=>$user_id,"total_bill"=>500,"summary"=>array("MTN"=>100,"9mobile"=>400));
        return response()->json($rate,201);
    }
    public function deductcredit(Request $request)
    {
        //

        $deduct = array ("user"=>$request->user_id,"balance"=>300,"credit_deducted"=>100,"status"=>true,"cammapign"=>$request->campaign_id);
        return response()->json($deduct,201);
    }
    public function totalcredit($group,$recepient,$sender)
    {
        //
        $rate = 500;
        return $rate;
    }
    public function checkbalance($user)
    {
        //
        $balance = 5000;
        return $balance;
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\message  $message
     * @return \Illuminate\Http\Response
     */
    public function edit(message $message)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\message  $message
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, message $message)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\message  $message
     * @return \Illuminate\Http\Response
     */
    public function destroy(message $message)
    {
        //
    }
}
