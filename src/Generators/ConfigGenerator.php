<?php

namespace IronFlow\Installer\Generators;

class ConfigGenerator
{
   protected $projectRoot;
   protected $selectedModules;
   protected $dbConfig;
   protected $appConfig;

   /**
    * Constructeur
    * 
    * @param string $projectRoot Chemin racine du projet
    * @param array $selectedModules Modules sélectionnés
    * @param array $dbConfig Configuration de la base de données
    * @param array $appConfig Configuration de l'application
    */
   public function __construct(string $projectRoot, array $selectedModules, array $dbConfig, array $appConfig = [])
   {
      $this->projectRoot = $projectRoot;
      $this->selectedModules = $selectedModules;
      $this->dbConfig = $dbConfig;
      $this->appConfig = $appConfig;
   }

   /**
    * Génère tous les fichiers de configuration
    */
   public function generate(): void
   {
      $this->ensureConfigDirectoryExists();

      // Génération des fichiers de configuration de base
      $this->generateAppConfig();
      $this->generateDatabaseConfig();

      // Génération des configurations spécifiques aux modules
      foreach (array_keys($this->selectedModules) as $module) {
         $methodName = 'generate' . ucfirst($module) . 'Config';
         if (method_exists($this, $methodName)) {
            $this->$methodName();
         }
      }
   }

   /**
    * S'assure que le répertoire de configuration existe
    */
   protected function ensureConfigDirectoryExists(): void
   {
      $configDir = $this->projectRoot . '/config';

      if (!is_dir($configDir)) {
         mkdir($configDir, 0755, true);
      }
   }

   /**
    * Génère le fichier de configuration de l'application
    */
   protected function generateAppConfig(): void
   {
      $appName = $this->appConfig['name'] ?? 'IronFlow Application';
      $debugMode = $this->appConfig['debug'] ?? true;
      $timezone = $this->appConfig['timezone'] ?? 'UTC';
      $locale = $this->appConfig['locale'] ?? 'fr';

      $content = <<<PHP
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Nom de l'application
    |--------------------------------------------------------------------------
    |
    | Ce nom est utilisé dans les journaux, notifications et autres endroits
    | où l'identité de l'application est nécessaire.
    |
    */
    'name' => '{$appName}',

    /*
    |--------------------------------------------------------------------------
    | Mode de débogage
    |--------------------------------------------------------------------------
    |
    | Lorsque votre application est en mode de débogage, les messages d'erreur
    | détaillés avec les traces de pile seront affichés pour chaque erreur.
    | Si désactivé, une page d'erreur générique sera affichée.
    |
    */
    'debug' => {$debugMode},

    /*
    |--------------------------------------------------------------------------
    | Fuseau horaire de l'application
    |--------------------------------------------------------------------------
    |
    | Ici vous pouvez spécifier le fuseau horaire par défaut pour votre
    | application, qui sera utilisé par les fonctions PHP de date et heure.
    |
    */
    'timezone' => '{$timezone}',

    /*
    |--------------------------------------------------------------------------
    | Locale de l'application
    |--------------------------------------------------------------------------
    |
    | La locale de l'application détermine la langue par défaut utilisée
    | par le framework pour les messages, les validations, et le formatage
    | des nombres, des dates, etc.
    |
    */
    'locale' => '{$locale}',

    /*
    |--------------------------------------------------------------------------
    | Prestataires de services
    |--------------------------------------------------------------------------
    |
    | Les prestataires de services sont enregistrés dans l'application lors
    | du démarrage. Vous pouvez ajouter vos propres prestataires ici.
    |
    */
    'providers' => [
        // Prestataires du framework
        IronFlow\\Kernel\\Providers\\KernelServiceProvider::class,
        IronFlow\\Routing\\Providers\\RoutingServiceProvider::class,
        
PHP;

      // Ajouter les providers des modules sélectionnés
      foreach ($this->selectedModules as $key => $module) {
         $className = ucfirst($key);
         $content .= "        IronFlow\\{$className}\\Providers\\{$className}ServiceProvider::class,\n";
      }

      $content .= <<<PHP
        
        // Prestataires de l'application
        App\\Providers\\AppServiceProvider::class,
    ],
];
PHP;

      file_put_contents($this->projectRoot . '/config/app.php', $content);
   }

   /**
    * Génère le fichier de configuration de la base de données
    */
   protected function generateDatabaseConfig(): void
   {
      $driver = $this->dbConfig['driver'];
      $content = <<<PHP
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Connexion de base de données par défaut
    |--------------------------------------------------------------------------
    |
    | Ici vous pouvez spécifier quelle connexion de base de données utiliser
    | par défaut. Vous pouvez avoir plusieurs connexions de base de données.
    |
    */
    'default' => '{$driver}',

    /*
    |--------------------------------------------------------------------------
    | Connexions de base de données
    |--------------------------------------------------------------------------
    |
    | Ici sont définies chacune des connexions de base de données pour
    | votre application. Des exemples de configuration pour chaque type
    | de base de données supporté sont fournis.
    |
    */
    'connections' => [

PHP;

      // Ajouter la configuration de MySQL si nécessaire
      if ($driver === 'mysql') {
         $content .= $this->getMySqlConfig();
      }

      // Ajouter la configuration de PostgreSQL si nécessaire
      if ($driver === 'pgsql') {
         $content .= $this->getPostgreSqlConfig();
      }

      // Ajouter la configuration de SQLite si nécessaire
      if ($driver === 'sqlite') {
         $content .= $this->getSqliteConfig();
      }

      // Ajouter la configuration de SQL Server si nécessaire
      if ($driver === 'sqlsrv') {
         $content .= $this->getSqlServerConfig();
      }

      $content .= <<<PHP
    ],

    /*
    |--------------------------------------------------------------------------
    | Préfixe des tables
    |--------------------------------------------------------------------------
    |
    | Vous pouvez définir un préfixe pour les tables de votre application.
    | Cela est utile si vous avez plusieurs applications qui partagent
    | la même base de données.
    |
    */
    'prefix' => '{$this->dbConfig['prefix']}',
];
PHP;

      file_put_contents($this->projectRoot . '/config/database.php', $content);
   }

   /**
    * Obtient la configuration spécifique à MySQL
    */
   protected function getMySqlConfig(): string
   {
      $host = $this->dbConfig['host'];
      $port = $this->dbConfig['port'];
      $database = $this->dbConfig['database'];
      $username = $this->dbConfig['username'];
      $password = $this->dbConfig['password'];
      $charset = $this->dbConfig['charset'];
      $collation = $this->dbConfig['collation'];

      return <<<PHP
        'mysql' => [
            'driver' => 'mysql',
            'host' => '{$host}',
            'port' => {$port},
            'database' => '{$database}',
            'username' => '{$username}',
            'password' => '{$password}',
            'charset' => '{$charset}',
            'collation' => '{$collation}',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

PHP;
   }

   /**
    * Obtient la configuration spécifique à PostgreSQL
    */
   protected function getPostgreSqlConfig(): string
   {
      $host = $this->dbConfig['host'];
      $port = $this->dbConfig['port'];
      $database = $this->dbConfig['database'];
      $username = $this->dbConfig['username'];
      $password = $this->dbConfig['password'];

      return <<<PHP
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => '{$host}',
            'port' => {$port},
            'database' => '{$database}',
            'username' => '{$username}',
            'password' => '{$password}',
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

PHP;
   }

   /**
    * Obtient la configuration spécifique à SQLite
    */
   protected function getSqliteConfig(): string
   {
      $database = $this->dbConfig['database'];

      return <<<PHP
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => '{$database}',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ],

PHP;
   }

   /**
    * Obtient la configuration spécifique à SQL Server
    */
   protected function getSqlServerConfig(): string
   {
      $host = $this->dbConfig['host'];
      $port = $this->dbConfig['port'];
      $database = $this->dbConfig['database'];
      $username = $this->dbConfig['username'];
      $password = $this->dbConfig['password'];

      return <<<PHP
        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'host' => '{$host}',
            'port' => {$port},
            'database' => '{$database}',
            'username' => '{$username}',
            'password' => '{$password}',
            'charset' => 'utf8',
            'prefix' => '',
        ],

PHP;
   }

   /**
    * Génère la configuration pour le module d'authentification
    */
   protected function generateAuthConfig(): void
   {
      $content = <<<PHP
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Modèle Utilisateur
    |--------------------------------------------------------------------------
    |
    | Ici vous pouvez spécifier quelle classe modèle utiliser pour
    | l'authentification. Par défaut, c'est le modèle User.
    |
    */
    'model' => App\\Models\\User::class,

    /*
    |--------------------------------------------------------------------------
    | Options d'authentification
    |--------------------------------------------------------------------------
    |
    | Vous pouvez configurer ici les différentes méthodes d'authentification
    | que vous souhaitez utiliser dans votre application.
    |
    */
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        
        'api' => [
            'driver' => 'jwt',
            'provider' => 'users',
            'ttl' => 60, // minutes
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fournisseurs d'utilisateurs
    |--------------------------------------------------------------------------
    |
    | Cette configuration définit comment les utilisateurs sont stockés et
    | récupérés pour l'authentification.
    |
    */
    'providers' => [
        'users' => [
            'driver' => 'orm',
            'model' => App\\Models\\User::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Options de réinitialisation de mot de passe
    |--------------------------------------------------------------------------
    |
    | Ici, vous pouvez configurer les options pour la réinitialisation
    | des mots de passe, comme la durée de validité des tokens.
    |
    */
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_resets',
            'expire' => 60, // minutes
            'throttle' => 60, // minutes
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Options de JWT
    |--------------------------------------------------------------------------
    |
    | Configuration pour l'authentification JWT (JSON Web Token).
    |
    */
    'jwt' => [
        'secret' => env('JWT_SECRET'),
        'ttl' => 60, // minutes
        'refresh_ttl' => 20160, // minutes (2 semaines)
        'algo' => 'HS256',
    ],
];
PHP;

      file_put_contents($this->projectRoot . '/config/auth.php', $content);
   }

   /**
    * Génère la configuration pour le module de cache
    */
   protected function generateCacheConfig(): void
   {
      $content = <<<PHP
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Driver de cache par défaut
    |--------------------------------------------------------------------------
    |
    | Vous pouvez spécifier quel driver de cache utiliser par défaut dans
    | toute votre application.
    |
    | Supported: "file", "array", "redis", "memcached"
    |
    */
    'default' => env('CACHE_DRIVER', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Magasins de cache
    |--------------------------------------------------------------------------
    |
    | Ici vous pouvez définir tous les magasins de cache qui seront utilisés
    | par l'application. Des exemples de configuration sont fournis.
    |
    */
    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
        ],

        'array' => [
            'driver' => 'array',
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
        ],

        'memcached' => [
            'driver' => 'memcached',
            'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
            'sasl' => [
                env('MEMCACHED_USERNAME'),
                env('MEMCACHED_PASSWORD'),
            ],
            'options' => [
                // Memcached::OPT_CONNECT_TIMEOUT => 2000,
            ],
            'servers' => [
                [
                    'host' => env('MEMCACHED_HOST', '127.0.0.1'),
                    'port' => env('MEMCACHED_PORT', 11211),
                    'weight' => 100,
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Préfixe de clé de cache
    |--------------------------------------------------------------------------
    |
    | Lors de l'utilisation d'un magasin de cache, il est souvent utile de
    | préfixer les clés afin d'éviter les collisions avec d'autres applications
    | utilisant le même cache.
    |
    */
    'prefix' => env('CACHE_PREFIX', 'ironflow_cache'),

    /*
    |--------------------------------------------------------------------------
    | Durée de vie par défaut du cache
    |--------------------------------------------------------------------------
    |
    | Temps en secondes pendant lequel les éléments du cache seront conservés
    | par défaut. Une valeur de 0 signifie pas d'expiration.
    |
    */
    'ttl' => env('CACHE_TTL', 3600),
];
PHP;

      file_put_contents($this->projectRoot . '/config/cache.php', $content);
   }

   /**
    * Génère la configuration pour le module de gestion des fichiers
    */
   protected function generateFileConfig(): void
   {
      $content = <<<PHP
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Disque de stockage par défaut
    |--------------------------------------------------------------------------
    |
    | Ici, vous pouvez spécifier le disque de stockage par défaut qui sera
    | utilisé par le framework. La valeur "local" pointe vers le disque local.
    |
    */
    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Disques de stockage
    |--------------------------------------------------------------------------
    |
    | Ici, vous pouvez configurer autant de "disques" que vous le souhaitez,
    | et vous pouvez même configurer plusieurs disques du même driver.
    |
    */
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        'uploads' => [
            'driver' => 'local',
            'root' => resource_path('uploads'),
            'url' => env('APP_URL').'/uploads',
            'visibility' => 'private',
            'throw' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Types de fichiers autorisés
    |--------------------------------------------------------------------------
    |
    | Liste des types MIME autorisés pour les téléchargements.
    |
    */
    'allowed_types' => [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
    ],

    /*
    |--------------------------------------------------------------------------
    | Taille maximale des fichiers
    |--------------------------------------------------------------------------
    |
    | Taille maximale des fichiers téléchargés en octets.
    | Par défaut: 7 Mo (7 * 1024 * 1024)
    |
    */
    'max_size' => 7 * 1024 * 1024,
];
PHP;

      file_put_contents($this->projectRoot . '/config/filesystems.php', $content);
   }
}
