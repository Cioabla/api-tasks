<?php
/**
 * Created by PhpStorm.
 * User: mihai
 * Date: 8/27/2018
 * Time: 5:54 PM
 */

namespace App\Http\Controllers\v1;


use App\Http\Controllers\Controller;
use App\Notification;

class NotificationController extends Controller
{
    public function userNotification()
    {
        try {
            $user = $this->validateSession();

            $notification = Notification::where('user_id',$user->id)->get();
            $total = Notification::where('user_id',$user->id)->count();

            $data = [
                'data' => $notification,
                'total' => $total
            ];

            return $this->returnSuccess($data);

        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $user = $this->validateSession();

            $notification = Notification::find($id);

            if($user->id !== $notification->user_id)
            {
                return $this->returnError('You don\'t have permission to delete this notification');
            }

            $notification->delete();

            return $this->returnSuccess();

        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }


    }
}