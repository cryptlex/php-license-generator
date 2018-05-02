<?php
require('CryptlexApi.php');
try {
    CryptlexApi::SetAccessToken("PERSONAL_ACCESS_TOKEN");

    $product_id = "PASTE_YOUR_PRODUCT_ID";

    $order_id = $_POST['order_id'];

    // license is searched using the order_id stored as metadata during license creation
    
    $license = CryptlexApi::RenewLicense($product_id, 'order_id', $order_id);

    http_response_code(200);
    echo "License new expiry date: ".$license->expiresAt;
} catch(Exception $e) {
    http_response_code(500);
    echo 'message: ' .$e->getMessage();
}