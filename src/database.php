<?php

require 'database_private.php';

function getGame($telegramId)
{
    return [
        'spaceship' => getObjectForId($telegramId, "Spaceships")[0],
        'enemies' => getObjectForId($telegramId, "Enemies"),
        'meteors' => getObjectForId($telegramId, "Meteors"),
        'tanks' => getObjectForId($telegramId, "Tanks")
    ];
}

function createGame($telegramId, $game)
{
    $conn = getConn();
    $conn->beginTransaction();
    
    $query = "INSERT INTO Spaceships " . prepareQueryForNewObject($telegramId, $game['spaceship']);
    $conn->exec($query);
    foreach ($game['enemies'] as $enemy) {
        $query = "INSERT INTO Enemies " . prepareQueryForNewObject($telegramId, $enemy);
        $conn->exec($query);
    }
    foreach ($game['meteors'] as $meteor) {
        $query = "INSERT INTO Meteors " . prepareQueryForNewObject($telegramId, $meteor);
        $conn->exec($query);
    }
    foreach ($game['tanks'] as $tank) {
        $query = "INSERT INTO Tanks " . prepareQueryForNewObject($telegramId, $tank);
        $conn->exec($query);
    }
    
    $conn->commit();
    $conn = null;
    
    return getGame($telegramId);
}

function updateGame($telegramId, $game)
{
    if(!$game['spaceship']['id']) {
        $old_game = getGame($telegramId);
        $game = array_merge($old_game, $game);
    }
    updateObjectForId("Spaceships", $game['spaceship']); 
    foreach ($game['enemies'] as $enemy) {
        updateObjectForId("Enemies", $enemy); 
    }
    foreach ($game['meteors'] as $meteor) {
        updateObjectForId("Meteors", $meteor); 
    }
    foreach ($game['tanks'] as $tank) {
        updateObjectForId("Tanks", $tank); 
    }
    return $game;
}

function deleteGame($telegramId)
{
    updateUser($telegramId, false);
}

function addScore($telegramId, $score)
{

}