<?php

function onFireLine($points, $objects) {
    
    $Q = $points['Q'];
    $AB = $points['AB'];
    $Ax = $points['Ax']; // 0
    $Ay = $points['Ay']; // 0
    $Bx = $points['Bx'];
    $By = $points['By'];
    
    foreach ($objects as $item) {
        if ($Q != $item['coords_q']) continue;
        $diff = $Q - $item['coords_q']; // [-8..8]

        $yFactor = floor( $diff / 3); // [-2, -1, 0, 1, 2]
        $xFactor = $diff < 0 ? -1 * (abs($diff) % 3) : $diff % 3; // [-2, -1, 0, 1, 2]

        $Cy = ( 200 * $yFactor ) + $item['coords_y'];
        $Cx = ( -200 * $xFactor) + $item['coordslaser _x'];

        $AC = sqrt( pow($Ax-$Cx, 2) + pow($Ay-$Cy, 2) );
        $CB = sqrt( pow($Cx-$Bx, 2) + pow($Cy-$By, 2) );

        if ($AC + $CB <= $AB + 100)
            echo "You hit something in Q{$item['coords_q']} it has {$item['health']} health left}\n";
            return $item;
    }
    return null;
}

function fireLine($game, $direction)
{
    // Contruct a line |AB| that cuts through the ship and the given angle
    // Convert all enenemies and meteors into same quadron and make them C
    // Check is C is on or near |AB| by doing |AC|+|CB|==|AB|

    $degree = round( $direction * 360 );
    
    $coordX = $game['spaceship']['coords_x']; 
    $coordY = $game['spaceship']['coords_y'];

    $Ax = $degree ? $coordX - (-900 - $coordY) * sin($degree) : $coordX;
    $Ay = $degree ? $coordY + (-900 - $coordY) * cos($degree) : -900;

    $Bx = $coordx - ($Ax - $coordX);
    $By = $coordY - ($Ay - $coordY);

    $AB = sqrt( pow($Ax-$Bx, 2) + pow($Ay-$By, 2) );
    
    $points = [
        'Q' => $game['spaceship']['coords_q'],
        'AB' => $AB,
        'Ax' => $Ax,
        'Ay' => $Ay,
        'Bx' => $Bx,
        'By' => $By,
        
    ];
    
    $meteor = onFireLine($points, $game['meteors']);
    $enemy = onFireLine($points, $game['enemies']);

    return $meteor ?: $enemy;
    
}

function damage($object, $dmg) {
    $dmg -= $object['shield'];
    $object['shield'] = $dmg >= 0 ? 0 : $dmg * -1;

    $dmg -= $object['health'];
    $object['health'] = $dmg >= 0 ? 0 : $dmg * -1;
    
    return $object;
}

function moveObject($object, $direction, $distance, $game) {

    if ($direction < 0 || $direction > 1 ||
        $distance <= 0 || $distance > 100)
        throw new ErrorException('Invalid input');

    if ($object['fuel'] < $distance / 2)
        throw new Exception('fuel_low');

    $degree = round( $direction * 360 );

    $coordX = $object['coords_x'];
    $coordY = $object['coords_y'];
    $coordQ = $object['coords_q'];

    $s = round(sin( deg2rad($degree)), 3);
    $c = round(cos( deg2rad($degree)), 3);

    $newX = $coordX - ($s * (-100 - $coordY));
    $newY = $coordY + ($c * (-100 - $coordY));

    $factor = $distance / (sqrt( pow($newX-$coordX, 2) + pow($newY-$coordY, 2)));

    echo "old: {$coordX},{$coordY},{$coordQ} new: {$newX},{$newY} factor: {$factor}";

    // SELFNOTE: the factor is multiplied wrong. When the total travel is 80 along one axis from start 80
    // the answer should be 160 but becomes 180 * 0.8 which is not what I want.

    $newX *= $factor;
    $newY *= $factor;

    if ($degree < 90) {
            // right and up.
            // x++,
            if ($newX > 100 && in_array($coordQ, [3,6,9])) {
                $x = 100;
                $q = $coordQ;
            } elseif ($newX > 100) {
                $x = -200 + $newX;
                $q = $coordQ + 1;
            } else {
                $x = $newX;
                $q = $coordQ;
            }

            //y --
            if ($newY < -100 && in_array($coordQ, [1,2,3])) {
                $y = -100;
                $q = $q;
            } elseif ($newY < -100) {
                $y = 200 + $newY;
                $q = $q - 3;
            } else {
                $y = $newY;
                $q = $q;
            }
    } elseif ($degree < 180) {
            // right and down.
            // x++,
            if ($newX > 100 && in_array($coordQ, [3,6,9])) {
                $x = 100;
                $q = $coordQ;
            } elseif ($newX > 100) {
                $x = -200 + $newX;
                $q = $coordQ + 1;
            } else {
                $x = $newX;
                $q = $coordQ;
            }
            //y ++
            if ($newY > 100 && in_array($coordQ, [7,8,9])) {
                $y = 100;
                $q = $q;
            } elseif ($newY > 100) {
                $y = -200 + $newY;
                $q = $q + 3;
            } else {
                $y = $newY;
                $q = $q;
            }

    } elseif ($degree < 270) {
            // left and down.
            // x--
            if ($newX < -100 && in_array($coordQ, [1,4,7])) {
                $x = -100;
                $q = $coordQ;
            } elseif ($newX < -100) {
                $x = 200 + $newX;
                $q = $coordQ - 1;
            } else {
                $x = $newX;
                $q = $coordQ;
            }

            //y ++
            if ($newY > 100 && in_array($coordQ, [7,8,9])) {
                $y = 100;
                $q = $q;
            } elseif ($newY > 100) {
                $y = -200 + $newY;
                $q = $q + 3;
            } else {
                $y = $newY;
                $q = $q;
            }

    } elseif ($degree <= 360) {
            //left and up
            // x--,
            if ($newX < -100 && in_array($coordQ, [1,4,7])) {
                $x = -100;
                $q = $coordQ;
            } elseif ($newX < -100) {
                $x = 200 + $newX;
                $q = $coordQ - 1;
            } else {
                $x = $newX;
                $q = $coordQ;
            }

            //y--
            if ($newY < -100 && in_array($coordQ, [1,2,3])) {
                $y = -100;
                $q = $q;
            } elseif ($newY < -100) {
                $y = 200 + $newY;
                $q = $q - 3;
            } else {
                $y = $newY;
                $q = $q;
            }
    } else {
            echo $degree;
            throw new ErrorException('Something went wrong with the angle');
    }

    $object['coords_y'] = $y;
    $object['coords_x'] = $x;
    $object['coords_q'] = $q;

    $object['fuel'] -= $distance / 2;

    return checkCoords($x, $y, $q, $object, $game);
}

function enemyTurn($telegramId, $game = null)
{
    if (!$game)
        $game = getGame($telegramId);

    foreach($game['enemies'] as $key => $enemy) {
        
        // if shield low, regenerate
        if ($game['enemies'][$key]['shield'] <= 100 && $game['enemies'][$key]['energy'] > 100) {
            $game['enemies'][$key]['energy'] -= 50;
            $game['enemies'][$key]['shield'] += 50;
        }

        // if health low, use half energy added to shield
        if ($game['enemies'][$key]['health'] < 75) {
            $game['enemies'][$key]['energy'] -= floor($game['enemies'][$key]['energy'] / 2);
            $game['enemies'][$key]['shield'] += $game['enemies'][$key]['energy'] / 2;
        }

        // move within same quadrant if player isn't there
        if ($game['enemies'][$key]['coords_q'] != $game['spaceship']['coords_q']) {
            echo "Enemy $key outside Q\n";
            //$game['enemies'][$key] = moveObject($game['enemies'][$key], rand(0,1), rand( $game['enemies'][$key]['fuel'] / 8, 100 ), $game);
        } else {
            $action = rand(5,30) / 10;
            
            echo "Enemy $key in Q taking action $action\n";

            if ($action < 1) {
                //torpedo
                if ($game['enemies'][$key]['torpedos'] > 0) {
                    $game['enemies'][$key]['torpedos'] -= 1;
                    $game['spaceship'] = damage($game['spaceship'], 150);
                    
                    if ($game['spaceship']['health'] < 1)
                        return endGame($telegramId, $game);
                }

            } elseif ($action < 2) {
                //laser
                if ($game['enemies'][$key]['energy'] >= 60) {
                    $game['enemies'][$key]['energy'] -= 60;
                    $game['spaceship'] = damage($game['spaceship'], 60);
                    
                    if ($game['spaceship']['health'] < 1)
                        return endGame($telegramId, $game);
                }

            } elseif ($action <= 3) {
                //move
                $retry = false;
                while ($retry){
                    try {
                        $game['enemies'][$key] = moveObject(
                            $game['enemies'][$key], 
                            rand(0,1), 
                            rand( $game['enemies'][$key]['fuel'] / 8, 60 ), 
                            $game
                        );
                    } catch (MyException $e) {
                        if ($e->getMessage() == "tank_event" || 
                            $e->getMessage() == "enemy_event" ||
                            $e->getMessage() == "meteor_event") 
                        {
                            $retry = true;
                            continue;
                        } else {
                            throw $e;
                        }
                    }
                }
            }
        }
    }

    return $game;
}

function findItem($items, $x, $y, $q) {
    foreach ($items as $key => $item) {
        if ($item['coords_q'] != $q)
            continue;
        if ( $x < $item['coords_x'] + 10 && $x > $item['coords_x'] - 10  &&
             $y < $item['coords_y'] + 10 && $y > $item['coords_y'] - 10 )
             return $key + 1;
    }
    return false;   
}

function checkCoords($x, $y, $q, $object, $game)
{
    $enemies = $game['enemies'];
    $meteors = $game['meteors'];
    $tanks = $game['tanks'];

    if ($coelision = findItem($tanks, $x, $y, $q))
        throw new MyException('tank_event', $object, $coelision);

    if ($coelision = findItem($enemies, $x, $y, $q))
        throw new MyException('enemy_event', $object, $coelision);

    if ($coelision = findItem($meteors, $x, $y, $q))
        throw new MyException('meteor_event', $object, $coelision);

    return $object;

}

function quadronCoords($items, $q)
{
    $temps;
    foreach ($items as $item)
    {
        if ($q != $item['coords_q'])
            continue;
        $x = round(( ($item['coords_x'] + 100) / 5) +1) ;
        $y = round(( ($item['coords_y'] + 100) / 10) +1) ;

        $temps[$y] = $temps[$y] ?: [];
        $temps[$y][] = $x;
    }
    return $temps;
}

function visualiseQuadron($game)
{
    $spaceShip = $game['spaceship'];
    $enemies = $game['enemies'];
    $meteors = $game['meteors'];
    $tanks = $game['tanks'];

    $spaceShipX = round(( ($spaceShip['coords_x'] + 100) / 5 ) + 1) ;
    $spaceShipY = round(( ($spaceShip['coords_y'] + 100) / 10 ) + 1) ;

    $enemies = quadronCoords($enemies, $spaceShip['coords_q']);
    $meteors = quadronCoords($meteors, $spaceShip['coords_q']);
    $tanks = quadronCoords($tanks, $spaceShip['coords_q']);

    $row_divs = "+= = = = = = = = = = = = = = = = = = = = =+\n";
    $row_fill = "| . . . . . . . . . . . . . . . . . . . . |\n";

    $board = "";
    for ($i = 0; $i < 23; $i++) {
        switch ($i) {
            case 0:
            case 22:
                $row = $row_divs;
                break;
            default:
                $row = $row_fill;
        }

        if ($spaceShipY == $i)
            $row = substr_replace($row, 'ᐂ', $spaceShipX, 1);

        if ($enemies[$i]) {
            foreach ($enemies[$i] as $value)
                $row = substr_replace($row, 'Ϙ', $value, 1);
        }

        if ($meteors[$i]) {
            foreach ($meteors[$i] as $value)
                $row = substr_replace($row, '*', $value, 1);
        }

        if ($tanks[$i]) {
            foreach ($tanks[$i] as $value)
                $row = substr_replace($row, '΢', $value, 1);
        }

        $board .= $row;
    }
    return $board;
}

function visualiseBoard($game)
{
    $spaceShip = $game['spaceship'];
    $enemies = $game['enemies'];
    $meteors = $game['meteors'];
    $tanks = $game['tanks'];

    $values = [
        1 => 0,
        2 => 0,
        3 => 0,
        4 => 0,
        5 => 0,
        6 => 0,
        7 => 0,
        8 => 0,
        9 => 0,
    ];

    foreach ($enemies as $item) {
        $values[$item['coords_q']] += 100;
    }
    foreach ($meteors as $item) {
        $values[$item['coords_q']] += 1;
    }
    foreach ($tanks as $item) {
        $values[$item['coords_q']] += 10;
    }

    foreach ($values as $key => $value) {
        if ($value < 1) {
            $values[$key] = " 000 ";
        }
        elseif ($value < 10) {
            $values[$key] = " 00{$value} ";
        }
        elseif ($value < 100) {
            $values[$key] = " 0{$value} ";
        }
        else {
            $values[$key] = " {$value} ";
        }
    }

    $values[$spaceShip['coords_q']] = substr_replace($values[$spaceShip['coords_q']] , '{', 0, 1);
    $values[$spaceShip['coords_q']] = substr_replace($values[$spaceShip['coords_q']] , '}', 4, 1);

    $board =  "*= = =*= = =*= = =*\n";
    $board .= "*{$values[1]}|{$values[2]}|{$values[3]}*\n";
    $board .= "*- - -*- - -*- - -*\n";
    $board .= "*{$values[4]}|{$values[5]}|{$values[6]}*\n";
    $board .= "*- - -*- - -*- - -*\n";
    $board .= "*{$values[7]}|{$values[8]}|{$values[9]}*\n";
    $board .= "*= = =*= = =*= = =*\n";


    return $board;
}