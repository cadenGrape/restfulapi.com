<?php
$pdo = new PDO('mysql:host=localhost;dbname=mydb', 'root','root');
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
return $pdo;