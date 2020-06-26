<?php
include('/app/common.php');

$username = $argv[1] ?? null;
$password = $argv[2] ?? null;

// Login to Vault as the admin and get their current token
$response = vaultRequest(
	'POST',
	'/v1/auth/userpass/login/' . $username,
	null,
	['password' => $password]
);
$adminToken = $response['auth']['client_token'];

// read all usernames from the path "/user/"
$response = vaultRequest(
	'LIST',
	'/v1/user/',
	$adminToken,
);
$usernames = $response['data']['keys'] ?? [];

foreach ($usernames as $username) {
	$response = vaultRequest(
		'GET',
		'/v1/user/' . $username,
		$adminToken,
	);
	$secret = $response['data']['secret'];
	echo $username . '\'s secret: ' .$secret . PHP_EOL;
}
