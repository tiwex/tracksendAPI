<?php

namespace App\Http\Controllers\Campaign;

use App\Message;
use App\Campaign;
use App\Message_transaction;
use App\User;
use App\Transaction;
use App\Send_group ;
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
         $url_id =$request->input('url_id');
         $msg=DB::table('messages')->where([['campaign_id', $campaign_id],['type',0]])->first();
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

    if (empty($price_credit) )
    {
       
       $price_credit= DB::table('settings')
        ->value('price_credit');
    }

  
        foreach ( $messages as $value)
    {
       
       $array= array("user_id"=>$user_id,"campaign_id"=>$campaign_id,"type"=>$value['type'],"message"=>$value['message']
                    ,"is_clicked"=>$value['is_clicked'],"scheduled_at"=>$value['scheduled_at']);
         $message[] = Message::create($array);        
   }

        
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
$pcontact="23480".$balance.$totalrate;
$channel=$campaign->channel_id;
$status=$campaign->status;
$m_status=array();
  if ($channel==2 && $status==0 ) 
  {
         if  ($balance > $totalrate )

      {
     
        foreach($contacts as $val)
        {
       /* $array=array('user_id'=>$user_id,'campaign_id'=>$campaign_id,
        'contact_id'=>$val->id,'message_id'=>$msg->id,'recepient'=>"");
        $msg_transaction[]= Message_transaction::create($array);*/
        $pcontact=$val->phone;
        if (substr($pcontact,0,3) != "234")
        {
            $pcontacts[]="234".substr($pcontact,1);
        } 
        else $pcontacts[]= $pcontact;
        }
        //$m_report=$this->getreport($msgid);
    
    foreach ($recepient as $value)
    {
       /* $array=array('user_id'=>$user_id,'campaign_id'=>$campaign_id,
        'contact_id'=>null,'message_id'=>$msg->id,'recepient'=>$value);
       $msg_transaction[]= Message_transaction::create($array);*/
        $pcontact=$value;
        if (substr($pcontact,0,3) != "234")
        {
            $pcontacts[]="234".substr($pcontact,1);
        } 
        else $pcontacts[]= $pcontact;
    }

   $m_status=$this->sendsms($msg->message,$sender_name,$pcontacts);
    $campaign->status=1;
    $campaign->save();
         
         $trans_id=uniqid('de_', true);
         $remark="SMS Send";
         $array=array("user_id"=>$user_id,"trans_id"=>$trans_id,"type"=>0,"remark"=>$remark,
               "credit"=>$totalrate,"amount"=>$amount);
            $transaction = Transaction::create($array);
         //deduct update the message _trasnaction table and transaction table
        //put in a queue ,save recors in message transaaction table
        $m_status =$m_status;     
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
  
  
    
    $result=array("message"=>$message,"sender"=>$sender_name,"status"=>$m_status,"contacts"=>$pcontacts,"balance"=>$balance);
         
         
       // $result=$status;
     
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
    protected function sendsms($message,$sender,$recepient)
    {
        //$message,$sender,$recepient
        // $message="testing send";
       //$sender="SPACEBA";
        //$recepient=array("2348022881418");
       // $numbers=array("2348022881418","2348089357063");
        foreach ($recepient as $val)
        {
            $recepients[]=array("to"=>$val);
        
        }
 
        //$arr=array("from"=>$sender,"to"=>$recepient,"text"=>$message);
        $arr=array("from"=>$sender,"destinations"=>$recepients,"text"=>$message);
        $arr1=array("messages"=>array($arr));
        $username="thinktech";
        $password="Tjflash8319#";
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
 print_r($result);
 
}
    }
 protected function getreport($msg_id)
    {
        //
        //$arr=array("from"=>"spaceba","to"=>array("2348022881418","2348089357063"),"text"=>"test SMS");
        $username="thinktech";
        $password="Tjflash8319#";
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
      
        return $balance;

        
      /*  $json1='{"messages":[{"to":"2348022881418","status":{"groupId":5,
            "groupName":"REJECTED","id":12,"name":"REJECTED_NOT_ENOUGH_CREDITS",
            "description":"Not enough credits"},"smsCount":1,"messageId":"2224685519820522881"}]}';
        $json='{"bulkId":"2224665520681630966","messages":[{"to":"2348022881418","status":{"groupId":5
            ,"groupName":"REJECTED","id":12,"name":"REJECTED_NOT_ENOUGH_CREDITS",
            "description":"Not enough credits"},"smsCount":1
            ,"messageId":"2224665520681630967"},{"to":"2348137094376","status":{"groupId":5,
            "groupName":"REJECTED","id":12,"name":"REJECTED_NOT_ENOUGH_CREDITS"
            ,"description":"Not enough credits"},"smsCount":1,"messageId":"2224665520691630968"}]}';
        $json=json_decode($json);
        //echo $json['messages'][0]['status'];
   //  print_r($json);
        echo "<br/><br/>";
            // var_dump($json);


        foreach($json->messages as $key=>$value)
        {
            //$bulkid[]=$value['bulkId'];
            $msg[]=$value->status->groupName;
            /*foreach($value as $key=>$value1)
            {
                $msg1=$value1;
            }
          
        }
    $msg_id="2225036286681630866";
$test=$this->getreport($msg_id);*/


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
            $count=DB::table('rates')
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
        $json='{"results":[{"bulkId":"2233276043823536966","messageId":"2233276043823536967","to":"2348022881418",
            "from":"SPACEBA","sentAt":"2018-04-10T02:33:24.377+0000",
            "doneAt":"2018-04-10T02:33:28.896+0000","smsCount":1,"mccMnc":"null",
            "price":{"pricePerMessage":1.1200000000,"currency":"NGN"},
            "status":{"groupId":3,"groupName":"DELIVERED","id":5,"name":"DELIVERED_TO_HANDSET"
                ,"description":"Message delivered to handset"},
                "error":{"groupId":0,"groupName":"OK","id":0,"name":"NO_ERROR","description":"No Error",
            "permanent":false}}]}';
        
        $json=json_decode($json,true);
        print_r($json);
        
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
