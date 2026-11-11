<?php
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
header('X-Content-Type-Options: nosniff');
if (preg_match('~(^|/)[.]|[\\\\:\x00-\x1f]|[.](sql|log|ini|json)$~i', $path)
    || ($path !== '/' && !is_file(__DIR__ . $path))) {
    http_response_code(404); exit('Not found');
}
return false;
