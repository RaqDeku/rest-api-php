<?php
	require "../bootstrap.php";

	use Src\TableGateways\UserGateway;

	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		// code...
		http_response_code(405);
		header("Allow: POST");
		exit();
	}

	$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

	if ($contentType != 'application/json') {
		// code...
		http_response_code(415);
		echo json_encode([
			"status" => "error",
			"message" => "Only JSON content is supported!"
		]);
		exit();
	}

	$data = json_decode(file_get_contents("php://input"), true);

	if ($data === null) {
		// code...
		http_response_code(400);
		echo json_encode([
			"status" => "error",
			"message" => "Invalid JSON data!"
		]);
		exit();
	}

	if (!array_key_exists('username', $data) || !array_key_exists('password', $data)) {
		// code...
		http_response_code(400);
		echo json_encode([
			"status" => "error",
			"message" => "Missing login cresidentials!"
		]);
		exit();
	}

	$user_gateway = new UserGateway($dbConnection);

	$user = $user_gateway->getByUsername($data['username']);

	if ($user === false) {
		// code...
		http_response_code(401);
		echo json_encode([
			"status" => "error",
			"message" => "Invalid authentication!",
		]);
		exit();
	}

	if (!password_verify($data['password'], $user[0]->password_hash)) {
		// code...
		http_response_code(401);
		echo json_encode([
			"status" => "error",
			"message" => "Invalid cresidentials!"
		]);
	}

	require ("tokens.php");

	$refresh_token_gateway = new RefreshTokenGatway($dbConnection, $_ENV['SECRET_KEY']);
	$refresh_token_gateway->create($refresh_token, $refresh_token_expiry);
