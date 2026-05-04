<?php

if(isset($_SESSION['id'])){
    header("Location: inicio.php");
    exit();
}