<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\Responses;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
	public function register(Request $request)
    {
		try {
			$rulesInput = [
				'name' => 'required|string|max:45',
				'email' => 'required|string|max:45',
				'password' => 'required|string|max:45',
			];

			// validasi input
			$isValidInput = Validator::make($request->all(), $rulesInput);
			if (!$isValidInput->passes()) {
				$apiResp = Responses::unprocessable_entity($isValidInput->messages()->first());
				return new Response($apiResp, $apiResp['code']);
			}

			$newUser = User::create([
				'name' => $request->input('name'),
				'email' => $request->input('email'),
				'password' => Hash::make($request->input('password')),
			]);
			
			$apiResp = Responses::success('success create new user');
			$apiResp['data'] = [
				'user' => $newUser,
			];
			return (new Response($apiResp, $apiResp['code']));
		} catch (\Exception $e) {
			$apiResp = Responses::error($e->getMessage());
			return (new Response($apiResp, $apiResp['code']));
		}
    }
    
    public function login(Request $request)
    {
		try {
			$rulesInput = [
				'email' => 'required|string|max:45',
				'password' => 'required|string|max:45',
			];

			// validasi input
			$isValidInput = Validator::make($request->all(), $rulesInput);
			if (!$isValidInput->passes()) {
				$apiResp = Responses::unprocessable_entity($isValidInput->messages()->first());
				return new Response($apiResp, $apiResp['code']);
			}

			$user = User::where('email', $request->input('email'))->first();

			$now = time();
			$str = json_encode(['user_id' => $user->id]);
			$payload = [
				'iss' => 'BE-LARAVEL',
				'aud' => 'FRONTEND',
				'iat' => $now,
				'nbf' => $now,
				'exp' => $now + (24 * (60 * 60)),
				'data' => Crypt::encryptString($str),
			];
			$jwt = JWT::encode($payload, env('JWT_SECRET'), 'HS256');
			
			$apiResp = Responses::success('success login');
			$apiResp['data'] = [
				'token' => $jwt,
			];
			return (new Response($apiResp, $apiResp['code']));
		} catch (\Exception $e) {
			$apiResp = Responses::error($e->getMessage());
			return (new Response($apiResp, $apiResp['code']));
		}
    }
}