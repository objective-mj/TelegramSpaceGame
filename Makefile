mysql:
	docker rm test-mysql
	docker run --name test-mysql -e MYSQL_ROOT_PASSWORD=1234 -e MYSQL_DATABASE=test -e MYSQL_USER=user -e MYSQL_PASSWORD=1234  -d mysql:5.7

build:
	docker build -t objective/game .

ROOT_DIR:=$(shell dirname $(realpath $(lastword $(MAKEFILE_LIST))))

run:
	docker rm objmj-telegame
	docker run -v $(ROOT_DIR)/src:/home/app/space_game -it \
	--name objmj-telegame --link test-mysql:mysql objective/game /bin/bash -c \
	"composer require longman/telegram-bot; /bin/bash"