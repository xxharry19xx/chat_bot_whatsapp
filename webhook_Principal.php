<?php
    const TOKEN_CHATBOT = "CHATBOTPHPAPIMETA";
    const WEBHOOK_URL = "link de tu servidor";

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

    function verificarConversacionTerminada($numero) {
        $file_path = "conversaciones_terminadas.txt"; 
        if (!file_exists($file_path)) {
            file_put_contents($file_path, "");  // Crear el archivo si no existe
        }
        $conversaciones_terminadas = file($file_path, FILE_IGNORE_NEW_LINES);
        return in_array($numero, $conversaciones_terminadas);
    }
    
    function marcarConversacionTerminada($numero) {
        $file_path = "conversaciones_terminadas.txt"; 
        file_put_contents($file_path, $numero . PHP_EOL, FILE_APPEND);
    }
    

    function recibirMensajes($req, $res) {
        try {
            $entry = $req['entry'][0];
            $changes = $entry['changes'][0];
            $value = $changes['value'];
            $objetomensaje = $value['messages'];
            $mensaje = $objetomensaje[0];
            
            // Extraer el ID del mensaje para la verificación de duplicados
            $message_id = $mensaje['id'];
            
            // Verificar y actualizar el archivo de mensajes procesados
            $file_path = "processed_messages.txt";
            if (!file_exists($file_path)) {
                file_put_contents($file_path, "");  // Crear el archivo si no existe
            }
            $processed_messages = file($file_path, FILE_IGNORE_NEW_LINES);
            
            // Evitar responder a mensajes ya procesados
            if (in_array($message_id, $processed_messages)) {
                $res->header('Content-Type: application/json');
                $res->status(200)->send(json_encode(['message' => 'EVENT_RECEIVED']));
                return;  // Termina la ejecución si el mensaje ya fue procesado
            } else {
                // Registrar el ID del mensaje como procesado
                file_put_contents($file_path, $message_id . PHP_EOL, FILE_APPEND);
            }
    
            // Procesar el mensaje y enviar la respuesta
            $comentario = $mensaje['text']['body'];
            $numero = $mensaje['from'];
            EnviarMensajeWhastapp($comentario, $numero);
    
            // Registrar el número en log.txt para seguimiento
            $archivo = fopen("log.txt", "a");
            $texto = json_encode($numero) . PHP_EOL;
            fwrite($archivo, $texto);
            fclose($archivo);
    
            // Confirmación de éxito para Meta
            $res->header('Content-Type: application/json');
            $res->status(200)->send(json_encode(['message' => 'EVENT_RECEIVED']));
            return;
        } catch (Exception $e) {
            // Manejar errores y enviar respuesta para evitar reintentos
            file_put_contents($file_path, $message_id . PHP_EOL, FILE_APPEND);
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
                    "body" => "🚀 Hola, visita mi web anderson-bastidas.com para más información.\n \n📌Por favor, ingresa un número #️⃣ para recibir información.\n \n1️⃣. Información del Curso. ❔\n2️⃣. Ubicación del local. 📍\n3️⃣. Enviar temario en pdf. 📄\n4️⃣. Audio explicando curso. 🎧\n5️⃣. Video de Introducción. ⏯️\n6️⃣. Hablar con AnderCode. 🙋‍♂️\n7️⃣. Horario de Atención. 🕜"
                ]
            ]);
        }else if ($comentario=='1') {
            $data = json_encode([
                "messaging_product" => "whatsapp",    
                "recipient_type"=> "individual",
                "to" => $numero,
                "type" => "text",
                "text"=> [
                    "preview_url" => false,
                    "body"=> "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum."
                ]
            ]);
        }else if ($comentario=='2') {
            $data = json_encode([
                "messaging_product" => "whatsapp",    
                "recipient_type"=> "individual",
                "to" => $numero,
                "type" => "location",
                "location"=> [
                    "latitude" => -6.7478572200449065,
                    "longitude" => -79.70192661483645,
                    "name" => "Mercado de Tuman",
                    "address" => "Frente a rayo sin soluciones"
                ]
            ]);
        }else if ($comentario=='3') {
            $data = json_encode([
                "messaging_product" => "whatsapp",    
                "recipient_type"=> "individual",
                "to" => $numero,
                "type" => "document",
                "document"=> [
                    "link" => "http://s29.q4cdn.com/175625835/files/doc_downloads/test.pdf",
                    "caption" => "Temario del Curso #001"
                ]
            ]);
        }else if ($comentario=='4') {
            $data = json_encode([
                "messaging_product" => "whatsapp",    
                "recipient_type"=> "individual",
                "to" => $numero,
                "type" => "audio",
                "audio"=> [
                    "link" => "https://filesamples.com/samples/audio/mp3/sample1.mp3",
                ]
            ]);
        }else if ($comentario=='5') {
            $data = json_encode([
                "messaging_product" => "whatsapp",
                "to" => $numero,
                "text" => array(
                    "preview_url" => true,
                    "body" => "Introducción al curso! https://youtu.be/6ULOE2tGlBM"
                )
            ]);
        }else if ($comentario=='6') {
            $data = json_encode([
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $numero,
                "type" => "text",
                "text" => array(
                    "preview_url" => false,
                    "body" => "🤝 En breve me pondré en contacto contigo. 🤓"
                )
            ]);
        }else if ($comentario=='7') {
            $data = json_encode([
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $numero,
                "type" => "text",
                "text" => array(
                    "preview_url" => false,
                    "body" => "📅 Horario de Atención: Lunes a Viernes. \n🕜 Horario: 9:00 a.m. a 5:00 p.m. 🤓"
                )
            ]);
        }else if (strpos($comentario,'gracias') !== false) {
            $data = json_encode([
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $numero,
                "type" => "text",
                "text" => array(
                    "preview_url" => false,
                    "body" => "Gracias a ti por contactarme. 🤩"
                )
            ]);
        }else if (strpos($comentario,'adios') !== false || strpos($comentario,'bye') !== false || strpos($comentario,'nos vemos') !== false || strpos($comentario,'adiós') !== false){
            $data = json_encode([
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $numero,
                "type" => "text",
                "text" => array(
                    "preview_url" => false,
                    "body" => "Hasta luego. 🌟"
                )
            ]);
        }
         else {
            $data = json_encode([
                "messaging_product" => "whatsapp",
                "recipient_type"=> "individual",
                "to" => $numero,
                "type" => "text",
                "text"=> [
                    "preview_url" => false,
                    "body"=> "🚀 Hola, visita mi web anderson-bastidas.com para más información.\n \n📌Por favor, ingresa un número #️⃣ para recibir información.\n \n1️⃣. Información del Curso. ❔\n2️⃣. Ubicación del local. 📍\n3️⃣. Enviar temario en pdf. 📄\n4️⃣. Audio explicando curso. 🎧\n5️⃣. Video de Introducción. ⏯️\n6️⃣. Hablar con AnderCode. 🙋‍♂️\n7️⃣. Horario de Atención. 🕜"
                ]
            ]);
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