<?php

namespace App\Traits;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

trait ApiResponser{

    /**
     *
     * Build Success Response
     * @param  string|array $data
     * @param int $code
     * @return Iluminate\Http\Response
     */

    public function successResponse($data,$code = Response::HTTP_OK){

        // return JsonResponse::create($data, 200, array('Content-Type'=>'application/json; charset=utf-8' ));
        // return response()->json($data);//header('Content-Type','application/json');
        return response($data,$code)->header('Content-Type','application/json');
    }



     /**
     *
     * Build Erros Response
     * @param  string|array $message
     * @param int $code
     * @return Iluminate\Http\JsonResponse
     */

    public function errorResponse($message,$code,$critical = true){
        if($critical){
        Log::stack(  ['stderr'])->critical($message);
        }
        return response()->json(['error' => $message ,'code'=>$code],$code);
    }


    /**
     *
     * Build Erros Response
     * @param  string|array $message
     * @param int $code
     * @return Iluminate\Http\Response
     */

    public function errorMessage($message, $code)
    {
        return response($message, $code)->header('Content-Type', 'application/json');
    }

    public function formatarData($data){
        if(!$data)
            return false;    
        $data = str_replace("/", "-", $data);
        return date('Y-m-d', strtotime($data));
    }

}