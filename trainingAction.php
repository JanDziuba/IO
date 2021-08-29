<?php
	// Nazwy plików placeholderowe. ID lekcji jest w $_GET['id'] i można z tej zmiennej korzystać w tych plikach.

	// Naciśnięto przycisk edit
	if ($_GET['submit'] == 'Edytuj') {
		require 'editTrainingForm.php';
	}
	// Delete
	else if ($_GET['submit'] == 'Usuń') {
		require 'deleteTraining.php';
	}
	// Naciśnięto lekcję, więc idziemy do treningów z nimi związanych
	else {
		require 'lessonTrainQA.php';
    }
?>