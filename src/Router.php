<?php

require "S3Controller.php";

class Router {
    
    private string $method;
    private array $params;
    private string $requestBody;

    public function __construct(string $method, array $params = [], ?string $body = null){
        $this->method = $method;
        $this->params = $params;
        $this->requestBody = $body ?? "";
    }
    
    public function routeEndpoint(array $parts, Credentials $credentials = null){

        $buckets = $parts[1] ?? null;
        $bucket = $parts[2] ?? null;
        $objects = $parts[3] ?? null;
        $object = $parts[4] ?? null;

        if ($buckets !== "buckets") {
            http_response_code(400);
            header("Content-Type: application/json");
            echo json_encode(["error" => "Invalid collection. Only 'buckets' is supported."]);
            return;
        }

        S3Controller::setAuthorisation($credentials);

        if ($this->method === "GET") {
            if ($bucket === null) {
                S3Controller::getAllBuckets();
            } elseif ($bucket !== null && $objects === "objects" && $object !== null) {
                S3Controller::getObject($object, $bucket);
            } else {
                S3Controller::getBucket($bucket);
            }
        } elseif ($this->method === "POST") {
            if ($bucket === null && $objects === null && $object === null) {
                S3Controller::postBucket($this->requestBody);
            } elseif ($bucket !== null && $objects === "objects" && $object === null) {
                S3Controller::postObject($bucket, $this->requestBody, $this->params);
            } else {
                http_response_code(400);
                header("Content-Type: application/json");
                echo json_encode(["error" => "Invalid POST format. Use '/buckets' or '/buckets/{id}/objects'"]);
            }
        } else {
            http_response_code(405);
            header("Content-Type: application/json");
            echo json_encode(["err" => "$this->method not permitted."]);
        }
    }
}