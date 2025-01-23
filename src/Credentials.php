<?php

    class Credentials {
        
        private string $accesskey;
        private string $secretkey;
        private string $url;
        
        public function __construct() {}
        
        public function read(string $credentialsPath): bool {
            if (!file_exists($credentialsPath)) {
                throw new Exception("Credentials file not found");
            }
            
            $json = file_get_contents($credentialsPath);
            if ($json === false) {
                throw new Exception("Failed to read credentials file");
            }
            
            $data = json_decode($json, true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                try {
                    // Validate required fields exist
                    if (!isset($data['accessKey'], $data['secretKey'], $data['url'])) {
                        throw new Exception("Missing required credentials fields");
                    }
                    
                    $this->accesskey = $data['accessKey'];
                    $this->secretkey = $data['secretKey'];
                    $this->url = $data['url'];
                    return true;
                }
                catch(Exception $ex) {
                    error_log($ex->getMessage());
                    return false;
                }
            }
            return false;
        }
        
        public function write(string $filename = "credentials.json"): int {
            try {
                if (empty($this->url) || empty($this->accesskey) || empty($this->secretkey)) {
                    throw new Exception("Cannot set null or empty credentials");
                }
                
                $credentials = [
                    "url" => $this->url,
                    "accessKey" => $this->accesskey,
                    "secretKey" => $this->secretkey
                ];
                
                $result = file_put_contents($filename, json_encode($credentials, JSON_UNESCAPED_SLASHES));
                if ($result === false) {
                    throw new Exception("Failed to write credentials file to: $filename");
                }
                return 0;
            } catch (Exception $ex) {
                error_log($ex->getMessage());
                return -1; // Failure
            }
        }
        
        public function setAuthorisation(array $params): void {
            $missingFields = [];
            
            if (empty($params['url'])) {
                $missingFields[] = 'URL';
            } else {
                $this->url = $params['url'];
            }
        
            if (empty($params['accessKey'])) {
                $missingFields[] = 'Access Key';
            } else {
                $this->accesskey = $params['accessKey'];
            }
        
            if (empty($params['secretKey'])) {
                $missingFields[] = 'Secret Key';
            } else {
                $this->secretkey = $params['secretKey'];
            }
        
            if (!empty($missingFields)) {
                throw new Exception("Missing required fields: " . implode(', ', $missingFields));
            }
        }
        
        // Getter methods could be improved with null checks
        public function accessKey(): ?string {
            return $this->accesskey ?? null;
        }
        
        public function secretKey(): ?string {
            return $this->secretkey ?? null;
        }
        
        public function url(): ?string {
            return $this->url ?? null;
        }
        
        // Setter methods could include validation
        public function setAccess(string $accesskey): void {
            if (empty($accesskey)) {
                throw new Exception("Access key cannot be empty");
            }
            $this->accesskey = $accesskey;
        }
        
        public function setSecret(string $secretkey): void {
            if (empty($secretkey)) {
                throw new Exception("Secret key cannot be empty");
            }
            $this->secretkey = $secretkey;
        }
        
        public function setEndpoint(string $url): void {
            if (empty($url)) {
                throw new Exception("URL cannot be empty");
            }
            $this->url = $url;
        }
    }