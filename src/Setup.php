<?php

namespace IronFlow\Installer;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

class Setup
{
   protected $io;
   protected $selectedModules = [];
   protected $projectRoot;

   protected $dbConfig = [];

   public static function start()
   {
      $setup = new static();
      $setup->run();
   }

   public function run()
   {
      $this->projectRoot = dirname(dirname(dirname(__DIR__)));

      // Création de l'interface de console
      $input = new ArgvInput();
      $output = new ConsoleOutput();
      $this->io = new SymfonyStyle($input, $output);

      $this->io->title('IronFlow Framework - Installation interactive');

      $this->askForModules();
      $this->askForDatabaseConfig();
      $this->installSelectedModules();
      $this->generateConfigFiles();

      $this->io->success('IronFlow Framework a été installé avec succès!');
   }

   protected function askForModules()
   {
      $this->io->section('Sélection des modules');

      $modules = [
         'auth' => 'Système d\'authentification',
         'admin' => 'Panneau d\'administration',
         'api' => 'Support API RESTful',
         'cache' => 'Système de cache avancé',
         'file' => 'Gestion des fichiers'
      ];

      $this->selectedModules = $this->io->choice(
         'Sélectionnez les modules que vous souhaitez installer (espace pour sélectionner, entrée pour valider)',
         $modules,
         null,
         true
      );
   }

   protected function askForDatabaseConfig()
   {
      $this->io->section('Configuration de la base de données');

      $dbDriver = $this->io->choice(
         'Quel système de base de données souhaitez-vous utiliser?',
         ['mysql', 'pgsql', 'sqlite'],
         'mysql'
      );

      // Stocker la configuration pour plus tard
      $this->dbConfig = [
         'driver' => $dbDriver
      ];

      if ($dbDriver !== 'sqlite') {
         $this->dbConfig['host'] = $this->io->ask('Hôte de la base de données', 'localhost');
         $this->dbConfig['port'] = $this->io->ask('Port', '3306');
         $this->dbConfig['database'] = $this->io->ask('Nom de la base de données', 'ironflow');
         $this->dbConfig['username'] = $this->io->ask('Utilisateur');
         $this->dbConfig['password'] = $this->io->askHidden('Mot de passe');
      }
   }

   protected function installSelectedModules()
   {
      $this->io->section('Installation des modules sélectionnés');

      foreach ($this->selectedModules as $module) {
         $this->io->text("Installation du module: $module");

         $process = new Process([
            'composer',
            'require',
            "ironflow/$module",
            '--no-scripts'
         ], $this->projectRoot);

         $process->run(function ($type, $buffer) {
            echo $buffer;
         });
      }
   }

   protected function generateConfigFiles()
   {
      $this->io->section('Génération des fichiers de configuration');

      // Génération du fichier .env
      $envGenerator = new Generators\EnvGenerator($this->projectRoot, $this->dbConfig);
      $envGenerator->generate();

      // Génération des fichiers de configuration
      $configGenerator = new Generators\ConfigGenerator(
         $this->projectRoot,
         $this->selectedModules,
         $this->dbConfig,
         [
            'name' => 'IronFlow Application',
            'env' => 'local',
            'debug' => 'true',
            'url' => 'http://localhost',
            'timezone' => 'UTC',
            'locale' => 'fr'
         ]
      );
      $configGenerator->generate();
      
   }
}
