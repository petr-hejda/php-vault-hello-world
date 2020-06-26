<?php
include('/app/common.php');

$username = $argv[1] ?? null;
$password = $argv[2] ?? null;
$secret = $argv[3] ?? null;

// Login to Vault as the user and get their current token
$response = vaultRequest(
	'POST',
	'/v1/auth/userpass/login/' . $username,
	null,
	['password' => $password]
);
$userToken = $response['auth']['client_token'];

// save a secret to the path of "/user/<username>"
vaultRequest(
	'POST',
	'/v1/user/' . $username,
	$userToken,
	["secret" => $secret]
);

echo 'Secret created' . PHP_EOL;
