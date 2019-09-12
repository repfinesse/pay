<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
$config = require_once($_SERVER['DOCUMENT_ROOT'].'/pay/stripe/webhook_config.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/pay/stripe/webhookclass.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/pay/stripe/master/init.php');
$event_array = require_once('eventarray.php');

if($config['log_type'] === 1){
    $config['db'] = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']}", $config['db_user'], $config['db_pass']);
}

\Stripe\Stripe::setApiKey($config['api_key']);

?>