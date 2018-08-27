<?php
/**
 * Created by PhpStorm.
 * User: mihai
 * Date: 8/25/2018
 * Time: 6:02 AM
 */

namespace App\Http\Controllers\v1;


use App\Http\Controllers\Controller;
use App\Log;
use App\Role;


class LogController extends Controller
{
    public function getAll()
    {
        try {
            $user = $this->validateSession();

            if($user->role_id !== Role::ROLE_ADMIN)
            {
                return $this->returnError('You don\'t have permission to get tasks log');
            }

            $log = Log::with('user','task')->paginate(10);

            return $this->returnSuccess($log);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }

    }
}