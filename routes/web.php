<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version().nl2br("\n\n Projeto 123Milhas - Prova Teste \n\n@Author: Washington do Nascimento Monteiro - E-mail: wasmont@gmail.com \n\n@Since: 21/12/2020 V1. \n\n API Requer Autenticacao com utilizacao de Token!");
});

/**
 * String deve ter 32 caracteres
 * A chave pode ser definida no arquivo de ambiente .env
 */
$router->get('/key', function() {
    return \Illuminate\Support\Str::random(32);
});

// JOB para atualizar tabelas de Integração com a API Voos Flights
$router->get('buscarVoos', ['as' => 'buscarVoos', 'uses' => 'VoosFlightsController@buscarVoos']);

// API
$router->group(['prefix' => 'api'], function () use ($router) {
    $router->get('/', function () {
        return microtime();
    });
    $router->post('/register', 'AuthController@register');
    $router->post('/login', 'AuthController@login');   
     //Metodo que só pode ser acessado com o usuário autenticado
    $router->group(['middleware' => 'auth'], function () use ($router) {
        $router->post('/buscarAgrupamentos', 'VoosFlightsController@buscarAgrupamentos'); // Agrupamento da Documentação - return
        $router->post('/buscarRota', 'VoosFlightsController@buscarRota'); // Criar uma Rota de Ida / Volta - Vários grupos de Voos
        $router->post('/me', 'AuthController@me'); // Testes de Implementação segurança com utilização de geração de Tokens a cada 4hrs JWT
    });
});
// FIM API
