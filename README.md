# API Rest Workshop Part 2 - Advanced - **Test with Breeze** #

Breeze is a startup kit for Laravel that can provide out-of-the-box frontend solutions for login, password recovery, and authentication options.

### Installation ###

```
composer require laravel/breeze --dev
```
```
php artisan breeze:install
```
Make sure tables are migrated:
```
$ php artisan migrate:fresh
```
Install and npm run dev
```
 npm install
 ```
 ```
 npm run dev
 ```

 ## Breeze Basics ##

- Go to localhost:8000, and you have options for registering and login, logout, password recovery.

- In the User model, you have a commented line 
```// use Illuminate\Contracts\Auth\MustVerifyEmail;```
  When implemented in the model, it will add the 'auth' and 'verified' parameters to the route in web.php
  
  In User.php model:
  ```
    class User extends Authenticatable implements MustVerifyEmail
    {
      use HasApiTokens, HasFactory, Notifiable;
  ```
  This automatically adds functionality for verifying your account by sending an email requesting confirmation / verification.


## Other Laravel Packages ##

- Cashier (Stripe)
- Dusk (Testing)
- Fortify (stronger authentication, for example 2-factor)
- Laravel Horizon (panel that shows process wait lists, workload, etc.)
- Laravel Scout (search engine functions)
- Telescope (allows you to see queries to database, logging, etc.)
- Socialite (connections with logins at Facebook, Twitter, Google, etc.)




