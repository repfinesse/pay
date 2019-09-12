<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
class Client
{
    private $username;
    private $email;
    private $id;
    private $uses;
    private $used;
    private $printoutUses;
    private $printoutUsed;
    private $ismember;
    private $memberUses;
    private $level;
    private $price;
    private $printPrice;
    private $isAdmin;
    private $deets;
    private $p_brands;
    private $e_brands;
    private $allBrands;
    public function __construct( $id )
    {
        require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/auth/db-connect.php';
        $db = $db2 = new Mysqlidb( 'localhost', 'repftfef_codes', 'alwknAFlwknd3ialko', 'repftfef_codes' );
        $db->where( "id", $id );
        $results = $db->getOne( "singleuse" );
        $db->disconnect();
        $db2->where( "user_id", $id );
        $sub = $db2->getOne( "user_subscriptions" );
        $t   = time();
        if ( $db2->count < 1 ) {
            $this->level = 0;
        } else {
            if ( strtotime( $sub[ 'valid_to' ] ) > $t ) {
                $this->level = $sub[ 'plan' ];
            } else {
                $this->level = 0;
            }
        }
        $db2->disconnect();
        $this->username     = $results[ 'username' ];
        $this->email        = $results[ 'email' ];
        $this->id           = $results[ 'id' ];
        $this->uses         = $results[ 'uses' ];
        $this->memberUses   = $results[ 'memberUses' ];
        $this->used         = $results[ 'used' ];
        $this->printoutUses = $results[ 'printoutUses' ];
        $this->printoutUsed = $results[ 'printoutUsed' ];
        if ( $results[ 'email_key' ] == "admin" ) {
            $this->isAdmin = true;
        } else {
            $this->isAdmin = false;
        }
        switch ( $this->level ) {
            case 1:
                $a = 10;
                $p = 15;
                break;
            case 2:
                $a = 8;
                $p = 12;
                break;
            case 3:
                $a = 7;
                $p = 8;
                break;
            default:
                $a = 13;
                $p = 20;
                break;
        }
        $this->price = $a;
        $this->printPrice = $p;
        
        $this->setBrands();
    }
    private function setBrands(){
        require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/auth/db-connect.php';
        $db = new Mysqlidb( 'localhost', 'repftfef_codes', 'alwknAFlwknd3ialko', 'repftfef_codes' );
        $aB = $db->get( "lp_brands" );
        $e = array();
        $p = array();
        foreach($aB as $b){
            if($b['type'] == "email"){
                $e[$b['brand']] = $b['name'];
            }else{
                $p[$b['brand']] = $b['name'];
            }
        }
        $this->e_brands = $_SESSION['e_brands'] = $e;
        $this->p_brands = $_SESSION['p_brands'] = $p;
        $this->allBrands = $aB;
        $db->disconnect();
    }
    public function getBrands($type){
        if($type == "email"){
            return $this->e_brands;
        }else{
            return $this->p_brands;
        }
    }
    function price($p = false  )
    {
        $a = $p === false ? $this->price : $this->printPrice;
        return $a;
    }
    function isAdmin( )
    {
        return $this->isAdmin;
    }
    function getPrice( $q, $p = false )
    {
        $a = $p === false ? $this->price : $this->printPrice;
        $t = number_format( $a * $q, 2 );
        $b = array(
             "amount" => number_format( $a, 2 ),
            "amountRaw" => $a,
            "quantity" => $q,
            "total" => $t 
        );
        return $b;
    }
    function checkCode($p = false )
    {
        if($p){
            if ( $this->printoutUses <= $this->printoutUsed ) {
            return false;
        } else {
            return "printout";
        }
        }else{
        if ( $this->level > 0 ) {
            if ( $this->memberUses > 0 ) {
                return "member";
            }
        }
        if ( $this->uses <= $this->used ) {
            return false;
        } else {
            return "client";
        }
        }
    }

    function isMember( )
    {
        $out = array( );
        if ( $this->level == 0 ) {
            return false;
        } else {
            $out[ 'level' ] = $this->level;
            $out[ 'uses' ]  = $this->memberUses;
            return $out;
        }
    }
    public function setHead($type){
        $left = $this->uses - $this->used;
        $pr_l = $this->printoutUses - $this->printoutUsed;
        $out = "";
        switch($this->level){
            case 0:
            default:
            $pp = 0;
            break;
            case 1:
            $pp = 10;
            break;
        case 2:
            $pp = 20;
            break;
        case 3:
            $pp = 100;
            break;
        }
        if($pp > 1){
            $label = "Plan: " . $this->memberUses . " receipt(s) left";
            $p = $this->memberUses / $pp * 100;
            $pb = <<<WWW
   <div class="container">
    <div class="progress md-progress" style="height: 30px">
    <div class="progress-bar breeze" role="progressbar" style="width: {$p}%; height: 30px" aria-valuenow="{$this->memberUses}" aria-valuemin="0" aria-valuemax="{$pp}">{$label}</div></div></div>
WWW;
        }else {
            $label = $pb = null;
        }
        if($type != "topup"){
            
            if($type == "email"){
                $term = "receipts";
                $p = $this->price();
                $br = $_SESSION['e_brands'];
            }else {
                $left = $pr_l;
                $term = "printout";
                $p = $this->price(true);
                $br = $_SESSION['p_brands'];
            
        }
        $out =  '

		<div aria-labelledby="tabemail" class="tab-pane fade active show" role="tabpanel">
			<div class="row">
				<div class="col-md-3">
				<ul class="rolldown-list" id="myList">
';
			$i = 1;
			foreach ($br as $k => $v){
			    
			    $out .=  '
			   
					<li class="nav-link text-left" id="tab'.$i.'"  href="/'.$type.'/'.$k.'"  >'.$v.'
					</li>
				
				';
				$i ++;
			}
				
			$out .=  <<<WWW
			</ul></div>
				<div class="col-md-9">
					<!-- Tab panels -->
					<div class="tab-content vertical">
						<div class="tab-pane fade in show active tabcontent" role="tabpanel">
							<div class="container text-center guarantee-payment" data-v-7805ee3a="">
        <div class="d-flex justify-content-center">
        
            <p class="h3-responsive breeze-text"><strong class="breeze-text">{$left}</strong> </p><span> </span>
            <p class="h5-responsive breeze-text">&nbsp;{$term}(s) left</p>
        
        
        </div>
        
        <div class="level-item">
            {$pb}
       </div>
        <div class="level-item">
            <br>
            
        <br>
        <form action="/stripe" class="range-field w-75" method="get">
<div class="form-check form-check-inline">
  
</div>
        		<span class="black-text justify-content-center d-flex" data-v-7805ee3a id="val">0</span><span class="black-text justify-content-center d-flex" data-v-7805ee3a id="term">&nbsp;{$term}(s)</span>
        		<input class="border-0 active coolio" id="q" max="10" min="0" name="q" oninput="slide()" type="range" value="0">
        		<span class="thumb value" style="left: 0px; height: 0px; width: 0px; top: 10px; margin-left: -6px;"></span> 
        		<input name="id" type="hidden" value="{$this->id}">
        		<input type="hidden" value="{$p}" id="p">
        		<input type="hidden" value="{$term}" name="type">
        		<input name="username" type="hidden" value=" {$this->username}"> <input name="submit" type="hidden" value="1">
        		<span class="black-text justify-content-center d-flex" data-v-7805ee3a id="amt">$0.00</span>
        		<div class="text-center form-group">
        		    <button    formaction="/card"class="demo2 btn btn-warning btn-md waves-effect waves-light" type="submit"><i class="fas fa-credit-card"></i> <span id="butt1">Credit Card</span></button>
        			<button    formaction="/paypal"class="demo2 btn btn-primary btn-md waves-effect waves-light" type="submit"><i class="fab fa-paypal"></i> <span id="butt3">Paypal</span></button>
        			<!--button formaction="/zz.php"class="demo2 btn btn-warning btn-md waves-effect waves-light" type="submit"><i class="fab fa-bitcoin"></i> <span id="butt2">Bitcoin</span></button>
        			<button formaction="/pm/process"class="demo2 btn btn-danger btn-md waves-effect waves-light" type="submit"> <span id="butt2">PerfectMoney</span></button-->
        		</div>
        	</form>
       </div>
							<div class="preloader-wrapper active" id="preloader">
								<div class="spinner-layer spinner-blue-only">
									<div class="circle-clipper left">
										<div class="circle"></div>
									</div>
									<div class="gap-patch">
										<div class="circle"></div>
									</div>
									<div class="circle-clipper right">
										<div class="circle"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>


WWW;
        
            

            
        }else{
            
        
        $out = <<<AAA
<div class="container text-center guarantee-payment" data-v-7805ee3a="">
        <div class="d-flex justify-content-around">
        <div class="d-flex justify-content-start">
            <p class="h3-responsive breeze-text"><strong class="breeze-text">{$left}</strong> </p><span> </span>
            <p class="h5-responsive breeze-text">&nbsp;receipt(s) left</p>
        </div>
        <div class="d-flex justify-content-end">
            <p class="h3-responsive breeze-text"><strong class="breeze-text">{$pr_l}</strong> </p><span> </span>
            <p class="h5-responsive breeze-text">&nbsp;printout(s) left</p>
        </div>
        </div>
        
        <div class="level-item">
            {$pb}
       </div>
        <div class="level-item">
            <br>
            
        <br><a href="" data-toggle="modal" data-target="#modalLRForm" class="btn btn-success btn-md waves-effect waves-light">Top up</a>
       <a href="/profile"  class="btn btn-success btn-md waves-effect waves-light">My Account</a>
       <a href="/subscribe"  class="btn btn-success btn-md waves-effect waves-light">Subscribe</a><br>
    </div>
       </div>
AAA;
}

$this->deets = $out;
    }
    public function getHead(){
        return $this->deets;
    }
    function getUses( )
    {
        $uses                   = array( );
        $uses[ 'uses' ]         = $this->uses;
        $uses[ 'used' ]         = $this->used;
        $uses[ 'left' ]         = $this->uses - $this->used;
        $uses[ 'printoutUses' ] = $this->printoutUses;
        $uses[ 'printoutUsed' ] = $this->printoutUsed;
        $uses[ 'printoutLeft' ] = $this->printoutUses - $this->printoutUsed;
        return $uses;
    }
    function addUse( $json, $intent )
    {
        include_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/auth/db-connect.php';
        $db = $sql2 = $sql3 = new Mysqlidb( 'localhost', 'repftfef_codes', 'alwknAFlwknd3ialko', 'repftfef_codes' );
        switch ( $intent ) {
            case "client":
                $newUses    = $this->used + 1;
                $t          = "used";
                $this->used = $newUses;
                $sql2->where( "username", $this->username );
                $data = array(
                     "used" => $newUses 
                );
                $sql2->update( "singleuse", $data );
                $sql2->disconnect();
                $log = true;
                break;
            case "member":
                if ( $this->level == 3 ) {
                } else {
                    $newUses          = $this->memberUses - 1;
                    $this->memberUses = $newUses;
                    $sql2->where( "username", $this->username );
                    $data = array(
                         "memberUses" => $newUses 
                    );
                    $sql2->update( "singleuse", $data );
                    $sql2->disconnect();
                }
                $log = true;
                break;
            case "printout":
                $newUses            = $this->printoutUsed + 1;
                $t                  = "used";
                $this->printoutUsed = $newUses;
                $sql2->where( "username", $this->username );
                $data = array(
                     "printoutUsed" => $newUses 
                );
                $sql2->update( "singleuse", $data );
                $sql2->disconnect();
                $log = false;
                break;
            case "memberPrintout":
                break;
        }
        if ( $log ) {
            $data = array(
                 "user_id" => $this->id,
                "message" => $json 
            );
            $sql3->insert( "receipts", $data );
        }
        $sql3->disconnect();
    }
    function topup( $uses, $admin = false , $print = false)
    {
        include_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/auth/db-connect.php';
        $db              = new Mysqlidb( 'localhost', 'repftfef_codes', 'alwknAFlwknd3ialko', 'repftfef_codes' );
        $user            = array( );
        $user[ 'added' ] = $uses;
        $user[ 'type' ]  = "topup";
        if($print === false){
            $adduses         =  $this->uses + $uses;
             $data            = array(
             "uses" => $adduses 
        );
        }else{
            $adduses         =  $this->printUses + $uses;
            $data            = array(
             "printUses" => $adduses 
        );
        }
        
       
        $sql2            = new Mysqlidb( 'localhost', 'repftfef_codes', 'alwknAFlwknd3ialko', 'repftfef_codes' );
        $sql2->where( "id", $this->id );
        if ( $sql2->update( "singleuse", $data ) ) {
            $x = $this->sendEmail( $user );
            if ( $x ) {
                if ( $admin ) {
                    return $this->username . " topped up successfully with " . $uses . " receipts";
                } else {
                    return true;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    function sub( $method, $amount, $plan, $subscr_id )
    {
        include_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/auth/db-connect.php';
        $db = $db2 = new Mysqlidb( 'localhost', 'repftfef_codes', 'alwknAFlwknd3ialko', 'repftfef_codes' );
        switch ( $plan ) {
            case 1:
                $a = 35;
                $r = 15;
                break;
            case 2:
                $a = 55;
                $r = 30;
                break;
            case 3:
                $a = 75;
                $r = 300;
                break;
        }
        $days      = ( $amount / $a ) * 30;
        $date_from = date( "Y-m-d H:i:s" );
        $date_to   = date( "Y-m-d H:i:s", strtotime( $date_from . ' +  30 days' ) );
        $data      = array(
             "user_id" => $this->id,
            "payment_method" => $method,
            "valid_from" => $date_from,
            "valid_to" => $date_to,
            "subscr_id" => $subscr_id,
            "plan" => $plan 
        );
        $sid       = $db->insert( "user_subscriptions", $data );
        if ( $sid ) {
            $emai  = array(
                 "type" => "subscribe",
                "plan" => $plan,
                "valid" => $valid_to 
            );
            $data2 = array(
                 "subscr_id" => $sid,
                "memberUses" => $r,
                "membershipStatus" => 1 
            );
            $db2->where( "id", $this->id );
            if ( $db2->update( 'singleuse', $data2 ) ) {
                return true;
            } else {
                return 'update failed: ' . $db2->getLastError();
            }
        } else {
            return 'insert failed: ' . $db->getLastError();
        }
    }
    public function gettabContent($email = true){
        if($email == true){$type = "email";}else{$type = "print";}
        switch($type){
            case "email":
                $br = $_SESSION['e_brands'];
                
            break;
            case "print":
                $br = $_SESSION['p_brands'];
            break;
        }
        $out =  '<div class="classic-tabs mx-2">
	<ul class="nav tabs-green" id="myClassicTabOrange" role="tablist">
		<li class="nav-item">
			<a aria-controls="content_panel" aria-selected="true" class="nav-boss waves-light active show" data-toggle="tab" href="/email/topup" id="tabemail" role="tab"><i aria-hidden="true" class="fas fa-envelope fa-2x pb-2"></i><br>
			Emails</a>
		</li>
		<li class="nav-item">
			<a class="nav-boss waves-light" data-toggle="tab" href="/print/topup" id="tabprintf" role="tab"><i aria-hidden="true" class="fas fa-print fa-2x pb-2"></i><br>
			Printouts</a>
		</li>
	</ul>
	<div class="tab-content card" id="content_panel">
		<div aria-labelledby="tabemail" class="tab-pane fade active show" id="content_panel" role="tabpanel">
			<div class="row">
				<div class="col-md-3">
				<ul class="rolldown-list" id="myList">
';
			$i = 1;
			foreach ($br as $k => $v){
			    
			    $out .=  '
			   
					<li class="nav-link text-left" id="tab'.$i.'"  href="/'.$type.'/'.$k.'"  >'.$v.'
					</li>
				
				';
				$i ++;
			}
				
			$out .=  '	
			</ul></div>
				<div class="col-md-9">
					<!-- Tab panels -->
					<div class="tab-content vertical">
						<div class="tab-pane fade in show active tabcontent" role="tabpanel">
							<h5 class="my-2 h5">Sup Nigga</h5>
							<div class="preloader-wrapper active" id="preloader">
								<div class="spinner-layer spinner-blue-only">
									<div class="circle-clipper left">
										<div class="circle"></div>
									</div>
									<div class="gap-patch">
										<div class="circle"></div>
									</div>
									<div class="circle-clipper right">
										<div class="circle"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div><!-- Classic tabs -->
';
return $out;    }
    public function tabContent($type){
        $o = [];
        
        switch($type){
            case "email":
            $o['uses'] = $this->uses;
            $o['used'] = $this->used;
            
                break;
            case "print":
            $o['uses'] = $this->printoutUses;
            $o['used'] = $this->printoutUsed;
            
                break;
        }
    }
    private function topupModal($type){
        
    }
    private function sidenav(array $links){
        
    }
    function subscribe( $ipnType )
    {
    }
    function email( )
    {
        return $this->email;
    }
    function sendEmail( array $vars )
    {
        require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/includes/PHPMailer/Exception.php';
        require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/includes/PHPMailer/PHPMailer.php';
        require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/includes/PHPMailer/SMTP.php';
        $user     = $vars;
        $type     = $vars[ 'type' ];
        $to       = $this->email;
        $uses     = $user[ 'added' ];
        $r        = $uses == 1 ? "use" : "uses";
        $username = $fullname = $this->username;
        switch ( $type ) {
            case "topup":
                $title   = "Thank you for your order!";
                $subject = "Your repfinesse account top-up was successful";
                $content = "<p style='font-size: 13px;color: #878787;'>Your repfinesse account @{$username} has been successfully topped up with {$uses} {$r} and you can use it <a href='https://www.repfinesse.com/receipts' style='color: #2F82DE;font-weight: bold;text-decoration: none;'>here</a>.</p>";
                break;
            case "subscribe":
                $title   = "Thank you for subscribing!";
                $subject = "Your repfinesse membership is now active!";
                $content = "<p style='font-size: 13px;color: #878787;'>Dear @{$username}, has been successfully topped up with {$uses} {$r} and you can use it <a href='https://www.repfinesse.com/receipts' style='color: #2F82DE;font-weight: bold;text-decoration: none;'>here</a>.</p>";
                break;
            case "forgot":
                $title   = "Reset your Password";
                $subject = "Reset your Repfinesse password";
                $pin     = $user[ 'pin' ];
                $url     = $user[ 'url' ];
                $content = <<<WWW
        <p>We have received a request to reset your account password at our website. &nbsp;If you made this request, follow the link below to reset your password.</p>
        <p>&nbsp;</p><p><span class="marker"><strong>Username: {$username}</strong></span></p>
        <p><span class="marker"><strong>Temporary Pin: {$pin}</strong></span></p>
        <p>{$url}</p>
WWW;
                break;
            default:
                $title   = "";
                $subject = "";
                $content = "";
                return "No type";
        }
        $e_head = <<<WWW
    <center>
<table class='entire-page' style='background: #C7C7C7;width: 100%;padding: 20px 0;font-family: 'Lucida Grande', 'Lucida Sans Unicode', Verdana, sans-serif;line-height: 1.5;'>
        <tr>
            <td style='font-size: 13px;color: #878787;'>
                <table class='email-body' style='max-width: 600px;min-width: 320px;margin: 0 auto;background: white;border-collapse: collapse;img { max-width: 100%;'>
                    <tr>
                        <td class='email-header' style='font-size: 13px;color: #878787;background: #00C967;padding: 30px;'>
                            <a href='https://repfinesse.com' style='color: #2F82DE;font-weight: bold;text-decoration: none;'><img alt='Order Success' src='https://www.repfinesse.com/images/logo_allblack.png' width='100'></a>
                        </td>
                    </tr>
                    <tr>
                        <td class='news-section' style='font-size: 13px;color: #878787;padding: 20px 30px;'>
                        <h1>{$title}</h1>
                            <p><strong>Hello {$fullname}, </strong></p>
                        <p>&nbsp;</p>    
WWW;
        $e_foot = <<<WWW
        
                        <p>&nbsp;</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</center>
WWW;
        $e_body = $e_head . $content . $e_foot;
        $mail   = new PHPMailer;
        $mail->isSMTP();
        $mail->SMTPDebug  = 0;
        $mail->Host       = 'repfinesse.com';
        $mail->Port       = 465;
        $mail->SMTPSecure = 'ssl';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'noreply@repfinesse.com';
        $mail->Password   = '^p&CGtZlod[a';
        $mail->From       = "noreply@repfinesse.com";
        $mail->FromName   = "RepFinesse";
        $mail->addAddress( $email, $username );
        $mail->addReplyTo( "admin@repfinesse.com", "Reply" );
        $mail->addBCC( "repfinesse@gmail.com" );
        $mail->isHTML( true );
        $mail->Subject = $subject;
        $mail->Body    = $e_body;
        if ( $mail->send() ) {
            return "Mail aint send for some reason";
        } else {
            return true;
        }
        ;
    }
    
function profile_details( )
{

    $profile_email    = $this->email;
    $profile_username = $this->username;
    echo '
		<form method="post" action="data/update-user" >
		<div class="md-form input-group drose-blue"><label style="color: #00C967;">Email Address</label> <input class="form-control" name="e" value="' . $profile_email . '" type="text">
		<div class="input-group-append">
    <button class="btn btn-breeze btn-md" type="submit" >Update</button>
  </div>
		</div>
		</form>';
}
}
class Printout{
     private $labels;
     private $form;
     private $price;
     private $info;
     private $name;
     private $title;
     private $type;
     
    public function __construct( $id , $form, $name, $type)
    {
        require_once $_SERVER[ 'DOCUMENT_ROOT' ] . '/auth/db-connect.php';
        $db = $db2 = $db3 = new Mysqlidb( 'localhost', 'repftfef_codes', 'alwknAFlwknd3ialko', 'repftfef_codes' );
        $form = str_replace( ' ', '', $form );
        $this->title = $form;
    $info = array( );
    $db->where( "user_id", $id );
    $info = $db->getOne( 'form_prefill' );
    if ( $db->count > 0 ) {
        $info[ 'address' ] = $info[ 'addy1' ] . " " . $info[ 'addy2' ];
        $info[ 'name' ]    = $info[ 'fname' ] . " " . $info[ 'lname' ];
    }else {
        $info[] = " ";
    }
    $this->info = $info;
    $this->name = $name;
    $this->price =  <<<WWW
<div class="row">
	<div class="col-sm">
		<div class="md-form form-group">
			<label>Price</label> <input class="form-control" name="purchaseprice" placeholder="168" type="number" value="">
		</div>
	</div>
	<div class="col-sm">
		<div class="md-form form-group">
			<select class="mdb-select" name="currency">
				<option selected value="USD_$">
					US dollar (USD)
				</option>
				<option value="EUR_&euro;">
					Euro (EUR)
				</option>
				<option value="JPY_¥">
					Japanese yen (JPY)
				</option>
				<option value="GBP_&#163;">
					Pound sterling (GBP)
				</option>
				<option value="AUD_$">
					Australian dollar (AUD)
				</option>
				<option value="CAD_$">
					Canadian dollar (CAD)
				</option>
				<option value="CHF_Fr">
					Swiss franc (CHF)
				</option>
				<option value="CNY_¥">
					Chinese Yuan
				</option>
				<option value="SEK_kr">
					Swedish krona (SEK)
				</option>
				<option value="NZD_$">
					New Zealand dollar (NZD)
				</option>
			</select>
		</div>
	</div>
</div>
WWW;
$l = array();
if($type == "email"){
    $this->type = "email";
    $db2->where( 'brand', $form );
    $cols  = Array(
         "label",
        "name",
        "value",
        "type" 
    );
    $users = $db2->get( "lp_forms", null, $cols );
    $this->form = $users;
    foreach($users as $user){
            $l[$user['name']] = $user['label'];
        }
}else {
    $this->type = "print";
    $labels = $db2->get( "labels" );
        foreach($labels as $label){
            $l[$label['name']] = $label['value'];
        }
        
        $db->disconnect();
        $db2->disconnect();
        $db3->where( "brand", $form );
        $sub = $db3->get( "forms" );
        $this->form = $sub;
}
$this->labels = $l;
        
        
        
       
    }
    function setForm( )
{
    
    $users = $this->form;
        $output = "";
        $button = '<button type="submit"class="btn btn-white-breeze btn-md waves-effect waves-light">Generate</button>';
        $class = $this->type == "email" ? "generate-form" : "printout-form";
        $output .= '
        <div class="container breeze">
						<br><h5 class="card-title h2 text-center text-white" id="' . $this->title . '" >' . $this->name . '</h5>
							<div class="card-body">
        <form   novalidate method="post" name="' . $this->title . '" method="post" class="'.$class.' needs-validation breeze-text text-breeze">' . $this->price;
        foreach ( $users as $user ) {
            $output .= $this->parseRow( $user[ 'name' ], $this->labels[$user[ 'name' ]], $user[ 'value' ], $user[ 'type' ], $this->info );
        }
        $output .= '
			                <input type="hidden" name="username" value="' . $_SESSION[ "username" ] . '">
			                <input type="hidden" name="id" value="' . $_SESSION[ "id" ] . '">
			                <input name="generate" id="generate" type="hidden" value="0"><input class="brand" id="brand" name="brand" type="hidden" value="' . $this->title . '">
			                <div class="field is-grouped">' . $button . '</div>
			                </form>
			                </div>
			                </div>
			                ';
			                
   
    $this->output = $output;
}
function getForm(){
    return $this->output;
}
function debug(){
    return $this;
}
function parseRow( $name, $label, $value, $type, array $info )
{
    
    if ( !empty( $value ) ) {
        if($name == "orderno"){$type = "orderno";}
        switch($type){
            case "select":
                $opt    = explode( "_", $value );
                $output = '<select class="mdb-select md-form" name="' . $name . '">
			                            <option value="disabled selected">' . $label . '</option>';
                foreach ( $opt as $k => $v ) {
                    $output .= '
			                            <option value="' . $v . '">' . ucfirst( $v ) . '</option>';
                }
                $output .= '</select>';
                break;
            case "date":
            
                $output = '
			                           <div class="md-form" >
                                          <input data-value="' . $value . '" type="text" name="' . $name . '" id="date-picker" class="form-control datepicker" required>
                                         <label for="date-picker">' . $label . '</label>
                                        </div>
			                           ';
            
                break;
            case "multirow":
                $output = '
                <div class="md-form form-group ">
                <table class="items">
<tr>
<td><input type="text" placeholder="Style number" class="form-control" name="items[]"></td>
<td><input type="text" placeholder="Quantity" class="form-control" name="items[]"></td>
<td><input type="text" placeholder="Description" class="form-control" name="items[]"></td>
<td><input type="text" placeholder="Color" class="form-control" name="items[]"></td>
<td><input type="text" placeholder="Size" class="form-control" name="items[]"></td>
</tr>
</table>
<a role="button" class="btn btn-breeze btn-md waves-effect waves-light add_item">Add New Item &nbsp;<span style="font-size:16px; font-weight:bold;">+ </span></a>
</div>
';
                break;
            case "orderno":
                $r = $value;
            eval( "\$v = $r;" );
            $output = '<div class="md-form form-group"><label>' . $label . '</label> <input required class="form-control" name="' . $name . '"  value="' . $v . '" type="text"></div>';
                break;
            default:
                $output = '<div class="md-form form-group"><label>' . $label . '</label> <input required class="form-control" name="' . $name . '" type="text" placeholder="'.$value.'"></div>';
                
        }
        
    } else if ( array_key_exists( $name, $info ) ) {
        $output = '<div class="md-form form-group"><label>' . $label . '</label> <input required class="form-control" name="' . $name . '" type="text" value="' . $info[ $name ] . '"></div>';
    } else {
        $output = '<div class="md-form form-group"><label>' . $label . '</label> <input required class="form-control" name="' . $name . '" type="text"></div>';
    }
    return $output;
}
}

function profile_details( $owner )
{
    include_once( 'auth/db-connect.php' );
    $db = new Mysqlidb( 'localhost', 'repftfef_codes', 'alwknAFlwknd3ialko', 'repftfef_codes' );
    $db->where( "id", $owner );
    $get_profile      = $db->getOne( "singleuse" );
    $profile_email    = $get_profile[ 'email' ];
    $profile_username = $get_profile[ 'username' ];
    echo '
		<form method="post" action="data/update-user" >
		<div class="md-form input-group drose-blue"><label style="color: #00C967;">Email Address</label> <input class="form-control" name="e" value="' . $profile_email . '" type="text">
		<div class="input-group-append">
    <button class="btn btn-breeze btn-md" type="submit" >Update</button>
  </div>
		</div>
		</form>';
}
function buttons( $plan, $class = null )
{
    if ( !$class ) {
        $class = "breeze";
    } else {
        $class = "white-breeze";
    }
    $a    = $_SESSION[ 'plans' ];
    $pp   = $a[ $plan ];
    $cli  = $plan . "_" . $_SESSION[ 'user_id' ];
    $out  = <<<WWW
<div class="btn-group" role="group">
    <a href="/unlimited/pp/{$_SESSION['user_id']}/{$plan}" class="btn btn-{$class} btn-rounded">
        <i class="fab fa-paypal"></i>
    </a>
    <a href="/unlimited/cc/{$_SESSION['user_id']}/{$plan}" class="btn btn-{$class} btn-rounded">
        <i class="fas fa-credit-card"></i>
    </a>
</div>
WWW;
    $out2 = <<<WWW
<div class="btn-group" role="group">
    <a href="/unlimited/pp/{$_SESSION['user_id']}/{$plan}" class="btn btn-{$class} btn-rounded">
        <i class="fab fa-paypal"></i>
    </a>
    <button value="{$cli}" role="link" id="{$pp}" class="btn btn-{$class} btn-rounded substripe-button">
        <i class="fas fa-credit-card"></i>
    </button>
</div>
WWW;
    return $out;
}
function form_info( $owner )
{
    include_once( 'auth/db-connect.php' );
    $db = new Mysqlidb( 'localhost', 'repftfef_codes', 'alwknAFlwknd3ialko', 'repftfef_codes' );
    $db->where( "user_id", $owner );
    $fi          = $db->getOne( "form_prefill" );
    $fname       = isset( $fi[ 'fname' ] ) ? $fi[ 'fname' ] : "";
    $lname       = isset( $fi[ 'lname' ] ) ? $fi[ 'lname' ] : "";
    $addy1       = isset( $fi[ 'addy1' ] ) ? $fi[ 'addy1' ] : "";
    $addy2       = isset( $fi[ 'addy2' ] ) ? $fi[ 'addy2' ] : "";
    $cardno      = isset( $fi[ 'cardno' ] ) ? $fi[ 'cardno' ] : rand(1111,4444);
    $expiry      = isset( $fi[ 'expiry' ] ) ? $fi[ 'expiry' ] : "";
    $addressmaps = isset( $fi[ 'addressmaps' ] ) ? $fi[ 'addressmaps' ] : "https://maps.google.com";
    $country     = isset( $fi[ 'country' ] ) ? $fi[ 'country' ] : "";
    $phone       = isset( $fi[ 'phone' ] ) ? $fi[ 'phone' ] : "";
    echo <<<WWW
		<form method="post" action="data/update-form-info">
		<div class="md-form form-group drose-white"><label style="color: #00C967;" for="fname">First Name </label><input style="color: #00C967;"  class="form-control" name="fname" id="fname" value="{$fname}" type="text"></div>
		<div class="md-form form-group"><label style="color: #00C967;" for="lname">Last Name </label><input style="color: #00C967;"  class="drose-white form-control" name="lname" id="lname" value="{$lname}" type="text"></div>
		<div class="md-form form-group"><label style="color: #00C967;" for="addy1">Address Line 1 </label><input style="color: #00C967;"  class="form-control" name="addy1" id="addy1" value="{$addy1}" type="text"></div>
		<div class="md-form form-group"><label style="color: #00C967;" for="addy2">Address Line 2  </label><input style="color: #00C967;"   class="form-control" name="addy2" id="addy2" value="{$addy2}" type="text"></div>
		<div class="md-form form-group"><label style="color: #00C967;" for="country">Country </label><input style="color: #00C967;"  class="form-control" name="country" id="country" value="{$country}" type="text"></div>
		<div class="md-form form-group"><label style="color: #00C967;" for="addressmaps">Google maps URL to address </label><input style="color: #00C967;"  class="form-control" name="addressmaps" id="addressmaps" value="{$addressmaps}" type="text"></div>
		<div class="md-form form-group"><label style="color: #00C967;" for="cardno">Last 4 Digits </label><input style="color: #00C967;"  class="form-control" name="cardno" id="cardno" value="{$cardno}" type="text"></div>
		<div class="md-form form-group"><label style="color: #00C967;" for="expiry">Expiry </label><input style="color: #00C967;"  class="form-control" name="expiry" id="expiry" value="{$expiry}" type="text"></div>
		<div class="md-form form-group"><label style="color: #00C967;" for="phone">Phone Number </label><input style="color: #00C967;"  class="form-control" name="phone" id="phone" value="{$phone}" type="text"></div>
		<input   type="submit" class="btn btn-breeze btn-md" value="Save">
		</form>'
WWW;
}
function user_table( )
{
    include_once( $_SERVER[ 'DOCUMENT_ROOT' ] . '/auth/db-connect.php' );
    $db = new Mysqlidb( 'localhost', 'repftfef_codes', 'alwknAFlwknd3ialko', 'repftfef_codes' );
    include_once( 'lang/' . $language . '.php' );
    $query = "SELECT * FROM " . $prefix . "members ORDER BY fullname ASC";
    $query = $mysqli->real_escape_string( $query );
    if ( $result = $mysqli->query( $query ) ) {
        $num_results = mysqli_num_rows( $result );
        while ( $row = $result->fetch_array() ) {
            $member     = $row[ 'id' ];
            $terms      = $row[ 'terms' ];
            $user_level = $row[ 'user_level' ];
            $plan       = $row[ 'plan_id' ];
            echo '<tr>
						<td>' . $row[ 'fullname' ] . '</td>
						<td>' . $row[ 'username' ] . '</td>
						<td>' . $row[ 'email' ] . '</td>
						<td>';
            if ( $row[ 'email_confirmed' ] == '1' ) {
                echo 'Yes';
            } else {
                echo 'No';
            }
            echo '</td>
						<td>';
            if ( $terms == '1' ) {
                echo 'Yes';
            } else {
                echo 'No';
            }
            echo '</td>
						<td>
							<form method="post" action="data/change-user-membership">
								<input type="hidden" name="m" value="' . $row[ 'id' ] . '">
								<select class="mdb-select md-form" name="l" onchange="this.form.submit()">
									<option value="">None</option>';
            membership_select( $plan );
            echo '
								</select>
							</form>
						</td>
						<td>
							<form method="post" action="data/change-user-level">
								<input type="hidden" name="m" value="' . $row[ 'id' ] . '">
								<select class="mdb-select md-form" name="l" onchange="this.form.submit()">';
            permission_select_selected( $user_level );
            echo '
								</select>
							</form>
						</td>
						<td>
						<form method="post" action="data/change-user-status">
							<input type="hidden" name="m" value="' . $row[ 'id' ] . '">
							<select class="mdb-select md-form" name="status" onchange="this.form.submit()">
								<option value="">No Status Set</option>
								<option value="active"';
            if ( $row[ 'status' ] == 'active' ) {
                echo 'selected';
            }
            echo '>Active</option>
								<option value="inactive"';
            if ( $row[ 'status' ] == 'inactive' ) {
                echo 'selected';
            }
            echo '>Inactive</option>
								<option value="suspended"';
            if ( $row[ 'status' ] == 'suspended' ) {
                echo 'selected';
            }
            echo '>Suspended</option>
								<option value="cancelled"';
            if ( $row[ 'status' ] == 'cancelled' ) {
                echo 'selected';
            }
            echo '>Cancelled</option>
							</select>
						</form>
						<td>' . date( "m/d/Y", strtotime( $row[ 'reg_date' ] ) ) . '</td>
						<td>';
            if ( $row[ 'last_login' ] == "0000-00-00 00:00:00" ) {
                echo 'Never';
            } else {
                echo date( "m/d/Y", strtotime( $row[ 'last_login' ] ) );
            }
            echo '</td>
						<td>
							<form method="post" action="data/delete-user">
								<input type="hidden" name="m" value="' . $row[ 'id' ] . '">
								<input type="submit" class="btn btn-sm btn-danger" value="Delete">
							</form>
						</td>
					</tr>';
        }
    }
}
function get_email_content( $email_type )
{
    include_once( $_SERVER[ 'DOCUMENT_ROOT' ] . '/auth/db-connect.php' );
    $db            = new Mysqlidb( 'localhost', 'repftfef_codes', 'alwknAFlwknd3ialko', 'repftfef_codes' );
    $get_ec        = mysqli_fetch_assoc( mysqli_query( $mysqli, "SELECT * FROM " . $prefix . "email_content WHERE id=1" ) );
    $email_content = $get_ec[ $email_type ];
    echo $email_content;
}
function get_form_email( $form )
{
    include_once( $_SERVER[ 'DOCUMENT_ROOT' ] . '/auth/db-connect.php' );
    $db            = new Mysqlidb( 'localhost', 'repftfef_codes', 'alwknAFlwknd3ialko', 'repftfef_codes' );
    $get_ec        = mysqli_fetch_assoc( mysqli_query( $mysqli, "SELECT * FROM " . $prefix . "generate2 WHERE `brand` = '" . $form . "'" ) );
    $email_content = $get_ec[ 'email' ];
    return $get_ec;
}
function get_form_fields( $form )
{
    include_once( $_SERVER[ 'DOCUMENT_ROOT' ] . '/auth/db-connect.php' );
    $db            = new Mysqlidb( 'localhost', 'repftfef_codes', 'alwknAFlwknd3ialko', 'repftfef_codes' );
    $get_ec        = mysqli_fetch_assoc( mysqli_query( $mysqli, "SELECT * FROM " . $prefix . "generate2 WHERE `brand` = '" . $form . "'" ) );
    $email_content = $get_ec[ 'email' ];
    return $get_ec;
}
function all_available_emails( $prefix )
{
    include_once( $_SERVER[ 'DOCUMENT_ROOT' ] . '/auth/db-connect.php' );
    $db    = new Mysqlidb( 'localhost', 'repftfef_codes', 'alwknAFlwknd3ialko', 'repftfef_codes' );
    $query = "SELECT fullname, email FROM " . $prefix . "members ORDER BY fullname DESC";
    $query = $mysqli->real_escape_string( $query );
    if ( $result = $mysqli->query( $query ) ) {
        $num_results = mysqli_num_rows( $result );
        while ( $row = $result->fetch_array() ) {
            echo '"' . $row[ 'fullname' ] . ' <' . $row[ 'email' ] . '>",';
        }
    }
}
function updateEmail( $brand, $message, $from, $subject )
{
    include_once( $_SERVER[ 'DOCUMENT_ROOT' ] . '/auth/db-connect.php' );
    $db   = new Mysqlidb( 'localhost', 'repftfef_codes', 'alwknAFlwknd3ialko', 'repftfef_codes' );
    $data = array(
         "message" => $message,
        "from" => $from,
        "subject" => $subject 
    );
    $db->where( "brand", $brand );
    if ( $db->update( "lp_generate2", $data ) ) {
        $ret = '1';
    }
    return $ret;
    $db->disconnect();
}
function randomKey( $length )
{
    $pool = array_merge( range( 0, 9 ), range( 'A', 'Z' ) );
    for ( $i = 0; $i < $length; $i++ ) {
        $key .= $pool[ mt_rand( 0, count( $pool ) - 1 ) ];
    }
    return $key;
}
function insertForm( $v )
{
    include_once( $_SERVER[ 'DOCUMENT_ROOT' ] . '/auth/db-connect.php' );
    $db   = new Mysqlidb( 'localhost', 'repftfef_codes', 'alwknAFlwknd3ialko', 'repftfef_codes' );
    $stmt = "INSERT INTO `lp_forms`(`brand`, `label`, `name`) VALUES " . $v;
    if ( $mysqli->query( $stmt ) === true ) {
        $r = "Records inserted successfully.";
    } else {
        $r = "ERROR: Could not able to execute $sql. " . $mysqli->error;
    }
    return $r;
}
function getForm( $form, $name, $uid, $test = false, $preview = false )
{
    include_once( $_SERVER[ 'DOCUMENT_ROOT' ] . '/auth/db-connect.php' );
    $db   = new Mysqlidb( 'localhost', 'repftfef_codes', 'alwknAFlwknd3ialko', 'repftfef_codes' );
    $form = str_replace( ' ', '', $form );
    $info = array( );
    $db->where( "user_id", $uid );
    $info = $db->getOne( 'form_prefill' );
    if ( $db->count > 0 ) {
        $info[ 'address' ] = $info[ 'addy1' ] . " " . $info[ 'addy2' ];
        $info[ 'name' ]    = $info[ 'fname' ] . " " . $info[ 'lname' ];
    }
    $action          = $test === false ? ' action="/g2/"' : " ";
    $info[ 'email' ] = $_SESSION[ 'user' ][ 'email' ];
    $form_price      = <<<WWW
<div class="row">
	<div class="col-sm">
		<div class="md-form form-group">
			<label>Price</label> <input class="form-control" name="purchaseprice" placeholder="168" type="number" value="">
		</div>
	</div>
	<div class="col-sm">
		<div class="md-form form-group">
			<select class="mdb-select" name="currency">
				<option selected value="USD_$">
					US dollar (USD)
				</option>
				<option value="EUR_&euro;">
					Euro (EUR)
				</option>
				<option value="JPY_¥">
					Japanese yen (JPY)
				</option>
				<option value="GBP_&#163;">
					Pound sterling (GBP)
				</option>
				<option value="AUD_$">
					Australian dollar (AUD)
				</option>
				<option value="CAD_$">
					Canadian dollar (CAD)
				</option>
				<option value="CHF_Fr">
					Swiss franc (CHF)
				</option>
				<option value="CNY_¥">
					Chinese Yuan
				</option>
				<option value="SEK_kr">
					Swedish krona (SEK)
				</option>
				<option value="NZD_$">
					New Zealand dollar (NZD)
				</option>
			</select>
		</div>
	</div>
</div>
WWW;
    $db2             = new Mysqlidb( 'localhost', 'repftfef_codes', 'alwknAFlwknd3ialko', 'repftfef_codes' );
    $db2->where( 'brand', $form );
    $cols  = Array(
         "label",
        "name",
        "value",
        "type" 
    );
    $users = $db2->get( "lp_forms", null, $cols );
    if ( $db2->count > 0 ) {
        $output = "";
        $button = '<button type="submit"class="btn btn-white-breeze btn-md waves-effect waves-light">Generate</button>';
        if ( $preview ) {
            $button .= '<button data-toggle="modal" data-target="#preview-modal" id="preview-button" type="button" class="btn btn-white-breeze btn-md waves-effect waves-light">Preview</button>';
        }
        $output .= '
        <div class="card drose-blue">
						<br><h5 class="card-title h2 text-center" id="' . $form . '" >' . $name . '</h5>
							<div class="card-body">
        <form   novalidate method="post" name="' . $form . '" method="post" class="generate-form needs-validation white-text text-white" ' . $action . '>' . $form_price;
        foreach ( $users as $user ) {
            $output .= parseRow( $user[ 'name' ], $user[ 'label' ], $user[ 'value' ], $user[ 'type' ], $info );
        }
        $output .= '
			                <input type="hidden" name="username" value="' . $_SESSION[ "username" ] . '">
			                <input type="hidden" name="id" value="' . $_SESSION[ "id" ] . '">
			                <input name="generate" id="generate" type="hidden" value="0"><input class="brand" id="brand" name="brand" type="hidden" value="' . $form . '">
			                <div class="field is-grouped">' . $button . '</div>
			                </form>';
        if ( $preview ) {
            $output .= '
            <div class="modal fade right" id="preview-modal" tabindex="-1" role="dialog" aria-labelledby="preview-label" aria-hidden="true">
  <div class="modal-dialog modal-lg  " role="document">
    <div class="modal-content">
      <div class="modal-header breeze">
        <h5 class="modal-title" id="preview-label">For Ultimate Members Only</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body  d-flex justify-content-center" id="preview-content">
        <p class="text-black">Live previews are only  available to subscribers of our Ultimate plan, please subscribe to continue with this request</p>
      </div>
    </div>
  </div>
</div>';
        }
    }
    return $output;
}

function parseRow( $name, $label, $value, $type, array $info )
{
    $r = $value;
    if ( !empty( $value ) ) {
        if ( !empty( $type ) ) {
            if ( $type == "select" ) {
                $opt    = explode( "_", $value );
                $output = '<select class="mdb-select md-form" name="' . $name . '">
			                            <option value="disabled selected">' . $label . '</option>';
                foreach ( $opt as $k => $v ) {
                    $output .= '
			                            <option value="' . $v . '">' . ucfirst( $v ) . '</option>';
                }
                $output .= '</select>';
            } else if ( $type == "date" ) {
                $output = '
			                           <div class="md-form" >
                                          <input data-value="' . $value . '" type="text" name="' . $name . '" id="date-picker" class="form-control datepicker" required>
                                         <label for="date-picker">' . $label . '</label>
                                        </div>
			                           ';
            }
        } else {
            eval( "\$v = $r;" );
            $output = '<div class="md-form form-group"><label>' . $label . '</label> <input required class="form-control" name="' . $name . '"  value="' . $v . '" type="text"></div>';
        }
    } else if ( array_key_exists( $name, $info ) ) {
        $output = '<div class="md-form form-group"><label>' . $label . '</label> <input required class="form-control" name="' . $name . '" type="text" value="' . $info[ $name ] . '"></div>';
    } else {
        $output = '<div class="md-form form-group"><label>' . $label . '</label> <input required class="form-control" name="' . $name . '" type="text"></div>';
    }
    return $output;
}
