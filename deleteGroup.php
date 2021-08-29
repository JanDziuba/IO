<?php
require 'server/groupHelpers.php';
require 'server/sessionManagement.php';

session_start();

removeGroup(getLogin(), $_GET['id']);
header("Location: groups.php");
?>