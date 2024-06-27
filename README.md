1.Add .env and .htaccess at the root location of the project
2.Update DB credentials at the env file. 
	DB_CONNECTION=<your-database-connection>
	DB_HOST=<your-database-host>
	DB_PORT=<your-database-port>
	DB_DATABASE=<your-database-name>
	DB_USERNAME=<your-database-username>
	DB_PASSWORD=<your-database-password> 
3.Run the following command to install the project dependencies using Composer: 
composer install
4.Generate an application key by running the following command: 
php artisan key:generate
5.Run the following command to run the database migrations and create the necessary tables:
php artisan migrate
6.Run the seeders for dummy data, you can run them using the following command:
php artisan db:seed
