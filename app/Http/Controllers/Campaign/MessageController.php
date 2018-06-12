<?php

namespace App\Http\Controllers\Campaign;

use App\Message;
use App\Campaign;
use App\Message_transaction;
use App\User;
use App\Transaction;
use App\Send_group ;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
       $url_id =$request->input('short_url');
       $campaign = Campaign::where('id',$campaign_id)->first();
       $campaign->recepient=implode(",",$recepient);
       $campaign->sender_id=$sender;
       $campaign->save();
       $balance=$this->checkbalance($user_id);
       $price_credit= DB::table('users')
       ->where('id', $user_id)
       ->value('price_credit');

       $sender_name= DB::table('senders')
       ->where([['id', $sender],['user_id',$user_id]])
       ->value('name');      

       if (empty($price_credit))
       {

         $price_credit= DB::table('settings')
         ->value('price_credit');
        }


     foreach ( $messages as $value)
     {
        $date=$value['scheduled_at'];
        if (!empty( $date)) $date=Carbon::createFromFormat('Y-m-d H:i:s', $date);
         $array= array("user_id"=>$user_id,"campaign_id"=>$campaign_id,"type"=>$value['type'],"message"=>$value['message']
            ,"is_clicked"=>$value['is_clicked'],"scheduled_at"=>$date);
         $message[] = Message::create($array);        
     }

     
     $msg=DB::table('messages')->where([['campaign_id', $campaign_id],['type',0]])->first();
     $totalrate=$this->totalcredit($groups,$recepient,$sender,$user_id,$msg->message);
     $contacts=array();
     $send_groups=array();
     $amount= $totalrate * $price_credit;

     $contacts = DB::table('contactgroups')
     ->join('contacts', 'contactgroups.contact_id', '=', 'contacts.id')
     ->whereIn('contactgroups.group_id', $groups)
     ->where('contactgroups.user_id',$user_id)
     ->select('contacts.*')
     ->get();
     foreach ($groups as $value)
     {
        $array=array('user_id'=>$user_id,'campaign_id'=>$campaign_id,
            'group_id'=>$value);
        $send_groups[] = Send_group::create($array);
    }



          // $send_groups[] = Send_group::create($array);

    $status=array();
    $pcontacts=array();
   // $pcontact="23480".$balance.$totalrate;
    $channel=$campaign->channel_id;
    $status=$campaign->status;
    $m_status=array();

    if ($channel==2 && $status==0 ) 
    {
       if  ($balance > $totalrate )
       {

        foreach($contacts as $val)
        {
            $array=array('user_id'=>$user_id,'campaign_id'=>$campaign_id,
                'contact_id'=>$val->id,'message_id'=>$msg->id,'recepient'=>"");
            $msg_transaction= Message_transaction::create($array);
            $pcontact=$val->phone;


            if (substr($pcontact,0,3) != "234")
            {
                $pcontact="234".substr($pcontact,1);
            } 
            $pcontacts[]=array("to"=>$pcontact,"messageId"=>$msg_transaction["id"]);
        }
        
        //$m_report=$this->getreport($msgid);

        foreach ($recepient as $value)
        {

            $pcontact=$value;
            if (substr($pcontact,0,3) != "234")
            {
                $pcontact="234".substr($pcontact,1);
            } 

            $array=array('user_id'=>$user_id,'campaign_id'=>$campaign_id,
                'contact_id'=>null,'message_id'=>$msg->id,'recepient'=>$pcontact);
            $msg_transaction= Message_transaction::create($array);
            $pcontacts[]=array("to"=>$pcontact,"messageId"=>$msg_transaction["id"]);
            $pmsg[]=array("to"=>$pcontact,"messageId"=>$msg_transaction["id"]);
        }   
    //$m_status="test";
        $schedule_at= Message::where([['campaign_id', $campaign->id],['user_id',$user_id],['type',0]])->value('scheduled_at');

            if (!empty( $schedule_at))
            {
           $schedule_at=Carbon::createFromFormat('Y-m-d H:i:s', $schedule_at)->format('Y-m-d').'T'.
           Carbon::createFromFormat('Y-m-d H:i:s', $schedule_at)->format('H:i:s').'.'.'000+01:00';
           $m_status=$this->sendsms($msg->message,$sender_name,$pcontacts,$schedule_at);  
       // $m_status=$schedule_at;
           $campaign->status=2;
           $campaign->save();
            }
        else
        {
             if (empty($url))
            {
                 $m_status=$this->sendmulsms($msg->message,$sender_name,$pcontacts);
            }
           $m_status=$this->sendsms($msg->message,$sender_name,$pcontacts);
           $campaign->status=1;
           $campaign->save();
        }

       $trans_id=uniqid('de_', true);
       $remark="SMS Send";
       $array=array("user_id"=>$user_id,"trans_id"=>$trans_id,"type"=>0,"remark"=>$remark,
         "credit"=>$totalrate,"amount"=>$amount);
       $transaction = Transaction::create($array);
         //deduct update the message _trasnaction table and transaction table
        //put in a queue ,save recors in message transaaction table

      /*  $array=array('user_id'=>$user_id,'campaign_id'=>$campaign_id,
        'contact_id'=>null,'message_id'=>$msg->id,'recepient'=>$value);
        $msg_transaction= Message_transaction::create($array);*/

       // $status =array("sent credit");
    } 
    elseIf ($balance < $totalrate)
    {
        $m_status =array("insufficient credit");
        $campaign->status=0;
        $campaign->save();

    }
   else
    {

       $m_status =array("in_draft"); 
       $campaign->status=0;
       $campaign->save();

   }
   
} 

$result=array("message"=>$message,"sender"=>$sender_name,"status"=>$m_status,"contacts"=>$pcontacts,"balance"=>$balance,"amount"=>$totalrate);



   //  $result=array($msg_transaction,$test);
return response()->json($result,201);
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
    protected function sendsms($message,$sender,$recepients,$scheduled_at="")
    {
        //$message,$sender,$recepient
        // $message="testing send";
       //$sender="SPACEBA";
        //$recepient=array("2348022881418");
       // $numbers=array("2348022881418","2348089357063");
       /* foreach ($recepient as $val)
        {
            $recepients[]=array("to"=>$val);
        
        }*/

        //$arr=array("from"=>$sender,"to"=>$recepient,"text"=>$message);
        $arr=array("from"=>$sender,"destinations"=>$recepients,"text"=>$message);
        if (!empty($scheduled_at))
        {
            $arr=array("from"=>$sender,"destinations"=>$recepients,"text"=>$message,"sendAt"=>$scheduled_at);
        }
        $arr1=array("messages"=>array($arr));
        $username="prowedge";
        $password="tjflash83";
        $header = "Basic " . base64_encode($username . ":" . $password);
        //$url = "https://api.infobip.com/sms/1/text/single";
        $url = "https://api.infobip.com/sms/1/text/advanced";
        $data_string = json_encode($arr1);
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL =>$url ,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $data_string,
          CURLOPT_HTTPHEADER => array(
            "accept: application/json",
            "authorization: ".$header,
            "content-type: application/json"
        ),
      ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

//$response=json_decode($response,true);

        curl_close($curl);

        if ($err) {
          return "cURL Error #:" . $err;
      } else {
        $result=json_decode($response,true);
        return $result;

    }
}
protected function sendmulsms( $message,$sender,$recepients,$url="",$scheduled_at="")
    {
        $message,$sender,$recepients,$url,$scheduled_at=""
        /* $message="testing send";
        $sender="SPACEBA";
        $recepient=array(array("to"=>"2348022881418","text"=>"grace makes it efforless"),array("to"=>"2348089357063","text"=>"works makes it stressful"));
        $scheduled_at="";
        $schedule_at=Carbon::now()->addDays(2)->format('Y-m-d').'T'.
          Carbon::now()->addDays(2)->format('H:i:s').'.'.'000+01:00';

        $n=0;*/
       // $numbers=array("2348022881418","2348089357063");
        foreach ($recepient as $val)
        {
          $n++;
           $arr[]=array("from"=>$sender,"to"=>$val["to"],"text"=>$val["text"]);
        
        }

        //$arr=array("from"=>$sender,"to"=>$recepient,"text"=>$message);
        //$arr=array("from"=>$sender,"destinations"=>$recepients,"text"=>$message);
      /*  if (!empty($scheduled_at))
        {
            $arr=array("from"=>$sender,"destinations"=>$recepients,"text"=>$message,"sent_At"=>$scheduled_at);
        }*/
        $arr1=array("messages"=>$arr);
        $username="prowedge";
        $password="tjflash83";
        $header = "Basic " . base64_encode($username . ":" . $password);
        //$url = "https://api.infobip.com/sms/1/text/single";
        $url = "https://api.infobip.com/sms/1/text/multi";
        $data_string = json_encode($arr1);
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL =>$url ,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $data_string,
          CURLOPT_HTTPHEADER => array(
            "accept: application/json",
            "authorization: ".$header,
            "content-type: application/json"
        ),
      ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

//$response=json_decode($response,true);

        curl_close($curl);

        if ($err) {
          return "cURL Error #:" . $err;
      } else {
        $result=json_decode($response,true);
        //$result=json_encode($arr1);
        //$result= $schedule_at;
        return $result;

    }
}
protected function getreport($msg_id)
{
        //
        //$arr=array("from"=>"spaceba","to"=>array("2348022881418","2348089357063"),"text"=>"test SMS");
    $username="prowedge";
    $password="tjflash83";
    $header = "Basic " . base64_encode($username . ":" . $password);
    $url = "https://api.infobip.com/sms/1/reports?messageId=".$msg_id;
       // $url = "https://api.infobip.com/sms/1/reports?limit=2";
      // $url = "https://api.infobip.com/sms/1/reports";
       // $data_string = json_encode($arr);
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL =>$url ,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
  //CURLOPT_POSTFIELDS => $data_string,
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
      return "cURL Error #:" . $err;
  } else {

     echo $response; 
 //$response=json_decode($response);
 //print_r($response);
 //Storage::disk('local')->put('file.txt', $response);

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
public function totalcredit($group,$recepient,$sender,$user_id,$message)
{
        //$group,$recepient,$sender,$user_id,$message
      /* $group=array(3,4);
        $recepient=array('08022881418','08089357063');
        $sender=1;
        $user_id=6;
        $message="A com ";*/
        $mlen=strlen($message);
        if ($mlen <=160)
           $mcount= 1;
       else
       {
        $mlen=$mlen-160;
        $mcount=ceil($mlen/153);
        $mcount=$mcount+1;

    }

     //  $groups=implode(",",$group);

    $contacts = DB::table('contactgroups')
    ->join('contacts', 'contactgroups.contact_id', '=', 'contacts.id')
    ->join('groups', 'contactgroups.group_id', '=', 'groups.id')
    ->whereIn('contactgroups.group_id', $group)
    ->where('contactgroups.user_id',$user_id)
    ->select('contacts.phone')
    ->get();

    $ccredit=array();
    $rcredit=array();
      // $sum=sizeof($contacts);
       //$r_credit=array();
      // $rcredit=0;

    foreach ($contacts as $value)
    {
       $ccredit[]=$this->countcredit($value->phone,$sender); 
         //$sum++;
   }
   foreach ($recepient as $value)
   {
       $rcredit[]=$this->countcredit($value,$sender);   
   }
   $total=array_sum($ccredit) + array_sum( $rcredit);
   $total=$total * $mcount;

   return $total;
   
}

public function checkbalance($user_id)
{
        //
        //$user
 $credit= DB::table('transactions')
 ->where([['user_id', $user_id],['type',1]])
 ->sum('credit');
 $debit= DB::table('transactions')
 ->where([['user_id', $user_id],['type',0]])
 ->sum('credit');
 $balance  = $credit-$debit;  
if (empty($balance)) $balance=0;

 return $balance;




}
public function countcredit($number,$sender)
{
       /* $campaign_id=18;
        $msg=DB::table('messages')->where([['campaign_id', $campaign_id],['type',0]])->first();
        echo $msg->message;*/

        $is_verified= DB::table('senders')->where('id', $sender)->value('is_verified');
        $number='%'.substr($number,0,4).'%';
        
        if ($is_verified == 1)
        {
            $count=DB::table('rates')
            ->where([
                ['type', '=', 1],
                ['number_set', 'like', $number],
            ])
            ->value('credit');
            
        }
        else 
        {
            $count=DB::table('rate')
            ->where([
                ['type', '=', 0],
                ['number_set', 'like', $number],
            ])
            ->value('credit');
        }
        
        return $count;
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\message  $message
     * @return \Illuminate\Http\Response 
     */

    public function testarray()
    {
       /* $json='{"results":[{"bulkId":"2233276043823536966","messageId":"2233276043823536967","to":"2348022881418",
            "from":"SPACEBA","sentAt":"2018-04-10T02:33:24.377+0000",
            "doneAt":"2018-04-10T02:33:28.896+0000","smsCount":1,"mccMnc":"null",
            "price":{"pricePerMessage":1.1200000000,"currency":"NGN"},
            "status":{"groupId":3,"groupName":"DELIVERED","id":5,"name":"DELIVERED_TO_HANDSET"
                ,"description":"Message delivered to handset"},
                "error":{"groupId":0,"groupName":"OK","id":0,"name":"NO_ERROR","description":"No Error",
            "permanent":false}}]}';
        
        $json=json_decode($json,true);
        print_r($json);*/

        $date='2018-04-18 16:18:50';
        $date1=Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('Y-m-d');
        $time=Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('H:i:s');
        echo $date1.'T'.$time.'.'.'000+01:00';
    }
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
