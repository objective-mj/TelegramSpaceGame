<?php

require 'database_private.php';

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