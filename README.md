# P7-BileMo

## Pré-requis

- PHP > 8.2
- Symfony 5.4.\*
- MySQL > 8
- Symfony CLI

## Installation

Pour installer le projet Bilemo, suivez ces étapes dans votre terminal :

### Étape 1 - Clonez le dépôt GitHub en utilisant la commande suivante :

```
git clone https://github.com/stevenoyer/P7-BileMo.git
```

### Étape 2 - Une fois la copie du projet terminée, accédez au dossier nouvellement créé :

Une fois la copie du projet terminé, rendez-vous dans le dossier avec la commande suivante :

```
cd P7-BileMo/
```

### Étape 3 - Installez les dépendances du projet avec la commande suivante :

```
composer install
```

### Étape 4 - Dupliquez le fichier .env en le renommant : .env.local

### Étape 5 - Configuration de la base de données

#### Utilisation de Docker

Si vous utilisez Docker, modifiez la configuration dans le fichier docker-compose.override.yml selon vos besoins. Exécutez ensuite la commande suivante dans le terminal :

```
docker-compose up -d
```

Attendez la fin du processus, puis accédez à l'adresse suivante : 127.0.0.1:PORT (remplacez PORT par 8080 si vous avez utilisé celui par défaut).

Dans le fichier .env.local, configurez l'accès à la base de données. Si vous avez utilisé la configuration fournie par défaut avec Docker, voici un exemple :

```
DATABASE_URL="mysql://root:@127.0.0.1:3306/bilemo?serverVersion=8&charset=utf8mb4"
```

Sinon, remplacez les éléments suivants :

```
DATABASE_URL="mysql://USER:PASSWORD@IP:PORT/DB_NAME?serverVersion=8&charset=utf8mb4"
```

- **USER :** nom d'utilisateur de votre base de données
- **PASSWORD :** mot de passe de votre base de données (généralement absent en environnement local)
- **DB_NAME :** nom de la base de données (dans notre cas, bilemo)
- **IP :** IP de votre base de données (généralement : 127.0.0.1 ou localhost)
- **PORT :** port de votre base de données (généralement 3306)

Si vous n'utilisez pas MySQL, mais un autre type de base de données, consultez la documentation Symfony :
https://symfony.com/doc/current/doctrine.html#configuring-the-database

Une fois la base de données connectée, exécutez les commandes suivantes dans le terminal :

```
symfony console doctrine:database:create ou php bin/console doctrine:database:create
symfony console doctrine:migrations:migrate ou php bin/console doctrine:migrations:migrate
symfony console doctrine:fixtures:load ou php bin/console doctrine:fictures:load
```

Si les commandes ci-dessus ne fonctionnent pas, vérifiez vos informations de connexion à la base de données.

### Configuration des clés SSL pour JWT

Pour utiliser l'authentification, générez des paires de clés SSL avec la commande suivante dans le terminal :

```
symfony console lexik:jwt:generate-keypair ou php bin/console lexik:jwt:generate-keypair
```

Une fois la paire de clés générée, ajoutez les lignes suivantes dans le fichier .env.local :

```
###> lexik/jwt-authentication-bundle ###
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=VOTRE_PASSPHRASE
###< lexik/jwt-authentication-bundle ###
```

Remplacez "VOTRE_PASSPHRASE" par celle que vous avez indiquée lors de la génération des clés. Si la passphrase n'est pas la même, l'authentification JWT ne fonctionnera pas.

En cas de problème, consultez l'une de ces deux documentations :
https://symfony.com/bundles/LexikJWTAuthenticationBundle/current/index.html#generate-the-ssl-keys
https://github.com/lexik/LexikJWTAuthenticationBundle/blob/2.x/Resources/doc/index.rst#installation

### Tester l'API

Pour tester l'API, démarrez le serveur interne Symfony en tapant cette commande dans votre terminal :

```
symfony serve
```

Si vous n'avez pas Symfony CLI, suivez cette documentation :
https://symfony.com/download

Générez un jeton pour tester l'API en utilisant la commande suivante dans votre terminal :

```
symfony console lexik:jwt:generate-token test@api.com
```

Récupérez le jeton, puis accédez à la documentation de l'API Bilemo via cette URL (environnement local) :
http://127.0.0.1:8000/api/doc
