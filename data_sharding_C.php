<?php

// Charger la configuration
$config = require 'config.php';

// Fonction pour se connecter à une base de données
function connect($dsn, $username, $password) {
    try {
        return new PDO($dsn, $username, $password);
    } catch (PDOException $e) {
        die("Erreur de connexion : " . $e->getMessage());
    }
}

// Connexion à la base de données B
$dbB = connect($config['siteB']['dsn'], $config['siteB']['username'], $config['siteB']['password']);

// Créer le serveur distant pour le site A sur le site B
$createServerQuery = "CREATE SERVER site_a
    FOREIGN DATA WRAPPER postgres_fdw
    OPTIONS (dbname '{$config['siteA']['database']}', host '{$config['siteA']['host']}', port '{$config['siteA']['port']}')";
$dbB->exec($createServerQuery);

// Créer l'utilisateur de mapping pour le site B qui se connecte au serveur distant du site A
$createUserMappingQuery = "CREATE USER MAPPING FOR current_user
    SERVER site_a
    OPTIONS (user '{$config['siteA']['username']}', password '{$config['siteA']['password']}')";
$dbB->exec($createUserMappingQuery);

// Créer la table étrangère users_young sur le site B
$createForeignTableQuery = "CREATE FOREIGN TABLE users_young (
    id INT,
    name VARCHAR(100)
) SERVER site_a OPTIONS (table_name 'users_young', foreign_server_name 'site_a')";
$dbB->exec($createForeignTableQuery);

// Créer la table étrangère users sur le site B
$createForeignTableQuery = "CREATE FOREIGN TABLE users (
    id INT,
    age INT,
    name VARCHAR(100)
) SERVER site_a OPTIONS (table_name 'users', foreign_server_name 'site_a')";
$dbB->exec($createForeignTableQuery);

// Fonction pour insérer des données dans la table étrangère users_young sur le site B
function insertYoungUser() {
    global $dbA;
    echo "Entrez le nom d'utilisateur : ";
    $name = trim(fgets(STDIN));
    echo "Entrez l'age : ";
    $age = trim(fgets(STDIN));
    $insertQuery = "INSERT INTO users_young (name, age) VALUES (:name, :age)";
    $stmt = $dbA->prepare($insertQuery);
    $stmt->execute(['name' => $name, 'age' => $age]);
}

// Fonction pour lire toutes les données de la table users sur le site B
function getAllUsers() {
    global $dbA;
    $selectQuery = "SELECT * FROM users";
    $stmt = $dbA->query($selectQuery);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $users;
}

do {
    echo "MENU\n";
    echo "1- Ajouter un nouvel utilisateur\n";
    echo "2- Afficher tous les utilisateurs\n";
    echo "3- Quitter\n";
    echo "Votre choix : ";
    $choice = trim(fgets(STDIN));
    switch ($choice) {
        case '1':
            insertYoungUser();
            echo 'utilisateur bien enregistré';
            break;
        
        case '2':
            $allUsers = getAllUsers();
            foreach ($allUsers as $user) {
                echo "Name:" . $user['name'] . ", Age: " . $user['age'] . "\n";
            }
            break;
        
        default:
            echo 'Recommencez\n';
            break;
    }
} while ($choice !=3);

?>