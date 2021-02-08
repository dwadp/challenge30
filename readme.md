### Setup the project
1. Copy this project to your web server public directory
2. Create a new database and import the database structure using the `dump/challenge.sql` file
3. Open terminal and type `cd to/project/folder` and run `composer dump-autoload -o`

### Project Configuration
1. Copy all file that has suffix `example.php` and paste it without the suffix. Or you can run the command below individually:

```sh
cp ./config/app-example.php ./config/app.php
```

```sh
cp ./config/database-example.php ./config/database.php
```

```sh
cp ./config/view-example.php ./config/view.php
```

2. Open `config/app.php` file and change the `baseUrl` based on your local development, for example: `http://localhost/challenge`, or you can leave it empty
3. Open `config/database.php` file and change the `host`, `name`, `username` and `password` according to your local running database
4. Open `config/view.php` and adjust the views path according to your local environment

Finally open the browser and access http://localhost/project or use the base url that you've set previously.