<?php session_start();
session_unset();
session_destroy();
header('Location: index.php?ok=Você saiu com sucesso.');