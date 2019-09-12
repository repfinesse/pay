<?php
//$test = false;
    
if(isset($test) && $test){
    $pk = "pk_test_sWAZb1S4HH5uF8ZkQMOfl3aE";
    $sk = "sk_test_Yh8tRq4TjLm9o5cpv7ppDuPn007H2byNoa";
    
}else{
     $pk = "pk_live_YfN88NHx8QzsxvYHDQX5yDXF";
    $sk = "sk_live_3acV16g0xtmz7FgjAE2Jdctj00T7V6brTE";
}

return [

    /*
     * Stripe Secret API Key,
     */
    'plans' => [
        null, "basic", "pro","ultimate"
        ],
    'api_key' => $sk,
    'pk' => $pk,
    /*
     * Path to stripe lib
     */
    'stripe_path' => 'stripe/master/init.php',

    /*
     *  Verification Code settings:
     *  To enable set to 1, and place your wh_secret inside of the 'secret' declaration.
     *  To verify by retrieving the event set to 0
     *  Default 0
     */

    'verification' => [
        'enabled' => 0,
        'code' => 'whsec_qVGGii8V15Bdv7mfDkNrJqikpgJlGnXR'
    ],

    /*
     * Debug settings:
     * To enable set to 1
     * To disable set to 0
     * Disabled by default
     */
    'debug' => 0,

    /*
     * Log settings:
     * To disable set to 0
     * Enable DB logging set to 1
     */

    'log_type' => 0,
    'log_path' => '',

    /*
     *  THIS SECTION REQUIRES THE PHPMAILER LIBRARY FOUND HERE:
     *  https://github.com/PHPMailer/PHPMailer/
     *
     * If you wish for email notifications for certain events add the event type
     * into the following array
     *  EXAMPLE:
     * 'email_events' => [
     *   'invoice.payment_succeeded',
     *   'invoice.payment_failed'
     *  ],
     *
     * Emails will be sent for those events.
     */
    'phpmailer' => 'path/to/PHPMailerAutoload.php',

    'email_events' => [

    ],

    /*
     * EMAIL settings.
     */

    /*
     * SMTP disabled by default. Set to 1 to enable.
     */
    'email' => [

        /*
         * SMTP settings
         */
        'use_smtp' => 0,
        'smtp_host' => '',
        'smtp_port' => '',
        'smtp_user' => '',
        'smtp_pass' => '',

        /*
         * Basic email settings
         */
        'from' => 'webhook@' . $_SERVER['SERVER_NAME'],
        'to' => '',
        'subject' => 'Flagged Webhook Event'
    ],

    /*
     * DATABASE Settings for DB logging
     */
    'db_host' => '',
    'db_user' => '',
    'db_pass' => '',
    'db_name' => '',
];
