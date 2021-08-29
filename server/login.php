<?php
	require 'usersSystem.php';
	require 'sessionManagement.php';

	function handleLoginRequest() {
		// Sprawdzamy czy użytkownik nie jest aktualnie zalogowany
		if (isLogged()) {
			return 1;
		}
		// Sprawdzamy czy dostaliśmy poprawnego requesta
		if (!isset($_POST['login']) || !isset($_POST['password'])) {
			return 2;
		}
		
		$login = $_POST['login'];
		$password = $_POST['password'];

		if (empty($login) || empty($password)) {
			return 3;
		}
		
		if (!isExistingUser($login, $password)) {
			return 4;
		}

		login($login);
		return 0;
	}

	session_start();
	$result = handleLoginRequest();
	if ($result != 0) session_destroy();
	echo json_encode(array("result" => $result));
?>