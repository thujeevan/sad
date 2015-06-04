SAD - Symfony2-Angular-Docker
===

A Symfony project created on June 1, 2015, 10:44 am.

Installation instructions
---

#### Step I - updating deps

1. Clone this repository and navigate to the root directory
2. Install composer
3. Install server deps via `composer install` or `php composer.phar install`
4. Navigate to `web/client` directory
5. Install client side dependencies via `npm install` (assume node and npm were already exist)
6. update DB credentials in parameters.yml ( db, user, pass = **sad** )

#### Step II - Dockerizing

1. Install Docker if necessary
2. Build the container with `sudo docker build -t thujeevan/sad .`
3. Run the container with `sudo docker run -d -p 8080:80 -v /<repo root dir>:/var/www/sad -i -t thujeevan/sad`

#### Step III - Running

1. go to http://localhost:8080/ and enjoy the app
2. you must register first before using address book by clicking `Sign up!` link
3. **NOTE:** in case if something not right, try clearing cache by `rm -rf <app root>\app\cache\* <app root>\app\logs\*` and try again

#### Testing - Server side

1. run `./bin/phpunit -c app/` by being in the root directory

#### Testing - Client side

1. run `./node_modules/.bin/karma start` from `web/client` directory