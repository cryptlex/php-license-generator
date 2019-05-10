<?php
require('CryptlexApi.php');

// pass this secret as query param in the url e.g. https://yourserver.com/generate-license.php?cryptlex_secret=SOME_RANDOM_STRING
$CRYPTLEX_SECRET = "SOME_RANDOM_STRING";

// access token must have following permissions (scope): license:write, user:read, user:write
$PERSONAL_ACCESS_TOKEN = "PASTE_PERSONAL_ACCESS_TOKEN";

// utility functions
function IsNullOrEmptyString($str){
    return (!isset($str) || trim($str) === '');
}

function ForbiddenRequest() {
    http_response_code(403);
    $message['error'] = 'You are not authorized to perform this action!';
    echo json_encode($message);
}

function BadRequest($error) {
    http_response_code(400);
    $message['error'] = $error;
    echo json_encode($message);
}

function VerifySecret($secret) {
    if($secret == $GLOBALS['CRYPTLEX_SECRET']) {
        return true;
    }
    return false;
}

function parseFastSpringPostData() {
    $postBody['company'] = $_POST['email'];
    if(IsNullOrEmptyString($postBody['email'])) {
        $postBody['company'] = "";
    }

    $postBody['quantity'] = $_POST['quantity'];
    if(IsNullOrEmptyString($postBody['quantity'])) {
        $postBody['quantity'] = NULL;
    }

    $postBody['email'] = $_POST['email'];
    if(IsNullOrEmptyString($postBody['email'])) {
        BadRequest('email is missing!');
        return NULL;
    }

    if(IsNullOrEmptyString($_POST['name'])) {
        BadRequest('name is missing!');
        return NULL;
    }
    $fullName = explode(" ", $_POST['name']);
    $postBody['first_name'] = $fullName[0];
    if(count($fullName)  == 1) {
        $postBody['last_name'] = "";
    } else {
        $postBody['last_name'] = $fullName[1];
    }
    
    $postBody['order_id'] = $_POST['reference'];
    if(IsNullOrEmptyString($postBody['order_id'])) {
        BadRequest('reference is missing!');
        return NULL;
    }
    return $postBody;
}

function parsePayPalPostData() {
    // TODO
}

function parseStripePostData() {
    // TODO
}


try {

    if(VerifySecret($_GET['cryptlex_secret']) == false) {
        return ForbiddenRequest();
    }

    CryptlexApi::SetAccessToken($GLOBALS['PERSONAL_ACCESS_TOKEN']);

    $product_id = "PASTE_PRODUCT_ID";

    $postBody = parseFastSpringPostData();

    if($postBody == NULL) {
        return;
    }

    $email = $postBody['email'];
    $first_name = $postBody['first_name'];
    $last_name = $postBody['last_name'];
    $quantity = $postBody['quantity'];

    // required for renewing the license subscription
    $order_id = $postBody['order_id'];

    // creating user is optional
    $user_exists = false;
    $user = CryptlexApi::GetUser($email);
    if($user == NULL) {
        $user_body["email"] = $email;
        $user_body["firstName"] = $first_name;
        $user_body["lastName"] = $last_name;
        $user_body["company"] = $last_name;
        // generate a random 8 character password
        $user_body["password"] = substr(md5(uniqid()), 0, 8);
        $user_body["role"] = "user";
        $user = CryptlexApi::CreateUser($user_body);
    } else {
        $user_exists = true;
    }

    // creating license
    if($quantity != NULL) {
        $license_body["allowedActivations"] = (int)$quantity;
    }
    $license_body["productId"] = $product_id;
    $license_body["userId"] = $user->id;
    $metadata["key"] = "order_id";
    $metadata["value"] = $order_id;
    $metadata["visible"] = false;
    $license_body["metadata"] = array($metadata);

    $license = CryptlexApi::CreateLicense($license_body);

    http_response_code(200);
    echo $license->key;

} catch(Exception $e) {
    http_response_code(500);
    echo 'message: ' .$e->getMessage();
}



