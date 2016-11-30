<?php

$version = 0;

function getConn()
{
    $host = "localhost";
    $port = 3306;
    $username = "bot";
    $password = "password1";
    $dbname = "space";
        
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", 
        $username, 
        $password);
        
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
    return $db;
}

function setUpTables()
{
    $conn = getConn();
    
    $table = "DROP TABLE IF EXISTS Users;
        CREATE TABLE Users (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        telegram_id INT(6) UNIQUE NOT NULL,
        in_game BIT NOT NULL
        );";
    $conn->exec($table);
        
    $table = "DROP TABLE IF EXISTS Scores;
        CREATE TABLE Scores (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        telegram_id INT(6) NOT NULL,
        score score TINYINT NOT NULL,
        version INT(6) NOT NULL
        );";
    $conn->exec($table);

    $table = "DROP TABLE IF EXISTS Spaceships;
        CREATE TABLE Spaceships (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        telegram_id INT(6) NOT NULL,
        health SMALLINT NOT NULL,
        energy SMALLINT NOT NULL,
        shield SMALLINT NOT NULL,
        torpedos TINYINT NOT NULL,
        fuel SMALLINT NOT NULL,
        score TINYINT NOT NULL,
        coords_x DOUBLE NOT NULL,
        coords_y DOUBLE NOT NULL,
        coords_q TINYINT NOT NULL,
        version INT(6) NOT NULL
        );";
    $conn->exec($table);

    $table = "DROP TABLE IF EXISTS Enemies;
        CREATE TABLE Enemies (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        telegram_id INT(6) NOT NULL,
        health SMALLINT NOT NULL,
        energy SMALLINT NOT NULL,
        shield SMALLINT NOT NULL,
        torpedos TINYINT NOT NULL,
        fuel SMALLINT NOT NULL,
        coords_x DOUBLE NOT NULL,
        coords_y DOUBLE NOT NULL,
        coords_q TINYINT NOT NULL,
        version INT(6) NOT NULL
        );";
    $conn->exec($table);

    $table = "DROP TABLE IF EXISTS Meteors;
        CREATE TABLE Meteors (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        telegram_id INT(6) NOT NULL,
        density TINYINT NOT NULL,
        coords_x DOUBLE NOT NULL,
        coords_y DOUBLE NOT NULL,
        coords_q TINYINT NOT NULL,
        version INT(6) NOT NULL
        );";
    $conn->exec($table);

    $table = "DROP TABLE IF EXISTS Tanks;
        CREATE TABLE Tanks (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        telegram_id INT(6) NOT NULL,
        fuel SMALLINT NOT NULL,
        coords_x DOUBLE NOT NULL,
        coords_y DOUBLE NOT NULL,
        coords_q TINYINT NOT NULL,
        version INT(6) NOT NULL
        );";
    $conn->exec($table);
    $conn = null;
    
    return "success";
}

function prepareQueryForNewObject($telegramId, $object) {
    global $version;
    $queryKeys = "";
    $queryVals = "";
    foreach ($object as $key => $value) {
        $queryKeys .= $key .", ";
        $queryVals .= $value .", ";
    }
    return "(telegram_id, {$queryKeys}version) VALUES ($telegramId, {$queryVals}$version);";
}

function getObjectForId($telegramId, $object) {
    $conn = getConn();
    
    $query = "SELECT * FROM $object WHERE telegram_id=$telegramId";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $conn = null;
    
    $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
    
    return $result ? $stmt->fetchAll() : [];
}

function updateObjectForId($tableName, $object) {
    $query = "UPDATE $tableName SET";
    foreach ($object as $key => $value) {
        $query .= " $key=$value,";
    }
    
    $query = substr($query, 0, -1) . " WHERE id=" . $object['id'];
    
    $conn = getConn();
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $conn = null;
    
    return $stmt->rowCount();
}

class MyException extends Exception {
    protected $object;
    protected $coelision;
    public function __construct($message, $object, $coelision) {
        parent::__construct($message);
        $this->object = $object;
        $this->coelision = $coelision;
    }
    public function getObject() {
        return $this->object;
    }
    public function getCoelision() {
        return $this->coelision - 1;
    }
}