<?php
	require 'server/lessonsHelpers.php';

	deleteLesson($_GET['id']);
	header("Location: homepage.php");
?>