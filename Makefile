install:
	composer install --no-scripts
	symfony console doctrine:migrations:migrate -n
	symfony console cache:clear
	setfacl -dR -m u:$(uid):rwX .
	setfacl -R -m u:$(uid):rwX .
