<?php
    session_start();
    $method = $_SERVER["REQUEST_METHOD"];
    switch(strtoupper($method)){
        case "POST":
            postHandler(requestbody());
            break;
        case "GET":
            getHandler();
            break;
        case "PUT":
            putHandler(requestBody());
            break;
        case "DELETE":
            deleteHandler();
            break;
        default:
            die("Unsupported request method");
    }

    function validRegistrationAttempt($json){
        return hasCredentials($json) && isset($json["nickname"]);
    }
    function hasCredentials($json){
        return isset($json["email"]) && isset($json["password"]);
    }
    function hasAction($json, $action){
        return isset($json["action"]) && $json["action"] === $action;
    }
    


    function putHandler($json){
        if(hasAction($json,"register")){
            register($json);
        }else{
            returnError("Unsupported action for this method");
        }
    }
    function postHandler($json){
        if(hasAction($json,"login")){
            login($json);
        }else if(hasAction($json,"sendMessage")){
            sendMessage($json);
        }
    }


    function register($json){
        if(validRegistrationAttempt($json)){
            $json["password"] = hashPassword($json["password"]);
            $stmt = getDb()->prepare("INSERT INTO users (email, password,nickname) VALUES (?,?,?)");
            if($stmt->execute([$json["email"], $json["password"], $json["nickname"]])){
                returnSuccess("User successfully created, you can login now");
            }else{
                returnError("The user cannot be created with these credential, please try again");
            }
        }else{
            returnError("Invalid registration attempt");
        }
    }

    function login($json){
        if(hasCredentials($json)){
            $json["password"] = hashPassword($json["password"]);
            $stmt = getDb()->prepare("SELECT id, nickname FROM WHERE email =? AND password = ?");
            $stmt->execute([$json["email"], $json["password"]]);
            $result = $stmt->fetchall(PDO::FETCH_ASSOC);
            if(count($result) === 1){
                $_SESSION["userId"] = $result[0]["id"];
                $_SESSION["nickname"] = $result[0]["nickname"];
                returnSuccess(["nickname" => result[0]["nickname"]]);
            }else{
                returnError("No user found with given credentials");
            }
            
            
        }else{
            returnError("Invalid login attempt");
        }
        
    }
    




    function responseBody($success, $data){
        return json_encode(["success" => $success, "data" => $data]);
    }
    function requestBody(){
        return json_decode(file_get_contents('php://input'),true);
    }
    function returnError($data){
        header('HTTP/1.1 400 Bad request', true, 400);
        header('Content-Type: application/json');
        die(responseBody(false, $data));
    }

    function returnSuccess($data){
        header('HTTP/1.1 200 OK', true, 200);
        header('Content-Type: application/json');
        echo(responseBody(true, $data));
    }









?>