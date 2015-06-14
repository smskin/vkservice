<?php
/**
 * Created by PhpStorm.
 * User: smskin
 * Date: 14.06.15
 * Time: 20:13
 * Документация https://vk.com/dev/wall.post
 */

namespace SMSkin\VKService\Requests;

use Illuminate\Support\Facades\Config;
use SMSkin\VKService\Core\ModelVK;
use SMSkin\VKService\Exception\SendMessageToWallException;
use SMSkin\VKService\Results\SendMessageToWallResult;

/**
 * Class SendMessageToWall
 * @package vkService
 */
class SendMessageToWall
{
    /**
     * @var ModelVK
     */
    private $vkConnect;
    /**
     * @var string
     */
    private $userId;

    private $groupId;

    /**
     * @var boolean
     *              Экспортировать запись, в случае если пользователь настроил соответствующую опцию.
     */
    private $exportMessage;

    /**
     * @var array
     *              список сервисов или сайтов, на которые необходимо экспортировать запись
     */
    private $exportServices;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->vkConnect = new ModelVK();
        $this->userId = Config::get('vksettings.userId');
        $this->groupId = '-'.Config::get('vksettings.groupId'); //Group ID указывается со знаком -
        $this->exportMessage = Config::get('vksettings.exportMessage');
        $this->exportServices = Config::get('vksettings.exportServices');
    }

    /**
     * @param string $message
     * @param bool|string $attachUrl При попытке приложить больше одной ссылки будет возвращена ошибка.
     * @param bool $toGroup
     *                       true — запись будет опубликована на стене группы
     *                       false — запись будет опубликована на стене пользователя
     * @param bool|integer $delay дата публикации записи в формате unixtime
     * @return array|false
     */
    public function submitMessage($message, $attachUrl = false, $toGroup = false, $delay = false)
    {
        switch ($toGroup){
            case true:
                $params = array(
                    'owner_id'=>$this->groupId,
                    'message'=>$message,
                    'from_group'=>$toGroup
                );
                break;
            default:
                $params = array(
                    'owner_id' => $this->userId,
                    'message' => $message
                );
                break;
        }
        if ($attachUrl!==false) {
            $params['attachments']=$attachUrl;
        }
        if ($this->exportMessage) {
            $params['services'] = implode(',', $this->exportServices);
        }
        if ($delay!==false) {
            $params['publish_date']=$delay;
        }
        return $this->parseResponse($this->vkConnect->method('wall.post', $params));
    }

    /**
     * @param string $response
     * @return SendMessageToWallException|SendMessageToWallResult
     */
    private function parseResponse($response)
    {
        $response = json_decode($response, true);
        if (array_key_exists('response', $response)) {
            $result = new SendMessageToWallResult();
            $result->result = true;
            $result->postId = $response['response']['post_id'];
            return $result;
        }
        $result = new SendMessageToWallException();
        if (array_key_exists('error', $response)) {
            $result->result = false;
            $result->errorCode = $response['error']['error_code'];
            $result->errorMsg = $response['error']['error_msg'];
            $result->request = $response['error']['request_params'];
            return $result;
        }
        $result->result = false;
        $result->errorCode = 0;
        $result->errorMsg = 'Undefined exception';
        return $result;
    }
}
