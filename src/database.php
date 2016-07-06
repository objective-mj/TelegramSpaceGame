<?php

$host = "mysql";
$username = "user";
$password = "pass";
$dbname = "test";

function executeQuery($query)
{
    $dbcon = new mysqli($host, $username, $password, $dbname);

    $result = $dbcon->query($query);

    $dbcon->close();

    return $result;
}

function createUser($telegramId, $version = 0)
{
    $query = "INSERT INTO Users (telegram_id, version)
        VALUES({$telegramId}, {$version});";

    executeQuery($query);
}

function updateUser($telegramId, $inGame, $version = 0)
{
    $query = "UPDATE Users
        SET in_game={telegramId}, version={telegramId}
        WHERE telegram_id={telegramId};";

    executeQuery($query);
}

function createSpaceShip()
{

}

function updateSpaceShip()
{

}

function isSetUp()
{
    try {
        return 0 <= executeQuery("SELECT COUNT(id) FROM Users;");
    } catch (Exception $e) {
        return false;
    }

}

function setUp()
{
    $tables = "CREATE TABLE Users (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        telegram_id INT(32) NOT NULL,
        in_game BIT(1) NOT NULL DEFAULT 0,
        version INT(6) NOT NULL
        );";

    $tables .= "CREATE TABLE Scores (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(6) NOT NULL FOREIGN KEY REFERENCES Users(id),
        score score TINYINT NOT NULL,
        version INT(6) NOT NULL
        );";

    $tables .= "CREATE TABLE Spaceship (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(6) NOT NULL FOREIGN KEY REFERENCES Users(id),
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

    $tables .= "CREATE TABLE Enemy (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(6) NOT NULL FOREIGN KEY REFERENCES Users(id),
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

    $tables .= "CREATE TABLE Meteor (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(6) NOT NULL FOREIGN KEY REFERENCES Users(id),
        density TINYINT NOT NULL,
        coords_x DOUBLE NOT NULL,
        coords_y DOUBLE NOT NULL,
        coords_q TINYINT NOT NULL,
        version INT(6) NOT NULL
        );";

     $tables .= "CREATE TABLE Tank (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(6) NOT NULL FOREIGN KEY REFERENCES Users(id),
        fuel SMALLINT NOT NULL,
        coords_x DOUBLE NOT NULL,
        coords_y DOUBLE NOT NULL,
        coords_q TINYINT NOT NULL,
        version INT(6) NOT NULL
        )";

    echo executeQuery( $tables );
}
