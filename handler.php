<?php
/*
 *  Set path to stripe lib in webhook_config.php*/
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
if(isset($_GET['action']) && $_GET['action'] == "subscribe"){
    $newSub = $_SESSION['sub'] = $test = true;
}
$config = require_once('webhook_config.php');
require_once('webhookclass.php');
require_once('./master/init.php');
$event_array = require_once('eventarray.php');

if($config['log_type'] === 1){
    $config['db'] = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']}", $config['db_user'], $config['db_pass']);
}

\Stripe\Stripe::setApiKey($config['api_key']);

/*
 *  Validate the event(ensure it is coming from stripe, and not a third party event.)
 */
$input = @file_get_contents("php://input");

function handleEvent($event, $event_array, $config){
    $event_type = $event->type;
    /*
     * replace '.' in the event type with _ to suit function naming conventions.
     */
    if(in_array($event_type, $event_array)) {
        $event_type = str_replace('.', '_', $event_type);

        $webhook = new Webhook($config, $event);

        $isNewEvent = $webhook->isNewEvent($event->id);

        if($isNewEvent === false){
            http_response_code(200);
            exit;
        }else if($isNewEvent === true){
            ob_start();
            $webhook->{$event_type}();
        }
    }else{
        http_response_code(200);
        exit;
    }
}

if($config['verification']['enabled'] === 0){
    try {
        $event_json = json_decode($input);
        $event = \Stripe\Event::retrieve($event_json->id);

        handleEvent($event, $event_array, $config);
    }catch(\Exception $e){
        error_log($e);
    }
}else if($config['verification']['enabled'] === 1){
    $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'];

    try{
        $event = \Stripe\Webhook::constructEvent($input,$signature,$config['verification']['code']);

        handleEvent($event, $event_array, $config);

    }catch(\UnexpectedValueException $e) {

        http_response_code(400);
        exit();
    } catch(\Stripe\Error\SignatureVerification $e) {

        http_response_code(400);
        exit();
    }
}else{
    http_response_code(200);
    exit();
}
