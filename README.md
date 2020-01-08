# Glamorous Cheetah

This is a POC for a directory application running with a Neo4j back end.

## Up and running

```
$ git clone https://github.com/hcpss-banderson/glamorous_cheetah.git
$ cd glamorous_cheetah
$ cp .env.dist .env
$ docker-compose up -d
$ docker exec directory_web composer install
$ docker exec directory_web ./bin/console app:data:refresh
$ docker exec directory_web ./bin/console app:data:index
```

The only parts of the application that work are the department listing and
individual pages:

http://localhost:9090/departments \
http://localhost:9090/department/operations

The Neo4j browser is at: http://localhost:7474
