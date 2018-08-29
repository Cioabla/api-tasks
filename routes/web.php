<?php

define("API_VERSION", 'v1');
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version() . ' - ' . 'Current API version: ' . API_VERSION;
});

/** CORS */
$router->options(
    '/{any:.*}', [
    'middleware' => ['cors'],
    function () {
        return response('OK', 200);
    }
]);

/** Routes that doesn't require auth */
$router->group(['namespace' => API_VERSION, 'prefix' => API_VERSION, 'middleware' => 'cors'], function () use ($router) {
    $router->post('/login', ['uses' => 'UserController@login']);
    $router->post('/register', ['uses' => 'UserController@register']);
    $router->post('/forgot-password', ['uses' => 'UserController@forgotPassword']);
    $router->post('/change-password', ['uses' => 'UserController@changePassword']);
});

/** Routes with auth */
$router->group(['namespace' => API_VERSION, 'prefix' => API_VERSION, 'middleware' => 'cors|jwt'], function () use ($router) {

    $router->get('/users', ['uses' => 'UserController@getAllUsersName']);
    $router->group(['prefix' => 'user'], function () use ($router) {
        $router->get('/', ['uses' => 'UserController@get']);
        $router->patch('/', ['uses' => 'UserController@update']);
    });

    $router->group(['prefix' => 'admin', 'middleware' => 'admin'], function () use ($router) {

        $router->group(['prefix' => 'users'], function () use ($router) {
            $router->get('/', ['uses' => 'AdminController@getAllUsers']);
            $router->get('/users', ['uses' => 'AdminController@getUsers']);
            $router->get('/admins', ['uses' => 'AdminController@getAdmins']);
            $router->get('/inactive', ['uses' => 'AdminController@getInactiveUsers']);
            $router->get('/active', ['uses' => 'AdminController@getActiveUsers']);
        });

        $router->group(['prefix' => 'user'], function () use ($router) {
            $router->post('/', ['uses' => 'AdminController@createUser']);
            $router->patch('/{id}', ['uses' => 'AdminController@updateUser']);
            $router->delete('/{id}', ['uses' => 'AdminController@deleteUser']);
        });
    });


    $router->group(['prefix' => 'tasks'], function () use ($router) {
        $router->get('/', ['uses' => 'TaskController@getAll']);
        $router->get('/user-tasks', ['uses' => 'TaskController@getAllUserTasks']);
        $router->get('/user-assigned-tasks', ['uses' => 'TaskController@getAllUserAssignedTasks']);
        $router->get('/user-in-progress-tasks', ['uses' => 'TaskController@getAllUserInProgressTasks']);
    });
    $router->group(['prefix' => 'task'], function () use ($router) {
        $router->post('/', ['uses' => 'TaskController@create']);
        $router->patch('/{id}', ['uses' => 'TaskController@update']);
        $router->delete('/{id}', ['uses' => 'TaskController@delete']);
    });

    $router->group(['prefix' => 'logs'], function () use ($router) {
        $router->get('/', ['uses' => 'LogController@getAll']);
    });

    $router->group(['prefix' => 'notification'], function () use ($router) {
        $router->get('/', ['uses' => 'NotificationController@userNotification']);
        $router->delete('/{id  }', ['uses' => 'NotificationController@delete']);
    });

    $router->group(['prefix' => 'group'], function () use ($router) {
        $router->get('/', ['uses' => 'GroupController@get']);
        $router->post('/', ['uses' => 'GroupController@create']);
        $router->patch('/{id}', ['uses' => 'GroupController@update']);
        $router->delete('/{id}', ['uses' => 'GroupController@delete']);
        $router->put('/{id}', ['uses' => 'GroupController@addMember']);
        $router->delete('/{id}/member', ['uses' => 'GroupController@deleteMember']);
    });
});