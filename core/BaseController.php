<?php

class BaseController
{
    private $_headers = [];

    public function __construct()
    {
        // reset header 
        $this->_headers = [];
    }

    protected function setHeader($strHeader)
    {
        $this->_headers[$strHeader] = '';
        return $this;
    }

    protected function response($code, $status, $data = [], $message = '') 
    {
        if(is_array($code) && sizeof($code) == 2) {
            list($responseCode, $errorCode) = $code;
        } else {
            $responseCode = $code;
            $errorCode = 0;
        }
         
        // if empty message
        if(empty($message)) {
            $message = isset(HTTP_MESSAGES[$responseCode]) ? HTTP_MESSAGES[$responseCode] : 'Unknown';
        }
        $response = [
            'status'    => $status ? 'success' : 'error',
            'code'      => $responseCode,
            'error_code'=> $errorCode,
            'datetime'  => date('Y-m-d\TH:i:s\Z'),
            'timestamp' => time(),
            'message'   => $message
        ];
        if(!empty($data) || is_array($data)){
            $response['data'] = $data;
        }

        // load http headers
        foreach($this->_headers as $key=>$value) {
            header($key);
        }
        http_response_code($responseCode);
        header('Content-Type: application/json');
        return json_encode($response);
    }

    protected function validate($inputs, $conditions = []) 
    {
        $validation = new FormValidation($inputs);
        $validation->validate($conditions);
        return $validation;
    }
}