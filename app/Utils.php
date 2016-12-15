<?php

namespace App;

class Utils
{

    /**
     * @param $token
     */
    public static function storeToken($token)
    {
        if (Config::STORE_TOKEN_IN == 'database') {
            $db = new DB;

            $accessToken = $token->getToken();
            $refreshToken = $token->getRefreshToken();
            $expires = $token->getExpires();

            $db->insertToken($accessToken, $refreshToken, $expires);
        } else {
            $_SESSION['kangaroo_access_token'] = $token;
        }
    }

    /**
     * @param $kangaroo
     * @return mixed
     */
    public static function retrieveToken($kangaroo)
    {
        if (Config::STORE_TOKEN_IN == 'database') {
            $db = new DB;
            $dbToken = $db->getToken();

            if (!$dbToken) {
                header('Location: ' . Config::REDIRECT_URI_OAUTH);
                die('Redirect');
            }

            $token = new \League\OAuth2\Client\Token\AccessToken([
                'access_token' => $dbToken['access_token'],
                'refresh_token' => $dbToken['refresh_token'],
                'expires' => $dbToken['expires'],
            ]);
        } else {

            // check whether the access token exists in session
            if (!isset($_SESSION['kangaroo_access_token'])) {
                header('Location: ' . Config::REDIRECT_URI_OAUTH);
                die('Redirect');
            }
            //getting token from session
            $token = $_SESSION['kangaroo_access_token'];
        }

        //Check if token expired
        if ($token->hasExpired()) {
            $newAccessToken = $kangaroo->getAccessToken('refresh_token', [
                'refresh_token' => $token->getRefreshToken(),
            ]);

            if (Config::STORE_TOKEN_IN == 'database') {
                self::storeToken($newAccessToken);
            } else {
                //store the new token in session
                $_SESSION['kangaroo_access_token'] = $newAccessToken;
            }

            $token = $newAccessToken;
        }

        return $token;
    }

    /**
     * @param $offerItem
     * @param $currencySymbol
     * @return mixed
     */
    public static function getOfferBadge($offerItem, $currencySymbol = '')
    {
        $r = [];
        $offerType = $offerItem['type'];

        if ($offerType == 1) {
            $r[] = $offerItem['multip_factor'] . 'x';
            $r[] = 'The Points';
        } elseif ($offerType == 2 || $offerType == 5 || $offerType == 8 || $offerType == 11) {
            $r[] = 'Free';
            $r[] = '';
        } elseif ($offerType == 3) {
            $r[] = $offerItem['units_awarded'] != '' ? $offerItem['units_awarded'] : 0;
            $r[] = 'Points';
        } elseif ($offerType == 4) {
            $r[] = $offerItem['multip_factor'] . 'x';
            $r[] = 'The Punch';
        } elseif ($offerType == 6 || $offerType == 9 || $offerType == 12) {
            $r[] = $offerItem['discount_value'] . '% ';
            $r[] = 'OFF';
        } elseif ($offerType == 7 || $offerType == 10 || $offerType == 13) {
            $r[] = $offerItem['discount_value'] . $currencySymbol . ' ';
            $r[] = 'OFF';
        } elseif ($offerType == 14) {
            if ($offerItem['real_value'] == $offerItem['discount_value']) {
                $r[] = $offerItem['real_value'] . $currencySymbol;
                $r[] = '';
            } else {
                $r[] = $offerItem['real_value'] . $currencySymbol;
                $r[] = $offerItem['discount_value'] . $currencySymbol;
            }
        } elseif ($offerType == 15 || $offerType == 16) {
            $r[] = $offerItem['units'];
            $r[] = ($offerItem['units'] == 1) ? 'Point' : 'Points';
        } elseif ($offerType == 17) {
            $r[] = $offerItem['units'];
            $r[] = ($offerItem['units'] == 1) ? 'Punch' : 'Punches';
        } else {
            $r = ['', ''];
        }

        return $r;
    }

    /**
     * @param $product
     * @param $currencySymbol
     * @return mixed
     */
    public static function getProductBadge($product, $currencySymbol)
    {
        $r = [];
        if ($product['actual_price'] == $product['real_price'] && $product['real_price'] != null) {
            $r[] = $product['real_price'] . $currencySymbol;
            $r[] = '';
        } elseif ($product['actual_price'] != $product['real_price']) {
            if ($product['actual_price'] != null && $product['real_price'] != null) {
                $r[] = $product['real_price'] . $currencySymbol;
                $r[] = $product['actual_price'] . $currencySymbol;
            } else if ($product['actual_price'] != null) {
                $r[] = $product['actual_price'] . $currencySymbol;
                $r[] = '';
            } else if ($product['real_price'] != null) {
                $r[] = $product['real_price'] . $currencySymbol;
                $r[] = '';
            }
        }
        return $r;
    }
}
