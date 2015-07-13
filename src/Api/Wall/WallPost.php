<?php
/**
 * Created by PhpStorm.
 * User: smskin
 * Date: 15.06.15
 * Time: 12:23
 */

namespace SMSkin\VKService\Api\Wall;

use Log;
use SMSkin\VKService\Api\Exceptions\ApiException;
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
     * @var string
     */
    private $connectionName;
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
     * @var bool|array
     */
    private $attachments;

    /**
     * @var bool|string дата публикации записи в формате unixtime
     */
    private $delay;

    /**
     * Class constructor
     * @param string $connectionName
     */
    public function __construct($connectionName = 'default')
    {
        $this->connectionName = $connectionName;
        $vkSettings = Config::get('vksettings.connections');
        if (!array_key_exists($this->connectionName, $vkSettings)) {
            Log::warning('Connection not found in config/vksettings.php. Using default connection.');
            $this->connectionName = 'default';
        }

        $this->vkConnect = new ModelVK($this->connectionName);
        $this->userId = Config::get('vksettings.connections.'.$this->connectionName.'.userId');
        $this->groupId = '-'.Config::get('vksettings.connections.'.$this->connectionName.'.groupId');
        $this->exportMessage = Config::get('vksettings.connections.'.$this->connectionName.'.exportMessage');
        $this->exportServices = Config::get('vksettings.connections.'.$this->connectionName.'.exportServices');
        $this->messageText = '';
        $this->toGroup = false;
        $this->delay = false;
        $this->attachUrl = false;
        $this->attachments = false;
    }

    /**
     * @param string $connectionName
     * @return $this
     */
    public function setConnection($connectionName)
    {
        $this->connectionName = $connectionName;
        return $this;
    }

    /**
     * @param array|bool $attachments
     * @return $this
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;
        return $this;
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
     * @return ApiException|WallPostResult
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
        $attachments = array();
        if ($this->attachUrl!==false) {
            $attachments[]=$this->attachUrl;
        }
        if ($this->attachments !== false) {
            $attachments = array_merge($attachments, $this->attachments);
        }
        if (count($attachments)) {
            $params['attachments']=implode(',', $attachments);
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
     * @return ApiException|WallPostResult
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
        new ApiException($response);
    }
}
