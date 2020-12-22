<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponser;

class VoosFlightsModel extends Model 
{
    use ApiResponser;
    protected $table="voos_flights";

    // public function __construct($type = null) {

    //     parent::__construct();

    //     $this->setTable($type);
    // }
    
    public function gravarVoos($voos = array()){
        $totalReg = [];
        $totalReg['inseridos'] = 0;
        $totalReg['erros'] = 0;
        $delete = DB::delete('delete from voos_flights'); //Limpar tabela antes para receber os dados novos da API endPoint Flights
        if($delete)
            DB::delete('delete from grupos_voos'); //remover todos os agrupamentos existentes

        //Insere os novos itens recebidos da API endPoint Flights
        foreach($voos as $dados){
            if(!empty($dados->fare)){ //Mantém a integridade de registros válidos que possuem tarifas
                $departureDate = empty($dados->departureDate)?null:$this->formatarData($dados->departureDate);
                $arrivalDate = empty($dados->arrivalDate)?null:$this->formatarData($dados->arrivalDate);
                $dateTime = date('Y-m-d H:i:s');
                $row = ['id' => $dados->id,
                        'cia' => "$dados->cia", 		
                        'fare' => "$dados->fare", 		
                        'flightNumber' => "$dados->flightNumber",
                        'departureDate' => "$departureDate",
                        'arrivalDate' => "$arrivalDate", 
                        'departureTime' => "$dados->departureTime", 
                        'arrivalTime' => "$dados->arrivalTime", 
                        'origin' => "$dados->origin", 		
                        'destination' => "$dados->destination", 
                        'price' => "$dados->price",
                        'outbound' => $dados->outbound,	
                        'inbound' => $dados->inbound, 	
                        'classService' => $dados->classService, 	
                        'tax' => "$dados->tax",
                        'duration' => "$dados->duration",
                        'created_at' => "$dateTime",
                        'updated_at' => "$dateTime",
                ]; 
                $resultado = DB::table('voos_flights')->insert($row);
                if($resultado){
                    $totalReg['inseridos'] = $totalReg['inseridos'] + 1;
                }else {
                    $totalReg['erros'] = $totalReg['erros'] + 1;
                }
            }    
        }
        return $totalReg;
    }
    // @param $rotas se realmente for uma rota desejada - caso contrário retorna o Agrupamento Geral da Documentação
    public function procurarGrupos($rota = null){ 
        $whereIda = "";
        $whereVolta = "";
        if(!empty($rota)){ //Para a Rota
            $whereIda = "WHERE origin = '".$rota->originIda."' AND destination = '".$rota->destinationIda."' AND departureDate = '".$rota->departureDateIda."' ";
            $whereVolta = "WHERE origin = '".$rota->originVolta."' AND destination = '".$rota->destinationVolta."' AND departureDate = '".$rota->departureDateVolta."' ";
            $resultado = DB::select("SELECT * FROM (
                                        SELECT v.*, gv.grupo  
                                            FROM voos_flights v
                                        INNER JOIN grupos_voos gv ON gv.codigo_voo = v.id
                                        $whereIda
                                    
                                    UNION 
                                    
                                        SELECT v.*, gv.grupo  
                                            FROM voos_flights v
                                        INNER JOIN grupos_voos gv ON gv.codigo_voo = v.id
                                        $whereVolta
                                    )R
                                    ORDER BY R.grupo, R.id ASC");
            return $resultado;

        }
        else //Agrupamento Geral
            $resultado = DB::select('SELECT v.*, gv.grupo
                                        FROM voos_flights v
                                    INNER JOIN grupos_voos gv ON gv.codigo_voo = v.id

                                    ORDER BY gv.grupo, v.id ASC');    

        // $results = DB::select('select * from users where id = :id', ['id' => 1]);    
        // $grupos = DB::table('voos_flights')->orderBy('price', 'asc')->get(); 
        return $resultado;
    }

    public function buscarGruposTotalizado($rota = null){

        $whereIda = "";
        $whereVolta = "";
        if(!empty($rota)){//Para a Rota
            $whereIda = "WHERE origin = '".$rota->originIda."' AND destination = '".$rota->destinationIda."' AND departureDate = '".$rota->departureDateIda."' ";
            $whereVolta = "WHERE origin = '".$rota->originVolta."' AND destination = '".$rota->destinationVolta."' AND departureDate = '".$rota->departureDateVolta."' ";
            $resultado = DB::select("SELECT SUM(t.price) AS total,t.fare,t.grupo FROM (
                                        SELECT v.price,v.fare,gv.grupo
                                            FROM voos_flights v
                                        INNER JOIN grupos_voos gv ON gv.codigo_voo = v.id
                                        $whereIda
                                        GROUP BY v.price,v.fare,gv.grupo
                                        
                                        UNION
                                        
                                        SELECT v.price,v.fare,gv.grupo
                                            FROM voos_flights v
                                        INNER JOIN grupos_voos gv ON gv.codigo_voo = v.id
                                        $whereVolta
                                        GROUP BY v.price,v.fare,gv.grupo
                                    )t
                                    GROUP BY t.fare,t.grupo
                                    ORDER BY t.grupo ASC");
            return $resultado;
        }else
            $resultado = DB::select('SELECT SUM(t.price) AS total,t.fare,t.grupo FROM (
                                        SELECT v.price,v.fare,gv.grupo
                                            FROM voos_flights v
                                        INNER JOIN grupos_voos gv ON gv.codigo_voo = v.id
                                        GROUP BY v.price,v.fare,gv.grupo
                                        ORDER BY gv.grupo ASC )t
                                    GROUP BY t.fare,t.grupo');    
        return $resultado;
    }

    //samples binding
    // $results = DB::select('select * from users where id = :id', ['id' => 1]);    

}
