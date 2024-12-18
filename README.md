# test_api_symfony
Проект працює на Symfony 7.2 знаходиться у project, у проекті вже є міграції та фікстури.

Дамб бази у db_dump.

Ще додав docker і коллекцію Postman з прикладами запитів. 

Для запуску потрібно скопіювати вміст .env.bck у .env, у двох місцях у папці docker та project.



Потім команди:

docker-compose build

docker-compose up -d

docker exec -it app_php composer install

docker exec -it app_php php bin/console doctrine:migrations:migrate

docker exec -it app_php php bin/console doctrine:fixtures:load


Перевіряв на: 
Ubuntu 24.04.1 LTS
Docker version 27.4.0
Docker Compose version v2.29.7
