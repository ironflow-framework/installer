<?php

namespace IronFlow\Installer\Questions;

use Symfony\Component\Console\Style\SymfonyStyle;

class DatabaseQuestion
{
   protected $io;
   protected $availableDrivers = [
      'mysql' => 'MySQL',
      'pgsql' => 'PostgreSQL',
      'sqlite' => 'SQLite',
      'sqlsrv' => 'SQL Server'
   ];

   public function __construct(SymfonyStyle $io)
   {
      $this->io = $io;
   }

   /**
    * Pose les questions concernant la base de données et retourne la configuration
    * 
    * @return array Configuration de la base de données
    */
   public function ask(): array
   {
      $this->io->section('Configuration de la base de données');

      // Demander le type de base de données
      $driver = $this->io->choice(
         'Quel système de base de données souhaitez-vous utiliser?',
         $this->availableDrivers,
         'mysql'
      );

      // Configuration de base
      $config = [
         'driver' => $driver
      ];

      // Si SQLite est sélectionné, la configuration est simplifiée
      if ($driver === 'sqlite') {
         $useDatabaseFile = $this->io->confirm('Souhaitez-vous utiliser un fichier SQLite spécifique?', false);

         if ($useDatabaseFile) {
            $database = $this->io->ask('Chemin du fichier de base de données', 'database/database.sqlite');
         } else {
            $database = 'database/database.sqlite';
         }

         $config['database'] = $database;

         // Créer le répertoire si nécessaire
         $databaseDir = dirname($database);
         if (!file_exists($databaseDir)) {
            $this->io->note("Le répertoire $databaseDir sera créé automatiquement.");
         }
      } else {
         // Configuration pour les bases de données relationnelles
         $config['host'] = $this->io->ask('Hôte de la base de données', 'localhost');

         // Port par défaut selon le driver
         $defaultPort = [
            'mysql' => '3306',
            'pgsql' => '5432',
            'sqlsrv' => '1433'
         ][$driver] ?? '3306';

         $config['port'] = $this->io->ask('Port', $defaultPort);
         $config['database'] = $this->io->ask('Nom de la base de données', 'ironflow');
         $config['username'] = $this->io->ask('Nom d\'utilisateur', 'root');
         $config['password'] = $this->io->askHidden('Mot de passe (ne sera pas affiché)');

         // Options supplémentaires pour certains drivers
         if ($driver === 'mysql') {
            $config['charset'] = $this->io->ask('Charset', 'utf8mb4');
            $config['collation'] = $this->io->ask('Collation', 'utf8mb4_unicode_ci');
         }
      }

      // Demander si une préfixe de table est souhaitée
      $usePrefix = $this->io->confirm('Souhaitez-vous utiliser un préfixe pour les tables?', false);
      if ($usePrefix) {
         $config['prefix'] = $this->io->ask('Préfixe des tables', 'if_');
      } else {
         $config['prefix'] = '';
      }

      return $config;
   }

   /**
    * Retourne la liste des drivers disponibles
    * 
    * @return array
    */
   public function getAvailableDrivers(): array
   {
      return $this->availableDrivers;
   }
}
