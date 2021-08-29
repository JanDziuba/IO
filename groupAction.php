<?php
// ID grupy jest w $_GET['id'], nazwa grupy w $_GET['name']

if ($_GET['submit'] == 'Lekcje') {
  require 'groupLessons.php';
}

else if ($_GET['submit'] == 'Edytuj') {
  require 'groupUsers.php';
}

else if ($_GET['submit'] == 'Usuń') {
  require 'deleteGroup.php';
}

else if ($_GET['submit'] == 'Opuść') {
  require 'leaveGroup.php';
}
?>