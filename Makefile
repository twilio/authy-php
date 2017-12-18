default:
	phpunit

build:
	mkdir -p pkg && pearfarm build && mv *.tgz pkg/

docker-build:
	docker build -t authy-php .

docker-test:
	docker run -v $(shell pwd):/home/authy-php authy-php phpunit
