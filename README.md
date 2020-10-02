# PHP Project (API banco)

## Instalação

* Instalação dos pacotes a partir do composer:
```
composer install
```

* Instalação do [JWT](https://github.com/tymondesigns/jwt-auth) (necessário para autenticação):
```
php artisan jwt:secret
```

* Banco de dados:
```
php artisan migrate:fresh --seed
```