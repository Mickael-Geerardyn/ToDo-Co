# ToDo-Co

# ⚙️ Installation
____________________
### Requirement 

- PHP 7.2.5 or higher
- composer 2.2 or higher
- You can check all requirements here:
https://symfony.com/releases/5.4

#### Installation :

click on the green "code" button on the top

copy the "https" link then clone the projet in your root directory local project like this

```bash
git clone https://github.com/Mickael-Geerardyn/ToDo-Co.git
```

Install project with
```bash
composer install
```

Create the .env.local file in root directory with
```bash
cp .env .env.local
```
If not already done, starting your database
```bash
sudo service mysql start
```

Then add your database informations
```bash
DATABASE_URL="mysql://app:!ChangeMe!@127.0.0.1:5432/app?serverVersion=15&charset=utf8"
```

Create the database
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:schema:update --force
```

Load fixtures to fill in the database
```bash
php bin/console doctrine:fixtures:load  
```

Load symfony server in the root project
```bash
symfony server:start
```
then you can add to the project at: http://localhost/8000
