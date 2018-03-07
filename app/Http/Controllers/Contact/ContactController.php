<?php

namespace App\Http\Controllers\Contact;
use App\Contact;
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
            'phone' => 'required|string|max:255',
            'email' => 'string|email|max:255',
            
        ]);
    }

    public function store(Request $request)
   {
	 $this->validator($request->all())->validate();
	 $contact = Contact::create($request->all());
	 return response()->json($contact,201);
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
