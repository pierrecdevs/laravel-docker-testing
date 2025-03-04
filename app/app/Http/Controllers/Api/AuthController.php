<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoginToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * root of /api
     * @param Illuminate\Http\Request $request (unused)
     * @return Illuminate\Contracts\Routing\ResponseFactory::json
     */
    public function index(Request $request)
    {
        return response()->json(['status' => 200, 'message' => 'OK']);
    }

    /**
     * method: login(Request $request)
     * path: /api/login
     *
     * @param Illuminate\Http\Request $request (unused)
     * @return Illuminate\Contracts\Routing\ResponseFactory::json
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email|exists:users',
            'password' => 'required',
        ]);


        if (Auth::attempt($credentials)) {

            if ($request->hasSession()) {
                $request->session()->regenerate();
            }

            $user = auth()->guard('sanctum')->user();

            $token = $user->createToken($user->id);

            return response()->json([
                'status' => 200,
                'message' => [
                    'user' => $user,
                    'token' => $token->plainTextToken,
                ]
            ], 200);
        }

        return response()->json([
            'status' => 401,
            'message' => 'Could not authorize user',
        ], 401);
    }

    public function user(Request $request)
    {
        if (auth()->guard('sanctum')->check()) {
            return response()->json([
                'status' => 200,
                'message' => [
                    'user' => $request->user(),
                ]
            ], 200);
        } else {
            return response()->json(['status' => 401, 'message' => 'Unauthorized.'], 401);
        }
    }

    public function register(Request $request)
    {

        $fields = $request->validate([
            'email' => 'required|email|unique:users,email',
            'firstname' => 'required|string|max:32',
            'lastname' => 'required|string|max:33',
            'password' => 'required|string|confirmed',
        ]);


        try {

            $options = [
                'firstname' => $fields['firstname'],
                'lastname' => $fields['lastname'],
                'email' => $fields['email'],
                'password' => Hash::make($fields['password']),
            ];

            /** NOTE: Temporary for SL
             * if ($fields['avkey']) {
             *    $options['avkey'] = $fields['avkey'];
             *}
             */

            $user = User::create($options);

            $token = $user->createToken($user->id)->plainTextToken;

            Auth::login($user);
            if ($request->hasSession()) {
                $request->session()->regenerate();
            }

            $cookie = cookie('auth_token', $token, 60 * 24 * 7); // set the cookie for 7 days

            return response()
                ->json([
                    'status' => 201,
                    'message' => [
                        'user' => $user,
                        'token' => $token,
                    ]
                ], 201)
                ->withCookie($cookie);
        } catch (ValidationException $e) {
            return response()->json(['status' => 401, 'message' => $e->getMessage()], 401);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => $e->getMessage()], 500);
        }
    }


    /**
     * method: logout(Request $request)
     * path: /api/logout
     *
     * @param Illuminate\Http\Request $request (unused)
     * @return Illuminate\Contracts\Routing\ResponseFactory::json
     */
    public function logout(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user || ! Auth::check()) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        // NOTE: don't invalidate a session if it doesn't exist.
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $request->user()->tokens()->delete();
        // NOTE: Log out of any web sessions (although we're using SPA)
        auth('web')->logout();

        // NOTE: This is used in Sanctum, which just sets the user to null
        Auth::forgetUser();

        // NOTE: Invalidate any cookies the user's browser has.
        $cookie = cookie('XSRF-TOKEN', null, -1);

        return response()
            ->json([
                'status' => 200,
                'message' => 'logged out'
            ])
            ->withCookie($cookie);
    }

    public function verify(Request $request, $token)
    {
        $token = LoginToken::whereToken(hash('sha256', $token))->firstOrFail();

        if (!$request->hasValidSignature()) {
            return response()->json([
                'status' => 401,
                'message' => 'invalid sig',
            ], 401);
        }
        if (!$token->isValid()) {
            return response()->json([
                'status' => 401,
                'message' => 'invalid token',
            ], 401);
        }

        $token->consume();

        Auth::login($token->user);

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        $user = auth()->guard('sanctum')->user();

        $token = $user->createToken($user->id);

        $cookie = cookie('auth_token', $token->plainTextToken, 60 * 24 * 7); // set the cookie for 7 days

        return response()->json([
            'status' => 200,
            'message' => [
                'user' => $user,
                'token' => $token->plainTextToken,
            ]
        ])->withCookie($cookie);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'firstname' => 'required|string|max:32',
            'lastname' => 'required|string|max:32',
            'avkey' => 'required|string',
        ]);

        if (!$validated) {
            return response()->json([
                'status' => 401,
                'message' => 'invalid request',
            ], 401);
        }

        $user = User::where('avkey', $request->json('avkey'))->first();

        if ($user) {
            /**
             * NOTE: this is for SL
             * $url = $user->generateTokenUrl($user->avkey, $user->firstname, $user->lastname);
             */
            $url = $user->generateTokenUrl();
            $parts = parse_url($url);

            parse_str($parts['query'], $query);
            $path = $parts['path'];

            $url = $path . '?' . $parts['query'];
            return response()->json([
                'status' => 200,
                'message' => [
                    'url' =>  $url, //[
                    /*'query' => $query,*/
                    /*'path' => $path,*/
                    /*'host' => $parts['host'] . ':' . $parts['port'],*/
                    //]
                ],
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'User not found.',
            ], 404);
        }

        return response()->json([
            'status' => 400,
            'message' => 'Unknown issue.',
        ]);
    }
}
