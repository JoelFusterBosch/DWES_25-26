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
docker cp /mysql/tmp/airports.csv mysql_container:/tmp/airports.csv

mysql> CREATE TABLE aeropuertos (     id INT,     ident VARCHAR(50),     tipo VARCHAR(50),     nombre_aeropuerto VARCHAR(255),     latitud_deg DECIMAL(10,6),     longitud_deg DECIMAL(10,6),     elevacion_ft VARCHAR(10),     continente VARCHAR(10),     iso_pais VARCHAR(10),     iso_region VARCHAR(50),     municipio VARCHAR(100),     servicio_programado VARCHAR(10),     codigo_icao VARCHAR(10),     codigo_iata VARCHAR(10),     codigo_gps VARCHAR(10),     codigo_local VARCHAR(10),     link_inicio TEXT,     link_wikipedia TEXT,     
palabras_clave TEXT );

mysql> LOAD DATA INFILE '/var/lib/mysql-files/airports.csv' INTO TABLE aeropuertos CHARACTER SET utf8mb4 FIELDS TERMINATED BY ','  OPTIONALLY ENCLOSED BY '"'  LINES TERMINATED BY '\r\n' IGNORE 1 LINES;