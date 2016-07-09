<?php

require 'database_private.php';

function createUser($telegramId, $version = 0)
{
    $query = "INSERT INTO Users (telegram_id, version, in_game)
        VALUES({$telegramId}, {$version}, true);";

    executeQuery($query);
}

function updateUser($telegramId, $inGame, $version = 0)
{
    $query = "UPDATE Users
        SET in_game={telegramId}, version={telegramId}
        WHERE telegram_id={telegramId};";

    executeQuery($query);
}

function getGame($telegramId)
{
    return [
        'spaceship' => $spaceship,
        'enemies' => $enemies,
        'meteors' => $meteors,
        'tanks' => $tanks
    ];
}

function createGame($telegramId, $game)
{
    createUser($telegramId);
    updateUser($telegramId, true);
}

function updateGame($telegramId, $game)
{

}

function deleteGame($telegramId)
{
    updateUser($telegramId, false);
}