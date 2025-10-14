# DWES_25-26

## Solucions al crear el docker-compose si apareixen problemes
### No apareix l'usuari
En la terminal en el docker accedim al docker de la següent forma:

```bash
# Canvia "slim_mysql" pel nom del contenidor de la base de dades 
docker exec -it slim_mysql mysql -u root -p
```

Si voleu vore els usuaris que teniu en la base de dades els podeu vore amb este comand:
```bash
select user.user, user.host from mysql.user;
```

Si en el cas de que no aparega el usuari, en este el usuari `alumno` el crearem de la següent forma:

```bash
# Crea l'usuari "alumno" amb la contrasenya "alumno"
CREATE USER 'alumno'@'%' IDENTIFIED BY 'alumno';

#Li dona els permisos al usuari "alumno"
GRANT ALL PRIVILEGES ON *.* TO 'alumno'@'%';
GRANT ALL PRIVILEGES ON *.* to 'alumno'@'%' WITH GRANT OPTION;
FLUSH PRIVILEGES;
```

I ja estaria creat l'usuari "alumno" amb els permisos necessaris

### No s'ha creat la base de dades

Si al anar a la web i voss dona error paregut a 
```bash
Unknown databse 'test'
```

Simplement entrem al conenedor de mysql:
```bash
docker exec -it slim_mysql mysql -u root -p
```

I el crearem la base de dades de la següent forma:
```bash
CREATE DATABASE test;
```