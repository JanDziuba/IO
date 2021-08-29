<?php
	// Nazwy plików placeholderowe. ID lekcji jest w $_GET['id'] i można z tej zmiennej korzystać w tych plikach.

	// Naciśnięto przycisk edit
	if ($_GET['submit'] == 'Edytuj') {
		require 'editLessonForm.php';
	}
	// Delete
	else if ($_GET['submit'] == 'Usuń') {
		require 'deleteLesson.php';
	}
	// Naciśnięto lekcję, czyli chyba chcemy się uczyć
	else {
		require 'trainings.php';
	}
?>