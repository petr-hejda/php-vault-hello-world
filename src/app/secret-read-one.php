<?php
include('/app/common.php');

$username = $argv[1] ?? null;
$password = $argv[2] ?? null;

// Login to Vault as the user and get their current token
$response = vaultRequest(
	'POST',
	'/v1/auth/userpass/login/' . $username,
	null,
	['password' => $password]
);
$userToken = $response['auth']['client_token'];

// get the secret from the path of "/user/<username>"
$response = vaultRequest(
	'GET',
	'/v1/user/' . $username,
	$userToken,
);
$secret = $response['data']['secret'];

echo 'Secret: ' .$secret . PHP_EOL;
