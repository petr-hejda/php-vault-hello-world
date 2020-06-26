<?php
include('/app/common.php');

$role = isset($argv[3]) && $argv[3] === 'admin' ? 'admin' : 'user';
$username = $argv[1] ?? null;
$password = $argv[2] ?? null;


// Login to Vault as "vaultUserManager" and get their current token
$response = vaultRequest(
	'POST',
	'/v1/auth/userpass/login/' . $config['vault']['userManager']['username'],
	null,
	['password' => $config['vault']['userManager']['password']]
);
$userManagerToken = $response['auth']['client_token'];

// Use the "vaultUserManager" token to create the user's userpass auth method
vaultRequest(
	'POST',
	'/v1/auth/userpass/users/' . $username,
	$userManagerToken,
	['password' => $password, 'policies' => 'default,' . $role]
);

// Get the mount accessor for the userpass login method
$response = vaultRequest(
	'GET',
	'/v1/sys/auth',
	$userManagerToken
);
$mountAccessor = $response['userpass/']['accessor'];

// Use the "vaultUserManager" token to create the user's entity
$response = vaultRequest(
	'POST',
	'/v1/identity/entity',
	$userManagerToken,
	['name' => $username, 'policies' => 'default,user', 'metadata' => ['username' => $username]]
);
$userEntityId = $response['data']['id'];

// Use the "vaultUserManager" token to crete the user's entity alias
$response = vaultRequest(
	'POST',
	'/v1/identity/entity-alias',
	$userManagerToken,
	['name' => $username, 'canonical_id' => $userEntityId, 'mount_accessor' => $mountAccessor, 'metadata' => ['username' => $username]]
);


echo 'User registered' . PHP_EOL;
