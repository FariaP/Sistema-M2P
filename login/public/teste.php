<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use Twilio\Rest\Client;

$sid  = "ACf85723c28661513be923873c69dda725"; // Seu SID
$token = "f4d17ef66cb5473c6e1fd822a1651b85"; // Seu Token
$twilio = new Client($sid, $token);

// ** VARIÁVEIS DA MENSAGEM **
$numero_servico = "5";
$placa_veiculo = "ABC-1234";
$template_sid  = "HXc0f69be39da0e47b38c9b4c605c9a497"; // <<-- SUBSTITUA ESTE AQUI

$message = $twilio->messages->create(
    "whatsapp:+553285101972",
    [
        "from" => "whatsapp:+14155238886",

        // 1. O SID do template aprovado
        "contentSid" => $template_sid,

        // 2. Variáveis do template
        "contentVariables" => json_encode([
            "1" => $numero_servico,
            "2" => $placa_veiculo
        ])
    ]
);

print($message->sid);