run:
    docker-compose up -d

connect:
    docker exec -it spacegame_game_1 php test.php bin\bash