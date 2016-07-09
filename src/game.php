<?php

require 'game_private.php';

/***********************
*      UNAFFECTIVE     *
************************/

function srs($telegramId, $game = null)
{
    if (!$game)
        $game = getGame($telegramId);

    return visualiseQuadron($game);

}

function lrs($telegramId, $game = null)
{
    if (!$game)
        $game = getGame($telegramId);

    return visualiseBoard($game);
}

function stats($telegramId, $game = null)
{
    if (!$game)
        $game = getGame($telegramId);

    unset($game['spaceship']['coords_x'], $game['spaceship']['coords_y'], $game['spaceship']['coords_q']);
    return $game['spaceship'];
}

/***********************
*   CHANGES THE GAME   *
************************/

function startGame($telegramId, $difficulty=1)
{
    $boardCheck = [
        1 => [],
        2 => [],
        3 => [],
        4 => [],
        5 => [
            0 => 0,
        ],
        6 => [],
        7 => [],
        8 => [],
        9 => [],
    ];
    $spaceship = [
        "health" => 100,
        "energy" => 500,
        "shield" => 0,
        "torpedos" => 5,
        "fuel" => 1000,
        "score" => 0,
        "coords_x" => 0,
        "coords_y" => 0,
        "coords_q" => 5
    ];

    $enemies = [];
    $count = rand(5, 8) * $difficulty;
    for ($i = 0; $i < $count; $i++) {
        $enemy = [
            "health" => 100 + 50 * $difficulty,
            "energy" => rand(50, 150) * $difficulty,
            "shield" => 0,
            "torpedos" => rand(2,4),
            "fuel" => rand(200,500),
            "coords_x" => rand(-100, 100),
            "coords_y" => rand(-100, 100),
            "coords_q" => rand(1,9)
        ];
        if ($boardCheck[$enemy["coords_q"]][$enemy["coords_y"]] == $enemy['coords_x']) {
            $i--;
            continue;
        }
        $boardCheck[$enemy["coords_q"]][$enemy["coords_y"]] = $enemy['coords_x'];
        $enemies[] = $enemy;
    }

    $meteors = [];
    $count = rand(8, 18) * $difficulty;
    for ($i = 0; $i < $count; $i++) {
        $meteor = [
            "density" => rand(1,10),
            "coords_x" => rand(-100, 100),
            "coords_y" => rand(-100, 100),
            "coords_q" => rand(1,9)
        ];
        if ($boardCheck[$meteor["coords_q"]][$meteor["coords_y"]] == $meteor['coords_x']) {
            $i--;
            continue;
        }
        $boardCheck[$meteor["coords_q"]][$meteor["coords_y"]] = $meteor['coords_x'];
        $meteors[] = $meteor;
    }

    $tanks = [];
    $count = rand(2, 4) - $difficulty;
    for ($i = 0; $i < $count; $i++) {
        $tank = [
            "fuel" => rand(200,1000),
            "coords_x" => rand(-100, 100),
            "coords_y" => rand(-100, 100),
            "coords_q" => rand(1,9)
        ];
        if ($boardCheck[$tank["coords_q"]][$tank["coords_y"]] == $tank['coords_x']) {
            $i--;
            continue;
        }
        $boardCheck[$tank["coords_q"]][$tank["coords_y"]] = $tank['coords_x'];
        $tanks[] = $tank;
    }

    return [
        'spaceship' => $spaceship,
        'enemies' => $enemies,
        'meteors' => $meteors,
        'tanks' => $tanks
    ];

}

function endGame($telegramId)
{

}

function shield($telegramId, $energy, $game = null)
{
    if (!$game)
        $game = getGame($telegramId);

    if ($game['spaceship']['energy'] < $energy)
        throw new Exception('energy_low');

    $game['spaceship']['energy'] -= $energy;
    $game['spaceship']['shield'] += $energy;

    updateGame($telegramId, $game);

    return $game;
}

function torpedo($telegramId, $direction, $game = null)
{
    if (!$game)
        $game = getGame($telegramId);

    $object = fireLine($game, $direction);
    if ($object && array_key_exists('health', $object)) {
        $game['spaceship']['score'] += 100;
        unset( $game['enemies'][array_search($object, $game['enemies'])];
    } elseif($object) {
        unset( $game['meteors'][array_search($object, $game['meteors'])];
    } else {
        $game = enemyTurn($telegramId, $game);
        updateGame($telegramId, $game);

        throw new Exception('torpedo_nohit');
    }

    $game = enemyTurn($telegramId, $game);
    updateGame($telegramId, $game);
    return $game;
}

function laser($telegramId, $direction, $energy, $game = null)
{
    if (!$game)
        $game = getGame($telegramId);

    $object = fireLine($game, $direction);
    if ($object && array_key_exists('health', $object)) {
        $leftOverEnergy = $object['shield'] - ($energy / 2); // shield requires double energy to be taken down.
            if ($leftOverEnergy <= 0 ) {
                $leftOverhealth = $object['health'] - ( -2 * $leftOverEnergy );
                if ($leftOverhealth <= 0) {
                    $game['spaceship']['score'] += 100;
                    unset( $game['enemies'][array_search($object, $game['enemies'])];
                } else {
                    $game['enemies'][array_search($object, $game['enemies'])]['$health'] = $leftOverhealth;
                    $game['enemies'][array_search($object, $game['enemies'])]['$energy'] = 0;
                }
            } else {
                $game['enemies'][array_search($object, $game['enemies'])]['$energy'] =  leftOverEnergy;
            }

    } elseif($object) {
        unset( $game['meteors'][array_search($object, $game['meteors'])];
    } else {
        $game = enemyTurn($telegramId, $game);
        updateGame($telegramId, $game);

        throw new Exception('laser_nohit');
    }

    $game = enemyTurn($telegramId, $game);
    updateGame($telegramId, $game);
    return $game;
}

function move($telegramId, $direction, $distance, $game = null)
{
    if (!$game)
        $game = getGame($telegramId);

    $game['spaceship'] = moveObject($game['spaceship'], $angle, $distance, $game, true);

    $game = enemyTurn($telegramId, $game);

    updateGame($telegramId, $game);

    return $game;
}