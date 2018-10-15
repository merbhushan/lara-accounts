<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Socialite;
use App\User;
use DB;
use Illuminate\Http\Request;

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
    public function redirectToProvider($strProvider, Request $request)
    {
        if(in_array($strProvider, $this->arrPortals)){
            return Socialite::driver($strProvider)->redirect();
            return Socialite::driver($strProvider)
                ->with(['redirect_url' => $request->redirect_url])
                ->redirect();
        }
        return abort(404);
    }

    // Function is being used to login using api call
    public function apiLogin(Request $request){
        if ($this->guard()->attempt(['email' => $request->email, 'password' => $request->password]))
        {
            return $this->startUserSessoin($this->guard()->user());
        }
        else{
            return redirect('api/error/INVALID_USER');
        }
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
            
            if(!$objUser instanceof \App\User){
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
            }
            return $this->startUserSessoin($objUser);
        }
        return abort(404);
    }

    public function startUserSessoin($objUser){
        $objRoleBasedScope = DB::table('scopes')
            ->leftJoin('scope_role', 'scopes.id', '=', 'scope_role.scope_id')
            ->leftJoin('user_role', 'user_role.role_id', '=', 'scope_role.role_id')
            ->select(DB::raw('scopes.name as scope, scopes.portal_id as portal_id'))
            ->where('user_role.user_id', $objUser->id)
            ->where('scopes.is_active', 1);
                
        $arrUserScopes = DB::table('scopes')
            ->leftJoin('scope_user', 'scopes.id', '=', 'scope_user.scope_id')
            ->select(DB::raw('scopes.name as scope, scopes.portal_id as portal_id'))
            ->where('scope_user.user_id', $objUser->id)
            ->where('scopes.is_active', 1)
            ->union($objRoleBasedScope)
            ->get()
            ->groupBy('portal_id')
            ->map(function($item, $key)use($objUser){
                return $objUser->createToken("Meditab", $item->pluck('scope')->toArray())->accessToken;
            })
            ->toArray();
            
            return view('login')->with('arrUserScopes', $arrUserScopes);
    }
}
