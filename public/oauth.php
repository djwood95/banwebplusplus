//Source: https://www.youtube.com/watch?v=-65R420rIUA

<php?
	public function OAuth(){
		
		require_once '/gplus-lib/vendor/autoload.php'; 
		$Client_Id = '079673860784-13aa7jbs3nrmo7t3j5pqk75lu795elec.apps.googleusercontent.com';
		$Client_Secret='fMbptgmA6tCpPWEGR92JriGx';
		$Redirect_Uri='http://banwebplusplus.me/redirect';

		session_start();

		$Client = new Google_Client();
		$Client->setClientId($Client_Id);
		$Client->setCleintSecret($Client_Secret);
		$Client->setRedirectUri($Redirect_Uri);
		$Client->setScopes('email');
	}

	$plus = new Google_Service_Plis($Client);

	if(isset($_REQUEST['logout'])){
		session_unset();
	}

	if(isset($_GET['code'])){
		$client->authenticate($_GET['code']);
		$redirect='http//'.$_server['HTTP_HOST'].$_SERVER['PHP_SELF'];
		header('Location:'.filter_var($redirect, FILTER_SANITITZE_URL));
	}

	if(isset($_SESSION['access_token']) && $_SESSION['access_token']){
		$client->setAccessToken($_SESSION['access_token']);
		$me=$plus->people->get('me');
		$id = $me['id'];
		$name = $me['displayName'];
		$email = $me['emails'][0]['value'];
		$profile_image_url = $me['image']['coverPhoto']['url'];
		$profile_url=$me['url'];
		
	} else {
		$authURL = $Client->createAuthUrl();
	}
	return 
?>
