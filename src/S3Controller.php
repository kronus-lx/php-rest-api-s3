<?php
    include "S3Gateway.php";

    use Aws\Exception\AwsException;

    class S3Controller {
        
        private static ?Credentials $settings = null;
        private static ?S3Gateway $gateway = null;

        public static function setAuthorisation(Credentials $credentials) : void {
            self::$settings = $credentials;
            self::$settings->setAccess($credentials->accessKey());
            self::$settings->setSecret($credentials->secretKey());
            self::$settings->setEndpoint($credentials->url());
    
                if (self::$gateway === null) {
                self::$gateway = new S3Gateway(self::$settings->accessKey(), 
                                               self::$settings->secretKey(), 
                                               self::$settings->url());
            } else {
                self::$gateway->setServerCredentials(
                    self::$settings->accessKey(),
                    self::$settings->secretKey(),
                    self::$settings->url()
                );
            }
            return;
        }

        public static function getAllBuckets() : void {
            $buckets = self::$gateway->listAllBuckets();
            if(array_key_exists("error", $buckets)){
                http_response_code(400);
                echo json_encode(["error" => $buckets["error"]]);
            } else {
                echo json_encode(["buckets" => $buckets]);
            }
            return;
        }

        public static function getBucket(string $bucketName): void {
            $objects = self::$gateway->listAllObjects($bucketName);
            if(array_key_exists("error", $objects)){
                http_response_code(400);
                echo json_encode(["error" => $objects["error"]]);
            } else {
                echo json_encode(["objects" => $objects]);
            }
            return;
        }

        public static function getObject(string $key, string $bucketName) : void {
            self::$gateway->downloadObject($key, $bucketName);
            return;
        }

        public static function postBucket(string $requestBody) : void {
            $body = json_decode($requestBody, true);
            if($body !== null && is_array($body)){  
                $json = json_decode($requestBody, true);
                $bucket = $json["bucket"] ?? null;
                if($bucket){
                    $result = self::$gateway->createBucket($bucket);
                    header("Content-Type: application/json");
                    
                    if(array_key_exists("error", $result)){
                        http_response_code(400);
                        echo json_encode(["error" => $result["error"]]);
                    } else {
                        echo json_encode($result);
                    }
                } else {
                    header("Content-Type: application/json");
                    http_response_code(400);
                    echo json_encode(["error" => "Invalid Schema."]);
                    return;
                }
            } else {
                header("Content-Type: application/json");
                http_response_code(400);
                echo json_encode(["error" => "Invalid JSON."]);
                return;
            }
            return;
        }

        public static function postObject(string $bucketName, string $requestBody, ?array $queries) : void {
            $key = $queries['key'] ?? null;
            if($key && $requestBody !== ""){
                $result = self::$gateway->uploadObject($bucketName, $key, $requestBody);
                header("Content-Type: application/json");
                if(array_key_exists("error", $result)){
                    http_response_code(400);
                    echo json_encode(["error" => $result["error"]]);
                } else {
                    echo json_encode($result);
                }
            } else {
                header("Content-Type: application/json");
                http_response_code(400);
                echo json_encode(["error" => "Key or body not present in request"]);
                return;
            }
            return;
        }
    }
?>