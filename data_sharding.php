<?php

// Load the configuration
$config = require 'config.php';

// Function to connect to a database
function connect($dsn, $username, $password) {
    try {
        return new PDO($dsn, $username, $password);
    } catch (PDOException $e) {
        die("Connection error: " . $e->getMessage());
    }
}

// Connect to database A
$dbA = connect($config['siteA']['dsn'], $config['siteA']['username'], $config['siteA']['password']);

// Create the users table if it doesn't exist
$createUsersTableQuery = "CREATE TABLE IF NOT EXISTS users (
    name VARCHAR(100),
    age INT
)";
$dbA->exec($createUsersTableQuery);

// Create the users_young table if it doesn't exist
$createUsersYoungTableQuery = "CREATE TABLE IF NOT EXISTS users_young (
    CHECK (age < 18)
) INHERITS (users)";
$dbA->exec($createUsersYoungTableQuery);

// Create the users_old table if it doesn't exist
$createUserOldTableQuery = "CREATE TABLE IF NOT EXISTS users_old (
    CHECK (age >= 18)
) INHERITS (users)";
$dbA->exec($createUserOldTableQuery);

// Create the users_special table if it doesn't exist
$createUserSpecialTableQuery = "CREATE TABLE IF NOT EXISTS users_special (
    CHECK (age >= 18 AND age <= 30)
) INHERITS (users)";
$dbA->exec($createUserSpecialTableQuery);

// Create the users_trigger function
$createTriggerFunction = "CREATE OR REPLACE FUNCTION users_trigger()
RETURNS TRIGGER AS $$
BEGIN
    IF (NEW.age < 18) THEN
        INSERT INTO users_young VALUES (NEW.*);
    END IF;
    IF (NEW.age >= 18) THEN
        INSERT INTO users_old VALUES (NEW.*);
    END IF;
    IF (NEW.age >= 18 AND NEW.age <= 30) THEN
        INSERT INTO users_special VALUES (NEW.*);
    END IF;
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;";
$dbA->exec($createTriggerFunction);

// Create a trigger after inserting into the users table
$createTriggerQuery = "CREATE OR REPLACE TRIGGER insert_users_trigger
BEFORE INSERT ON users FOR EACH ROW
EXECUTE PROCEDURE users_trigger();";
$dbA->exec($createTriggerQuery);

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