<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Log;
use App\Notification;
use App\Role;
use App\Task;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class TaskController
 *
 * @package App\Http\Controllers\v1
 */
class TaskController extends Controller
{

    protected $assign_old_value;

    protected $assign_new_value;

    protected $status_old_value;

    protected $status_new_value;

    /**
     * Get tasks list
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll()
    {
        try {
            $user = $this->validateSession();

            if ($user->role_id === Role::ROLE_USER) {
                $tasks = Task::where('assign', $user->id)->with('user','assign')->paginate(5);
            } else {
                $tasks = Task::with('user','assign')->paginate(5);
            }

            return $this->returnSuccess($tasks);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    public function getAllUserAssignedTasks()
    {
        try {
            $user = $this->validateSession();

            $tasks = Task::where('assign' , $user->id)->where('status',Task::STATUS_ASSIGNED)->with('user','assign')->paginate(5);

            return $this->returnSuccess($tasks);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    public function getAllUserInProgressTasks()
    {
        try {
            $user = $this->validateSession();

            $tasks = Task::where('assign' , $user->id)->where('status','>',Task::STATUS_ASSIGNED)->where('status','<',Task::STATUS_DONE)->with('user','assign')->paginate(5);

            return $this->returnSuccess($tasks);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    public function getAllUserTasks()
    {
        try {
            $user = $this->validateSession();

            $tasks = Task::where('assign' , $user->id)->with('user','assign')->paginate(5);

            return $this->returnSuccess($tasks);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    /**
     * Create a task
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        try {
            $user = $this->validateSession();

            $rules = [
                'name' => 'required',
                'description' => 'required',
                'assign' => 'required|exists:users,id'
            ];

            $validator = Validator::make($request->all(), $rules);

            if (!$validator->passes()) {
                return $this->returnBadRequest('Please fill all required fields');
            }

            $task = new Task();

            $task->name = $request->name;
            $task->description = $request->description;
            $task->status = Task::STATUS_ASSIGNED;
            $task->user_id = $user->id;
            $task->assign = $request->assign;

            $task->save();

            $notification = new Notification();

            $notification->user_id = $request->assign;
            $notification->task_id = $task->id;
            $notification->message = 'The '. $task->name .' task has been assigned to you';

            $notification->save();

            return $this->returnSuccess();
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    /**
     * Update a task
     *
     * @param Request $request
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $user = $this->validateSession();

            $task = Task::find($id);

            if ($user->role_id === Role::ROLE_USER && $user->id !== $task->assign) {
                return $this->returnError('You don\'t have permission to update this task');
            }

            if ($request->has('name')) {
                $task->name = $request->name;
            }

            if ($request->has('description')) {
                $task->description = $request->description;
            }

            if ($request->has('status')) {
                $this->status_old_value = $this->statusName($task->status);
                $this->status_new_value = $this->statusName($request->status);
                $task->status = $request->status;
            }

            if ($request->has('assign')) {
                $user2 = User::where('id',$task->assign)->first();
                if(! $user2)
                {
                    $this->assign_old_value = 'Unknown';
                }else {
                    $this->assign_old_value = $user2->name;
                }
                $user2 = User::where('id',$request->assign)->first();
                $this->assign_new_value = $user2->name;
                $task->assign = $request->assign;
            }

            $task->save();


            if($this->assign_new_value!= $this->assign_old_value)
            {

                Log::create([
                    'task_id' => $task->id,
                    'user_id' => $user->id,
                    'type' => Log::TYPE_ASSIGNED,
                    'old_value' => $this->assign_old_value,
                    'new_value' => $this->assign_new_value
                ]);

                Notification::create([
                    'user_id' => $task->assign,
                    'task_id' => $task->id,
                    'message' => 'The '. $task->name .' task has been assigned to you'
                ]);
            }

            if($this->status_old_value != $this->status_new_value)
            {
                Log::create([
                    'task_id' => $task->id,
                    'user_id' => $user->id,
                    'type' => Log::TYPE_STATUS,
                    'old_value' => $this->status_old_value,
                    'new_value' => $this->status_new_value
                ]);
            }

            return $this->returnSuccess();
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }


    /**
     * Delete a task
     *
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        try {
            $user = $this->validateSession();

            if ($user->role_id !== Role::ROLE_ADMIN) {
                return $this->returnError('You don\'t have permission to delete this task');
            }

            $task = Task::find($id);

            $task->delete();

            return $this->returnSuccess();
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }
}