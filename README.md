# Lumen PHP Framework

[![Build Status](https://travis-ci.org/laravel/lumen-framework.svg)](https://travis-ci.org/laravel/lumen-framework)
[![Total Downloads](https://poser.pugx.org/laravel/lumen-framework/d/total.svg)](https://packagist.org/packages/laravel/lumen-framework)
[![Latest Stable Version](https://poser.pugx.org/laravel/lumen-framework/v/stable.svg)](https://packagist.org/packages/laravel/lumen-framework)
[![License](https://poser.pugx.org/laravel/lumen-framework/license.svg)](https://packagist.org/packages/laravel/lumen-framework)

Laravel Lumen is a stunningly fast PHP micro-framework for building web applications with expressive, elegant syntax. We believe development must be an enjoyable, creative experience to be truly fulfilling. Lumen attempts to take the pain out of development by easing common tasks used in the majority of web projects, such as routing, database abstraction, queueing, and caching.

## Official Documentation

Documentation for the framework can be found on the [Lumen website](https://lumen.laravel.com/docs).

## Setup

**Step 1** - Clone this project into a directory

```
https://github.com/shamscorner/API-starter-lumen.git
```

**Step 2** - Clone the **Laradock** module in that same directory

```
https://github.com/laradock/laradock.git
```

So the directory structure will be something similar like this,

-   SomeDirectory
    -   laradock
    -   API-base-lumen

**Step 3** - Copy the `env-example` file to `.env` in laradock directory

```
cd laradock
cp env-example .env
```

**Step 4** - Change compose path separator if the operating system is Windows in `.env` file inside the `laradock` folder

```
# Change the separator from : to ; on Windows (Only for Windows Users)
COMPOSE_PATH_SEPARATOR=;
```

**Step 5** - Add the Site URL to the `host` file in the local machine

```
127.0.0.1	api-base-lumen.test
```

**Step 6** - Run the `Docker` containers

```
docker-compose up -d nginx mysql phpmyadmin
```

**Step 7** - Create database

Visit `http://localhost:8080` and login with the following credentials,

```
server: mysql
username: root
password: root
```

**Step 8** - Execute the workspace container

```
docker-compose exec workspace bash
```

**Step 9** - Copy the `.env.example` file to `.env` in

```
cd API-base-lumen
cp .env.example .env
```

**Step 10** - Install `Composer`

```
composer install
```

**Step 11** - Generate application key (It may not work for Lumen, so put any random string as key in the .env file)

```
php artisan key:generate
```

**Step 12** - Run Migration

```
php artisan migrate
```

Happy Coding :)

## To Test if working or not

**Step 1** - Hit this endpoint from Postman with the following body (replace with your own domain)

```
http://api-base-lumen.test/register
```

body,

```
{
	"name": "John Doe",
	"email": "john@example.com",
	"password": "john@1234"
}
```

response (token will be different),

```
{
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiMWFmMDhmYWQyODM3OTcxMjhlYjAxMGQzNmE4NGZiZWVjMmI0NTU4N2IxMDkyZjdkN2IwNzcwZWNhNWE0YjJlNzFjZTc2YTVmNjM3ZTk4N2QiLCJpYXQiOjE1OTc3NjU2ODcsIm5iZiI6MTU5Nzc2NTY4NywiZXhwIjoxNjI5MzAxNjg3LCJzdWIiOiIxIiwic2NvcGVzIjpbXX0.atSlbSIupt0gRuwG22LPa4g1u2dyAulVgfrCsmRtlIVFF0lv-TKE8Fpcc0otRQrwhTXbKm7c19QYh3l4FfbMdDAsmoRG18l4PGqRZ1_Lq3UOtTQoavzZldNJ8Pvmz67YHfP9iVzmahEmS0l_aZkahLV7v_Zi1IO_soF8ogM8oL-kYDV358Wk9k-sM9T4BuUOS1QQ29Ww0VfBNL_YYtpAfU6dZkHispPz1f-SjuipsNtvf-Chd7lcEQyZogsGLvdOc9RNzLdHE3zMPUSkW_GStDaJSIG2RpkBQmCqfT_2yI0SNwBWPbOmEJya_CbNPuhLZGfEJyZ-PNjRuVz9E_6tgyZLnYrnS8HM4J4A9MVOCmYsl2nKQ-tZT0k5RAaFxn78Zt1jWViVzs-h1Lbimb9XpWMkFzwru0POrgZEYtJsFHbBeOz7pQWoDyhUh_HSHYuJzoafjRuTLSacduMKhmd5dt8_J0VIwizy6KXAp2Xikw4-nDIcB3XgbDAPfgLxtMp3YrChbowsNKG8R7Z-D9ST5iVuxBWRmsVoGY-hjW62rGatqyO35yewpFy1pA9OssW3jYHiVLoABRBbWdGc5jEQRMq44jM07GTGNOXGtJhDtP9BEd0Jl8VsofsHK2STKJo8Y6Bvsakanw4LEv1x3qgVVL5AYYJQyVTgYVgbHcuQsuM",
    "name": "John Doe"
  },
  "message": "Account created successfully!",
  "status": true
}
```

**Step 2** - Hit the next link as well (replace with your own domain)

```
http://api-base-lumen.test/v1/oauth/token
```

body (replace with your own client id and secret),

```
{
	"grant_type": "password",
	"client_id": 2,
	"client_secret": "tWEqYw8MwfNTMmHbWksiOvTxGMwofA4djayU0OeT",
	"username": "john@example.com",
	"password": "john@1234",
	"scope": ""
}
```

response (token will be different),

```
{
  "token_type": "Bearer",
  "expires_in": 31536000,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIyIiwianRpIjoiYjlhZDEyOGQzZjhiODFjNTE0YTc2OGMzM2JkOTk1OWQ1MmExZGE3ODRiNWZjZWU3NmQ5YTUxYjg1YWU5NGRhMmMwNDhjMmI3MzJlM2YwNTUiLCJpYXQiOjE1OTc3NjY1NjEsIm5iZiI6MTU5Nzc2NjU2MSwiZXhwIjoxNjI5MzAyNTYxLCJzdWIiOiIxIiwic2NvcGVzIjpbXX0.LWwAjgbirJC_kzhhXwAY3p5iA2nkxBMOFHkd8VABPW4wRL539Fb7HtZzEDpnKlQl19ku6P28tAsL31z0S6AgZN1_z2bCmXfTrZ2ZXo7yQlZQIt6mGOMKbu7I1vf6rfQfhUSLCI5qL4Dsij-J-qCbltu67wZseAiRaqhKLg97_4o9dCu2YhF-gGO5Ry-bLB-EBvCZU5MGOov_vWt6ig820JnRQDE_YcBtw05Unnk3kisxzCGcOuA1uWvQgSnT0NDhjhz3_e_z9q06Se3wxcVXK_tWBUSbLZ4TGqbZg-s1hH1QGGGm-oEjL-wpX4IY8bq3cTr3l-PUuVCrNDvyGRzXuQGdvTnc2OKgBQDK1L_v3CIr4Q_axcBniYQp87Gj1gjwJyfOdy5qg284kvNlz-sbjEqlXE0sAjGYrKfCX0Osemia2ZajnonC3gXDwu9I_My_GnOEvVE9vITMI95YxlpZKC7pQUsctT4ezzUw2NupeYPpBgk5VKXBEJxufM6GYfj8Xru4JKRek3CssvS_V_pAjj3h2OccniOmGL2ziqDjQC1xg9f4roZzPG8S3hppqROarVAcqV2wRKDSNJBglB4GQb2UJtItPi-WoLypfZPHpv8O3STXHiuS08xzOuyuEzrMzoCDBPUVZTNIzbR3Tf3KTLlEVJH5F-4PYGnuNMir9kk",
  "refresh_token": "def502003b6943dfb69e6d51bd9402a1d1870b9b15a52c7ee98dbdd3d46618a4fcb73d799f222ba9477f4e40083812c53013e154639794170365f9f3d124602084779cfd391def2f721fae30e27e140834dd4f5b029a8fdbbc20c3b196f6f3f396b5db4ba747b1975e89cceba911b5752ac2501886d2de6b2f8fb6462458fa2024f06bf687dc01f35ed658ef2acf9c67c8bf958e8f6315cab23ef704cae28f096dbc0d7de7137611266c1b7e2f904ae8d45b8d1714a69d3ad972ebe6db5aaf1cceaa99bc5d6dfe8960cb3a815e6d965e41f003fb565ca5c7c2aa0b2ad12e20f8c459aeb34dc12ba99fc95c847308996a08d52c74a83a922040cbc0df8fa95a61adf5bdd16ac1c4e8eb62176b269d597f027f04e58ea67928d706a8c88b8ddcd543e96e10055331fd2a08e117b89be1a7e54a4597fe82be491bef3a94c242e36c40199cf6323d9ad981eb715e9767e78c67a23a1e5218547b9486ef509a4fc2de66"
}
```

If everything works, then you are good to go.

## To setup the development environment

```
php artisan dev:setup
```

## License

The Lumen framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
