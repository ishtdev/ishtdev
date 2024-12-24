1.Add .env and .htaccess at the root location of the project
2.Update DB credentials at the env file. 

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ishtadev
DB_USERNAME=ishtadev_user
DB_PASSWORD=MySQL@123

3.Run the following command to install the project dependencies using Composer: 
composer install
4.Generate an application key by running the following command: 
php artisan key:generate
5.Run the following command to run the database migrations and create the necessary tables:
php artisan migrate
6.Run the seeders for dummy data, you can run them using the following command:
php artisan db:seed


Server:

1. Admin Panel on netlify

	https://www.netlify.com/

	Login with Arpit sir github

	steps for deployment: 
	a. sites/ishtdev-dev/
	b. click on Deploys in sidebar -> trigger deploy -> clear cache and deploy site

2.  Development deployed on Intelliateh Godaddy:

    	Godaddy- intelliatech/Intelli@123
	web hosting-> intelliatech.in -> CPannel -> Terminal/File Manager -> 
	-> ishtadev.intelliatech.in
	gitbranch: dev


3. Production on client AWS:

    AWS- dev@vershama.com/Ind@dev#2
Instance- ishtdev
directory: /var/www/ishtdev
gitbranch- production
Database :
DB_HOST= database-1.chgcci8aqrh9.ap-south-1.rds.amazonaws.com
DB_PORT=3306
DB_DATABASE=ishtdev
DB_USERNAME=admin
DB_PASSWORD=ishtdev123
