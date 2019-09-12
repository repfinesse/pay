<?php 
  require_once("config.php"); 
  require_once("create-subscription.php"); 
?>
<html>
  <head>
    <style>
      .spacing {
        margin-top:20px;
      }
      .success {
        color: #fff;
        background-color: green;
      }
      .error {
        color: #fff;
        background-color: red;
      }

    </style>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  </head>
  <body>
    <?php if (isset($success)): ?>
      <div class='success'><?php echo $success; ?></div>
    <?php else: ?>
      <?php if (isset($error)): ?>
        <div class='error'><?php echo $error; ?></div>
      <?php endif ?>       
      
<style>
* {
    box-sizing: border-box;
}

body {
    margin:50px 0; 
    padding:0;
    text-align:center;
}

.content {
     
    margin:0 auto;
    text-align:left;
    padding:15px;   
}

.columns {
    float: left;
    width: 33.3%;
    padding: 8px;
}

.price {
    list-style-type: none;
    border: 1px solid #eee;
    margin: 0;
    padding: 0;
    -webkit-transition: 0.3s;
    transition: 0.3s;
}

.price:hover {
    box-shadow: 0 8px 12px 0 rgba(0,0,0,0.2)
}

.price .header {
    background-color: #703b09;
    color: white;
    font-size: 25px;
}

.price li {
    border-bottom: 1px solid #eee;
    padding: 20px;
    text-align: center;
}

.price .grey {
    background-color: #eee;
    font-size: 20px;
}

.button {
    background-color: #4CAF50;
    border: none;
    color: white;
    padding: 10px 25px;
    text-align: center;
    text-decoration: none;
    font-size: 18px;
}

@media only screen and (max-width: 600px) {
    .columns {
        width: 100%;
    }
}
</style>
 
<div class="content" style="width:60%;">
<h2 style="text-align:center">Stripe Subscriptions</h2>
<p style="text-align:center">with Responsive Pricing Tables</p>

<div class="columns">
  <ul class="price">
    <li class="header">Premium</li>
    <li class="grey">$ 49.99 / year</li>
    <li>Premium Features</li>
    <li class="grey">
      <form action="" method="POST" class="spacing">     
              <input name="plan" type="hidden" value="Unlimited" />         
              <input name="interval" type="hidden" value="month" />         
              <input name="price" type="hidden" value="74.99" />         
              <input name="currency" type="hidden" value="usd" />           
              <script
                src="https://checkout.stripe.com/checkout.js" class="stripe-button"
                data-key="<?php echo $config['pk']; ?>"
                data-image="https://www.w3school.info/wp-content/uploads/2017/08/w3school_logo.png"
                data-name="Unlimited Receipts"
                data-description="Generate as much receipts as you need"
                data-panel-label="Subscribe Now"
                data-label="Subscribe Now"
                data-locale="auto">
              </script>
      </form>
    </li>
  </ul>
</div>

  <?php endif ?>  

</div>
  </body>
</html>