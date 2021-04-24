<?php

namespace App\Http\Controllers;

use App\post;
use App\region;
use App\User;
use App\user_post;
use App\enregistrerPost;
use App\abonnement;
use App\signaleUser;
use App\signalePost;
use App\pushNotifToken;
use App\usersNotifications;
use App\adminNotifications;
use App\superAdmin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //
    public function getCurrenUser(Request $request)
    {
        // return response()->json (['message'=>json_decode($request),'message2'=>$request->header('Authorization')],400);
        $user = auth()->user();
        $user->img = asset('/pictures/profile_pic/' . $user->img);
        return response()->json($user);
    }
    public function getCurrenUserSuperAdmin(Request $request)
    {
        // return response()->json (['message'=>json_decode($request),'message2'=>$request->header('Authorization')],400);
        $superAdmin = superAdmin::where('email','=',$request->email)->first();
        $superAdmin->img = asset('/pictures/profile_pic/' . $superAdmin->img);
        return response()->json($superAdmin);
    }

    public function getImage(Request $request)
    {
        // return response()->json (['message'=>json_decode($request),'message2'=>$request->header('Authorization')],400);
        return response()->json(auth()->user()->img);
    }

    public function getUserPosts(Request $request)
    {
        //return response()->json (['message'=>$request->idUser],400);

        $post = post::where('user_id', '=', $request->idUser)->get();
        //return response()->json (['message'=>$user_post],400);
        $user_post = new Object;

        foreach ($post as $up) {
            //return response()->json (['message'=>$user_post],400);
            $p = post::find($up->post_id);
            $p->imageP = asset('/pictures/profile_pic/' . $p->imageP);
            $p->id_region = (region::find($p->id_region));

            $up->post_id = $p;
            $user_post->post_id=$up;
        }
        return response()->json([
            'message1' => $user_post
        ], 201);
    }

    public function getEnregistrerPosts(Request $request)
    {
        //return response()->json (['message'=>$request->idUser],400);
        $user_post = enregistrerPost::where('user_id', '=', $request->idUser)->get();
        foreach ($user_post as $up) {
            //return response()->json (['message'=>$user_post],400);
            $p = post::find($up->post_id);
            $p->imageP = asset('/pictures/profile_pic/' . $p->imageP);
            $p->id_region = (region::find($p->id_region));

            $up->post_id = $p;
        }
        return response()->json([
            'message1' => $user_post
        ], 201);
    }

    public function modifUser(Request $request)
    {
        // return response()->json (['message'=>$request->user['idUser']],400);
        $idUser = Auth::id();
        $nomM = $request->user['nomM'];
        $prenomM = $request->user['prenomM'];
        $dateNaissM = $request->user['dateNaissM'];
        $telM = $request->user['telM'];
        $adresseM = $request->user['adresseM'];
        $emailM = $request->user['emailM'];
        $rmdp = $request->user['rmdp'];
        $user = User::find($idUser);
        if ($nomM != "") {
            $user->nom->$nomM;
        }
        if ($prenomM != "") {
            $user->prenom->$prenomM;
        }
        if ($dateNaissM != "") {
            $user->dateNaiss = $dateNaissM;
        }
        if ($telM != "") {
            $user->tel = $telM;
        }
        if ($adresseM != "") {
            $user->adresse = $adresseM;
        }
        if ($emailM != "") {
            $user->email = $emailM;
        }
        if ($rmdp != "") {
            $user->motDePass = $rmdp;
        }
        if ($user->save()) {
            return response()->json([
                'message' => $user->motDePass
            ], 201);
        } else {
            return response()->json([
                'message' => 'echec'
            ], 400);
        }
    }

    public function modifUserSuperAdmin(Request $request)
    {
        // return response()->json (['message'=>$request->user['idUser']],400);
        $idSuperAdmin = superAdmin::where('email','=',$request->email)->first()->id;
        $nomM = $request->user['nomM'];
        $prenomM = $request->user['prenomM'];
        $dateNaissM = $request->user['dateNaissM'];
        $telM = $request->user['telM'];
        $adresseM = $request->user['adresseM'];
        $emailM = $request->user['emailM'];
        $rmdp = $request->user['rmdp'];
        $superAdmin = superAdmin::find($idSuperAdmin);
        if ($nomM != "") {
            $superAdmin->nom->$nomM;
        }
        if ($prenomM != "") {
            $superAdmin->prenom->$prenomM;
        }
        if ($dateNaissM != "") {
            $superAdmin->dateNaiss = $dateNaissM;
        }
        if ($telM != "") {
            $superAdmin->tel = $telM;
        }
        if ($adresseM != "") {
            $superAdmin->adresse = $adresseM;
        }
        if ($emailM != "") {
            $superAdmin->email = $emailM;
        }
        if ($rmdp != "") {
            $superAdmin->motDePass = $rmdp;
        }
        if ($superAdmin->save()) {
            return response()->json([
                'message' => $superAdmin->motDePass
            ], 201);
        } else {
            return response()->json([
                'message' => 'echec'
            ], 400);
        }
    }

    public function modifImageUser(Request $request)
    {
        //return response()->json (['message'=>$request->idUser],400);
        $idUser = $request->idUser;
        $user = User::find($idUser);

        $imageExtensions = ['jpg', 'jpeg', 'png', 'jpe', 'JPG', 'JPEG', 'PNG', 'JPE'];

        if ($request->hasFile('img')) {
            $imageExtensions = ['jpg', 'jpeg', 'png', 'jpe', 'JPG', 'JPEG', 'PNG', 'JPE'];

            if (!in_array($request->file('img')->getClientOriginalExtension(), $imageExtensions)) {
                return response()->json(['message' => 'Only image file'], 400);
            }

            $file = $request->file('img');
            $file_name = date('YmdHis') . '.' . $file->getClientOriginalExtension();
            $request->file('img')->move(public_path("/pictures/profile_pic/"), $file_name);
            $user->img = $file_name;
        }

        if ($user->save()) {
            return response()->json([
                'message' => 'Successfully Modified!'
            ], 201);
        } else {
            return response()->json([
                'message' => 'echec'
            ], 400);
        }
    }
    public function modifImageUserSuperAdmin(Request $request)
    {
        //return response()->json (['message'=>$request->idUser],400);
        $idUser = $request->idUser;
        $superAdmin = superAdmin::find($idUser);
        $imageExtensions = ['jpg', 'jpeg', 'png', 'jpe', 'JPG', 'JPEG', 'PNG', 'JPE'];

        if ($request->hasFile('img')) {
            $imageExtensions = ['jpg', 'jpeg', 'png', 'jpe', 'JPG', 'JPEG', 'PNG', 'JPE'];

            if (!in_array($request->file('img')->getClientOriginalExtension(), $imageExtensions)) {
                return response()->json(['message' => 'Only image file'], 400);
            }

            $file = $request->file('img');
            $file_name = date('YmdHis') . '.' . $file->getClientOriginalExtension();
            $request->file('img')->move(public_path("/pictures/profile_pic/"), $file_name);
            $superAdmin->img = $file_name;
        }

        if ($superAdmin->save()) {
            return response()->json([
                'message' => 'Successfully Modified!'
            ], 201);
        } else {
            return response()->json([
                'message' => 'echec'
            ], 400);
        }
    }

    public function getUserPw(Request $request)
    {
        $idUser = $request->idUser;
        $user = User::find(Auth::id());
        return response()->json([
            'message' => $user->motDePass
        ], 201);
    }

    public function getUserPwSuperAdmin(Request $request)
    {
        $idUser = $request->idUser;
        $superAdmin = superAdmin::find($idUser);
        return response()->json([
            'message' => $superAdmin->motDePass
        ], 201);
    }

    public function getUserById(Request $request)
    {
        //return response()->json (['message'=>$request->idUser],400);
        $idUser = $request->idUser;
        $user = User::find($idUser);
        $user->img = asset('/pictures/profile_pic/' . $user->img);
        return response()->json($user, 201);
    }

    public function getSumPostsUser(Request $request)
    {
        $idUser = $request->idUser;
        $n = user_post::where('user_id', '=', $idUser)->get()->count();
        return response()->json($n, 201);
    }

    public function getSumAbonnementsUser(Request $request)
    {
        $idUser = $request->idUser;
        $n = abonnement::where('abonne_id', '=', $idUser)->get()->count();
        return response()->json($n, 201);
    }

    public function getSumSuivitsUser(Request $request)
    {
        $idUser = $request->idUser;
        $n = abonnement::where('suivi_id', '=', $idUser)->get()->count();
        return response()->json($n, 201);
    }

    public function followUser(Request $request)
    {
        // return response()->json (['message'=>$request->ids],400);
        $abonne_id = $request->ids['abonne_id'];
        $suivi_id = $request->ids['suivi_id'];
        $abonnement = new abonnement([]);
        $abonnement->abonne_id = $abonne_id;
        $abonnement->suivi_id = $suivi_id;
        $followerName = User::where("id", "=", $abonne_id)->first()->prenom . " " . User::where("id", "=", $abonne_id)->first()->nom;
        $followeeTokens = pushNotifToken::where("user_id", "=", $suivi_id)->get();
        $tsPArray = array();
        foreach ($followeeTokens as $tP) {
            array_push($tsPArray, $tP->pushNotToken);
        }
        if ($abonnement->save()) {
            $user = User::where("id", "=", $abonne_id)->first();
            $usersNotifications = new usersNotifications([
                "user_id" => $suivi_id,
                "action_id" => $abonne_id,
                "action" => "user",
                "notification" => "Désormais, " . $user->prenom . " "  . $user->nom . " vous suit"
            ]);
            $usersNotifications->save();
            return response()->json([
                'message' => 'Successfully created abonnement!',
                'followerName' => $followerName,
                'followeeTokens' => $tsPArray
            ], 201);
        } else {
            return response()->json([
                'message' => 'echec'
            ], 400);
        }
    }

    public function ifCurrentUFollowsUser(Request $request)
    {
        //return response()->json (['message'=>$request->ids],400);
        $abonne_id = Auth::id();
        $suivi_id = $request->ids['suivi_id'];
        $ifFolows = false;
        $followers = abonnement::where('suivi_id', '=', $suivi_id)->get();
        if ($followers->count() < 0)
            return response()->json($ifFolows, 201);
        foreach ($followers as $f) {
            if ($f->abonne_id == $abonne_id)
                $ifFolows = true;
        }
        return response()->json($ifFolows, 201);
    }


    public function unFollowUser(Request $request)
    {
        // return response()->json (['message'=>$request->ids],400);
        $abonne_id = Auth::id();
        $suivi_id = $request->ids['suivi_id'];
        $followers = abonnement::where('suivi_id', '=', $suivi_id)->get();
        foreach ($followers as $f) {
            if ($f->abonne_id == $abonne_id)
                $b = $f->delete();
        }
        if ($b) {
            return response()->json([
                'message' => 'Successfully deleted follower!'
            ], 201);
        } else {
            return response()->json([
                'message' => 'echec'
            ], 400);
        }
    }

    public function getAllUsers(Request $request)
    {
        //$id=Auth::id();
        //$user = User::where('id', '!=', $id)->get();
        $user = User::where('admin', '!=', "superAdmin")->get();
        //return $user;
        foreach ($user as $u) {
            $u->img = asset('/pictures/profile_pic/' . $u->img);
        }
        return response()->json([
            'message1' => $user
        ], 201);
    }

    public function getDetailUsers(Request $request)
    {
        // return response()->json (['message'=>$request->idUser],400);
        $idUser = $request->idUser;
        $user = User::find($idUser);
        $user->img = asset('/pictures/profile_pic/' . $user->img);
        return response()->json([
            'message1' => $user,
        ], 201);
    }

    public function deleteUserById(Request $request)
    {
        $idUser = $request->idUser;
        if (User::where('id', '=', $idUser)->delete()) {
            return response()->json([
                'message' => 'Successfully deleted User !'
            ], 201);
        } else {
            return response()->json([
                'message' => 'echec'
            ], 400);
        }
    }

    public function userSignaleUser(Request $request)
    {
        //return response()->json (['message'=>$request->signalUser],400);
        $idUser = Auth::id();
        $user_idToS = $request->signalUser['idUserToSign'];
        $resSignale = $request->signalUser['resSignale'];
        $signaleUser = new signaleUser([
            'user_id' => $idUser
        ]);
        $signaleUser->user_idToS = $user_idToS;
        $signaleUser->raison = $resSignale;
        $signaleUser->accepte = "";
        if ($signaleUser->save()) {

            $user = User::where("id", "=", $idUser)->first();
            $userToSignale = User::where("id", "=", $user_idToS)->first();
            $adminNotifications = new adminNotifications([
                "action" => "signalerUser",
                "notification" => $user->prenom . " " . $user->nom  . " veut signaler " . $userToSignale->prenom . " " . $userToSignale->nom
            ]);
            $adminNotifications->save();
            return response()->json([
                'message' => 'Successfully created signaleUser!'
            ], 201);
        } else {
            return response()->json([
                'message' => 'echec'
            ], 400);
        }
    }

    public function ifCurrentUSignaledUser(Request $request)
    {
        //return response()->json (['message'=>$request->ids],400);
        $user_id = Auth::id();
        $user_idToS = $request->ids['user_idToS'];
        $ifSignaled = false;
        $signales = signaleUser::where('user_idToS', '=', $user_idToS)->where('user_id', '=', $user_id)->first();
        if ($signales)
            $ifSignaled = true;
        return response()->json($ifSignaled, 201);
    }

    public function getSignalUsers(Request $request)
    {
        $signaleUser = signaleUser::where('accepte', '=', ' ')->get();
        foreach ($signaleUser as $s) {
            $user = User::where('id', '=', $s->user_id)->first();
            $user->img = asset('/pictures/profile_pic/' . $user->img);
            $s->user_id = $user;
            $user = User::where('id', '=', $s->user_idToS)->first();
            $user->img = asset('/pictures/profile_pic/' . $user->img);
            $s->user_idToS = $user;
        }
        return response()->json([
            'message1' => $signaleUser
        ], 201);
    }

    public function signalerUser(Request $request)
    {
        //return response()->json (['message'=>$request->idSign],400);
        $signaleUser = signaleUser::where('id', '=', $request->idSign)->first();
        $nbSignales = signaleUser::where('user_idToS', '=', $signaleUser->user_idToS)->where('accepte', '=', 'accepte')->get()->count();
        if ($nbSignales >= 10) {
            if (signaleUser::where('id', '=', $request->idSign)->delete() && User::where('id', '=', $signaleUser->user_idToS)->first()->delete()) {
                $Signales = signaleUser::where('user_idToS', '=', $signaleUser->user_idToS)->get();
                foreach ($Signales as $s) {
                    signaleUser::where('user_idToS', '=', $s->user_idToS)->first()->delete();
                }
                return response()->json([
                    'message' => 'deleted'
                ], 201);
            } else {
                return response()->json([
                    'message' => 'echec'
                ], 400);
            }
        } else {
            $signaleUser->accepte = "accepte";
            if ($signaleUser->save()) {
                $nbSignales = signaleUser::where('user_idToS', '=', $signaleUser->user_idToS)->where('accepte', '=', 'accepte')->get()->count();
                return response()->json([
                    'message' => $nbSignales
                ], 201);
            } else {
                return response()->json([
                    'message' => 'echec'
                ], 400);
            }
        }
    }

    public function refuserUserSignale(Request $request)
    {
        //return response()->json (['message'=>$request->idSign],400);
        if (signaleUser::where('id', '=', $request->idSign)->delete()) {
            return response()->json([
                'message' => 'Successfully deleted signal!'
            ], 201);
        } else {
            return response()->json([
                'message' => 'echec'
            ], 400);
        }
    }

    public function getAllUsersNames(Request $request)
    {

        $user = User::all();
        $array = [];
        //return $user;
        foreach ($user as $u) {
            $nom = $u->id . " " . $u->prenom . " " . $u->nom;
            array_push($array, $nom);
        }
        return response()->json(
            $array,
            201
        );
    }

    public function getValideUserPosts(Request $request)
    {
        //return response()->json (['message'=>$request->idUser],400);

        $user_post = user_post::where('user_id', '=', $request->idUser)->get();
        $posts = array();
        foreach ($user_post as $up) {
            $post = post::where('id', '=', $up->post_id)->first();
            // return response()->json (['message'=>$post],400);
            if ($post->valide == "valide") {
                $p = post::find($up->post_id);
                $p->imageP = asset('/pictures/profile_pic/' . $p->imageP);
                $p->id_region = (region::find($p->id_region));
                $up->post_id = $p;
                array_push($posts, $p);
            } else {
                $up->delete();
            }
        }
        return response()->json([
            'message1' => $posts
        ], 201);
    }


    public function valideUserToAdmin(Request $request)
    {
        //  return response()->json (['message'=>$request->idUser],400);
        $user = User::where('id', '=', $request->idUser)->first();
        $user->admin = "admin";
        if ($user->save()) {
            return response()->json([
                'message' => 'Successfully created Admin!'
            ], 201);
        } else {
            return response()->json([
                'message' => 'echec'
            ], 400);
        }
    }

    public function unValideAdminToUser(Request $request)
    {
        //  return response()->json (['message'=>$request->idUser],400);
        $user = User::where('id', '=', $request->idUser)->first();
        $user->admin = "user";
        if ($user->save()) {
            return response()->json([
                'message' => 'Successfully created Admin!'
            ], 201);
        } else {
            return response()->json([
                'message' => 'echec'
            ], 400);
        }
    }

    public function getSuperAdmin(Request $request)
    {
        //return response()->json (['message'=>$request->idUser],400);
        $user = User::where('admin', '=', 'superAdmin')->first();
        $user->img = asset('/pictures/profile_pic/' . $user->img);
        return response()->json($user, 201);
    }

    public function getNumberUsers(Request $request)
    {
        $user = User::all()->count();
        return response()->json($user, 201);
    }

    public function getNumberSignales(Request $request)
    {
        $signaleUser = signaleUser::where('accepte', '=', '')->get()->count();
        $signalePost = signalePost::where('accepte', '=', '')->get()->count();
        return response()->json($signaleUser + $signalePost, 201);
    }

    public function userStaticts(Request $request)
    {
        $stats = array();
        /**janv */
        $time = strtotime(date('Y-1-0 00:00:00'));
        $date1 = date('Y-m-d H:i:s', $time);
        $time = strtotime(date('Y-1-31 00:00:00'));
        $date2 = date('Y-m-d H:i:s', $time);
        $jan = User::where("created_at", '>=', $date1)->where("created_at", '<=', $date2)->count();
        array_push($stats, $jan);

        $time = strtotime(date('Y-2-0 00:00:00'));
        $date1 = date('Y-m-d H:i:s', $time);
        $time = strtotime(date('Y-2-28 00:00:00'));
        $date2 = date('Y-m-d H:i:s', $time);
        $fev = User::where("created_at", '>=', $date1)->where("created_at", '<=', $date2)->count();
        array_push($stats, $fev);

        $time = strtotime(date('Y-3-0 00:00:00'));
        $date1 = date('Y-m-d H:i:s', $time);
        $time = strtotime(date('Y-3-31 00:00:00'));
        $date2 = date('Y-m-d H:i:s', $time);
        $mars = User::where("created_at", '>=', $date1)->where("created_at", '<=', $date2)->count();
        array_push($stats, $mars);

        $time = strtotime(date('Y-4-0 00:00:00'));
        $date1 = date('Y-m-d H:i:s', $time);
        $time = strtotime(date('Y-4-30 00:00:00'));
        $date2 = date('Y-m-d H:i:s', $time);
        $avril = User::where("created_at", '>=', $date1)->where("created_at", '<=', $date2)->count();
        array_push($stats, $avril);

        $time = strtotime(date('Y-5-0 00:00:00'));
        $date1 = date('Y-m-d H:i:s', $time);
        $time = strtotime(date('Y-5-31 00:00:00'));
        $date2 = date('Y-m-d H:i:s', $time);
        $mais = User::where("created_at", '>=', $date1)->where("created_at", '<=', $date2)->count();
        array_push($stats, $mais);


        $time = strtotime(date('Y-6-0 00:00:00'));
        $date1 = date('Y-m-d H:i:s', $time);
        $time = strtotime(date('Y-6-30 00:00:00'));
        $date2 = date('Y-m-d H:i:s', $time);
        $juin = User::where("created_at", '>=', $date1)->where("created_at", '<=', $date2)->count();
        array_push($stats, $juin);


        $time = strtotime(date('Y-7-0 00:00:00'));
        $date1 = date('Y-m-d H:i:s', $time);
        $time = strtotime(date('Y-7-31 00:00:00'));
        $date2 = date('Y-m-d H:i:s', $time);
        $juillet = User::where("created_at", '>=', $date1)->where("created_at", '<=', $date2)->count();
        array_push($stats, $juillet);

        $time = strtotime(date('Y-8-0 00:00:00'));
        $date1 = date('Y-m-d H:i:s', $time);
        $time = strtotime(date('Y-8-31 00:00:00'));
        $date2 = date('Y-m-d H:i:s', $time);
        $ao = User::where("created_at", '>=', $date1)->where("created_at", '<=', $date2)->count();
        array_push($stats, $ao);

        $time = strtotime(date('Y-9-0 00:00:00'));
        $date1 = date('Y-m-d H:i:s', $time);
        $time = strtotime(date('Y-9-30 00:00:00'));
        $date2 = date('Y-m-d H:i:s', $time);
        $sep = User::where("created_at", '>=', $date1)->where("created_at", '<=', $date2)->count();
        array_push($stats, $sep);


        $time = strtotime(date('Y-10-0 00:00:00'));
        $date1 = date('Y-m-d H:i:s', $time);
        $time = strtotime(date('Y-10-31 00:00:00'));
        $date2 = date('Y-m-d H:i:s', $time);
        $oct = User::where("created_at", '>=', $date1)->where("created_at", '<=', $date2)->count();
        array_push($stats, $oct);

        return response()->json($stats, 201);
    }

    public function getAbonneUsers(Request $request)
    {
        $userAb = abonnement::where('abonne_id', '=', Auth::id())->get();
        $userAs = array();
        foreach ($userAb as $u) {
            $user = User::where('id', '=', $u->suivi_id)->first();
            $user->img = asset('/pictures/profile_pic/' . $user->img);
            array_push($userAs, $user);
        }

        return response()->json([
            'message1' =>  $userAs
        ], 201);
    }

    public function getSuiviUsers(Request $request)
    {
        $userAb = abonnement::where('suivi_id', '=', Auth::id())->get();
        $userAs = array();
        foreach ($userAb as $u) {
            $user = User::where('id', '=', $u->abonne_id)->first();
            $user->img = asset('/pictures/profile_pic/' . $user->img);
            array_push($userAs, $user);
        }

        return response()->json([
            'message1' =>  $userAs
        ], 201);
    }

    public function isLoggedAdmin(Request $request)
    {
        $admin = Auth::user()->admin;
        return response()->json($admin == "admin", 201);
    }

    public function isLoggedIn(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            return response()->json(true, 201);
        } else {
            return response()->json(false, 401);
        }
    }

    public function isLoggedSuperAdmin(Request $request)
    {
        if (!(Auth::user())) {
            return response()->json(false, 201);
        }
        $superAdmin = Auth::user()->admin;
        if ($superAdmin == "superAdmin")
            return response()->json(true, 202);
        else
            return response()->json(false, 203);
    }

    public function pushNotifTokenAdd(Request $request)
    {
        $pushNotifToken = new pushNotifToken([

            'user_id' => Auth::id(),
            'pushNotToken' => $request->pushNotToken
        ]);

        if ($pushNotifToken->save())
            return response()->json("done", 202);
        else
            return response()->json("done", 203);
    }
    public function pushNotifTokenDelete(Request $request)
    {
        $ppT = pushNotifToken::where('user_id', '=', Auth::id())->get();
        foreach ($ppT as $t) {
            $t->delete();
        }

        if (!(empty($ppT)))
            return response()->json("done", 202);
        else
            return response()->json("notDone", 401);
    }

    public function nbNotif(Request $request)
    {
        //return response()->json (['message'=>$request->idUser],400);

        $nbNotif = usersNotifications::where('user_id', '=', Auth::id())->get()->count();

        return response()->json($nbNotif, 201);
    }

    public function getNotifs(Request $request)
    {
        //return response()->json (['message'=>$request->idUser],400);

        $nbNotif = usersNotifications::where('user_id', '=', Auth::id())->get();

        return response()->json($nbNotif, 201);
    }

    public function deleteNotif(Request $request)
    {
        $notif = usersNotifications::where('id', '=', $request->idNotif)->first();
        if ($notif->delete()) {
            return response()->json([
                'message' => 'Successfully deleted!'
            ], 201);
        } else {
            return response()->json([
                'message' => 'echec'
            ], 400);
        }
    }

    public function nbNotifAdmin(Request $request)
    {
        //return response()->json (['message'=>$request->idUser],400);

        $nbNotif = adminNotifications::all()->count();

        return response()->json($nbNotif, 201);
    }

    public function getNotifsAdmin(Request $request)
    {
        //return response()->json (['message'=>$request->idUser],400);

        $nbNotif = adminNotifications::all();

        return response()->json($nbNotif, 201);
    }

    public function deleteNotifAdmin(Request $request)
    {
        $notif = adminNotifications::where('id', '=', $request->idNotif)->first();
        if ($notif->delete()) {
            return response()->json([
                'message' => 'Successfully deleted!'
            ], 201);
        } else {
            return response()->json([
                'message' => 'echec'
            ], 400);
        }
    }

    public function ifPostBelongsUser(Request $request)
    {
        $idUser=Auth::id();
        $idPost=$request->idPost;

        if (user_post::where('user_id', '=', $idUser)->where('post_id', '=', $idPost)->first()) {
            return response()->json([
                'message' => true
            ], 201);
        } else {
            return response()->json([
                'message' => false
            ], 201);
        }
    }

    public function ifUserExists(Request $request)
    {
        $idUser=$request->idUser;

        if (User::where('id', '=', $idUser)->exists()) {
            return response()->json([
                'message' => true
            ], 201);
        } else {
            return response()->json([
                'message' => false
            ], 201);
        }
    }
}
