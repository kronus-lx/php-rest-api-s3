<?php
    class Credentials {
        
        private string $accesskey;
        private string $secretkey;
        private string $url;

        public function __construct($credentialsPath){

            $json = file_get_contents($credentialsPath);
            $data = json_decode($json, true);

            if(json_last_error() === JSON_ERROR_NONE){
                $this->accesskey = $data['accessKey'];
                $this->secretkey = $data['secretKey'];
                $this->url = $data['url'];
            }
            else {
                throw new Exception("Invalid Credentials file");
            }
        }

        public function setAccess(string $accesskey){
            $this->accesskey = $accesskey;
        }

        public function setSecret(string $secretkey){
            $this->secretkey = $secretkey;
        }

        public function setEndpoint(string $url){
            $this->url = $url;
        }

        public function accessKey(){
            return $this->accesskey;
        }

        public function secretKey(){
            return $this->secretkey;
        }

        public function url(){
            return $this->url;
        }
    }
?>