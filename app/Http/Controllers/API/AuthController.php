<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Lang;

class AuthController extends BaseController
{
    /**
     * register a user
     *
     * @param Request $request
     * @return response
     */
    public function register(Request $request)
    {
        try {
            $input = $request->only('first_name', 'last_name', 'email', 'username', 'password', 'confirm_password', 'mobile', 'role');
                
            $validator = Validator::make($input,[
                'first_name'        => 'required|string|max:255',
                'last_name'         => 'required|string|max:255',
                'email'             => 'required|string|email|max:255|unique:users',
                'username'          => 'required|string|max:255|unique:users',
                'password'          => 'required|string|min:8',
                'confirm_password'  => 'required|string|same:password',
                'mobile'            => 'required|integer',
                'role'              => 'required|string',
            ]);

            if ($validator->fails()) {
                return $this->handleError('Required field missing.', $validator->errors()->all()[0].' Please check and try again.', 422);
            }

            $user = User::create([
                'first_name'    => $request->first_name,
                'last_name'     => $request->last_name,
                'email'         => $request->email,
                'username'      => $request->username,
                'password'      => Hash::make($request->password),
                'mobile'        => $request->mobile,
                'role'          => $request->role,
            ]);
			$user->sendEmailVerificationNotification();

            $token = $user->createToken('auth_token')->plainTextToken;

            $result = [
                'access_token' => $token,
                'token_type' => 'Bearer',
            ];

            return $this->handleResponse($result, 'User registered successfully and email verification link sent to yor mail address!');
            
        }
        catch(Exception $e) {
            logger('registration error');
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    /**
     * login a user
     *
     * @param Request $request
     * @return response
     */
    public function login(Request $request)
    {
        try {

            $input = $request->only('username', 'password');
                
            $validator = Validator::make($input,[
                'username'          => 'required',
                'password'          => 'required',
            ]);

            if ($validator->fails()) {
                return $this->handleError('Required field missing.', $validator->errors()->all(), 422);
            }

            if (!Auth::attempt($request->only('username', 'password'))) {
                return $this->handleError('Email/Password are invalid. Please check your details and try again.', [], 401);
            }
            $user = User::select('id','first_name', 'last_name', 'mobile', 'email', 'role', 'email_verified_at')
            ->where('username', $request['username'])->firstOrFail();
            
			if(!$user->email_verified_at) {
                return $this->handleError('Email not verified',[], 422);
            }
			
            $token = $user->createToken('auth_token')->plainTextToken;

            $result = [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ];

            return $this->handleResponse($result, 'User logged in successfully!');
            
        }
        catch(Exception $e) {
            logger('login error');
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    /**
     * send reset link
     *
     * @param Request $request
     * @return response
     */
    public function sendResetLinkResponse(Request $request)
    {
        try {

            $input = $request->only('email');
            $validator = Validator::make($input, 
                ['email' => "required|email"]
            );
            
            if ($validator->fails()) {
                return $this->handleError([], $validator->errors()->all(), 422);
            }
            $response =  Password::sendResetLink($input);
            
            if($response == Password::RESET_LINK_SENT){
                return $this->handleResponse([], "An email with the reset link has been sent, please check.");
            }
            logger($response);
                    
            return $this->handleError('Email could not be sent to this email address', [], 401);
            
        }
        catch(Exception $e) {
            logger('reset list send error: '.$e->getMessage());
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    /**
     * set reset password
     *
     * @param Request $request
     * @return response
     */
    public function sendResetResponse(Request $request)
    {
        try {

            $input = $request->only('email','token', 'password', 'password_confirmation');
            
            $validator = Validator::make($input, 
                [
                    'token' => 'required',
                    'email' => 'required|email',
                    'password' => 'required|confirmed|min:8',
                ]);
            
            if ($validator->fails()) {
                return $this->handleError([], $validator->errors()->all(), 422);
            }
                
            $response = Password::reset($input, function ($user, $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
                //$user->setRememberToken(Str::random(60));
                event(new PasswordReset($user));
            });
            
            if($response == Password::PASSWORD_RESET){
                return $this->handleResponse([], "Password reset successfully");
            }
                
            return $this->handleError([], 'Email could not be sent to this email address', 401);
            
        }
        catch(Exception $e) {
            logger('send reset response error');
            return $this->handleError('Something went wrong', [], 500);
        }
    }

    /**
     * reset password
     *
     * @param Request $request
     * @return response
     */
    public function resetPassword(Request $request)
    {
        try {
            $input = $request->only('email', 'hash', 'expires');
            
            $validator = Validator::make($input, 
                [
                    'email' => 'required|email',
                    'hash' => 'required',
					'expires' => 'required',
                ]);
            
            if ($validator->fails()) {
                return $this->handleError([], $validator->errors()->all(), 422);
            }
	
            if((sha1($request['email']) !=  $request['hash']) || ($request['expires'] < Carbon::now()->timestamp)) {
                //return $this->handleError([],'The link seems to have expired, please check or reset your password again.', 422);
				$response = [
					'status' => false,
					'message' => "The link seems to have expired, please check or reset your password again."
				];
				return redirect()->to(env('APP_FRONTEND_URL'))->withCookie(cookie('password_reset', json_encode($response), time() + 10*60, '/', '99ideaz.com', false));
            }

            $user = User::select('id','first_name', 'last_name', 'mobile', 'email', 'role')
            ->where('email', $request['email'])->first();

            if($user) {
                $password = Str::random(8);
                $user->forceFill(['password' => Hash::make($password)])->save();

				$user->original = $password;
                event(new PasswordReset($user));
                
                // return $this->handleResponse([], "You're password has been reset successfully and sent on email, please check.");
				$response = [
					'status' => true,
					'message' => "You're password has been reset successfully and sent on email, please check."
				];
				return redirect()->to(env('APP_FRONTEND_URL'))->withCookie(cookie('password_reset', json_encode($response), time() + 10*60, '/', '99ideaz.com', false));
            }          
                
            //return redirect()->to(env('APP_FRONTEND_URL'))->withCookie(cookie('password_reset', false));
			$response = [
				'status' => false,
				'message' => "You're password reset failed, please try again later."
			];
			return redirect()->to(env('APP_FRONTEND_URL'))->withCookie(cookie('password_reset', json_encode($response), time() + 10*60, '/', '99ideaz.com', false));
            
        }
        catch(Exception $e) {
            logger('reset password error');
            return $this->handleError('Something went wrong', [], 500);
        }
    }
	
	public function verify($user_id, Request $request) {
        if (!$request->hasValidSignature()) {
            //return $this->handleError('Invalid/Expired url provided.', [], 401);
			$response = [
				'status' => false,
				'message' => "The link seems to have expired or invalid, please check or verify again."
			];
			return redirect()->to(env('APP_FRONTEND_URL'))->withCookie(cookie('email_verify', json_encode($response), time() + 10*60, '/', '99ideaz.com', false));
        }
    
        $user = User::findOrFail($user_id);
    
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }
    
        //return redirect()->to(env('APP_FRONTEND_URL'))->withCookie(cookie('email_verified', true));
		$response = [
			'status' => true,
			'message' => "Your email verified successfully"
		];
		return redirect()->to(env('APP_FRONTEND_URL'))->withCookie(cookie('email_verify', json_encode($response), time() + 10*60, '/', '99ideaz.com', false));
		//return $this->handleResponse([], "Email verified successfully");
		//return 'Email verified'; 
    }
    
    public function resend() {
        if (auth()->user()->hasVerifiedEmail()) {
            return $this->handleError('Email already verified.', [], 400);
        }
    
        auth()->user()->sendEmailVerificationNotification();
    
        return $this->handleResponse([], "Email verification link sent on your email id");
    }
}
