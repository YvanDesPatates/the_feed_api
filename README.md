# The feed API
similar to Twitter API ... almost similar :)

## install
configure the .env.local to set up the database

run :
- php bin/console make:migration
- php bin/console doctrine:migrations:migrate

warning ! it will erase everything on the table

then run :
-  php bin/console lexik:jwt:generate-keypair

to generate encrypted keys