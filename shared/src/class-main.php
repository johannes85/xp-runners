<?php namespace xp;

// Set CLI specific handling
$home= getenv('HOME');
$cwd= '.';

require 'xar-support.php';
require 'scan-path.php';
require 'bootstrap.php';
require 'class-path.php';

if ('cgi' === PHP_SAPI || 'cgi-fcgi' === PHP_SAPI) {
  ini_set('html_errors', 0);
  define('STDIN', fopen('php://stdin', 'rb'));
  define('STDOUT', fopen('php://stdout', 'wb'));
  define('STDERR', fopen('php://stderr', 'wb'));
} else if ('cli' !== PHP_SAPI) {
  throw new \Exception('[bootstrap] Cannot be run under '.PHP_SAPI.' SAPI');
}

set_exception_handler(function($e) {
  fputs(STDERR, 'Uncaught exception: '.\xp::stringOf($e));
  exit(0xff);
});

ini_set('display_errors', 'false');
register_shutdown_function(function() {
  static $types= array(
    E_ERROR         => 'Fatal error',
    E_USER_ERROR    => 'Fatal error',
    E_PARSE         => 'Parse error',
    E_COMPILE_ERROR => 'Compile error'
  );

  $e= error_get_last();
  if (null !== $e && isset($types[$e['type']])) {
    __error($e['type'], $e['message'], $e['file'], $e['line']);
    create(new \lang\Error($types[$e['type']]))->printStackTrace();
  }
});

// Start I/O layers
$encoding= get_cfg_var('encoding');
iconv_set_encoding('internal_encoding', \xp::ENCODING);
array_shift($_SERVER['argv']);
array_shift($argv);
if ($encoding) {
  foreach ($argv as $i => $val) {
    $_SERVER['argv'][$i]= $argv[$i]= iconv($encoding, \xp::ENCODING, $val);
  }
}

foreach ($include as $path => $_) {
  \lang\ClassLoader::registerPath($path);
}

try {
  exit(\lang\XPClass::forName($argv[0])->getMethod('main')->invoke(null, array(array_slice($argv, 1))));
} catch (\lang\SystemExit $e) {
  if ($message= $e->getMessage()) echo $message, "\n";
  exit($e->getCode());
}
