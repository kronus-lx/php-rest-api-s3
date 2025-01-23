
<?php
    include "src/Credentials.php";
    include "src/Router.php";

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    header("Access-Control-Allow-Methods: GET, POST");
    header("Access-Control-Allow-Origin: *");

    $url = $_SERVER["REQUEST_URI"];
    $method = $_SERVER["REQUEST_METHOD"];
    $scriptName = dirname($_SERVER["SCRIPT_NAME"]);    
    
    $parameters = parse_url($url) ?: [];
    $queries = $parameters['query'] ?? "";
    $uri = $parameters['path'] ?? "";                         
    $requestBody = file_get_contents('php://input') ?: "";

    $params = [];

    if (!empty($queries)) { parse_str($queries, $params); }
    
    if(!empty($params['url'])){
        $params['url'] = urldecode($params['url']);
    }

    $api = str_replace($scriptName, '', $uri);
    $path = trim($api, '/');
    $parts = explode('/', $path);
  
    $tag = $parts[0];
   
    $credentials = new Credentials();
   
    if ($tag == "" || $tag != "api") {
        if ($tag == "") {
            header("Content-Type: application/json");
            http_response_code(404);
            echo json_encode(["error" => "Invalid Request"]);
            exit;
        } else if ($tag == "authorisation") {
            header("Content-Type: application/json");
            $credentials->setAuthorisation($params);
            $result = $credentials->write(); 
            echo json_encode(["result" => $result]);
            exit;
        } else {
            header("Content-Type: application/json");
            http_response_code(404);
            exit;
        }
    }
    
    if(!$credentials->read("credentials.json")){
        http_response_code(500);
        header("Content-Type: application/json");
        echo json_encode(["error" => "Invalid Credentials"]);
        exit;
    }
    
    $router = new Router($method, $params, $requestBody);
    $router->routeEndpoint($parts, $credentials);

    exit;
?>