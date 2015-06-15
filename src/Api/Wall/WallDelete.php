<?php
/**
 * Created by PhpStorm.
 * User: smskin
 * Date: 15.06.15
 * Time: 14:40
 */

namespace SMSkin\VKService\Api\Wall;

use Illuminate\Support\Facades\Config;
use SMSkin\VKService\Api\Wall\Exceptions\PostException;
use SMSkin\VKService\Api\Wall\Results\WallEditResult;
use SMSkin\VKService\Core\ModelVK;

class WallDelete
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
     */
    public function __construct()
    {
        $this->vkConnect = new ModelVK();
        $this->userId = Config::get('vksettings.userId');
        $this->groupId = '-'.Config::get('vksettings.groupId'); //Group ID указывается со знаком -
        $this->messageId = 0;
        $this->toGroup = false;
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
     * @return array|false
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
     * @return PostException|WallEditResult
     */
    private function parseResponse($response)
    {
        $response = json_decode($response, true);
        if (array_key_exists('response', $response)) {
            $result = new WallEditResult();
            $result->result = true;
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