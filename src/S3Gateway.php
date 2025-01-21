<?php
    include "aws/aws-autoloader.php";

    use Aws\S3\S3Client;
    use Aws\Exception\AwsException;

    class S3Gateway {
        
        private S3Client $client;
        private string $accessKey;
        private string $secretKey;
        private string $endpoint;
        
        public function __construct(string $accessKey, string $secretKey, string $endpoint){
            $this->accessKey = $accessKey;
            $this->secretKey = $secretKey;
            $this->endpoint = $endpoint;
        }

        public function setServerCredentials(string $accessKey, string $secretKey, string $endpoint){
            $this->accessKey = $accessKey;
            $this->secretKey = $secretKey;
            $this->endpoint = $endpoint;
        }

        public function createBucket(string $name) : array {
            header("Content-Type: application/json;charset=UTF8");
            $this->client = new S3Client([
                'version' => 'latest',
                'region'  => 'eu-west-2',
                'endpoint' => $this->endpoint,
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key' => $this->accessKey,
                    'secret' => $this->secretKey,
                ]
            ]);
            
            try {
                $result = $this->client->createBucket([
                    'Bucket' => $name,
                    'CreateBucketConfiguration' => ['LocationConstraint' => 'eu-west-2'],
                ]);
                return ["result" => $result];

            } catch (AwsException $ex){
                return ["error" => $ex->getMessage()];
            }
        }
        
        public function uploadObject(string $bucket, string $key, string $content) : array {
            header("Content-Type: application/json;charset=UTF8");
            $this->client = new S3Client([
                'version' => 'latest',
                'region'  => 'eu-west-2',
                'endpoint' => $this->endpoint,
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key' => $this->accessKey,
                    'secret' => $this->secretKey,
                ]
            ]);

            try {
                $result = $this->client->putObject([
                    'Bucket' => $bucket,
                    'Body' => $content,
                    'Key' => $key,
                ]);
                return ["result" => $result];

            } catch (AwsException $ex){
                header("Content-Type: application/json");
                return ["error" => $ex->getMessage()];
            }
        }

        public function listAllBuckets() : array {
            header("Content-Type: application/json");
            $this->client = new S3Client([
                'version' => 'latest',
                'region'  => 'eu-west-2',
                'endpoint' => $this->endpoint,
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key' => $this->accessKey,
                    'secret' => $this->secretKey,
                ]
            ]);
            
            try {
                $buckets = [];
                $result = $this->client->listBuckets();
                foreach ($result['Buckets'] as $bucket) {
                    array_push($buckets, $bucket["Name"]);
                }
                return $buckets;
            } 
            catch(AwsException $ex) {
                return ["error" => $ex->getMessage()];
            }
        }   

        public function listAllObjects($bucketName) : array {
            header("Content-Type: application/json");
            $this->client = new S3Client([
                'version' => 'latest',
                'region'  => 'eu-west-2',
                'endpoint' => $this->endpoint,
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key' => $this->accessKey,
                    'secret' => $this->secretKey,
                ]
            ]);

            try {
                $objects = [];
                $result = $this->client->listObjectsV2([
                    'Bucket' => $bucketName,
                ]);
                foreach($result['Contents'] as $object){
                    array_push($objects, $object['Key']);
                }
                return $objects; 

            } catch(AwsException $ex){
                return ["error" => $ex->getMessage()];
            }
        }

        public function downloadObject(string $key, string $bucketName) : void {
            $this->client = new S3Client([
                'version' => 'latest',
                'region'  => 'eu-west-2',
                'endpoint' => $this->endpoint,
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key' => $this->accessKey,
                    'secret' => $this->secretKey,
                ]
            ]);

            try {
                $result = $this->client->getObject([
                    'Bucket' => $bucketName,
                    'Key' => $key
                ]);

                $contentType = $result['ContentType'];
                $contentLength = $result['ContentLength'];

                header('Content-Type: ' . $contentType);
                header('Content-Length: ' . $contentLength);
                header('Content-Description: File Transfer');
                header('Content-Disposition: attachment; filename="' . $key . '"');
                header('Content-Transfer-Encoding: binary');

                echo $result['Body'];

                return;

            } catch(AwsException $ex){
                header("Content-Type: application/json");
                echo json_encode(["error" => $ex->getMessage()]);
            }
        }
    }
?>