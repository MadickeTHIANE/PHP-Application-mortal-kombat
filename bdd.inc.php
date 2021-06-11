<?php
//*bdd.inc.php

$db = new PDO('mysql:host=localhost;dbname=test;chartset=utf8', 'root', '');

// On émet une alerte à chaque fois qu'une requête a échoué.
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING); 


?>
