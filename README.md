Laravel 5 VKontakte library
-------------------------
Library for working with API social network VKontakte

Installation
-------------
Add the following packages to your `composer.json`
```
"repositories": [
	{
      "url": "git@github.com:smskin/vkservice.git",
      "type": "git"
    }
]
...
"require": {
   "smskin/vkservice": "1.0.0"
}
```
Next, run `composer update`.

Next, add the following service providers to your `config/app.php`.
```
'providers' => [
	...
	'SMSkin\VKService\ServiceProviders\VKServiceProvider'
]
```
Next, run `php artisan vendor:publish `.

This creates a config file `app/config/vksettings.php`.

Configuration
-------------

 - accessToken
	1. You must create Standalone application VKontakte at http://vk.com/editapp?act=create. After that you get Application ID.
	2. It is necessary to go to the address for access_token (appears in the address bar).
	Access_token needs to work with a closed part of Api VK.
	http://oauth.vk.com/authorize?client_id=[ApplicationID]&scope=wall,photos,offline&redirect_uri=http://oauth.vk.com/blank.html&response_type=token
	3.  Copy access_token from the address bar.
 - userId - Social network user ID
 - groupId - Social network group ID
 - exportMessage - Export recording, if the user has configured the appropriate option.
 - exportServices - An array of social networks in which to export the recording. When the function is activated exportMessage
    
Working with the wall
-------------
An example of sending a message on the wall
```
$post = new WallPost();
$post->setConnection('default');
$post->setMessageText('Test message');
$post->setAttachUrl('http://vk.com');
$post->setAttachments(array(
    'photo100172_166443618',
    'photo66748_265827614'
));
$post->setToGroup(true); //Send a message on the wall of the group
$post->save();
```
Example of editing posts
```
$post = new WallEdit();
$post->setConnection('default');
$post->whereMessageId(1);
$post->setMessageText('New message text');
$post->setAttachUrl('http://google.com');
$post->setAttachments(array(
    'photo100172_166443618',
    'photo66748_265827614'
));
$post->whereInGroup(true);
$post->save();
```
Example of deleting posts
```
$post = new WallDelete();
$post->setConnection('default');
$post->whereMessageId(1);
$post->whereInGroup(true);
$post->delete();
```
Example of restoring posts
```
$post = new WallRestore();
$post->setConnection('default');
$post->whereMessageId(1);
$post->whereInGroup(true);
$post->restore();
```
Example of uploading image to wall
```
$image = new PhotosStoreOnWall();
$post->setConnection('default');
$image->setToGroup(true); //Send a image on the wall of the group
$image->setImagePath('file.jpg');
$image->save();
```