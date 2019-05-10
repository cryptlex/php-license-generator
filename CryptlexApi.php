<?php
class CryptlexApi
{
	private static $access_token;

	// Leave it as such unless you're using the self-hosted version of Cryptlex.
	private static $base_path = "https://api.cryptlex.com/v3";

    public static function SetAccessToken($access_token)
	{
		self::$access_token = $access_token;
	}

	public static function CreateUser($body)
	{
		$api_url = self::$base_path . "/users";

        // creating new user...
        $user = self::PostRequest($api_url, $body);
        return $user;
	}

	public static function GetUser($email)
	{
		$api_url = self::$base_path . "/users";
		$query['email'] = $email;
        // check whether user exists
        $users = self::GetRequest($api_url."?".http_build_query($query));
        if (count($users)) {
            // user already exists!
            return $users[0];
		} 
		// user not found
		return NULL;
	}

	public static function CreateLicense($body)
	{
		$api_url = self::$base_path . "/licenses";
		
        // creating license...
        $license = self::PostRequest($api_url, $body);
		return $license;
	}

	public static function RenewLicense($productId, $metadataKey, $metadataValue)
	{
		$api_url = self::$base_path . "/licenses";
		
        // fetching existing license...
        $licenses = self::GetRequest($api_url."?productId=".$productId."&metadataKey=".$metadataKey."&metadataValue=".$metadataValue);
		if (count($licenses) == 0) {
            throw new Exception("License does not exist!");
        }
        $license = $licenses[0];
		// renewing existing license...
		$renewedLicense = self::PostRequest($api_url."/".$license->id."/renew", null);
        return $renewedLicense;
	}

	private static function GetRequest($url)
	{
		if (!self::$access_token)
		{
			throw new Exception("You must set the access token.");
		}
		$headers = array("Authorization: Bearer ".self::$access_token, "Content-Type: application/json");
		
		$request = curl_init($url);
		curl_setopt($request, CURLOPT_HEADER, 0);
		curl_setopt($request, CURLOPT_ENCODING, "");
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($request, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($request, CURLOPT_HTTPHEADER, $headers);
		
		$response = curl_exec($request);
		$info = curl_getinfo($request);
		if($info["http_code"] != 200)
		{
			throw new Exception($response);
		}
		curl_close($request);
		//var_dump($response);
		return json_decode($response);
	}

	private static function PostRequest($url, $body)
	{
		if (!self::$access_token)
		{
			throw new Exception("You must set the access token.");
		}
		$headers = array("Authorization: Bearer ".self::$access_token, "Content-Type: application/json");

		$request = curl_init($url);
		curl_setopt($request, CURLOPT_HEADER, 0);
		curl_setopt($request, CURLOPT_ENCODING, "");
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($request, CURLOPT_POSTFIELDS, json_encode($body));
		curl_setopt($request, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($request, CURLOPT_HTTPHEADER, $headers);
		
		$response = curl_exec($request);
		$info = curl_getinfo($request);
		if($info["http_code"] != 200 && $info["http_code"] != 201)
		{
			throw new Exception($response);
		}
		curl_close($request);
		//var_dump($response);
		return json_decode($response);;
	}

}
