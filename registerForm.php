<?php
	require "sessionHelpers.php";
	redirectLogged();
?>

<!DOCTYPE html>
<html lang="pl-PL">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta charset="utf-8">
<link rel="stylesheet" href="style.css">
<title>Zarejestruj się</title>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"
		integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
		crossorigin="anonymous"></script>	
<script src="scripts/errorReporting.js"></script>
<script src="scripts/register.js"></script>
</head>
<body>
<div class="login">
<form action="/register.php" method="post">
	<input type="text" id="form-login" name="login" placeholder="Login" required><br>
	<input type="password" id="form-password" name="password" placeholder="Hasło" required><br>
	<input type="password" id="form-password-check" name="password-check" placeholder="Potwierdź hasło" required><br>
	<input type="submit" id="form-submit" value="Zarejestruj się"><br>
</form>
<p class="form-message"></p>
<br>
<button type="button" onclick="window.location='loginForm.php'">Powrót do logowania</button>
</div>
</body>
</html>