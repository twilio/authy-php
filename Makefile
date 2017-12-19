default:
	phpunit

build:
	mkdir -p pkg && pearfarm build && mv *.tgz pkg/

docker-build:
	docker build -t authy-php .

docker-deps:
	docker run -v $(shell pwd):/home/authy-php authy-php composer update

docker-test:
	docker run -v $(shell pwd):/home/authy-php authy-php phpunit -v --colors=always

docker-shell:
	docker run -it -v $(shell pwd):/home/authy-php authy-php bash
