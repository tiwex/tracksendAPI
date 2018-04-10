<?php

namespace App\Http\Controllers\Bill;

use Illuminate\Support\Facades\DB;
use App\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class TransactionController extends Controller
{
    //
    public function store(Request $request)
    {
        //
    $trans_id=uniqid('cr_', true);
    $user_id=$request->input('user_id');
    $type=$request->input('type');
    $remark=$request->input('remark');
    $amount=$request->input('amount');
 
  
   $price_credit= DB::table('users')
                     ->select('price_credit')
                     ->where('id', $user_id)
                     ->first();
               

    if (empty($price_credit->price_credit) )
    {
       
       $price_credit= DB::table('settings')
        ->select('price_credit')
        ->first();
    }
 $credit=ceil($amount/$price_credit->price_credit);
  $array=array("user_id"=>$user_id,"trans_id"=>$trans_id,"type"=>$type,"remark"=>$remark,
               "credit"=>$credit,"amount"=>$amount);
    $transaction = Transaction::create($array);
    return response()->json($transaction,201);
    }

    public function balance($user_id)
    {


   $credit= DB::table('transactions')
                     ->where([['user_id', $user_id],['type',1]])
                     ->sum('credit');
    $debit= DB::table('transactions')
                     ->where([['user_id', $user_id],['type',0]])
                     ->sum('credit');
     $balance  = $credit-$debit;  
  

    return response()->json($balance,201);
    }
}
