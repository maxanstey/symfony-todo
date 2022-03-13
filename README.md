# Getting started

Run the below commands:

  ```
git clone git@github.com:maxanstey/symfony-todo.git
cd symfony-todo
cp .env.example .env
composer install
npm i
npm run dev
bin/console doctrine:migrations:migrate
bin/phpunit
symfony server:start
```

Start playing!

