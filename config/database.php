<?php

// NOTE: this function *must not* throw exceptions, otherwise Laravel will fail
// to boot. So, instead of throwing exceptions, we just return an intentionally
// invalid (empty) configuration.
function generateAptibleConnection() {
  if (getenv('DB_CONNECTION') !== 'aptible') {
    // If the DB_CONNECTION is not Aptible, then this won't be used, and we
    // should just bail out.
    return [];
  }

  $raw_url = getenv('DATABASE_URL');
  if (!$raw_url) {
    error_log('DB_CONNECTION is aptible, but DATABASE_URL is not set!');
    return [];
  }

  $url = parse_url($raw_url);
  $aptibleConnection = [
    'host'      => $url["host"],
    'port'      => $url["port"],
    'username'  => $url["user"],
    'password'  => $url["pass"],
    'database'  => substr($url["path"], 1),
    'charset'   => 'utf8',
    'prefix'    => '',
  ];

  $scheme = $url["scheme"];

  if ($scheme === "mysql") {
    // NOTE: PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT is a non-standard option
    // provided by the quay.io/aptible/php Docker image. If you're using
    // another image, this won't work (of course, if you're using Postgres,
    // that's not a problem).
    // View https://bugs.php.net/bug.php?id=71003 for more information.
    $aptibleConnection['driver'] = 'mysql';
    $aptibleConnection['collation'] = 'utf8_unicode_ci';
    $aptibleConnection['options'] = [
      PDO::MYSQL_ATTR_SSL_CIPHER => 'DHE-RSA-AES256-SHA',
      PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];
  } elseif ($scheme === "postgresql") {
    $aptibleConnection['driver'] = 'pgsql';
    $aptibleConnection['schema'] = 'public';
  } else {
    error_log("DB_CONNECTION is aptible and DATABASE_URL is set, but the scheme '$scheme' is invalid!");
    return [];
  }

  return $aptibleConnection;
}

return [

    /*
    |--------------------------------------------------------------------------
    | PDO Fetch Style
    |--------------------------------------------------------------------------
    |
    | By default, database results will be returned as instances of the PHP
    | stdClass object; however, you may desire to retrieve records in an
    | array format for simplicity. Here you can tweak the fetch style.
    |
    */

    'fetch' => PDO::FETCH_CLASS,

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver'   => 'sqlite',
            'database' => database_path('database.sqlite'),
            'prefix'   => '',
        ],

        'mysql' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', 'localhost'),
            'database'  => env('DB_DATABASE', 'forge'),
            'username'  => env('DB_USERNAME', 'forge'),
            'password'  => env('DB_PASSWORD', ''),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
            'engine'    => null,
        ],

        'pgsql' => [
            'driver'   => 'pgsql',
            'host'     => env('DB_HOST', 'localhost'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset'  => 'utf8',
            'prefix'   => '',
            'schema'   => 'public',
        ],

        'sqlsrv' => [
            'driver'   => 'sqlsrv',
            'host'     => env('DB_HOST', 'localhost'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset'  => 'utf8',
            'prefix'   => '',
        ],
        'aptible' => generateAptibleConnection(),
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'cluster' => false,

        'default' => [
            'host'     => env('REDIS_HOST', 'localhost'),
            'password' => env('REDIS_PASSWORD', null),
            'port'     => env('REDIS_PORT', 6379),
            'database' => 0,
        ],

    ],

];
