<?php
function getDB() {
    $host = 'localhost';
    $dbname = 'banque';
    $username = 'root';
    $port = '3306';
    $password = '';

    try {
        return new PDO("mysql:host=$host;dbname=$dbname;port=$port;charset=utf8", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    } catch (PDOException $e) {
        die(json_encode(['error' => $e->getMessage()]));
    }
}
