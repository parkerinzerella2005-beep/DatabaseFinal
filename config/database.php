<?php
$host = "localhost"; //where the db is running 
$dbname = "shipmywhip"; //whats the name of the database 
$username = "root"; //whats the username for the database
$password = ""; //whats the password for the database

try {
    //try to connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    //if something goes wrong, show the error 
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    //if connection fails, stop everything and show the error message
    die("Connection failed: " . $e->getMessage());
}
?>