<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponser;
use App\Models\VoosFlightsModel;
use Illuminate\Http\Request;
use stdClass;

class VoosFlightsController extends Controller
{
    use ApiResponser;
    private $voos = "";
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->voos = new VoosFlightsModel();
    }
    
    /**
     * Responsável por buscar todos os voos da API Flights
     */
    public function buscarVoos(){
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://prova.123milhas.net/api/flights",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $data = json_decode($response);
            // Gravar os voos no banco para não precisar consultar a API a todo momento
            $resultado = $this->tratarVoosRecebidosAPI($data);
            return 'Os Voos Flights foram atualizados!!! Total de registros: '. $resultado['inseridos']."<br>Total de erros: ".print_r($resultado['erros'],true);
        }
    }

    /**
     * Method responsável por gravar no banco os voos recebidos da API
     */
    private function tratarVoosRecebidosAPI($voos = array()){
        $resultado = $this->voos->gravarVoos($voos);
        $this->criarAgrupamentos(); //Definir os agrupamentos dos Voos
        return $resultado;
    }

    /**
     * @author: Washington Monteiro
     * @since: 20/12/2020 V1.
     * Method responsável por definir os agrupamentos dos voos Ida e Volta
     */
    private function criarAgrupamentos(){
        //Capturar Voos que são de Ida
        $vIda = DB::table('voos_flights')->where('outbound', '=', 1)->orderBy('fare', 'asc')->get();    
        //Capturar Voos que são de Saida
        $vSaida = DB::table('voos_flights')->where('inbound', '=', 1)->orderBy('fare', 'asc')->get();    
        
        /**
         * @author: Washington Monteiro
         * @since: 20/12/2020 V1.
         * Observação meu entendimento dos Voos recebidos da API Flights - Seguindo o que foi proposto na documentação para elaboração do código
         * Obtive o seguinte entendimento: Todos os Voos que são de Ida precisam de pelo menos uma volta
         * desta forma todas as Idas conseguem abordar todas as voltas, considerando que a cada volta precisei ter antes um conjunto de Idas que o cliente podia escolher
         * de mesmo tipo de TARIFA e selecionar a sua volta desejada do grupo de Voos.
         */

        $volta = 0;
        $grupo = 1;
        for($i=0;$i<count($vSaida);$i++){
            foreach ($vIda as $ida){
                if($vSaida[$i]->fare == $ida->fare){
                    //Insere a Volta referente ao grupo Current de Idas
                    if($volta == 0){
                        $rowSaida = ['codigo_voo' => $vSaida[$i]->id,'grupo' => $grupo]; 		
                        DB::table('grupos_voos')->insert($rowSaida);  
                        $volta++;     
                    }
                    
                    //São voos do mesmo grupo - Insere as Idas referente ao grupo Current = $grupo
                    $row = ['codigo_voo' => $ida->id,'grupo' => $grupo]; 		
                    DB::table('grupos_voos')->insert($row);
                }
            }
            $volta=0; // Limpar variável
            $grupo++;    
        }
    }

    public function buscarAgrupamentos($rotas = null){
        $gruposFinal = [];
        $flights = DB::table('voos_flights')->orderBy('price', 'asc')->get(); 
        $grupos = $this->buscarGrupos($rotas);
        $gruposTotais = $this->buscarGruposTotalizado($rotas);
        $grAtualID = "";
        $totalGrAtual = "";
        $outbound = [];
        $inbound = [];
        $totalDeGrupos = count($gruposTotais);
        $totalDeVoosUnicos = count($flights);
        $menorPreco = 999999999999999;
        $idMenorPreco = "";
        if(is_array($grupos)){
            if(!empty($grupos)){
                foreach($gruposTotais as $value){
                    $grAtualID = $value->grupo;
                    $totalGrAtual = $value->total;

                    if(floatVal($menorPreco) > floatVal($value->total)){
                        $idMenorPreco = $value->grupo;
                        $menorPreco = $value->total;
                    }

                    for($i=0;$i<count($grupos);$i++){ // $grupos Array retorno consulta do banco - possui os grupos
                        if($value->grupo == $grupos[$i]->grupo){
                            //Verifica se registro é referente ao Voo de Ida ou Saída
                            if(intVal($grupos[$i]->outbound)==1 && ($grupos[$i]->grupo == $grAtualID)){
                                unset($grupos[$i]->created_at);
                                unset($grupos[$i]->updated_at);
                                $outbound[] = $grupos[$i];
                            }    
                            else if (intVal($grupos[$i]->outbound)==0 && ($grupos[$i]->grupo == $grAtualID)){
                                unset($grupos[$i]->created_at);
                                unset($grupos[$i]->updated_at);
                                $inbound[] = $grupos[$i];
                            }
                        }
                    }
                    $gruposFinal[] = array("uniqueId"=>$grAtualID,"totalPrice"=>$totalGrAtual,"outbound"=>$outbound,"inbound"=>$inbound);
                    $grAtualID = "";
                    $totalGrAtual = "";
                    $outbound = array();
                    $inbound = array();
                }
            }
        }
        $this->removerElementoGrupos($gruposFinal,'grupo'); // remover a chave de identificação de grupo para as Idas e Saídas
        $data = ['flights'=>$flights,'groups'=>$gruposFinal,"totalGroups"=>$totalDeGrupos,"totalFlights"=>$totalDeVoosUnicos,"cheapestPrice"=>$menorPreco,"cheapestGroup"=>$idMenorPreco];
        //print_r(json_encode($data));
        
        if(!empty($rotas))
            return $data;
        else    
            return $this->successResponse($data);
    }

    private function buscarGrupos(){
        $resultado = $this->voos->procurarGrupos();
        return $resultado;
    }
    
    private function buscarGruposTotalizado(){
        $resultado = $this->voos->buscarGruposTotalizado();
        return $resultado;
    }

    function removerElementoGrupos($array, $key){
        foreach($array as $subKey => $subArray){
            for($i=0;$i<count($subArray['outbound']);$i++){
                unset($subArray['outbound'][$i]->grupo);
            }
            for($i=0;$i<count($subArray['inbound']);$i++){
                unset($subArray['inbound'][$i]->grupo);
            }
        }
        return $array;
    }

    public function buscarRota(Request $request){
        if ($this->validarRegistro($request)) {
            $data = $this->buscarAgrupamentos($request);
            return $this->successResponse($data);
        }
    }

    /**
     * @param Request $request
     * @return array
     * @throws ValidationException
     */
    public function validarRegistro(Request $request)
    {
        return $this->validate(
            $request,
            [
                'originIda' => 'required',
                'originVolta' => 'required',
                'departureDateIda' => 'required|max:10',
                'departureDateVolta' => 'required|max:10',
                'destinationIda' => 'required',
                'destinationVolta' => 'required',
            ]
        );
    }

}
