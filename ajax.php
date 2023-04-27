<?php
require_once 'bootstrap.php';

use KangarooRewards\Api\KangarooApi;
use KangarooRewards\OAuth2\Client\Provider\Kangaroo as KangarooProvider;

try {
    $kangaroo = new KangarooProvider([
        'clientId' => App\Config::CLIENT_ID,
        'clientSecret' => App\Config::CLIENT_SECRET,
        'redirectUri' => App\Config::REDIRECT_URI_OAUTH,
    ]);

    $accessToken = App\Utils::retrieveToken($kangaroo);

    $api = new KangarooApi([
        'access_token' => $accessToken->getToken(),
        'base_api_url' => App\Config::KANGAROO_API_BASE_URL,
        'headers' => ['X-Application-Key' => App\Config::X_APPLICATION_KEY],
    ]);

} catch (Exception $e) {
    // print_r($e);

    header('HTTP/1.1 500 Internal Server Error');
    header('Content-Type: application/json');
    json_encode(['message' => $e->getMessage()]);
    exit;
}

header('Content-Type: application/json');

if (isset($_GET['q']) && $_GET['q'] === 'branches') {
    $resourceOwner = $api->me(['include' => 'branches']);
    $branches = $resourceOwner['included']['branches'];

    header('HTTP/1.1 200 OK');
    echo json_encode($branches);
    exit;
}

header('HTTP/1.1 204 No Content');
