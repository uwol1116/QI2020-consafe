<?php	
// Routes
//HomeController
$app->get('/', 'App\Controller\HomeController:dispatch')
    ->setName('homepage');

$app->get('/pagecurrent', 'App\Controller\HomeController:pagecurrent')
    ->setName('pagecurrent');

$app->get('/current', 'App\Controller\HomeController:current')
    ->setName('current');

$app->get('/postory', 'App\Controller\HomeController:postory')
    ->setName('postory');

$app->get('/viewpolarhistory', 'App\Controller\HomeController:viewpolarhistory')
    ->setName('viewpolarhistory');

$app->get('/polarreal', 'App\Controller\HomeController:polarreal')
    ->setName('polarreal');

$app->get('/viewpolar', 'App\Controller\HomeController:viewpolar')
    ->setName('viewpolar');

$app->get('/calendar', 'App\Controller\HomeController:calendar')
    ->setName('calendar');

$app->get('/showpolar', 'App\Controller\HomeController:showpolar')
    ->setName('showpolar');

$app->get('/No2history', 'App\Controller\HomeController:No2history')
    ->setName('No2history');

$app->get('/No2map', 'App\Controller\HomeController:No2map')
    ->setName('No2map');

$app->get('/O3history', 'App\Controller\HomeController:O3history')
    ->setName('O3history');

$app->get('/O3map', 'App\Controller\HomeController:O3map')
    ->setName('O3map');

$app->get('/COhistory', 'App\Controller\HomeController:COhistory')
    ->setName('COhistory');

$app->get('/Comap', 'App\Controller\HomeController:Comap')
    ->setName('Comap');

$app->get('/So2history', 'App\Controller\HomeController:So2history')
    ->setName('So2history');

$app->get('/So2map', 'App\Controller\HomeController:So2map')
    ->setName('So2map');

$app->get('/pmhistory', 'App\Controller\HomeController:pmhistory')
    ->setName('pmhistory');

$app->get('/pmmap', 'App\Controller\HomeController:pmmap')
    ->setName('pmmap');

//UserController
$app->get('/signup','App\Controller\UserController:signup')
    ->setName('signup');

$app->post('/handlesignup','App\Controller\UserController:handlesignup')
    ->setName('handlesignup');

$app->get('/signin','App\Controller\UserController:signin')
    ->setName('signin');

$app->post('/handlesignin','App\Controller\UserController:handlesignin')
    ->setName('handlesignin');

$app->get('/signout','App\Controller\UserController:signout')
    ->setName('signout');

$app->get('/changepassword','App\Controller\UserController:changepassword')
    ->setName('changepassword');

$app->post('/handlechangepassword','App\Controller\UserController:handlechangepassword')
    ->setName('handlechangepassword');

$app->get('/profile','App\Controller\UserController:profile')
    ->setName('profile');

$app->get('/removeaccount','App\Controller\UserController:removeaccount')
    ->setName('removeaccount');

$app->post('/handleremoveaccount','App\Controller\UserController:handleremoveaccount')
    ->setName('handleremoveaccount');

$app->get('/editprofile','App\Controller\UserController:editprofile')
    ->setName('editprofile');

$app->post('/storeprofile','App\Controller\UserController:storeprofile')
    ->setName('storeprofile');
//MailController
$app->get('/findaccount','App\Controller\MailController:findaccount')
    ->setName('findaccount');

$app->post('/handlefindaccount','App\Controller\MailController:handlefindaccount')
    ->setName('handlefindaccount');

$app->get('/passwordchange/{nonce}','App\Controller\MailController:passwordchange')
    ->setName('passwordchange');

$app->post('/updateaccount','App\Controller\MailController:updateaccount')
    ->setName('updateaccount');

$app->get('/sendmail/{id}/{link}/{flag}','App\Controller\MailController:sendmail')
    ->setName('sendmail');

$app->get('/verifymail/{nonce}','App\Controller\MailController:verifymail')
    ->setName('verifymail');

//AppController
$app->post('/appsignin','App\Controller\AppController:appsignin')
    ->setName('appsignin');

$app->post('/appsignup','App\Controller\AppController:appsignup')
    ->setName('appsignup');

$app->post('/appfindaccount','App\Controller\AppController:appfindaccount')
    ->setName('appfindaccount');

$app->get('/appsendmail/{id}/{link}/{flag}','App\Controller\AppController:appsendmail')
    ->setName('appsendmail');

$app->post('/appchangepassword','App\Controller\AppController:appchangepassword')
    ->setName('appchangepassword');

$app->post('/appremoveaccount','App\Controller\AppController:appremoveaccount')
    ->setName('appremoveaccount');

$app->post('/appregisensor','App\Controller\AppController:appregisensor')
    ->setName('appregisensor');

$app->post('/storepolar','App\Controller\AppController:storepolar')
    ->setName('storepolar');

$app->post('/appconnect','App\Controller\AppController:appconnect')
    ->setName('appconnect');

$app->post('/appdisconnect','App\Controller\AppController:appdisconnect')
    ->setName('appdisconnect');

$app->post('/polarhistory','App\Controller\AppController:polarhistory')
    ->setName('polarhistory');

$app->post('/boardhistory','App\Controller\AppController:boardhistory')
    ->setName('boardhistory');

$app->post('/storeboard','App\Controller\AppController:storeboard')
    ->setName('storeboard');

$app->post('/realview','App\Controller\AppController:realview')
    ->setName('realview');

$app->post('/sensorssn','App\Controller\AppController:sensorssn')
    ->setName('sensorssn');

$app->get('/test','App\Controller\AppController:test')
    ->setName('test');
