# APRES AVOIR CREER VOTRE UTILISATEUR

# INSTALLER L'EXTENSION posgres_postgres_fwd

CREATE EXTENSION IF NOT EXISTS postgres_fwd;

# Pour chacun des sites, creer la table étrangère (a faire sur chaque site) pointant vers 
#la table que je vous aurais attribuer
# NB CECI EST UN EXEMPLE

CREATE SERVER server_name FOREIGN DATA WRAPPER postgres_fdw OPTIONS (host 'ip_adresse', port '5432', dbname 'sylvanus');

# CREER UN UTILISATEUR POUR GERER LA TABLE ETRANGÈRES

CREATE USER MAPPING FOR <votre_utilisateur> SERVER server_name OPTIONS (user 'sylvanus', password 'cypher10');

CREATE FOREIGN TABLE <nom_table>_<nom_site> (
    id SERIAL,
    name VARCHAR(50),
    age INT
) SERVER server_name
OPTIONS (schema_name 'public', table_name '<nom_de_la_table_pointée');

CREATE FOREIGN TABLE users_foreign (
    id SERIAL,
    name VARCHAR(50),
    age INT
) SERVER server_name
OPTIONS (schema_name 'public', table_name 'master');