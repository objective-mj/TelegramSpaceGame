<?php

require 'game_private.php';
require 'database.php';

/***********************
*      UNAFFECTIVE     *
*     string outputs   *
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

    unset($game['spaceship']['coords_x'], 
        $game['spaceship']['coords_y'], 
        $game['spaceship']['coords_q'],
        $game['spaceship']['id'],
        $game['spaceship']['telegram_id'],
        $game['spaceship']['version']);
    $result = "Your spaceship looks as follows:\n";
    foreach ($game['spaceship'] as $key => $val) {
        $result .= "$key = $val\n";
    }
    return $result;
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
            "torpedos" => rand(0,3),
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

    $game = [
        'spaceship' => $spaceship,
        'enemies' => $enemies,
        'meteors' => $meteors,
        'tanks' => $tanks
    ];

    return createGame($telegramId, $game);

}

function endGame($telegramId, $game = null)
{
    if (!$game)
        $game = getGame($telegramId);

    if($game['spaceship']['health'] > 0)
        throw new ErrorException('Spaceship still alive');

    $score = $game['spaceship']['score'];

    $score += $game['spaceship']['fuel'] / 50;
    foreach ($game['tanks'] as $tank)
        $score += $tank['fuel'] / 50;

    echo "\nSCORE: {$score}\n";
    addScore($telegramId, $score);

    throw new Exception('game_over');
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
        unset( $game['enemies'][array_search($object, $game['enemies'])]);
    } elseif($object) {
        unset( $game['meteors'][array_search($object, $game['meteors'])]);
    } else {
        $game = enemyTurn($telegramId, $game);
        updateGame($telegramId, $game);

        throw new MyException('torpedo_nohit');
    }

    $game = enemyTurn($telegramId, $game);
    updateGame($telegramId, $game);
    return $game;
}

function laser($telegramId, $direction, $energy, $game = null)
{
    if (!$game)
        $game = getGame($telegramId);
        
    if ($game['spaceship']['energy'] < $energy) {
        return "Not enough energy!";
    }
    $game['spaceship']['energy'] -= $energy; 
    
    $object = fireLine($game, $direction);
    if ($object && array_key_exists('health', $object)) {
        $index = array_search($object, $game['enemies']);
        $leftOverShield = $object['shield'] - ($energy / 2); // shield requires double energy to be taken down.
            if ($leftOverShield <= 0 ) {
                $leftOverhealth = $object['health'] - ( -2 * $leftOverShield );
                if ($leftOverhealth <= 0) {
                    $game['spaceship']['score'] += 100;
                    unset( $game['enemies'][$index]);
                } else {
                    $game['enemies'][$index]['$health'] = $leftOverhealth;
                    $game['enemies'][$index]['$energy'] = 0;
                }
            } else {
                $game['enemies'][$index]['shield'] =  $leftOverShield;
            }

    } elseif($object) {
        unset( $game['meteors'][array_search($object, $game['meteors'])]);
    } else {
        $game['extra'] = 'laser_nohit';
    }

    $game = enemyTurn($telegramId, $game);
    return updateGame($telegramId, $game);
}

function move($telegramId, $direction, $distance, $game = null)
{
    if (!$game)
        $game = getGame($telegramId);
        
    $result = null;

    try {
        $game['spaceship'] = moveObject($game['spaceship'], $angle, $distance, $game, true);
    } catch (MyException $e) {
        $game['spaceship'] = $e->getObject();
        if ($e->getMessage() == "tank_event") {
            $game['extra'] = "tank";
        } elseif ($e->getMessage() == 'enemy_event') {
            $game['spaceship'] = damage($game['spaceship'], 60);
            if ($game['spaceship']['health'] < 1) {
                return endGame($telegramId, $game);
            }
            unset($game['enemies'][$e->getCoelision()]);
            $game['extra'] = "enemy";
        } elseif ($e->getMessage() == 'meteor_event') {
            $game['spaceship'] = damage($game['spaceship'], 
                $game['meteors'][$e->getCoelision()]['density'] * 10);
            if ($game['spaceship']['health'] < 1) {
                return endGame($telegramId, $game);
            }
            unset($game['meteors'][$e->getCoelision()]);
            $game['extra'] = 'meteor';
        }
    }

    $game = enemyTurn($telegramId, $game);

    updateGame($telegramId, $game);

    return $game;
}