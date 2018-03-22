<?php

namespace App\Http\Controllers\Contact;
use App\Contact;
use App\Contactgroup;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    //
        protected function validator(array $data)
    {
        return Validator::make($data, [
            'contact.phone' => 'required|string|max:255',
            
        ]);
    }

    public function store(Request $request)
   {
     //s$this->validator($request->all())->validate();

   $user_id=$request->input('user_id');
      /* $phone=$request->input('phone');
     $email=$request->input('email');
     $fname=$request->input('fname');
     $lname=$request->input('lname');
     $lname=$request->input('name');
     $country=$request->input('country');
     $state=$request->input('state');
     $city=$request->input('city');*/
     
     $contact=$request->input('contact');
     $group=$request->input('group');
     $i=0;
    
     foreach ( $contact as $value)
     {
         $array=array("user_id" => $user_id,"phone"=>$value['phone'],"email"=>$value['email'],"fname"=>$value['fname'],
         "lname"=>$value['lname'],"name"=>$value['name'],"country"=>$value['country']
         ,"state"=>$value['state'],"city"=>$value['city']);

        $contacts[] = Contact::create($array);

        if (!empty($group))
      {
        foreach ( $group as $value)
        {
            $array_group=array("user_id"=>$user_id,"contact_id"=> $contacts[$i]->id,"group_id"=>$value);
            $contact_group[]=Contactgroup::create($array_group);
        }
        $i++;
       
} else  $contact_group="empty group";
     }

    // print_r($contact);
    // print_r($contact_group);
    // $contact = Contact::create($request->all());
    $final=array("contact"=>$contacts,"group"=>$contact_group);
	 return response()->json($final,201);
   }

    public function bulk(Request $request)
   {
   $this->validator($request->all())->validate();

    $contacts = $request->all();;

   $contacts= DB::table('users')->insert($contacts);
   return response()->json($contacts,201);
   }

      public function show($userid)
   {
    $contact = Contact::where('user_id',$userid) 
               ->orderby('created_at','desc')
               ->get();

    return response()->json($contact,200);
   }
   public function showbygroup($groupid,$user_id)
   {
//$contact = Article::Find($contact);

$contact = DB::table('contactgroups')
            ->join('groups', 'groups.id', '=', 'contactgroups.group_id')
            ->join('contacts', 'contacts.id', '=', 'contactgroups.contact_id')
            ->where([['groups.id',$groupid],['groups.user_id',$user_id]])
            ->select('contacts.*','groups.id as group_id', 'groups.name as group_name','groups.description as group_description')
            ->get();

return response()->json($contact,200);
   }
}
