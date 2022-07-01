<?php

session_start();

$host = 'localhost';
$user = 'root';
$pass = 'admin';
$banco = 'MedicalChallenge';
$banco2 = '0temp';

// Conexão com o banco da clínica fictícia:
$connMedical = mysqli_connect($host, $user, $pass, $banco)
  or die("Não foi possível conectar os servidor MySQL: MedicalChallenge\n");
  mysqli_set_charset($connMedical, "utf8");

// Conexão com o banco temporário:
$connTemp = mysqli_connect($host, $user, $pass, $banco2)
  or die("Não foi possível conectar os servidor MySQL: 0temp\n");
  mysqli_set_charset($connTemp, "utf8");

?>