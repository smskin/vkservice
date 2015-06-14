Регистрируем ServiceProvider ('SMSkin\VKService\ServiceProviders\VKServiceProvider') в app.php

Даем комманду php artisan vendor:publish

В папке app/config появится конфигурационный файл vksettings.php

Для работы необходимо создать Standalone приложение в ВКонтакте по адресу http://vk.com/editapp?act=create

После создания вы получаете ID приложения

Необходимо перейти по адресу для получения access_token (появится в адресной строке)

http://oauth.vk.com/authorize?client_id=[ID приложения]&scope=wall,offline&redirect_uri=http://oauth.vk.com/blank.html&response_type=token

access_token необходим для работы с закрытой частью Api VK

[code]
$SendMessageToWall = new SendMessageToWall();

$SendMessageToWall->submitMessage('работает!','http://ya.ru',true);
[/code]
