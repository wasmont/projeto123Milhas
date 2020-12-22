<?php

    namespace App\Http\Controllers;

    use App\Models\User;
    use App\Traits\ApiResponser;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Illuminate\Validation\ValidationException;

    class AuthController extends Controller
    {
        use ApiResponser;

        /**
         * Create a new controller instance.
         *
         * @return void
         */
        public function __construct(){}

        /**
         * @param Request $request
         * @return array
         * @throws ValidationException
         */
        public function isRegisterValid(Request $request)
        {
            return  $this->validate(
                $request,
                [
                    'name' => 'required',
                    'email' => 'required|email|unique:users',
                    'password' => 'required|min:5'
                ]
            );
        }

        /**
         * @param Request $request
         * @return App\Traits\Iluminate\Http\Response|App\Traits\Iluminate\Http\JsonResponse|void
         * @throws ValidationException
         */
        public function register(Request $request)
        {
            if ($this->isRegisterValid($request)) {
                try {
                    $user = new User();
                    $user->password = $request->password;
                    $user->email = $request->email;
                    $user->name = $request->name;
                    $user->save();
                    return $this->successResponse($user);
                } catch (\Exception $e) {
                    return $this->errorResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
                }
            }
        
        }

        /**
         * @param Request $request
         * @return array
         * @throws ValidationException
         */
        public function isLoginValid(Request $request)
        {

            return $this->validate($request, [
                'email' => 'required|string',
                'password' =>  'required|string'
            ]);
        }

        /**
         * @param Request $request
         * @return App\Traits\Iluminate\Http\Response|void
         * @throws ValidationException
         */
        public function login(Request $request)
        {
            if ($this->isLoginValid($request)) {
                $credentials = $request->only(['email', 'password']);

                $token = auth()->setTTL(env('JWT_TTL','3600'))->attempt($credentials);
                if($token){
                    return $this->respondWithToken($token);
                }else{
                    return $this->errorResponse('User not found', Response::HTTP_NOT_FOUND);
                }
            }
        }

        public function me(){
            $user = auth()->user();
            return $this->successResponse($user);
        }
        
    }