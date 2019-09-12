<?php 
// Check for a POSTed stripeToken and subscription
if (isset($_POST['stripeToken']) && isset($_POST['plan'])){

  try {

    /*$plan = \Stripe\Plan::create([
      "amount" => $_POST['price']*100,
      "interval" => $_POST['interval'],
      "product" => [
        "name" => $_POST['plan']
      ],
      "currency" => $_POST['currency'],
      "id" => "plan-".$_POST['plan']
    ]);*/

    $customer = \Stripe\Customer::create(array(
      "email" => $_POST['stripeEmail'],
      "source" => $_POST['stripeToken'], // The token submitted from Checkout
    ));

    \Stripe\Subscription::create(array(
      "customer" => $customer->id,
      "items" => array(
        array(
          "plan" => "plan-".$_POST['plan'],
        ),
      ),
    ));
    
    $success = "Thanks! You've subscribed to the " . $_POST['plan'] .  " plan.";
  }
  catch(\Stripe\Error\Card $e) {
    $body = $e->getJsonBody();
    $error  = $body['error']['message'];
  } 
  catch (\Stripe\Error\RateLimit $e) {
    $error = "Sorry, we weren't able to authorize your card. You have not been charged.1";
  } catch (\Stripe\Error\InvalidRequest $e) {
    $error = "Sorry, we weren't able to authorize your card. You have not been charged.2";
  } catch (\Stripe\Error\Authentication $e) {
    $error = "Sorry, we weren't able to authorize your card. You have not been charged.3";
  } catch (\Stripe\Error\ApiConnection $e) {
    $error = "Sorry, we weren't able to authorize your card. You have not been charged.4";
  } catch (\Stripe\Error\Base $e) {
    $error = "Sorry, we weren't able to authorize your card. You have not been charged.5";
  } catch (Exception $e) {
    $error = "Sorry, we weren't able to authorize your card. You have not been charged.6";
  }
}
?>