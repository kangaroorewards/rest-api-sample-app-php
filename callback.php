<?php
require_once 'bootstrap.php';

use KangarooRewards\OAuth2\Client\Provider\Kangaroo as KangarooProvider;
use App\Config;

$kangaroo = new KangarooProvider([
    'clientId' => Config::CLIENT_ID,
    'clientSecret' => Config::CLIENT_SECRET,
    'redirectUri' => Config::REDIRECT_URI_OAUTH,
    'urlAuthorize' => Config::KANGAROO_API_BASE_URL . '/oauth/authorize',
    'urlAccessToken' => Config::KANGAROO_API_BASE_URL . '/oauth/token',
    'urlResourceOwnerDetails' => Config::KANGAROO_API_BASE_URL . '/me',
]);

if (isset($_GET['error'])) {
    echo $_GET['error'];
    $message = (isset($_GET['message'])) ? $_GET['message'] : '';
    if ($message) {
        echo ': ' . $message;
    }
    exit;

} elseif (!isset($_GET['code'])) {

    // If we don't have an authorization code then get one
    $authUrl = $kangaroo->getAuthorizationUrl();

    $_SESSION['oauth2state'] = $kangaroo->getState();

    header('Location: ' . $authUrl);exit;

    // Check given state against previously stored one to mitigate CSRF attack
}
 elseif (empty($_GET['state']) ||
    ($_GET['state'] !== $_SESSION['oauth2state'])
    // ($_GET['state'] !== OAUTH_STATE_TOKEN)
    ) {

    unset($_SESSION['oauth2state']);
    echo 'Invalid state.';
    exit;
}

try {
    // Try to get an access token (using the authorization code grant)
    $token = $kangaroo->getAccessToken('authorization_code', [
        'code' => $_GET['code'],
        'scope' => 'admin',
    ]);
    
    App\Utils::storeToken($token);

    // print_r($token); die;
} catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $ex) {
    echo $ex->getMessage();die;
} catch (\Exception $e) {
    // Failed to get user details
    echo $e->getMessage();die;
}

header('Location: ' . Config::REDIRECT_URI_MAIN);
die('Redirecting...');
