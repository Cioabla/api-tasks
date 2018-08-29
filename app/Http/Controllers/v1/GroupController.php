<?php
/**
 * Created by PhpStorm.
 * User: andra
 * Date: 29.08.2018
 * Time: 11:05
 */

namespace App\Http\Controllers\v1;


use App\Group;
use App\GroupMembers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GroupController extends Controller
{
    public function get()
    {
        try {
            $user = $this->validateSession();

            $ownerGroups = Group::where('user_id',$user->id)->get();
            $memberGroups = GroupMembers::where('user_id',$user->id)->with('group_find')->get();

            return $this->returnSuccess([
                'owner_groups' => $ownerGroups,
                'member_groups' => $memberGroups->pluck('group_find')
            ]);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    public function create(Request $request)
    {
        try {
            $rules = [
                'name' => 'required',
                'description' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);

            if (!$validator->passes()) {
                return $this->returnBadRequest('Please fill all required fields');
            }

            $user = $this->validateSession();

            $group = new Group();

            $group->user_id = $user->id;
            $group->name = $request->name;
            $group->description = $request->description;

            $group->save();

            return $this->returnSuccess();
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    public function update(Request $request, $id )
    {
        try {
            $user = $this->validateSession();
            $group = Group::find($id);

            if($user->id !== $group->user_id)
            {
                return $this->returnError('U are not authorized to update this group.');
            }

            if($request->has('name'))
            {
                $group->name = $request->name;
            }

            if($request->has('description'))
            {
                $group->description = $request->description;
            }

            $group->save();

            return $this->returnSuccess();
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $user = $this->validateSession();
            $group = Group::find($id);

            if($user->id !== $group->user_id)
            {
                return $this->returnError('U are not authorized to delete this group.');
            }

            $group->delete();

            return $this->returnSuccess();
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    public function addMember($id,Request $request)
    {
        try {
            $data = [];

            $user = $this->validateSession();
            $group = Group::find($id);

            $member = GroupMembers::where('group_id', $group->id)
                ->where('user_id',$user->id)
                ->first();

            if($user->id !== $group->user_id && !$member)
            {
                return $this->returnError('U are not authorized to add members in this group.');
            }

            foreach ($request->user as $userRequest)
            {
                $checkMember = GroupMembers::where('group_id', $group->id)
                    ->where('user_id',$userRequest)
                    ->first();

                if($checkMember)
                {
                    $data = array_merge_recursive($data,['error' => 'Member '. $checkMember->user->name .' exist in group.']);
                }else {
                    $newMember = new GroupMembers();

                    $newMember->group_id = $id;
                    $newMember->user_id = $userRequest;

                    $newMember->save();


                    $data = array_merge_recursive($data,['success' => 'Member is inserted']);
                }
            }
            return $this->returnSuccess($data);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    public function deleteMember($id,Request $request)
    {
        try {
            $data = [];

            $user = $this->validateSession();
            $group = Group::find($id);


            if($user->id !== $group->user_id)
            {
                return $this->returnError('U are not authorized to delete members from this group.');
            }

            foreach ($request->user as $userRequest)
            {
                $checkMember = GroupMembers::where('group_id', $group->id)
                    ->where('user_id',$userRequest)
                    ->first();

                if(!$checkMember)
                {
                    $data = array_merge_recursive($data,['error' => 'Member does not exist in the group.']);

                }else {
                    $newMember = GroupMembers::where('group_id', $group->id)
                        ->where('user_id',$userRequest)
                        ->first();

                    $newMember->delete();

                    $data = array_merge_recursive($data,['success' => 'Member is deleted']);
                }
            }
            return $this->returnSuccess($data);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }


}