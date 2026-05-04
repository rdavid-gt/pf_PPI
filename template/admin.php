<?php

if($_SESSION['id'] != 1){
    header("Location: inicio.php");
    exit();
}