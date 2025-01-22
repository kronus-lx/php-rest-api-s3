<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    header("Access-Control-Allow-Methods: GET, POST");
    header("Access-Control-Allow-Origin: *");

    $url = $_SERVER["REQUEST_URI"];
    $method = $_SERVER["REQUEST_METHOD"];
    $scriptName = dirname($_SERVER["SCRIPT_NAME"]);    
    
    $parameters = parse_url($url) ?: [];                           // Default to empty array if no parameters are present
    $queries = $parameters['query'] ?? "";                        // Default to an empty string if no query is present
    $uri = $parameters['path'] ?? "";                            // Default to uri 
    $requestBody = file_get_contents('php://input') ?: "";      // Acquire Request Body

    $params = [];

    if (!empty($queries)) { parse_str($queries, $params); }
    
    $api = str_replace($scriptName, '', $uri);
    $path = trim($api, '/');
    $parts = explode('/', $path);
  
    $tag = $parts[0];
    
    include "src/Credentials.php";
    include "src/Router.php";

    $credentials = new Credentials();
    $setAuth = strcmp($tag, "authorisation");
    
    if ($tag == "" || $tag != "api") {
        if ($tag == "") {
            http_response_code(404);
            exit;
        } else if ($setAuth === 0) {
            $credentials->setAuthorisation($params);
            $result = $credentials->write(); 
            echo json_encode(["result" => $result]);
            exit;
        } else {
            http_response_code(404);
            exit;
        }
    }
    
    if(!$credentials->read("credentials.json")){
        http_response_code(500);
        header("Content-Type: application/json");
        echo json_encode(["error" => "Invalid Credntials Check format"]);
        exit;
    }
    
    $router = new Router($method, $params, $requestBody);
    $router->routeEndpoint($parts, $credentials);

    exit;
?>