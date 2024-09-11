# Documentation de mon Projet Symfony - Site KoreanIsta

## Introduction

### KoreaIsta

  - **Contexte** : Développé dans le cadre de l'examen du cours Symfony
  - **Objectif** : Ce projet permet de gérer des cours, de coréen, en ligne avec une hiérarchie de catégories, de leçons, la gestion d'utilisateurs (visiteurs, professeurs et admin), des notifications par email et de gestion de mot de passe. 

## Fonctionnalités principales

### 1. Gestion des utilisateurs et des rôles
  - 1. **Contexte** : J'ai implémenté un système de gestion des utilisateurs avec différents rôles (administrateur, enseignant, visiteur (ceux non définit par un rôle)) pour contrôler l'accès aux différentes parties du site.
  - 2. **Explication** : J'ai utilisé le composant **Security** de Symfony pour gérer l'authentification et les rôles. Les utilisateurs peuvent s'inscrire, se connecter et voir leurs pages respectivement accessibles. Les contrôles d'accès sont définis dans les contrôleurs. J'ai décidé d'utiliser des rôles ne pas permettre à tout le monde de créer, modifier ou supprimer tout ce qu'il veut. 
  Voici comment sont répartis les autorisations, elles sont directement mises dans les contrôleurs:

  ```php

  ```

    **Administrateur** : un admin peut tout faire sur le site, il a, cependant, des autorisations exclusives tel que : Création/modification/suppression d'une catégorie, suppression d'une sous-catégorie, Création/modification/suppression d'un admin, il peut aussi supprimer n'importe quel leçon (quelque soit l'enseignant) et n'importe quel enseignant (son user lié aussi). Il a aussi accés à une dashboard dédié.

    **Enseignant** : Un enseignant a des autorisations principalement centrée sur lui : il peut faire une création/modification/suppression d'une leçon qu'il a écrit seulement, une création/modification d'une sous-catégorie, il peut aussi modifier/supprimer son profil. Il a aussi accés à une page profil.

    **Visiteur** : Un visiteur qui n'a pas de compte sur le site peut voir les catégories, sous-catégories, les leçons, le profile des enseignants et il a accés à une page de connection.

### 2. Système de formulaires et validation
  - **Contexte** : Les utilisateurs interagissent avec le site via des formulaires, que ce soit pour l'inscription, l'ajout de contenu, la réinitialisation du mot de passe, le mot de passe oublié, etc.
  - **Explication** : J'ai utilisé le composant **Form** de Symfony pour générer les formulaires. J'ai également ajouté des règles de validation (dans `src/Validator/`) pour garantir que les données soumises sont bien des vidéos avant d'être enregistrées dans la base de données. J'ai du faire ces deux fichiers de **validation de type vidéo** car il n'y en avait d'iné à symfony. Je me suis donc inspirée de la contrainte de validation fichier et je me suis aidée de chatgpt pour pouvoir faire une contrainte cohérente et fonctionnelle, sans avoir un modifier et déplacer la contrainte de validation fichier, évitant comme ça un potenciel conflis.

  - **Exception** : Le formulaire de création/modification d'un utilisateur a été créé de manière modulable pour s'adapter à plusieurs cas de figures. Il est composé de plusieurs **options** qui viennent se rajouter dans les différent(e)s controleurs/routes. Il est dans un premier temps conçu pour créer un utilisateur avec un mot de passe et un email. Dans le controleur admin, grâce à l'option **is_user**, on vient rajouter le rôle d'admin (seulement les administrateur peuvent accéder à ce contrôleur) et seulement une partie du formulaire est accessible. Dans un deuxième temps, le formulaire est conçu pour créer un enseignant (lié à un object de l'entité user). Si le formulaire est généré par le contrôleur teacher alors une deuxième partie du formulaire apparaît avec les champs nécessaires pour créer un objet de l'entité Teacher, qui serait automatiquement lié à l'object de l'entité User. Cependant, le formulaire changera une dernière fois grâce à l'option **is_edit** qui viens cacher le champs mot de passe, lors de la modification du profil.

### 3. Gestion des notifications par email
  - **Contexte** : L'application envoie des emails de confirmation et des notifications.
  - **Explication** : J'ai utilisé le composant **Mailer** de Symfony pour configurer l'envoi d'emails via des services tiers. Cela est utile pour informer les utilisateurs après certaines actions comme l'inscription à la newsletter, la création de compte ou la confirmation de modification de mot de passe. De plus, j'ai fait une fonction "mot de passe oublié", qui envoi un mail avec un token, lié à l'adresse mail de l'utilisateur, pour pouvoir modifier/changer son mot de passe.

```php
// Générer l'URL complète pour la réinitialisation du mot de passe
$resetUrl = $this->urlGenerator->generate('reset_password', ['token' => $resetToken], UrlGeneratorInterface::ABSOLUTE_URL);

// Créer et envoyer l'email
$email = (new Email())
    ->from($this->adminEmail)
    ->to($user->getEmail())
    ->subject('KoreanIsta - Réinitialisation de mot de passe')
    ->html(sprintf(
        '<p>Bonjour,</p><p>Pour réinitialiser votre mot de passe, veuillez cliquer sur le lien suivant : 
        <a href="%s">Réinitialiser mon mot de passe</a></p>',
        $resetUrl
    ));
```
### 4. Autres fonctionnalités

  - 1. **EventSubscriber** : J'ai fait un eventSubscriber pour hasher le mot de passer juste avant de l'enregistrer dans la bdd, lors de la modification ou de la création (grâce au prePersist et preUpdate).
  - 2. **Messages flash** : J'ai aussi décidé d'utiliser des messages flash plutôt que des vue de confirmation.

## Attention

  - 1. **Taille Vidéos/Images** : Attention, si sur le formulaire vous avez l'erreur "The uploaded file was too large. Please try to upload a smaller file." ça veut dire qu'il faut modifier le fichier php.ini pour augmenter la capacité d'upload. La commande "php --ini", vous permez de trouver l'emplacement du fichier. Cliquez sur le lien qui apparait dans le terminal et modifiez les valeurs suivantes : upload_max_filesize et post_max_size. En maximum dans lessonsType.php, j'ai mis "'maxSize' => '5120M'" (->5 Go), pour être sur.
  - 2. **Mailer** : Un mailer est utilisé la variable d'environnement **MAILER_DSN** est donc nécessaire dans le .env.local.
  - 3. **Les médias** : J'ai mis une vidéo dans `assets/video/` pour faire les tests. C'est pas une de mes vidéos personnel, c'est une vidéo prise de youtube dont j'ai pas les droits. J'ai mis deux images dans `assets/images/`.

## Point d'amélioration

J'aurais aimé aller plus encore loin dans le projet. J'ai aussi délaissé toute la partie front que j'ai fait faire par chatgpt. J'aurais aimé pouvoir mettre en place plus de sécurity, quand à la création (inscription) et la gestion des enseignants, avec une mise en attente de la demande et une vérification par un admin du formulaire d'inscription.


