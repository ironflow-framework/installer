<?php

namespace IronFlow\Installer\Questions;

use Symfony\Component\Console\Style\SymfonyStyle;

class ModuleQuestion
{
   protected $io;
   protected $availableModules = [
      'auth' => [
         'name' => 'Authentification',
         'description' => 'Système d\'authentification (formulaire, JWT, OAuth)',
         'package' => 'ironflow/auth',
         'default' => true
      ],
      'admin' => [
         'name' => 'Panel d\'administration',
         'description' => 'Interface d\'administration avec CRUD automatisé',
         'package' => 'ironflow/admin',
         'default' => false
      ],
      'api' => [
         'name' => 'API REST',
         'description' => 'Support complet pour API RESTful avec documentation',
         'package' => 'ironflow/api',
         'default' => false
      ],
      'cache' => [
         'name' => 'Cache avancé',
         'description' => 'Système de cache configurable (fichier, Redis, Memcached)',
         'package' => 'ironflow/cache',
         'default' => true
      ],
      'file' => [
         'name' => 'Gestionnaire de fichiers',
         'description' => 'Système de gestion des uploads et stockage sécurisé',
         'package' => 'ironflow/file',
         'default' => false
      ],
      'notification' => [
         'name' => 'Notifications',
         'description' => 'Système de notifications (email, SMS, in-app)',
         'package' => 'ironflow/notification',
         'default' => false
      ],
      'log' => [
         'name' => 'Logs avancés',
         'description' => 'Système de logging avec plusieurs canaux et niveaux',
         'package' => 'ironflow/log',
         'default' => true
      ]
   ];

   public function __construct(SymfonyStyle $io)
   {
      $this->io = $io;
   }

   /**
    * Pose les questions concernant les modules et retourne les modules sélectionnés
    * 
    * @return array Les modules sélectionnés avec leurs packages correspondants
    */
   public function ask(): array
   {
      $this->io->section('Configuration des modules');
      $this->io->text([
         'IronFlow est un framework modulaire. Vous pouvez choisir',
         'les modules que vous souhaitez installer maintenant.',
         'Vous pourrez ajouter ou supprimer des modules ultérieurement.'
      ]);

      // Préparer les choix pour l'affichage
      $choices = [];
      $defaults = [];

      foreach ($this->availableModules as $key => $module) {
         $choices[$key] = sprintf(
            "%s - %s",
            $module['name'],
            $module['description']
         );

         if ($module['default']) {
            $defaults[] = $key;
         }
      }

      // Demander à l'utilisateur de sélectionner les modules
      $selectedKeys = $this->io->choice(
         'Sélectionnez les modules à installer (utilisez espace pour sélectionner, entrée pour valider)',
         $choices,
         implode(',', $defaults),
         true
      );

      // Préparer le tableau des modules sélectionnés
      $selectedModules = [];
      foreach ($selectedKeys as $key) {
         $selectedModules[$key] = [
            'name' => $this->availableModules[$key]['name'],
            'package' => $this->availableModules[$key]['package']
         ];
      }

      $this->io->newLine();
      $this->io->text('Modules sélectionnés : ' . implode(', ', array_column($selectedModules, 'name')));

      return $selectedModules;
   }

   /**
    * Retourne la liste complète des modules disponibles
    * 
    * @return array
    */
   public function getAvailableModules(): array
   {
      return $this->availableModules;
   }
}
