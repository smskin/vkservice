<?php
/**
 * Created by PhpStorm.
 * User: smskin
 * Date: 15.06.15
 * Time: 12:23
 */

namespace SMSkin\VKService\Api\Wall;

use SMSkin\VKService\Api\Wall\Exceptions\PostException;
use SMSkin\VKService\Api\Wall\Results\WallPostResult;
use SMSkin\VKService\Core\ModelVK;
use Illuminate\Support\Facades\Config;

/**
 * Class WallPost
 * @package SMSkin\VKService\Api\Wall
 */
class WallPost
{
    /**
     * @var ModelVK
     */
    private $vkConnect;
    /**
     * @var string
     */
    private $userId;

    /**
     * @var string
     */
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
     * @var string
     */
    private $messageText;
    /**
     * @var bool|string При попытке приложить больше одной ссылки будет возвращена ошибка.
     */
    private $attachUrl;
    /**
     * @var bool
     *          true — запись будет опубликована на стене группы
     *          false — запись будет опубликована на стене пользователя
     */
    private $toGroup;
    /**
     * @var bool|string дата публикации записи в формате unixtime
     */
    private $delay;

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
        $this->messageText = '';
        $this->attachUrl = false;
        $this->toGroup = false;
        $this->delay = false;
    }

    /**
     * @param bool|string|integer $delay
     *              дата публикации записи
     * @return $this
     */
    public function setDelay($delay)
    {
        if (is_int($delay)) {
            $this->delay = $delay;
        } else {
            $this->delay = strtotime($delay);
        }
        return $this;
    }

    /**
     * @param boolean $toGroup
     *          true — запись будет опубликована на стене группы
     *          false — запись будет опубликована на стене пользователя
     * @return $this
     */
    public function setToGroup($toGroup)
    {
        $this->toGroup = $toGroup;
        return $this;
    }

    /**
     * @param bool|string $attachUrl
     *         При попытке приложить больше одной ссылки будет возвращена ошибка.
     * @return $this
     */
    public function setAttachUrl($attachUrl)
    {
        $this->attachUrl = $attachUrl;
        return $this;
    }

    /**
     * @param string $messageText
     * @return $this
     */
    public function setMessageText($messageText)
    {
        $this->messageText = $messageText;
        return $this;
    }

    /**
     * @param boolean $exportMessage Экспортировать запись, в случае если пользователь настроил соответствующую опцию.
     * @return $this
     */
    public function setExportMessage($exportMessage)
    {
        $this->exportMessage = $exportMessage;
        return $this;
    }

    /**
     * @param string $groupId
     * @return $this
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
        return $this;
    }

    /**
     * @param string $userId
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return array|false
     */
    public function save()
    {
        switch ($this->toGroup){
            case true:
                $params = array(
                    'owner_id'=>$this->groupId,
                    'from_group'=>$this->toGroup
                );
                break;
            default:
                $params = array(
                    'owner_id' => $this->userId

                );
                break;
        }
        if ($this->attachUrl!==false) {
            $params['attachments']=$this->attachUrl;
        }
        if ($this->exportMessage) {
            $params['services'] = implode(',', $this->exportServices);
        }
        if ($this->delay!==false) {
            $params['publish_date']=$this->delay;
        }
        $params['message'] = $this->messageText;
        return $this->parseResponse($this->vkConnect->method('wall.post', $params));
    }

    /**
     * @param string $response
     * @return PostException|WallPostResult
     */
    private function parseResponse($response)
    {
        $response = json_decode($response, true);
        if (array_key_exists('response', $response)) {
            $result = new WallPostResult();
            $result->result = true;
            $result->postId = $response['response']['post_id'];
            return $result;
        }
        $result = new PostException();
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