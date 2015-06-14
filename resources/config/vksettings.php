<?php
/**
 * Created by PhpStorm.
 * User: smskin
 * Date: 14.06.15
 * Time: 21:34
 */

return [
    'accessToken'=>'',
    'userId'=>'',
    'groupId'=>'',
    'exportMessage'=>false, //Экспортировать запись, в случае если пользователь настроил соответствующую опцию.
    'exportServices'=>array( //Список сервисов или сайтов, на которые необходимо экспортировать запись
        'twitter',
        'facebook'
    )
];
