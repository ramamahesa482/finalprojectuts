<?php
 
namespace App\Http\Middleware;
 
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
// use Symfony\Component\HttpFoundation\Response;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Crypt;
use Firebase\JWT\Key;
use App\Responses;
 
class JWTMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
			$response = explode('Bearer ', $request->header('Authorization'));
			$jwt = trim($response[1]);

			$decodedJwt = JWT::decode($jwt, new Key(env('JWT_SECRET'), 'HS256'));
			$decodedData = Crypt::decryptString($decodedJwt->data);

			$request->merge([
				'auth' => $decodedData
			]);

			return $next($request);
		} catch (\Exception $e) {
			$apiResp = Responses::unauthorized('Unauthorized');
			return (new Response($apiResp, $apiResp['code']));
		}
    }
}