<?php
//require ('.env.php')
//require_once (ROOT.'/vendor/autoload.php');
use unsized\crawl\Crawl;

$username='bt_broadband_username@email.com';
$password='bt_broadband_password';

//detect if we are logged in
$bt_home='https://www.btwifi.com:8443/home';
$live=new Crawl();
$live->curlInit();

$web_page=$live->getWebPage($bt_home);

if ( str_contains($web_page, "now logged on to BT") ){
  echo '<h1>now logged on to BT</h1>';
  }

elseif ( str_contains($web_page, "You may have lost your connection to the BTWiFi signal")){
  echo "<p>Not connected to BT Broadband - Check wifi connection</p>";
}
else{ //we are not logged in to BT, so try to login
  echo "<p>Not logged into BT</p>";

//Attempt to connect to BT using credentials
echo "<p>- Attempting to connection login to BT Broadband</p>";
  $bt=new Crawl();
  $bt->curlInit();

  $fields=[
            'username'=>$username,
            'password'=>$password,
            'xhtmlLogon'=>'https://www.btwifi.com:8443/tbbLogon'
          ];

  $target_url='https://www.btwifi.com:8443/tbbLogon';
  $bt->postForm($fields, $target_url);

  //check if we are logged in
  if ( str_contains($live->getWebPage($bt_home), "now logged on to BT") ){
    echo '<h4>Now logged on to BT</h4>';
    }
}
