<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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
    return $router->app->version();
});

// AUTHENTICATION
$router->post('/signin', 'AuthController@signIn');
$router->get('/signout', 'AuthController@signOut');

// ADMINISTRATOR
$router->group(['middleware' => 'admin'], function () use ($router) {
    $router->get('/getstudents', 'AdminController@getStudents');

    $router->post('/addparent', 'AdminController@addParent');
    $router->post('/banparent', 'AdminController@banParent');
    $router->post('/resetparent', 'AdminController@resetParent');

    $router->post('/addteacher', 'AdminController@addTeacher');
    $router->post('/addstudent', 'AdminController@addStudent');

    $router->post('/banteacher', 'AdminController@banTeacher');
    $router->post('/rehireteacher', 'AdminController@reHireTeacher');

    $router->get('/getprofiles', 'AdminController@getProfiles');
    
    $router->post('/addclass', 'AdminController@addClass');
    $router->post('/editclass', 'AdminController@editClass');
    $router->post('/delclass', 'AdminController@delClasses');
    $router->post('/rebuildclass', 'AdminController@rebuildClass');

    $router->post('/banstudent', 'AdminController@banStudent');
    $router->post('/resetstudent', 'AdminController@resetStudent');

    $router->get('/archivefiles', 'ItemController@archiveItems');
    $router->get('/delarchives', 'ItemController@delArchives');
    $router->get('/resetfiles', 'ItemController@resetFiles');
});

// TEACHERS
$router->group(['middleware' => 'teacher'], function () use ($router) {
    $router->post('/editteacher', 'AdminController@editTeacher');
    $router->get('/getclasses', 'AdminController@getClasses');
    $router->get('/getclasses/{userId}', 'AdminController@getClassesPerTeacher');
    $router->get('/getteachers', 'AdminController@getTeachers');
    $router->post('/additem', 'ItemController@addItem');
    $router->post('/delitem', 'ItemController@delItem');
    $router->post('/addlink', 'ItemController@addLink');
    $router->post('/edititem', 'ItemController@editItem');
});

// PARENTS
$router->group(['middleware' => 'parent'], function () use ($router){
    $router->get('/getstudents/{parentId}', 'ParentController@getStudents');
    $router->get('/getstudents', 'AdminController@getStudents');
    $router->get('/getparents', 'AdminController@getParents');
    $router->post('/editparent', 'ParentController@editParent');
    $router->post('/editstudent', 'ParentController@editStudent');
});

$router->get('/getitem/{classe}', 'ItemController@getItems');
$router->get('/download/{itemId}/{userId}', 'ItemController@downloadItem');
$router->post('/checkpwd', 'UserController@checkPassword');
$router->post('/editpwd', 'UserController@editPassword');
