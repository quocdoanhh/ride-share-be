# Introduction

A Legacy PHP development environment on Docker.

- Laravel 11.x
- Php-fpm 8.2
- Nginx 1.19.0
- PostgreSQL 15.x

# Getting Started

*Note: Above change only need on first install project. If your application decided that no need unit test, you can comment out it on docker/entrypoint.sh file*

After that, lets following below command to build the docker containers:

*Note: If you are using M1 or M2 chip, let using this command `export DOCKER_DEFAULT_PLATFORM=linux/amd64` to set up docker compatible with your OS before building the containers.*

```shell
docker-compose build
```

Waiting for a while to finish building containers. Then start run containers.
```shell
docker-compose up -d
```

You should be seen all containers state is `up`

#### Setup laravel
Open workspace container then install composer for project
```shell
docker exec -it rs_app bash
```

```shell
composer install
```

Directory permissions to `bootstrap/cache` and `storage` folder.
```shell
chmod -R 775 bootstrap/cache/ storage/
```

Make sure root folder has `.env` file and `APP_KEY` has been set. If not please using this command to add them.
```shell
php artisan key:generate
```

Open browser and type `api.localhost` then it should be load successful.

## Requirements

- [Git](https://git-scm.com/downloads)
- [Docker](https://store.docker.com/editions/community/docker-ce-desktop-mac)
- [Laravel Framework](https://laravel.com/docs)

# Twilio Setup for RideShare

## Step 1: Create Twilio Account
1. Sign up for an account at [Twilio.com](https://www.twilio.com)
2. Get your Account SID and Auth Token from the Dashboard
3. Purchase a Twilio phone number to send SMS

## Step 2: Configure Environment Variables
Add the following environment variables to your `.env` file:

```env
# Twilio Configuration
TWILIO_ACCOUNT_SID=your_account_sid_here
TWILIO_AUTH_TOKEN=your_auth_token_here
TWILIO_FROM=+1234567890
```

## Step 3: Install Package
The `laravel-notification-channels/twilio` package is already installed in `composer.json`.

## Step 4: Test Configuration
Run the following command to test the configuration:
```bash
php artisan tinker
```

In tinker, test:
```php
$user = App\Models\User::first();
$user->notify(new App\Notifications\LoginVerification());
```

## Notes
- Make sure the phone number in `TWILIO_FROM` is a valid Twilio number
- Recipient phone numbers must be in international format (e.g., +84...)
- In development environment, you can use Twilio Test Credentials
