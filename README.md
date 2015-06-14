Необходимо создать Standalone приложение в ВКонтакте по адресу http://vk.com/editapp?act=create

После создания вы получаете ID приложения

Необходимо перейти по адресу для получения access_token

http://oauth.vk.com/authorize?client_id=[ID приложения]&scope=wall,offline&redirect_uri=http://oauth.vk.com/blank.html&response_type=token

access_token ьудет необходим для работы с закрытой частью Api VK

