<?php

/*******************************************************************************
  Captura informações do Webhook do Youcanbook.me e envia para a API do Mautic
********************************************************************************
*
*   @property               Powertic
*   @author                 Luiz Eduardo - luiz@powertic.com
*   @author                 Rafael Queiroz
*   @license                GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*   @mautic-min-version     2.10.0
*
*/

include __DIR__ . '/vendor/autoload.php';

use Mautic\Auth\ApiAuth;
use Mautic\MauticApi;

// captura o palyload
$raw_data = file_get_contents('php://input');

// converte o payload em um array
$meeting = json_decode( $raw_data, true );

// debug array
// print_r($meeting);

$email = $meeting["email"];
$firstname = $meeting["firstName"];
$meetingdate = $meeting["meeting_date"];

session_start();

// preencha com o endereço do seu mautic
$mauticUrl = "https://mkt.site.com";

// preencha com um login e senha de um usuario do mautic
$credentials = array(
    'userName'   => "",
    'password'   => ""
);

// Conecta no objeto de autenticação através da BasicAuth
$initAuth = new ApiAuth();
$auth = $initAuth->newAuth($credentials, 'BasicAuth');

// Objeto do Mautic API
$api = new MauticApi();

// Nova instância do objeto Contact
$contactApi = $api->newApi('contacts', $auth, $mauticUrl);

// Cria o payload para a API do Mautic
$mautic_data = array(
    'email'        =>    $email,  // customize a variavel
    'firstname'    =>    $firstname,    // customize a variavel
    'meeting_date' => $meetingdate
);

// Pesquisa o contato pelo e-mail
// "email:luiz@powertic.com"
$response = $contactApi->getList("email:$email");
$json = json_encode($response);
$decodedJson = json_decode($json, true);

$id = 0;
foreach ($decodedJson as $lista) {
    foreach ($decodedJson["contacts"] as $listaTotal) {
        $id = $listaTotal["id"];
        break;
    }
    break;
}

// Permite criar um novo contato caso o contato especificado
// não seja encontrado
$createIfNotFound = true;

// Envia a requisição para o Mautic atualizar ou criar o contato
$convertedDate = new DateTime(strtotime($meetingdate), new DateTimeZone('America/Sao_Paulo'));
$contact = $contactApi->edit($id, $convertedDate, $createIfNotFound);

// finalizado
echo "OK";


//$convertedDate = new DateTime(strtotime($value), new DateTimeZone('America/Chicago'));

//print_r( $convertedDate );