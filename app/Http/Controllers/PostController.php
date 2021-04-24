<?php

namespace App\Http\Controllers;

use App\post;
use App\region;
use App\user_post;
use App\modifUser;
use App\signalePost;
use App\enregistrerPost;
use App\User;
use App\abonnement;
use App\pushNotifToken;
use App\usersNotifications;
use App\adminNotifications;
use Input, Session, Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class PostController extends Controller
{
    //
    public function addPost(Request $request)
    {
        //return response()->json (json_decode($request->region),400);
        //,'message2'=>$header = $request->header('Authorization')
        $reqRegion = json_decode($request->region);

        $region = new region([

            'latitude' => $reqRegion->_latitude,
            'longitude' => $reqRegion->_longitude
        ]);
        $region->nom_region = $reqRegion->_nom_region;



        if (!(region::where('nom_region', $reqRegion->_nom_region)->exists())) {
            $region->save();
            $idRegionAr = response()->json(array('success' => true, 'last_insert_id' => $region->id), 200);
            $idRegion = $idRegionAr->original["last_insert_id"];
        } else {
            $idRegion = (region::where('nom_region', $reqRegion->_nom_region)->first())->id;
        }

        if ($idRegion) {
            $post = new post([
                'titre' => $request->titre,
                'description' => $request->des,
                'signe' => $request->signe,
                'id_region' => $idRegion
            ]);
            $post->id_region = $idRegion;
            $post->valide = "invalide";
            //  $post->signe=
            if ($request->hasFile('img')) {
                $imageExtensions = ['jpg', 'jpeg', 'png', 'jpe'];

                if (!in_array($request->file('img')->getClientOriginalExtension(), $imageExtensions)) {
                    return response()->json(['message' => 'Only image file'], 400);
                }

                $file = $request->file('img');
                $file_name = date('YmdHis') . '.' . $file->getClientOriginalExtension();
                $request->file('img')->move(public_path("/pictures/profile_pic/"), $file_name);
                $post->imageP = $file_name;
            }

            $post->save();
            $idNewPost = response()->json(array('success' => true, 'last_insert_id' => $post->id), 200);
            if ($idNewPost) {
                $idUser = Auth::id();
                $user_post = new user_post([

                    'post_id' => $idNewPost->original["last_insert_id"]
                ]);
                $user_post->user_id = $idUser;
                $user_post->datePost = date("Y-m-d H:i:s");
                //return response()->json (['message'=>$idNewPost->original["last_insert_id"]],400);
                $user_post->save();
                if (response()->json(array('success' => true), 200)) {
                    $user = User::where("id", "=", $idUser)->first();
                    $adminNotifications = new adminNotifications([
                        "action" => "validerPost",
                        "notification" => $user->prenom . " " . $user->nom  . " veut valider un post"
                    ]);
                    $adminNotifications->save();

                    return response()->json([
                        'message' => 'Successfully created post!'
                    ], 201);
                } else {
                    return response()->json([
                        'message' => 'Echec user_post not created'
                    ], 400);
                }
            } else {
                return response()->json([
                    'message' => 'Echec post not created'
                ], 400);
            }
        } else {
            return response()->json([
                'message' => 'Echec region not created'
            ], 400);
        }
    }

    public function getInvalidePosts(Request $request)
    {
        $post = post::where('valide', '=', 'invalide')->get();
        foreach ($post as $p) {
            $p->imageP = asset('/pictures/profile_pic/' . $p->imageP);
            $p->id_region = (region::find($p->id_region))->nom_region;
        }
        return response()->json([
            'message1' => $post
        ], 201);
    }

    public function getValidePosts(Request $request)
    {
        $post = post::where('valide', '=', 'valide')->get();
        
        foreach ($post as $p) {
            $p->imageP = asset('/pictures/profile_pic/' . $p->imageP);
            $p->id_region = (region::find($p->id_region));
            $idUser = (user_post::where('post_id', '=', $p->id)->first())->user_id;
        }
        return response()->json([
            'message1' => $post,
            'message2' => $idUser
        ], 201);
    }

    public function getDetailsPost(Request $request)
    {
        //return response()->json (['message'=>$request->idPost],400);
        $id = $request->idPost;
        $post = post::where('id', '=', $id)->first();
        $post->imageP = asset('/pictures/profile_pic/' . $post->imageP);
        $post->id_region = (region::find($post->id_region))->nom_region;

        $idUser = (user_post::where('post_id', '=', $id)->first())->user_id;
        $user = User::where('id', '=', $idUser)->first();
        $user->img = asset('/pictures/profile_pic/' . $user->img);
        return response()->json([
            'message1' => $post,
            'message2' => $user
        ], 201);
    }

    public function validPost(Request $request)
    {
        // return response()->json (['message'=>$request->idPost],400);
        $userId = user_post::where("post_id", "=", $request->idPost)->first()->user_id;
        $followers = abonnement::where("abonne_id", "=", $userId)->get();
        $ts = array();
        $tsP = pushNotifToken::where("user_id", "=", $userId)->get();
        $tsPArray = array();
        foreach ($tsP as $tP) {
            array_push($tsPArray, $tP->pushNotToken);
        }
        foreach ($followers as $f) {
            $user = User::where("id", "=", $userId)->first();
            $usersNotifications = new usersNotifications([
                "user_id" => $f->suivi_id,
                "action_id" => $request->idPost,
                "action" => "post",
                "notification" => $nomUser = $user->prenom . " " . $user->nom  . " a ajouté un post"
            ]);
            $usersNotifications->save();
            $t = pushNotifToken::where("user_id", "=", $f->suivi_id)->first();
            if ($t) {

                $nomUser = $user->nom . " " . $user->prenom;
                $ts[$nomUser] = $t->pushNotToken;
            }
        }
        $post = post::where('id', '=', $request->idPost)->first();
        $post->valide = "valide";
        if ($post->save()) {
            $usersNotifications = new usersNotifications([
                "user_id" => $userId,
                "action_id" => $request->idPost,
                "action" => "post",
                "notification" => "Votre post a été validé"
            ]);
            $usersNotifications->save();
            return response()->json([
                'message1' => 'Successfully created post!',
                'message2' => $ts,
                'message3' => $tsPArray
            ], 201);
        } else {
            return response()->json([
                'message' => 'echec'
            ], 400);
        }
    }

    public function refusePost(Request $request)
    {
        // return response()->json (['message'=>$request->idPost],400);
        //$post= post::where('id', '=', $request->idPost)->first();
        if (post::where('id', '=', $request->idPost)->delete()) {
            return response()->json([
                'message' => 'Successfully deleted post!'
            ], 201);
        } else {
            return response()->json([
                'message' => 'echec'
            ], 400);
        }
    }

    public function userModifPost(Request $request)
    {
        // return response()->json (['message'=>$request->modifPost],400);
        $idUser = $request->modifPost['idUserPost'];
        $idPost = $request->modifPost['idPost'];
        $novDescription = $request->modifPost['novDescription'];
        $modifUser = new modifUser([
            'user_id' => $idUser
        ]);
        $modifUser->post_id = $idPost;
        $modifUser->descriptionM = $novDescription;
        if ($modifUser->save()) {

            $user = User::where("id", "=", $idUser)->first();
            $adminNotifications = new adminNotifications([
                "action" => "modifierPost",
                "notification" => $user->prenom . " " . $user->nom  . " veut modifier un post"
            ]);
            $adminNotifications->save();

            return response()->json([
                'message' => 'Successfully created modifUser!'
            ], 201);
        } else {
            return response()->json([
                'message' => 'echec'
            ], 400);
        }
    }



    public function getModifPosts(Request $request)
    {
        $modifUser = modifUser::get();
        foreach ($modifUser as $m) {
            $post = post::where('id', '=', $m->post_id)->first();
            $post->imageP = asset('/pictures/profile_pic/' . $post->imageP);
            $post->id_region = (region::find($post->id_region))->nom_region;
            $m->post_id = $post;
        }
        return response()->json([
            'message1' => $modifUser
        ], 201);
    }

    public function ModifierPost(Request $request)
    {
        //return response()->json (['message'=>$request->idModif],400);
        $modifUser = modifUser::where('id', '=', $request->idModif)->first();
        $post = post::where('id', '=', $modifUser->post_id)->first();
        $post->description = $modifUser->descriptionM;
        $userId = user_post::where("post_id", "=", $post->id)->first()->user_id;
        $tsP = pushNotifToken::where("user_id", "=", $userId)->get();
        $tsPArray = array();
        foreach ($tsP as $tP) {
            array_push($tsPArray, $tP->pushNotToken);
        }
        if ($post->save()) {
            $usersNotifications = new usersNotifications([
                "user_id" => $userId,
                "action_id" => $post->id,
                "action" => "post",
                "notification" => "Votre post a été modifié"
            ]);
            $usersNotifications->save();
            modifUser::where('id', '=', $request->idModif)->delete();
            return response()->json([
                'message' => 'Successfully modified post!',
                'message2' => $tsPArray
            ], 201);
        } else {
            return response()->json([
                'message' => 'echec'
            ], 400);
        }
    }

    public function refusePostModification(Request $request)
    {
        //return response()->json (['message'=>$request->idModPost],400);
        if (modifUser::where('id', '=', $request->idModPost)->delete()) {
            return response()->json([
                'message' => 'Successfully created post!'
            ], 201);
        } else {
            return response()->json([
                'message' => 'echec'
            ], 400);
        }
    }
    public function getDetailsPost2(Request $request)
    {
        //return response()->json (['message'=>$request->idPost],400);
        $id = $request->idPost;
        $id_region = post::where('id', '=', $id)->first()->id_region;
        $post = post::where('id_region', '=', $id_region)->get();

        foreach ($post as $p) {
            $p->imageP = asset('/pictures/profile_pic/' . $p->imageP);
            $p->id_region = (region::find($p->id_region))->nom_region;

            $idUser = (user_post::where('post_id', '=', $id)->first())->user_id;
            $user = User::where('id', '=', $idUser)->first();
            $user->img = asset('/pictures/profile_pic/' . $user->img);

            $p->valide = $user;
        }
        return response()->json([
            'message1' => $post
        ], 201);
    }

    public function getSignalPosts(Request $request)
    {
        //return response()->json (['message'=>$request],400);
        $signalePost = signalePost::where('accepte', '=', ' ')->get();
        foreach ($signalePost as $s) {
            $post = post::where('id', '=', $s->post_id)->first();
            $post->imageP = asset('/pictures/profile_pic/' . $post->imageP);
            $post->id_region = (region::find($post->id_region))->nom_region;
            $s->post_id = $post;
        }
        return response()->json([
            'message1' => $signalePost
        ], 201);
    }


    public function signalerPost(Request $request)
    {
        //return response()->json (['message'=>$request->idSign],400);
        $signalePost = signalePost::where('id', '=', $request->idSign)->first();
        $nbSignales = signalePost::where('post_id', '=', $signalePost->post_id)->where('accepte', '=', 'accepte')->get()->count();
        if ($nbSignales >= 10) {
            if (signalePost::where('id', '=', $request->idSign)->delete() && post::where('id', '=', $signalePost->post_id)->first()->delete()) {
                $Signales = signalePost::where('post_id', '=', $signalePost->post_id)->get();
                foreach ($Signales as $s) {
                    signalePost::where('post_id', '=', $s->post_id)->first()->delete();
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
            $signalePost->accepte = "accepte";
            if ($signalePost->save()) {
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

    public function refuserPostSignale(Request $request)
    {
        //return response()->json (['message'=>$request->idSign],400);
        if (signalePost::where('id', '=', $request->idSign)->delete()) {
            return response()->json([
                'message' => 'Successfully deleted signal!'
            ], 201);
        } else {
            return response()->json([
                'message' => 'echec'
            ], 400);
        }
    }

    public function userSignalePost(Request $request)
    {
        // return response()->json (['message'=>$request->signalPost],400);
        $idUser = Auth::id();
        $idPost = $request->signalPost['idPost'];
        $resSignale = $request->signalPost['resSignale'];
        $signalePost = new signalePost([
            'user_id' => $idUser
        ]);
        $signalePost->user_id = $idUser;
        $signalePost->post_id = $idPost;
        $signalePost->raison = $resSignale;
        $signalePost->accepte = "";
        if ($signalePost->save()) {

            $user = User::where("id", "=", $idUser)->first();
            $user2 = User::where("id", "=", $request->signalPost['idUserPost'])->first();
            $adminNotifications = new adminNotifications([
                "action" => "signalerPost",
                "notification" => $user->prenom . " " . $user->nom  . " veut signaler un post de ".$user2->prenom . " " . $user2->nom  
            ]);
            $adminNotifications->save();

            return response()->json([
                'message' => 'Successfully created signalePost!'
            ], 201);
        } else {
            return response()->json([
                'message' => 'echec'
            ], 400);
        }
    }

    public function userEnregistrerPost(Request $request)
    {
        //return response()->json (['message'=>$request->enregistrerPost],400);
        $idUser = Auth::id();
        $idPost = $request->enregistrerPost;
        $enregistrerPost = new enregistrerPost([
            'user_id' => $idUser,
        ]);
        $enregistrerPost->post_id = $idPost;
        if ($enregistrerPost->save()) {
            return response()->json([
                'message' => 'Successfully enregistrer Poste!'
            ], 201);
        } else {
            return response()->json([
                'message' => 'echec'
            ], 400);
        }
    }

    public function deletePost(Request $request)
    {
        //return response()->json (['message'=>$request->deletePost],400);
        if (post::where('id', '=', $request->deletePost)->delete()) {
            return response()->json([
                'message' => 'Successfully deleted post!'
            ], 201);
        } else {
            return response()->json([
                'message' => 'echec'
            ], 400);
        }
    }

    public function deletePostEnregistrer(Request $request)
    {
        //return response()->json (['message'=>$request->deletePostEnregistrer],400);
        $post = enregistrerPost::where('post_id', '=', $request->deletePostEnregistrer);
        //return response()->json (['message'=>$post],400);
        if ($post->delete()) {
            return response()->json([
                'message' => 'Successfully deleted post!'
            ], 201);
        } else {
            return response()->json([
                'message' => 'echec'
            ], 400);
        }
    }

    public function getNumberPosts(Request $request)
    {
        $post = post::where('valide', '=', 'invalide')->get()->count();
        return response()->json($post, 201);
    }

    public function getNumberModifs(Request $request)
    {
        $modifUser = modifUser::all()->count();
        return response()->json($modifUser, 201);
    }

    public function postStaticts(Request $request)
    {
        $statsP = array();
        /**janv */
        $time = strtotime(date('Y-1-0 00:00:00'));
        $date1 = date('Y-m-d H:i:s', $time);
        $time = strtotime(date('Y-1-31 00:00:00'));
        $date2 = date('Y-m-d H:i:s', $time);
        $jan = post::where("signe", "=", "positive")->where("created_at", '>=', $date1)->where("created_at", '<=', $date2)->count();
        array_push($statsP, $jan);

        $time = strtotime(date('Y-2-0 00:00:00'));
        $date1 = date('Y-m-d H:i:s', $time);
        $time = strtotime(date('Y-2-28 00:00:00'));
        $date2 = date('Y-m-d H:i:s', $time);
        $fev = post::where("signe", "=", "positive")->where("created_at", '>=', $date1)->where("created_at", '<=', $date2)->count();
        array_push($statsP, $fev);

        $time = strtotime(date('Y-3-0 00:00:00'));
        $date1 = date('Y-m-d H:i:s', $time);
        $time = strtotime(date('Y-3-31 00:00:00'));
        $date2 = date('Y-m-d H:i:s', $time);
        $mars = post::where("signe", "=", "positive")->where("created_at", '>=', $date1)->where("created_at", '<=', $date2)->count();
        array_push($statsP, $mars);

        $time = strtotime(date('Y-4-0 00:00:00'));
        $date1 = date('Y-m-d H:i:s', $time);
        $time = strtotime(date('Y-4-30 00:00:00'));
        $date2 = date('Y-m-d H:i:s', $time);
        $avril = post::where("signe", "=", "positive")->where("created_at", '>=', $date1)->where("created_at", '<=', $date2)->count();
        array_push($statsP, $avril);

        $time = strtotime(date('Y-5-0 00:00:00'));
        $date1 = date('Y-m-d H:i:s', $time);
        $time = strtotime(date('Y-5-31 00:00:00'));
        $date2 = date('Y-m-d H:i:s', $time);
        $mais = post::where("signe", "=", "positive")->where("created_at", '>=', $date1)->where("created_at", '<=', $date2)->count();
        array_push($statsP, $mais);


        $time = strtotime(date('Y-6-0 00:00:00'));
        $date1 = date('Y-m-d H:i:s', $time);
        $time = strtotime(date('Y-6-30 00:00:00'));
        $date2 = date('Y-m-d H:i:s', $time);
        $juin = post::where("signe", "=", "positive")->where("created_at", '>=', $date1)->where("created_at", '<=', $date2)->count();
        array_push($statsP, $juin);


        $time = strtotime(date('Y-7-0 00:00:00'));
        $date1 = date('Y-m-d H:i:s', $time);
        $time = strtotime(date('Y-7-31 00:00:00'));
        $date2 = date('Y-m-d H:i:s', $time);
        $juillet = post::where("signe", "=", "positive")->where("created_at", '>=', $date1)->where("created_at", '<=', $date2)->count();
        array_push($statsP, $juillet);

        $time = strtotime(date('Y-8-0 00:00:00'));
        $date1 = date('Y-m-d H:i:s', $time);
        $time = strtotime(date('Y-8-31 00:00:00'));
        $date2 = date('Y-m-d H:i:s', $time);
        $ao = post::where("signe", "=", "positive")->where("created_at", '>=', $date1)->where("created_at", '<=', $date2)->count();
        array_push($statsP, $ao);

        $time = strtotime(date('Y-9-0 00:00:00'));
        $date1 = date('Y-m-d H:i:s', $time);
        $time = strtotime(date('Y-9-30 00:00:00'));
        $date2 = date('Y-m-d H:i:s', $time);
        $sep = post::where("signe", "=", "positive")->where("created_at", '>=', $date1)->where("created_at", '<=', $date2)->count();
        array_push($statsP, $sep);


        $time = strtotime(date('Y-10-0 00:00:00'));
        $date1 = date('Y-m-d H:i:s', $time);
        $time = strtotime(date('Y-10-31 00:00:00'));
        $date2 = date('Y-m-d H:i:s', $time);
        $oct = post::where("signe", "=", "positive")->where("created_at", '>=', $date1)->where("created_at", '<=', $date2)->count();
        array_push($statsP, $oct);

        /**negative Posts */

        $statsN = array();
        /**janv */
        $time = strtotime(date('Y-1-0 00:00:00'));
        $date1 = date('Y-m-d H:i:s', $time);
        $time = strtotime(date('Y-1-31 00:00:00'));
        $date2 = date('Y-m-d H:i:s', $time);
        $jan = post::where("signe", "=", "negative")->where("created_at", '>=', $date1)->where("created_at", '<=', $date2)->count();
        array_push($statsN, $jan);

        $time = strtotime(date('Y-2-0 00:00:00'));
        $date1 = date('Y-m-d H:i:s', $time);
        $time = strtotime(date('Y-2-28 00:00:00'));
        $date2 = date('Y-m-d H:i:s', $time);
        $fev = post::where("signe", "=", "negative")->where("created_at", '>=', $date1)->where("created_at", '<=', $date2)->count();
        array_push($statsN, $fev);

        $time = strtotime(date('Y-3-0 00:00:00'));
        $date1 = date('Y-m-d H:i:s', $time);
        $time = strtotime(date('Y-3-31 00:00:00'));
        $date2 = date('Y-m-d H:i:s', $time);
        $mars = post::where("signe", "=", "negative")->where("created_at", '>=', $date1)->where("created_at", '<=', $date2)->count();
        array_push($statsN, $mars);

        $time = strtotime(date('Y-4-0 00:00:00'));
        $date1 = date('Y-m-d H:i:s', $time);
        $time = strtotime(date('Y-4-30 00:00:00'));
        $date2 = date('Y-m-d H:i:s', $time);
        $avril = post::where("signe", "=", "negative")->where("created_at", '>=', $date1)->where("created_at", '<=', $date2)->count();
        array_push($statsN, $avril);

        $time = strtotime(date('Y-5-0 00:00:00'));
        $date1 = date('Y-m-d H:i:s', $time);
        $time = strtotime(date('Y-5-31 00:00:00'));
        $date2 = date('Y-m-d H:i:s', $time);
        $mais = post::where("signe", "=", "negative")->where("created_at", '>=', $date1)->where("created_at", '<=', $date2)->count();
        array_push($statsN, $mais);


        $time = strtotime(date('Y-6-0 00:00:00'));
        $date1 = date('Y-m-d H:i:s', $time);
        $time = strtotime(date('Y-6-30 00:00:00'));
        $date2 = date('Y-m-d H:i:s', $time);
        $juin = post::where("signe", "=", "negative")->where("created_at", '>=', $date1)->where("created_at", '<=', $date2)->count();
        array_push($statsN, $juin);


        $time = strtotime(date('Y-7-0 00:00:00'));
        $date1 = date('Y-m-d H:i:s', $time);
        $time = strtotime(date('Y-7-31 00:00:00'));
        $date2 = date('Y-m-d H:i:s', $time);
        $juillet = post::where("signe", "=", "negative")->where("created_at", '>=', $date1)->where("created_at", '<=', $date2)->count();
        array_push($statsN, $juillet);

        $time = strtotime(date('Y-8-0 00:00:00'));
        $date1 = date('Y-m-d H:i:s', $time);
        $time = strtotime(date('Y-8-31 00:00:00'));
        $date2 = date('Y-m-d H:i:s', $time);
        $ao = post::where("signe", "=", "negative")->where("created_at", '>=', $date1)->where("created_at", '<=', $date2)->count();
        array_push($statsN, $ao);

        $time = strtotime(date('Y-9-0 00:00:00'));
        $date1 = date('Y-m-d H:i:s', $time);
        $time = strtotime(date('Y-9-30 00:00:00'));
        $date2 = date('Y-m-d H:i:s', $time);
        $sep = post::where("signe", "=", "negative")->where("created_at", '>=', $date1)->where("created_at", '<=', $date2)->count();
        array_push($statsN, $sep);


        $time = strtotime(date('Y-10-0 00:00:00'));
        $date1 = date('Y-m-d H:i:s', $time);
        $time = strtotime(date('Y-10-31 00:00:00'));
        $date2 = date('Y-m-d H:i:s', $time);
        $oct = post::where("signe", "=", "negative")->where("created_at", '>=', $date1)->where("created_at", '<=', $date2)->count();
        array_push($statsN, $oct);

        return response()->json([
            'message1' => $statsP,
            'message2' => $statsN
        ], 201);
    }

    public function getNumberPostsP(Request $request)
    {
        $post = post::where('signe', '=', 'positive')->get()->count();
        return response()->json($post, 201);
    }

    public function getNumberPostsN(Request $request)
    {
        $post = post::where('signe', '=', 'negative')->get()->count();
        return response()->json($post, 201);
    }

    public function getStoryUser(Request $request)
    {

        $date = today();
        $us = user_post::all();
        $users = array();
        $uniques = array();
        foreach ($us as $u) {
            array_push($users, $u);
        }

        for ($i = 0; $i < count($users); $i++) {
            // return response()->json($users[$i]->post_id, 401);
            if (abonnement::where('abonne_id', '=', Auth::id())->where('suivi_id', '=', $users[$i]->user_id)->first()) {
                $post = post::where('id', '=', $users[$i]->post_id)->first();
                $user = User::where('id', '=', $users[$i]->user_id)->first();
                $post->imageP = asset('/pictures/profile_pic/' . $post->imageP);
                $user->img = asset('/pictures/profile_pic/' . $user->img);
                $users[$i]->datePost = $user;
                //  $users[$i]->post_id=$post;
                //$h=$date->diffInHours($u->created_at);
                //return response()->json(($date->diffInHours($users[$i]->created_at, true)), 401);

                if (($date->diffInHours($users[$i]->created_at, true) <= 24)) {
                    $uniques[$users[$i]->user_id] = $users[$i];
                }
            }
        }
        $users1 = array();
        $users1 = array_values($uniques);
        return response()->json($users1, 201);
    }

    public function getStoryUserId(Request $request)
    {

        $date = today();
        $us = user_post::where('user_id', '=', $request->idUser)->get();
        $users = array();
        $users1 = array();
        foreach ($us as $u) {
            array_push($users, $u);
        }
        for ($i = 0; $i < count($users); $i++) {
            if (($date->diffInHours($users[$i]->created_at, true) <= 24)) {
                array_push($users1, $users[$i]->post_id);
            }
        }
        return response()->json($users1, 201);
    }
}
