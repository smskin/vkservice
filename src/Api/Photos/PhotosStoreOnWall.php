<?php
/**
 * Created by PhpStorm.
 * User: smskin
 * Date: 16.06.15
 * Time: 11:19
 */

namespace SMSkin\VKService\Api\Photos;

use App;
use CURLFile;
use Illuminate\Support\Facades\Config;
use SMSkin\VKService\Api\Exceptions\ApiException;
use SMSkin\VKService\Api\Photos\Results\PhotosStoreOnWallResult;
use SMSkin\VKService\Core\ModelVK;

/**
 * Class PhotosStoreOnWall
 * @package SMSkin\VKService\Api\Photos
 */
class PhotosStoreOnWall
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
     * @var
     */
    private $uploadUrl;
    /**
     * @var bool
     *          true — запись будет опубликована на стене группы
     *          false — запись будет опубликована на стене пользователя
     */
    private $toGroup;

    /**
     * @var string
     */
    private $imagePath;

    /**
     * @param boolean $toGroup
     * @return $this
     */
    public function setToGroup($toGroup)
    {
        $this->toGroup = $toGroup;
        return $this;
    }

    /**
     * @param string $imagePath
     * @return $this
     */
    public function setImagePath($imagePath)
    {
        $this->imagePath = $imagePath;
        return $this;
    }

    /**
     *
     */
    public function __construct()
    {
        $this->vkConnect = new ModelVK();
        $this->userId = Config::get('vksettings.userId');
        $this->groupId = Config::get('vksettings.groupId');
        $this->toGroup = false;
    }

    public function save()
    {
        if (!file_exists($this->imagePath)) {
            App::abort(500, 'File not found ('.$this->imagePath.')');
        }
        $this->getWallUploadServer();
        $sendFileResponse = $this->sendFile();
        return $this->saveWallPhoto($sendFileResponse);
    }

    private function getWallUploadServer()
    {
        switch ($this->toGroup){
            case true:
                $params = array(
                    'group_id'=>$this->groupId
                );
                break;
            default:
                $params = array(
                    'group_id'=>$this->userId
                );
                break;
        }
        $response = json_decode($this->vkConnect->method('photos.getWallUploadServer', $params), true);
        if (!array_key_exists('response', $response)) {
            new ApiException($response);
        }
        $this->uploadUrl = $response['response']['upload_url'];
    }

    private function sendFile()
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_VERBOSE, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible;)');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_URL, $this->uploadUrl);
        $photo = new CURLFile($this->imagePath);
        $postArray = array(
            'photo'=>$photo
        );
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postArray);
//        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE); httpCode always 0...
        $response = curl_exec($curl);
        curl_close($curl);
//        if ((int)$httpCode !== 200) {
//            App::abort(500, 'The server returned '.$httpCode.' error while uploading image');
//        }
        return json_decode($response); //{"server": "1234", "photo": "1234", "hash": "12345abcde"}
    }

    private function saveWallPhoto($sendFileResponse)
    {
        $params = array(
            'photo'=>$sendFileResponse->photo,
            'server'=>$sendFileResponse->server,
            'hash'=>$sendFileResponse->hash
        );
        switch ($this->toGroup){
            case true:
                $params['user_id']=$this->groupId;
                $params['group_id']=$this->groupId;
                break;
            default:
                $params['user_id']=$this->userId;
                $params['group_id']=$this->userId;
                break;
        }
        return $this->saveWallPhotoParseResponse($this->vkConnect->method('photos.saveWallPhoto', $params));
    }

    /**
     * @param string $response
     * @return ApiException|PhotosStoreOnWallResult
     */
    private function saveWallPhotoParseResponse($response)
    {
        $response = json_decode($response, true);
        if (array_key_exists('response', $response)) {
            $imageNode = $response['response'][0];
            $result = new PhotosStoreOnWallResult();
            $result->result = true;
            $result->pid = $imageNode['pid'];
            $result->id = $imageNode['id'];
            $result->aid = $imageNode['aid'];
            $result->ownerId = $imageNode['owner_id'];
            $result->src = $imageNode['src'];
            $result->srcBig = $imageNode['src_big'];
            $result->srcSmall = $imageNode['src_small'];
            $result->width = $imageNode['width'];
            $result->height = $imageNode['height'];
            $result->text = $imageNode['text'];
            $result->created = $imageNode['created'];
            return $result;
        }
        new ApiException($response);
    }
}