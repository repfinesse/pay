<?php 

	$plan = \Stripe\Plan::create([
      "amount" => $_POST['price']*100,
      "interval" => $_POST['interval'],
      "product" => [
        "name" => $_POST['plan']
      ],
      "currency" => $_POST['currency'],
      "id" => "plan-".$_POST['plan']
    ]);