<?php
return [
    'siteA' => [
        'dsn' => 'pgsql:host=localhost;port=5432;dbname=sylvanus',
        'username' => 'sylvanus',
        'password' => 'cypher10'
    ],
    'siteB' => [
        'dsn' => 'pgsql:host=hostname_b;port=5432;dbname=dbname_b',
        'username' => 'dbuser',
        'password' => 'passwd_b'
    ],
    'siteC' => [
        'dsn' => 'pgsql:host=hostname_c;port=5432;dbname=dbname_c',
        'username' => 'dbuser',
        'password' => 'passwd_c'
    ],
];
