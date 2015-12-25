<?php
/**
 * Created by PhpStorm.
 * User: smskin
 * Date: 14.06.15
 * Time: 20:05
 */

namespace SMSkin\VKService\Core;

use Illuminate\Support\Facades\Config;

/**
 * Class ModelVK
 * @package vkService
 */
class ModelVK
{

    /**
     * @var string
     */
    private $accessToken;
    /**
     * @var string
     */
    private $url = 'https://api.vk.com/method/';

    /**
     * Class constructor
     * @param $connectionName
     */
    public function __construct($connectionName)
    {
        $this->accessToken = Config::get('vksettings.connections.'.$connectionName.'.accessToken');
    }

    /**
     * Делает запрос к Api VK
     * @param $method
     * @param array $params
     * @return false|string
     */
    public function method($method, array $params = array())
    {
       $params['access_token']=$this->accessToken;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_VERBOSE, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible;)');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_URL, $this->url . $method);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        $response = curl_exec($curl);
        curl_close($curl);

        if ($response) {
            return $response;
        }
        return false;
    }
}
