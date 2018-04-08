<?php
/*

Реализация сервера http_upload на php + nginx для Prosody расширения mod_http_upload_external.

Как пользоваться?

- В Prosody после VirtualHost добавить:
	Component "upload.domain.com" "http_upload_external"
	http_upload_external_base_url = "https://upload.domain.com/"
	http_upload_external_secret = "secret"
	http_upload_external_file_size_limit = 52428800 -- Upload limit in bytes

- В файле конфигурации для nginx отредактировать пути до ключей сертификатов, имя сервера (server_name) и путь до папки prosody_http (root).
- В файле cron.sh Отредактировать переменную DAYS, через сколько дней загруженные файлы будут удаляться.
- Добавить в cron выполнение файла cron.sh раз в сутки (0 1 * * * root /home/prosody_http/cron.sh)

*/

/* Эта константа должна соответствовать переменной http_upload_external_secret в конфигурации Prosody */
define('CONFIGSECRET', 'secret');



/*----------------------------------------------*/
if(!isset($_SERVER['url_p1']) || !isset($_SERVER['url_p2'])) {
	header('HTTP/1.0 400 Bad Request');
	exit;
}

$request_method	= $_SERVER['REQUEST_METHOD'];
$fileuid		= ($_SERVER['url_p1']);
$filename		= ($_SERVER['url_p2']);
$filehash		= hash('sha256', $fileuid . $filename);
$file			= __DIR__ . '/uploads/' . $filehash;
$ext			= strtolower(substr(strrchr($filename, '.'), 1));


if(isset($_GET['v']) && $request_method == 'PUT') {
	
	$upload_file_size	= $_SERVER['CONTENT_LENGTH'];
	$upload_token		= $_GET['v'];
	$calculated_token	= hash_hmac('sha256', "$fileuid/$filename $upload_file_size", CONFIGSECRET);
	
	if($upload_token !== $calculated_token) {
		header('HTTP/1.0 403 Forbidden');
		exit;
	}
	
	$putdata = fopen('php://input', 'r');
	$fp = fopen($file, 'x');

	if($fp == false) {
		header('HTTP/1.0 409 Conflict');
		exit;
	}
	
	while ($data = fread($putdata, 4096)) {
  		fwrite($fp, $data);
	}
	
	fclose($putdata);
	fclose($fp);
	
} else if($request_method == 'GET' || $request_method == 'HEAD') {
	
	switch ($ext) {
	case 'jpg':
	case 'jpeg':
		$mime = 'image/jpeg'; break;
	case 'png':
		$mime = 'image/png'; break;
	case 'gif':
		$mime = 'image/gif'; break;
	case 'webm':
		$mime = 'video/webm'; break;
	case 'txt':
	case 'conf':
	case 'log':
		$mime = 'text/plain'; break;
	case 'mp4':
		$mime = 'video/mp4'; break;
	case 'webp':
		$mime = 'image/webp'; break;
	default:
		$mime = 'application/octet-stream';
	}
	
	if(file_exists($file)) {
		header('Content-Type: ' . $mime);
		header('Content-Length: ' . filesize($file));
		if($request_method != 'HEAD') {
			header('X-Accel-Redirect: /uploads/' . $filehash);
		}
	} else {
		header('HTTP/1.0 404 Not Found');
		exit;
	}
} else {
	header('HTTP/1.0 400 Bad Request');
	exit;
}



?>
