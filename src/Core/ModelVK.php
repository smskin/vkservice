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
        $paramsQuery = '';
        foreach ($params as $key => $param) {
            $paramsQuery .= ($paramsQuery === '' ? '' : '&') . $key . '=' . urlencode($param);
        }
        $response = file_get_contents(
            $this->url . $method . '?' . ($paramsQuery ? $paramsQuery . '&' : '') . 'access_token=' . $this->accessToken
        );

        if ($response) {
            return $response;
        }
        return false;
    }
}
