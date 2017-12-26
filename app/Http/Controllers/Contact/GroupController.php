<?php

namespace App\Http\Controllers\Contact;

use App\Group;
use App\Contactgroup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
      protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',

             ]);
    }

    public function store(Request $request)
   {
	 $this->validator($request->all())->validate();
	 $group = Group::create($request->all());

	 return response()->json($group,201);
   }
 public function assign(Request $request)
   {
//	 $this->validator($request->all())->validate();
	 $assign = Contactgroup::create($request->all());

	 return response()->json($assign,201);
   }


      public function show($userid)
   {
		//$group = Group::Find($contact);

		$group = DB::table('contactgroups')
            ->rightjoin('groups', 'groups.id', '=', 'contactgroups.group_id')
            ->leftjoin('contacts', 'contacts.id', '=', 'contactgroups.contact_id')
            ->where('groups.user_id',$userid)
            ->select('groups.id', 'groups.name',DB::raw('count(contactgroups.contact_id) as aggregate'))
            ->groupby('groups.id','groups.name')
            ->get();

	return response()->json($group,200);
   }
    
}
