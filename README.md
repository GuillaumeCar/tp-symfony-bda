# Projet Symfony LA2 - Archives BDA

## Introduction

Dans le cadre de notre cours sur les Web Services, nous avons abordé le framework PHP Symfony.

Nous avons alors réalisé un projet amenant à la création d'archives pour le Bureau Des Arts de l'école.

## Installation

Commencer par cloner le projet

```shell
mkdir archive-bda
git clone https://github.com/Borobo/tp-symfony-bda.git archive-bda
```

Une fois le projet récupéré, vous allez pouvoir configurer le projet, notamment le driver de base de données. Actuellement, le projet utilise une base de donnée PostgreSQL.

```
# .env
DATABASE_URL="postgresql://symfony:symfony@127.0.0.1:5432/archive-bda?serverVersion=12.6&charset=utf8"
```

Maintenant, on peut installer les dépendances du projet via composer, créer la base de données et charger les fixtures :

```bash
composer update
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

Composer installe toute nos dépendances selon celles qui sont définies dans le fichier `composer.json`.

Nous utilisons un système de mailing pour notre projet mais utilisons mailhog pour le moment. Pour installer mailhog, veuillez vous référer à la documentation du projet : https://github.com/mailhog/MailHog
*Mailhog* permet un client web à l'adresse `localhost:8025` 

On peut maintenant et démarrer le projet via la commande suivante :

```shell
sudo symfony server:start
```

Le projet est alors effectif à l'adresse `localhost:8000`

## Fonctionnalités

### Home

La page d'accueil liste les derniers ajouts pour chaque type de production du BDA : Journal Ch'ti Marcel, Newsletters et Podcasts "PoteCasts"

<img src="ressources\image-20210607010946322.png" alt="image-20210607010946322" style="zoom: 50%;" />

### Type de production

On trouvera dans la navbar du site les différentes productions du BDA historisés par type. A chaque, vous trouverez un lien vers une page de détails

<img src="ressources\image-20210607011007475.png" alt="image-20210607011007475" style="zoom: 50%;" />

<img src="ressources\image-20210607011041981.png" alt="image-20210607011041981" style="zoom:50%;" />

### Utilisateurs

Il est possible de s'inscrire et de se connecter sur notre site via les options de la navbar. 

<img src="ressources\image-20210607011252766.png" alt="image-20210607011252766" style="zoom:50%;" />

<img src="ressources\image-20210607011308707.png" alt="image-20210607011308707" style="zoom:50%;" />

De plus, dans le cas où vous auriez oublié votre mot de passe, il est possible de le réinitialiser via le lien prévu à cet effet. Le process vous enverra un mail (réception via mailhog si vous l'avez installé)

Il existe 2 types d'utilisateurs : 

- Les admins, qui peuvent ajouter des productions du BDA via un formulaire
- Les utilisateurs classiques, qui peuvent visionner et commenter les productions

<img src="ressources\image-20210607013149925.png" alt="image-20210607013149925" style="zoom:50%;" />

Cependant, même si un utilisateur n'est pas connecté, il peut visionner les productions mais ne peut pas les commenter.

### Mailing

A chaque ajout d'une production via le formulaire ci dessus, un mail est envoyé afin d'alerter les utilisateurs qu'une nouvelle production est sortie.
