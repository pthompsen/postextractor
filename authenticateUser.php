<?php
/**
 * This file is a modification of the original from the D2L Valence PHP SDK
 * 
 * Copyright (c) 2012 Desire2Learn Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the license at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

require_once 'config.php';
require_once $config['libpath'] . '/D2LAppContextFactory.php';

// Configuration values from config.php
$host = $config ['host'];
$port = $config ['post'];
$appId = $config ['appId'];
$appKey = $config ['appKey'];
$scheme = $config ['scheme'];

$userId = trim($_GET['userIDField']);
$userKey = trim($_GET['userKeyField']);

if($_GET['authBtn'] == 'Deauthenticate') {
    session_start();
    unset($_SESSION['userId']);
    unset($_SESSION['userKey']);
    session_write_close();
    header("location: index.php");
} else if($_GET['authBtn'] == 'Load Defaults') {
    session_start();
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header("location: index.php");
} else if ($_GET['authBtn'] == 'Save') {
    session_start();
    
    $_SESSION['userId'] = $userId;
    $_SESSION['userKey'] = $userKey;
    session_write_close();
    header("location: index.php");
} else {
    $redirectPage = $_SERVER["HTTP_REFERER"];


    $authContextFactory = new D2LAppContextFactory();
    $authContext = $authContextFactory->createSecurityContext($appId, $appKey);

    $hostSpec = new D2LHostSpec($host, $port, $scheme);

    $url = $authContext->createUrlForAuthenticationFromHostSpec($hostSpec, $redirectPage);

    header("Location: $url");
}
?>
