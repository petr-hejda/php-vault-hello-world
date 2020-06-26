<?php

/**
 * @param string $method
 * @param string $path
 * @param string|null $token default null
 * @param array $data default []
 * @return array|null
 */
function vaultRequest(string $method, string $path, ?string $token = null, array $data = []): ?array
{
	$requestHeaders = ['Content-Type: application/json'];

	if ($token !== null) {
		$requestHeaders[] = 'X-Vault-Token: ' . $token;
	}

	$curl = curl_init();
	curl_setopt_array($curl, [
		CURLOPT_URL => 'http://vault:8200' . $path,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_CUSTOMREQUEST => $method,
		CURLOPT_POSTFIELDS => json_encode($data),
		CURLOPT_HTTPHEADER => $requestHeaders,
	]);

	$response = curl_exec($curl);

	/*
	echo $path . PHP_EOL;
	echo json_encode($data) . PHP_EOL;
	echo $response . PHP_EOL . '----------' . PHP_EOL . PHP_EOL;
	*/

	curl_close($curl);

	if (!$response) {
		return null;
	}

	return json_decode($response, true);
}

$config = json_decode(file_get_contents('/app/config.json'), true);
