<?php
require('CryptlexApi.php');
try {
    CryptlexApi::SetAccessToken(getenv('PERSONAL_ACCESS_TOKEN'));

    $product_id = "PASTE_YOUR_PRODUCT_ID";

    $email = $_POST['email'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];

    // required for renewing the license subscription
    $order_id = $_POST['order_id'];

    // creating user is optional

    $user_body["email"] = $email;
    $user_body["firstName"] = $first_name;
    $user_body["lastName"] = $last_name;

    // make sure you change logic for setting the password
    $user_body["password"] = "top_secret"; 
    
    $user_body["role"] = "user";

    $user = CryptlexApi::CreateUser($user_body);

    // creating license

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



