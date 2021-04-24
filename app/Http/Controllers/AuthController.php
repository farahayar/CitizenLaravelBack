<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\User;
use App\superAdmin;

class AuthController extends Controller
{
    /**
     * Create user
     *
     */
    
    public function signup(Request $request)
    {
        //return response()->json (['message'=>$request->user],400);
       /*  $request->validate([
            'email' => 'required|string|email',
            'motDePass' => 'required|string',
            'remember_me' => 'boolean'
        ]); */
       $reqUser=json_decode($request->user);
        $user = new User([
            'nom' => $reqUser->_nom, 
            'email' => $reqUser->_email,
            'motDePass' => bcrypt($reqUser->_motDePass)
        ]);
        $user->prenom=$reqUser->_prenom;
        $user->dateNaiss=$reqUser->_dateNaiss;
        $user->adresse=$reqUser->_adresse;
        $user->cin=$reqUser->_cin;
        $user->tel=$reqUser->_tel;
        $user->admin="user";
        if ($request->hasFile('img')) {
            $imageExtensions = ['jpg', 'jpeg', 'png','jpe','JPG','JPEG','PNG','JPE']; 

                if(!in_array($request->file('img')->getClientOriginalExtension(), $imageExtensions))
                {
                    return response ()->json (['message'=>'Only image file'],400);

                }
                
            $file=$request->file('img');
            $file_name = date('YmdHis').'.'.$file->getClientOriginalExtension();
            $request->file('img')->move(public_path("/pictures/profile_pic/"),$file_name);
            $user->img = $file_name;
        }
        if(User::where('email','=',$user->email)->exists()){
            return response()->json([
                'message' => 'Email existant'
            ], 401);
        }
        $user->save();
        return response()->json([
            'message' => 'Successfully created user!'
        ], 201);
    }
  
    
    /**
     * Login user and create token
     *
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'motDePass' => 'required|string',
            'remember_me' => 'boolean'
        ]);
        $credentials = ['email' => $request->input('email'), 'password' => $request->input('motDePass')];
        if(!Auth::attempt($credentials))
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        Auth::login($user);
        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();
        return response()->json([
            'ifLoggedIn'=>auth()->check(),
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()
        ]);
    }

    public function signupSuperAdmin(Request $request)
    {
        //return response()->json (['message'=>$request->user],400);
        
       $reqUser=json_decode($request->user);

       //return response()->json (['message'=>$reqUser],400);
        $superAdmin = new superAdmin([
            'nom' => $reqUser->nom, 
            'email' => $reqUser->email,
            'motDePass' => bcrypt($reqUser->motDePass)
        ]);
        $superAdmin->prenom=$reqUser->prenom;
        $superAdmin->dateNaiss=$reqUser->dateNaiss;
        $superAdmin->adresse=$reqUser->adresse;
        $superAdmin->cin=$reqUser->cin;
        $superAdmin->tel=$reqUser->tel;
        if ($request->hasFile('img')) {
            $imageExtensions = ['jpg', 'jpeg', 'png','jpe','JPG','JPEG','PNG','JPE']; 

                if(!in_array($request->file('img')->getClientOriginalExtension(), $imageExtensions))
                {
                    return response ()->json (['message'=>'Only image file'],400);

                }
                
            $file=$request->file('img');
            $file_name = date('YmdHis').'.'.$file->getClientOriginalExtension();
            $request->file('img')->move(public_path("/pictures/profile_pic/"),$file_name);
            $superAdmin->img = $file_name;
        }
        if(superAdmin::where('email','=',$superAdmin->email)->exists()){
            return response()->json([
                'message' => 'Email existant'
            ], 401);
        }
        $superAdmin->save();
        return response()->json([
            'message' => 'Successfully created superAdmin!'
        ], 201);
    }
  

    public function loginSuperAdmin(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'motDePass' => 'required|string'
        ]);
        $credentials = ['email' => $request->input('email'), 'password' => $request->input('motDePass')];
        
        if(!superAdmin::where('email','=',$request->input('email'))->exists())
        {
            if(!Hash::check( $request->input('motDePass'),superAdmin::where('email','=',$request->input('email'))->first()->motDePass)) 
                return response()->json(['message' => $request->user()], 401);
        }
        $superAdmin = superAdmin::where('email','=',$request->input('email'))->first();
        //return response()->json(['message' => $request], 401);
        $tokenResult = $superAdmin->createToken('Personal Access Token');
        $token = $tokenResult->token;
        Auth::login($superAdmin);
        $token->save();
        return response()->json([
            'ifLoggedIn'=>auth()->check(),
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()
        ]);
    }

    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

  
    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    } 

    
  
}  