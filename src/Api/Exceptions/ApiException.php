<?php
/**
 * Created by PhpStorm.
 * User: smskin
 * Date: 14.06.15
 * Time: 21:06
 */

namespace SMSkin\VKService\Api\Exceptions;

use App;
use Log;

class ApiException
{
    private $errorCode;
    private $errorMsg;
    private $request;

    public function __construct($response)
    {
        if (array_key_exists('error', $response)) {
            $this->result = false;
            $this->errorCode = $response['error']['error_code'];
            $this->errorMsg = $response['error']['error_msg'];
            $this->request = $response['error']['request_params'];
        } else {
            $this->errorCode = 0;
            $this->errorMsg = 'Undefined exception';
        }

        App::abort(500, $this->errorMsg);
        Log::error('VKService: '.$this->errorMsg.' ('.$this->errorCode.')');
    }
}
