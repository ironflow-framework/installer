<?php

namespace IronFlow\Installer\Generators;

class EnvGenerator
{
    protected $projectRoot;
    protected $dbConfig;
    protected $appConfig;

    /**
     * Constructeur
     * 
     * @param string $projectRoot Chemin racine du projet
     * @param array $dbConfig Configuration de la base de données
     * @param array $appConfig Configuration de l'application
     */
    public function __construct(string $projectRoot, array $dbConfig, array $appConfig = [])
    {
        $this->projectRoot = $projectRoot;
        $this->dbConfig = $dbConfig;
        $this->appConfig = $appConfig;
    }

    /**
     * Génère le fichier .env
     */
    public function generate(): void
    {
        // Génération d'une clé d'application aléatoire
        $appKey = $this->generateRandomKey();
        
        // Paramètres de base
        $appName = $this->appConfig['name'] ?? 'IronFlow Application';
        $appEnv = $this->appConfig['env'] ?? 'local';
        $appDebug = $this->appConfig['debug'] ?? 'true';
        $appUrl = $this->appConfig['url'] ?? 'http://localhost';
        $appTimezone = $this->appConfig['timezone'] ?? 'UTC';
        $appLocale = $this->appConfig['locale'] ?? 'fr';
        
        $content = <<<ENV
                  APP_NAME="{$appName}"
                  APP_ENV={$appEnv}
                  APP_KEY={$appKey}
                  APP_DEBUG={$appDebug}
                  APP_URL={$appUrl}

                  # Timezone et locale
                  APP_TIMEZONE={$appTimezone}
                  APP_LOCALE={$appLocale}

                  # Configuration de la base de données
                  DB_CONNECTION={$this->dbConfig['driver']}

                  ENV;

           // Ajout des paramètres spécifiques à la base de données
           if ($this->dbConfig['driver'] === 'sqlite') {
               $content .= "DB_DATABASE={$this->dbConfig['database']}\n";
           } else {
               $content .= <<<ENV
               DB_HOST={$this->dbConfig['host']}
               DB_PORT={$this->dbConfig['port']}
               DB_DATABASE={$this->dbConfig['database']}
               DB_USERNAME={$this->dbConfig['username']}
               DB_PASSWORD={$this->dbConfig['password']}
               DB_PREFIX={$this->dbConfig['prefix']}

               ENV;
         }

        // Ajout des paramètres de cache
        $content .= <<<ENV
# Configuration du cache
CACHE_DRIVER=file
CACHE_PREFIX=ironflow_cache
CACHE_TTL=3600

# Configuration des sessions
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Configuration des logs
LOG_CHANNEL=file
LOG_LEVEL=debug

# Configuration des emails
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=null
MAIL_FROM_NAME="$appName"

# Configuration de Redis (si utilisé)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# JWT Secret (pour l'authentification API)
JWT_SECRET={$this->generateRandomKey()}

# Autres configurations
FILESYSTEM_DISK=local
ENV;

        // Écriture du fichier .env
        file_put_contents($this->projectRoot . '/.env', $content);
        
        // Copie vers .env.example avec des valeurs génériques
        $this->generateEnvExample();
    }

    /**
     * Génère une clé aléatoire pour l'application
     * 
     * @return string
     */
    protected function generateRandomKey(): string
    {
        return 'base64:' . base64_encode(random_bytes(32));
    }

    /**
     * Génère le fichier .env.example
     */
    protected function generateEnvExample(): void
    {
        $content = <<<ENV
APP_NAME="IronFlow Application"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

# Timezone et locale
APP_TIMEZONE=UTC
APP_LOCALE=fr

# Configuration de la base de données
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ironflow
DB_USERNAME=root
DB_PASSWORD=
DB_PREFIX=

# Configuration du cache
CACHE_DRIVER=file
CACHE_PREFIX=ironflow_cache
CACHE_TTL=3600

# Configuration des sessions
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Configuration des logs
LOG_CHANNEL=file
LOG_LEVEL=debug

# Configuration des emails
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=null
MAIL_FROM_NAME="IronFlow Application"

# Configuration de Redis (si utilisé)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# JWT Secret (pour l'authentification API)
JWT_SECRET={$this->generateRandomKey()}

# Autres configurations
FILESYSTEM_DISK=local
ENV;

        file_put_contents($this->projectRoot . '/.env.example', $content);
    }
}
