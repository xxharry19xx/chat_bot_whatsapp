<?php
    const TOKEN_CHATBOT = "CHATBOTPHPAPIMETA";
    const WEBHOOK_URL = "https://rojasmarkert.com/webhook.php";

    function verificarToken($req,$res){
        try{
            $token = $req['hub_verify_token'];
            $challenge = $req['hub_challenge'];

            if (isset($challenge) && isset($token) && $token == TOKEN_CHATBOT){
                $res->send($challenge);
            }else{
                $res ->status(400)->send();
            }

        }catch(Exception $e){
            $res ->status(400)->send();
        }
    }

    function recibirMensajes($req,$res){
        try{
            $entry = $req['entry'][0];
            $changes = $entry['changes'][0];
            $value = $changes['value'];
            $objetomensaje = $value['messages'];
            $mensaje = $objetomensaje[0];

            // Procesar el mensaje y enviar respuesta
            $comentario = $mensaje['text']['body'];
            $numero = $mensaje['from'];
            EnviarMensajeWhastapp($comentario,$numero);

            $archivo = fopen("log.txt","a");
            $texto = json_encode($numero);
            fwrite($archivo,$texto);
            fclose($archivo);

            // Enviar respuesta de éxito
            $res->header('Content-Type: application/json');
            $res->status(200)->send(json_encode(['message' => 'EVENT_RECEIVED']));
        }catch(Exception $e){
            $res->header('Content-Type: application/json');
            $res->status(200)->send(json_encode(['message' => 'EVENT_RECEIVED']));
        }
    }

    function EnviarMensajeWhastapp($comentario,$numero){
        $comentario = strtolower($comentario);

        if (strpos($comentario, 'hola') !== false) {
            $data = json_encode([
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $numero,
                "type" => "text",
                "text" => [
                    "preview_url" => false,
                    "body" => "Hola visita mi web"
                ]
            ]);
        } else {
            // Agregar más condiciones para responder a otros mensajes
        }

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-type: application/json\r\nAuthorization: Bearer EAAMZBy2GZAr7ABO5NTdxvbodSqXWIeSmpi9YC72VGwK9GuEc1cJuZCvNiovnlZBQ6VhXlMCSkg94gY3VEgApwWAS25ogy0zRmFvwbtO1Y9E4f4z3LMESreO3L6bLYsnC3EUn78erXoIu29x3cmsSbwto2XBQ9T3cFBSbX5oiZCmdF6UwZCRvz1C6PwlFmuzQjoTfS8Gv4g1bthZBXGm9TmglxaxGY3ZAjhEsK0fDtizWl0kZD\r\n",
                'content' => $data,
                'ignore_errors' => true
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents('https://graph.facebook.com/v21.0/279602398567489/messages', false, $context);

        if ($response === false) {
            echo "Error al enviar el mensaje\n";
        } else {
            echo "Mensaje enviado correctamente\n";
        }
    }

    if ($_SERVER['REQUEST_METHOD']==='POST'){
        $input = file_get_contents('php://input');
        $data = json_decode($input,true);

        recibirMensajes($data,http_response_code());
    }else if($_SERVER['REQUEST_METHOD']==='GET'){
        if(isset($_GET['hub_mode']) && isset($_GET['hub_verify_token']) && isset($_GET['hub_challenge']) && $_GET['hub_mode'] === 'subscribe' && $_GET['hub_verify_token'] === TOKEN_CHATBOT){
            echo $_GET['hub_challenge'];
        }else{
            http_response_code(403);
        }
    }
?>