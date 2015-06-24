<?php
/**
 * Created by PhpStorm.
 * User: smskin
 * Date: 15.06.15
 * Time: 14:40
 */

namespace SMSkin\VKService\Api\Wall;

use Illuminate\Support\Facades\Config;
use SMSkin\VKService\Api\Exceptions\ApiException;
use SMSkin\VKService\Api\Wall\Results\WallEditResult;
use SMSkin\VKService\Core\ModelVK;

/**
 * Class WallDelete
 * @package SMSkin\VKService\Api\Wall
 */
class WallDelete
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
     * @var int
     */
    private $messageId;

    /**
     * @var bool
     *          true — запись будет опубликована на стене группы
     *          false — запись будет опубликована на стене пользователя
     */
    private $toGroup;

    /**
     * Class constructor
     * @param string $connectionName
     */
    public function __construct($connectionName = 'default')
    {
        $this->connectionName = $connectionName;
        $vkSettings = Config::get('vksettings.connections');
        if (!array_key_exists($this->connectionName, $vkSettings)) {
            $this->connectionName = 'default';
        }

        $this->vkConnect = new ModelVK($this->connectionName);
        $this->userId = Config::get('vksettings.connections.'.$this->connectionName.'.userId');
        $this->groupId = '-'.Config::get('vksettings.connections.'.$this->connectionName.'.groupId');
        $this->messageId = 0;
        $this->toGroup = false;
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
     * @param boolean $toGroup
     *          true — запись будет опубликована на стене группы
     *          false — запись будет опубликована на стене пользователя
     * @return $this
     */
    public function whereInGroup($toGroup)
    {
        $this->toGroup = $toGroup;
        return $this;
    }

    /**
     * @param int $messageId
     * @return $this
     */
    public function whereMessageId($messageId)
    {
        $this->messageId = $messageId;
        return $this;
    }

    /**
     * @param string $groupId
     * @return $this
     */
    public function whereToGroup($groupId)
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
     * @return ApiException|WallEditResult
     */
    public function delete()
    {
        switch ($this->toGroup){
            case true:
                $params = array(
                    'owner_id'=>$this->groupId
                );
                break;
            default:
                $params = array(
                    'owner_id' => $this->userId

                );
                break;
        }
        $params['post_id'] = $this->messageId;
        return $this->parseResponse($this->vkConnect->method('wall.delete', $params));
    }

    /**
     * @param string $response
     * @return ApiException|WallEditResult
     */
    private function parseResponse($response)
    {
        $response = json_decode($response, true);
        if (array_key_exists('response', $response)) {
            $result = new WallEditResult();
            $result->result = true;
            return $result;
        }
        new ApiException($response);
    }
}
