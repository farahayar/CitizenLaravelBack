<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
/* 
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');
 */

Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('login', 'AuthController@login');
    Route::post('signup', 'AuthController@signup');
    Route::post('loginSuperAdmin', 'AuthController@loginSuperAdmin');
    Route::post('signupSuperAdmin', 'AuthController@signupSuperAdmin');
    Route::get('getInvalidePosts', 'PostController@getInvalidePosts');
    Route::post('getDetailsPost', 'PostController@getDetailsPost');
    Route::post('validPost', 'PostController@validPost');
    Route::get('getValidePosts', 'PostController@getValidePosts');
    Route::post('refusePost', 'PostController@refusePost');
    Route::post('userModifPost', 'PostController@userModifPost');
    Route::get('getModifPosts', 'PostController@getModifPosts');
    Route::post('ModifierPost', 'PostController@ModifierPost');
    Route::post('refusePostModification', 'PostController@refusePostModification');
    Route::post('getDetailsPost2', 'PostController@getDetailsPost2');
    Route::post('getUserById', 'UserController@getUserById');
    Route::post('getSumPostsUser', 'UserController@getSumPostsUser');
    Route::post('getSumAbonnementsUser', 'UserController@getSumAbonnementsUser');
    Route::post('getSumSuivitsUser', 'UserController@getSumSuivitsUser');
    Route::get('getSignalPosts', 'PostController@getSignalPosts');
    Route::post('signalerPost', 'PostController@signalerPost');
    Route::post('refuserPostSignale', 'PostController@refuserPostSignale');
    Route::post('getAllUsers', 'UserController@getAllUsers');
    Route::post('getDetailUsers', 'UserController@getDetailUsers');
    Route::post('deleteUserById', 'UserController@deleteUserById');
    Route::get('getSignalUsers', 'UserController@getSignalUsers');
    Route::post('signalerUser', 'UserController@signalerUser');
    Route::post('refuserUserSignale', 'UserController@refuserUserSignale');
    Route::get('getAllUsersNames', 'UserController@getAllUsersNames');
    Route::post('getValideUserPosts', 'UserController@getValideUserPosts');
    Route::get('getSuperAdmin', 'UserController@getSuperAdmin');
    Route::get('getNumberUsers', 'UserController@getNumberUsers');
    Route::get('getNumberPosts', 'PostController@getNumberPosts');
    Route::get('getNumberSignales', 'UserController@getNumberSignales');
    Route::get('getNumberModifs', 'PostController@getNumberModifs');
    Route::get('userStaticts', 'UserController@userStaticts');
    Route::post('valideUserToAdmin', 'UserController@valideUserToAdmin');
    Route::post('unValideAdminToUser', 'UserController@unValideAdminToUser');
    Route::get('postStaticts', 'PostController@postStaticts');
    Route::get('getNumberPostsP', 'PostController@getNumberPostsP');
    Route::get('getNumberPostsN', 'PostController@getNumberPostsN');
    Route::post('getStoryUserId', 'PostController@getStoryUserId');
    Route::post('getUserPosts', 'UserController@getUserPosts');
    Route::post('ifUserExists', 'UserController@ifUserExists');
    Route::post('getCurrenUserSuperAdmin', 'UserController@getCurrenUserSuperAdmin');
    Route::get('nbNotifAdmin', 'UserController@nbNotifAdmin');
    Route::get('getNotifsAdmin', 'UserController@getNotifsAdmin');
    Route::post('deleteNotifAdmin', 'UserController@deleteNotifAdmin');
    Route::post('modifUserSuperAdmin', 'UserController@modifUserSuperAdmin');
    Route::post('modifImageUserSuperAdmin', 'UserController@modifImageUserSuperAdmin');
    Route::post('getUserPwSuperAdmin', 'UserController@getUserPwSuperAdmin');

    Route::group([
        'middleware' => 'auth:api'
    ], function () {
        Route::get('logout', 'AuthController@logout');
        Route::get('user', 'AuthController@user');
        Route::post('addPost', 'PostController@addPost');
        Route::get('getCurrenUser', 'UserController@getCurrenUser');
        Route::get('getImage', 'UserController@getImage');
        Route::post('modifUser', 'UserController@modifUser');
        Route::post('modifImageUser', 'UserController@modifImageUser');
        Route::post('getUserPw', 'UserController@getUserPw');
        Route::post('followUser', 'UserController@followUser');
        Route::post('ifCurrentUFollowsUser', 'UserController@ifCurrentUFollowsUser');
        Route::post('unFollowUser', 'UserController@unFollowUser');
        Route::post('userEnregistrerPost', 'PostController@userEnregistrerPost');
        Route::post('getEnregistrerPosts', 'UserController@getEnregistrerPosts');
        Route::post('deletePost', 'PostController@deletePost');
        Route::post('deletePostEnregistrer', 'PostController@deletePostEnregistrer');
        Route::post('userSignaleUser', 'UserController@userSignaleUser');
        Route::post('userSignalePost', 'PostController@userSignalePost');
        Route::post('ifCurrentUSignaledUser', 'UserController@ifCurrentUSignaledUser');
        Route::get('getAbonneUsers', 'UserController@getAbonneUsers');
        Route::get('getSuiviUsers', 'UserController@getSuiviUsers');
        Route::get('getStoryUser', 'PostController@getStoryUser');
        Route::get('isLoggedAdmin', 'UserController@isLoggedAdmin');
        Route::get('isLoggedIn', 'UserController@isLoggedIn');
        Route::get('isLoggedSuperAdmin', 'UserController@isLoggedSuperAdmin');
        Route::post('pushNotifTokenAdd', 'UserController@pushNotifTokenAdd');
        Route::get('pushNotifTokenDelete', 'UserController@pushNotifTokenDelete');
        Route::get('nbNotif', 'UserController@nbNotif');
        Route::get('getNotifs', 'UserController@getNotifs');
        Route::post('deleteNotif', 'UserController@deleteNotif');
        Route::post('ifPostBelongsUser', 'UserController@ifPostBelongsUser');
    });
});

Route::middleware('auth:api', 'cors')->get('/user', function (Request $request) {
    return $request->user();
});
