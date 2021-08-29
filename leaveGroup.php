<?php
require 'server/groupHelpers.php';
require 'server/sessionManagement.php';

session_start();

leaveGroup(getLogin(), $_GET['id']);
header("Location: groups.php");
?>