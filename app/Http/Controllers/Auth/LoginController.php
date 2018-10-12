<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Socialite;
use App\User;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';
    
     /**
     * Allowed portals for a login.
     *
     * @var array
     */
    protected $arrPortals = ["google", "github"];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider($strProvider)
    {
        if(in_array($strProvider, $this->arrPortals)){
            return Socialite::driver($strProvider)->redirect();
        }
        return abort(404);
    }

    
    /**
     * Obtain the user information from GitHub.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback($strProvider)
    {
        if(in_array($strProvider, $this->arrPortals)){
            $user = Socialite::driver($strProvider)->stateless()->user();
            
            switch ($strProvider){
                case 'google':
                case 'github':
                    $strUserEmail = $user->email;
                    break;
            }
            
            $objUser = User::username($strUserEmail)->active()->first();
            
            if($objUser instanceof \App\User){
                dd("user is already exist please sign in to account.");
            }
            else{
                $objUser = new User();
                
                switch ($strProvider){
                    case 'google':
                    case 'github':
                        $objUser->email = $user->email;
                        $arrName = explode(' ', $user->name);
                        $objUser->fname = $arrName[0];
                        $objUser->lname = $arrName[count($arrName)-1];
                        break;
                }
                $objUser->save();
            
                dd("Account is created successfully..");
            }
        }
        return abort(404);
        // $user->token;
    }
}
