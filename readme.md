### Setup the project
1. Copy this project to your web server public directory
2. Create a new database and import the database structure using the `dump/challenge.sql` file
3. Open terminal and type `cd to/project/folder` and run `composer dump-autoload -o`

### Project Configuration
1. Open `config/app.php` file and change the `baseUrl` based on your local development, for example: `http://localhost/challenge`
2. Open `config/database.php` file and change the `host`, `name`, `username` and `password` according to your local running database

Finally open the browser and access http://localhost/project/challenge.php or use the base url that you've set previously.