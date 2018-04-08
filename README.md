#### Реализация сервера http_upload на php + nginx для Prosody с расширением mod_http_upload_external.

Как пользоваться?

- В Prosody после VirtualHost добавить:
```
Component "upload.domain.com" "http_upload_external"
http_upload_external_base_url = "https://upload.domain.com/"
http_upload_external_secret = "secret"
http_upload_external_file_size_limit = 52428800 -- Upload limit in bytes
```
- В файле конфигурации для nginx отредактировать пути до ключей сертификатов, имя сервера (server_name) и путь до папки prosody_http.
- В файле cron.sh Отредактировать переменную DAYS, через сколько дней загруженные файлы будут удаляться.
- Добавить в cron выполнение файла cron.sh раз в сутки (0 1 * * * root /home/prosody_http/cron.sh)
