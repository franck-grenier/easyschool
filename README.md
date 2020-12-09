# Easy School test project

a simple API to manage students and grades

## How to start ?

### Requirements

* Docker and docker-compose (https://www.docker.com/get-started)
* Symfony CLI (https://symfony.com/download)

### 1 - Clone the repo

```shell script
$ git clone git@github.com:franck-grenier/easyschool.git
$ cd API/
```

### 2 - Install dependencies

```shell script
$ symfony composer install
```

### 3 - Setup database and run migrations

```shell script
$ docker-compose up -d
$ symfony console doctrine:migrations:migrate
```

### 4 - Start Symfony HTTP server

```shell script
$ symfony server:start -d
```

### 5 - Play with the up and running API ! 

I provided you with a Postman collection including examples of each available request : `API/easyschool.postman_collection.json`.

The API documentation is available on `/api/doc`.


## TODOs

We could go further on the exercise with more work :  
- Add an authentication (simple login/password or better OAuth) with the security component to protect the personal data of the students
- Setup a more conventional UUID identifier on student entity
- Add versioning to the API for future evolution needs
- Add fixtures with test data to start faster
- Implement unit testing
