<?php
require 'sessionHelpers.php';

redirectNotLogged();

require 'pageTemplate.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header('Location: homepage.php');
}

$lessonId = $_GET['id'];
$trainingId = $_GET['trainingId'];
$content = <<<ENT
    <div id="edit-training">
    <div class="title">Edytuj trening</div>
    <div id="errors"></div>
        <form action="server/editTraining.php" method="post" 
        id="editTrainingForm">
            <input type="hidden" name="lessonId" value=$lessonId>
            <input type="hidden" name="trainingId" value=$trainingId>
            <div>Nowa nazwa treningu: </div>
            <input type="text" name="name" required><br> 
            <div>Ile pytań w partii treningowej: </div>
            <input type="number" name="batchSize" required><br> 
            <div>Ile razy chcesz powtórzyć każde pytanie: </div>
            <input type="number" name="trainingRepetitions" required><br> 
            <div><input type="submit" value="Edytuj trening"></div>
        </form>
    <div>
    ENT;

echo genPage("Edytuj trening", $content, $_SESSION['login'], ["scripts/editTraining.js"], ["style.css"]);
?>