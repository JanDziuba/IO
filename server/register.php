<?php
	require "usersSystem.php";
	require "namingConstraints.php";
	require "sessionManagement.php";

	function handleRegistrationRequest() {
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

		if (empty($login) || empty($password)) return 3;
		if (!isValidLogin($login)) return 4;
		if (!isValidPassword($password)) return 5;
		if (!isLoginVacant($login)) return 6;
		if (!registerUser($login, $password)) return 7;

		login($login);
		return 0;
	}
	
	session_start();
	$result = handleRegistrationRequest();
	if ($result != 0) {
		session_destroy();
	}
	echo json_encode(array("result" => $result));
?>