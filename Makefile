default:
	phpunit

build:
	mkdir -p pkg && pearfarm build && mv *.tgz pkg/

