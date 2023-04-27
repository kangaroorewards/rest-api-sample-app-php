<?php
require_once 'bootstrap.php';

use KangarooRewards\Api\KangarooApi;
use KangarooRewards\OAuth2\Client\Provider\Kangaroo as KangarooProvider;
use App\Config;
use App\Utils;

// To make API requests
try {

    $kangaroo = new KangarooProvider([
        'clientId' => Config::CLIENT_ID,
        'clientSecret' => Config::CLIENT_SECRET,
        'redirectUri' => Config::REDIRECT_URI_OAUTH,
        'urlAuthorize' => Config::KANGAROO_API_BASE_URL . '/oauth/authorize',
        'urlAccessToken' => Config::KANGAROO_API_BASE_URL . '/oauth/token',
        'urlResourceOwnerDetails' => Config::KANGAROO_API_BASE_URL . '/me',
    ]);

    $accessToken = Utils::retrieveToken($kangaroo);

    $api = new KangarooApi([
        'access_token' => $accessToken->getToken(),
        'base_api_url' => Config::KANGAROO_API_BASE_URL,
        'headers' => ['X-Application-Key' => Config::X_APPLICATION_KEY],
    ]);
    
    // print_r($accessToken->getToken()); die;

    try {
        $customer = $api->getCustomer(Config::DEMO_CUSTOMER_ID, ['include' => 'balance,tier_level']);
        $balance = $customer['included']['balance'];
        $customer = $customer['data'];
    } catch (\Exception $e) {
        dd($e);
    }
    
    // dd($customer);

    try {
        $resourceOwner = $api->me(['include' => 'settings,business,offers,giftcards,products,catalog_items,social_media']);
    } catch (\Exception $e) {
        dd($e);
    }

    // dd($resourceOwner);

    $business = $resourceOwner['included']['business'];
    $offers = isset($resourceOwner['included']['offers']) ? $resourceOwner['included']['offers'] : null;
    $giftCards = isset($resourceOwner['included']['giftcards']) ? $resourceOwner['included']['giftcards'] : null;
    $rewards = isset($resourceOwner['included']['catalog_items']) ? $resourceOwner['included']['catalog_items'] : null;
    $products = isset($resourceOwner['included']['products']) ? $resourceOwner['included']['products'] : null;
    $socialMediaLinks = isset($resourceOwner['included']['social_media']) ? $resourceOwner['included']['social_media'] : null;
    $currencySymbol = $business['settings']['currency']['symbol'];
} catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $ex) {
    // echo '<pre>'; var_export($ex); die;
    echo $ex->getMessage();
    die;
} catch (\Exception $e) {
    echo $e->getMessage();
    // print_r($e);
    die;
}

$ver = 1;
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kangaroo Rewards - demo app</title>
    <link rel="stylesheet" href="assets/perfect-scrollbar/css/perfect-scrollbar.min.css?v=<?php echo $ver; ?>">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <style type="text/css">
        .container {
            background-image: -moz-linear-gradient(top, rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url("<?php echo $business['cover_photo']; ?>");
            background-image: -webkit-linear-gradient(top, rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url("<?php echo $business['cover_photo']; ?>");
            background-image: -ms-linear-gradient(top, rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url("<?php echo $business['cover_photo']; ?>");
            background-image: linear-gradient(top, rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url("<?php echo $business['cover_photo']; ?>");
            background-attachment: fixed;
            background-position: center center;
            background-repeat: no-repeat;
            background-size: cover;
            padding-top: 0;
        }
    </style>
</head>
<body>

    <div class="container" style="display: none;">
        <header id="header" class="alt">
            <h1><?php echo $business['name'] ?></h1>
        </header>

        <section id="banner">
            <div class="inner">
                <h2><?php echo $business['name'] ?></h2>

                <?php if ($balance['points'] > 0): ?>
                    <div class="customer-balance">
                        <?php echo $balance['points'] ?> pts |
                        <?php echo number_format(($balance['points'] / 100), 2, '.', ',') . ' ' . $currencySymbol; ?>
                    </div>
                <?php endif?>

                <?php if ($offers): ?>
                    <div class="container-wrapper">
                        <h3 class="box-title">Offers you may like</h3>
                        <ul class="box-wrapper offers-list box-wrapper__scroll icons">
                            <?php foreach ($offers as $key => $offer):
                                $badge = Utils::getOfferBadge($offer, $currencySymbol);
                            ?>
	                                <li>
	                                    <div class="image">
	                                        <div class="offer_triangle__container">
	                                            <div class="offer_triangle__segment">
	                                                <div class="offer_triangle__content">
	                                                    <div class="offer_triangle__content_text">
	                                                        <?php
                                                                echo '<div class="offer_triangle__content_text1">' . $badge[0] . '</div>';
                                                                echo '<div class="offer_triangle__content_text2">' . $badge[1] . '</div>';
                                                            ?>
	                                                    </div>
	                                                </div>
	                                            </div>
	                                        </div>
	                                        <div class="offer-title"><?php echo stripslashes($offer['title']); ?></div>
	                                        <img src="<?php echo $offer['images'][0]['medium']; ?>">
	                                    </div>
	                                </li>
	                            <?php endforeach?>
                        </ul>
                    </div>
                <?php endif?>


                <?php if ($giftCards): ?>
                    <div class="container-wrapper">
                        <h3 class="box-title">Gift Cards for your friends</h3>
                        <ul class="box-wrapper offers-list box-wrapper__scroll icons">
                            <?php foreach ($giftCards as $key => $giftCard):
                                $badge = Utils::getOfferBadge($giftCard, $currencySymbol);
                            ?>
	                                <li>
	                                    <div class="image">
	                                        <div class="offer_triangle__container">
	                                            <div class="offer_triangle__segment">
	                                                <div class="offer_triangle__content">
	                                                    <div class="offer_triangle__content_text">
	                                                        <?php
                                                                echo '<div class="offer_triangle__content_text1">' . $badge[0] . '</div>';
                                                                echo '<div class="offer_triangle__content_text2">' . $badge[1] . '</div>';
                                                            ?>
	                                                    </div>
	                                                </div>
	                                            </div>
	                                        </div>
	                                        <div class="offer-title"><?php echo stripslashes($giftCard['title']); ?></div>
	                                        <img src="<?php echo $giftCard['images'][0]['medium']; ?>">
	                                    </div>
	                                </li>
	                            <?php endforeach?>
                            </ul>
                    </div>
                <?php endif?>

                <?php if ($rewards): ?>
                <div class="container-wrapper">
                    <h3 class="box-title">Redeem your points for...</h3>
                    <ul class="box-wrapper offers-list box-wrapper__scroll icons">
                        <?php foreach ($rewards as $key => $reward):
                            $badge = Utils::getOfferBadge($reward, $currencySymbol);
                        ?>
	                            <li>
	                                <div class="image">
	                                    <div class="offer_triangle__container">
	                                        <div class="offer_triangle__segment">
	                                            <div class="offer_triangle__content">
	                                                <div class="offer_triangle__content_text">
	                                                    <?php
                                                            echo '<div class="offer_triangle__content_text1">' . $badge[0] . '</div>';
                                                            echo '<div class="offer_triangle__content_text2">' . $badge[1] . '</div>';
                                                        ?>
	                                                </div>
	                                            </div>
	                                        </div>
	                                    </div>
	                                    <div class="offer-title"><?php echo stripslashes($reward['title']); ?></div>
	                                    <img src="<?php echo $reward['images'][0]['medium']; ?>">
	                                </div>
	                            </li>
	                        <?php endforeach?>
                    </ul>
                </div>
                <?php endif?>

                <?php if ($products): ?>
                <div class="container-wrapper">
                    <h3 class="box-title">Our featured products</h3>
                    <ul class="box-wrapper offers-list box-wrapper__scroll icons">
                        <?php foreach ($products as $key => $product):
                                $badge = Utils::getProductBadge($product, $currencySymbol);
                        ?>
	                            <li>
	                                <div class="image">
	                                    <?php if ($badge): ?>
	                                        <div class="offer_triangle__container">
	                                            <div class="offer_triangle__segment">
	                                                <div class="offer_triangle__content">
	                                                    <div class="offer_triangle__content_text">
	                                                        <?php
                                                                echo '<div class="offer_triangle__content_text1">' . $badge[0] . '</div>';
                                                                echo '<div class="offer_triangle__content_text2">' . $badge[1] . '</div>';
                                                            ?>
	                                                    </div>
	                                                </div>
	                                            </div>
	                                        </div>
	                                    <?php endif?>
                                    <div class="offer-title"><?php echo stripslashes($product['title']); ?></div>
                                    <img src="<?php echo $product['images'][0]['medium']; ?>">
                                </div>
                            </li>
                        <?php endforeach?>
                    </ul>
                </div>
                <?php endif?>

                <?php if ($socialMediaLinks): ?>
                <div class="container-wrapper" id="social-media-icons">
                    <h3 class="box-title">We're here too!</h3>
                    <ul class="box-wrapper box-wrapper__scroll icons align-center">
                        <?php foreach ($socialMediaLinks as $key => $value): ?>
                            <?php if ($value != null): ?>
                                <li class="social-icon">
                                    <a href="<?php echo $value['url']; ?>" class="social-link" target="_blank"> <img src="<?php echo $value['icon'] ?>" class="icon-60x60"> </a>
                                </li>
                            <?php endif?>
                        <?php endforeach?>
                    </ul>
                </div>
                <?php endif?>

                <?php if ($business['about']): ?>
                    <div class="container-wrapper">
                        <h3 class="box-title">Who we are</h3>
                        <ul class="icons align-left">
                            <li style="display: block; width: 100%;">
                                <h4"><?php echo stripslashes(nl2br($business['about'])); ?></h4>
                                <div id="map-canvas" class="map-container"></div>
                            </li>
                        </ul>
                    </div>
                <?php endif?>

            </div>
        </section>

        <footer id="footer">
            <ul class="icons">
                <li><a href="#" class="icon fa-twitter"><span class="label">Twitter</span></a></li>
                <li><a href="#" class="icon fa-facebook"><span class="label">Facebook</span></a></li>
                <li><a href="#" class="icon fa-instagram"><span class="label">Instagram</span></a></li>
                <li><a href="#" class="icon fa-dribbble"><span class="label">Dribbble</span></a></li>
                <li><a href="#" class="icon fa-envelope-o"><span class="label">Email</span></a></li>
            </ul>
            <ul class="copyright">
                <li>&copy; <a href="#"><?php echo $business['name']; ?></a></li>
            </ul>
        </footer>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <script src="assets/js/jquery.scrollex.min.js"></script>
    <script src="assets/js/jquery.scrolly.min.js"></script>
    <script src="assets/js/skel.min.js"></script>
    <script src="assets/js/util.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/perfect-scrollbar/js/min/perfect-scrollbar.jquery.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo Config::GOOGLE_MAPS_KEY;  ?>&callback=initMap" async defer></script>
</body>
</html>