unnamed-project
===============

Template for CV website using [Fat-Free framework](https://github.com/bcosca/fatfree "Fat Free") and the [Twig template engine](http://twig.sensiolabs.org/ "Twig").


Template pour des sites CV utilisant [Fat-Free framework](https://github.com/bcosca/fatfree "Fat Free") et le moteur de template [Twig](http://twig.sensiolabs.org/ "Twig").

Contenu
=======

Ce document présentera comment configurer et utiliser ce projet. Par la suite, "Fat-Free" sera abrégé *F3*

## Sommaire
* [Structure des dossiers] (#structure-des-dossiers)
* [Configuration] (#configuration)
* [Routes] (#routes)
* [Contrôleurs] (#controleurs)
* [Moteur de templates] (#moteur-de-template)
 

##Structure des dossiers

La structure des dossiers utilisée pour ce projet est plus stricte que celle utilisée par *F3*, elle est la suivante :

```
app/                (fichiers de l'application)
    classes/        (fichiers de classe, ne correspond pas au modèle de données)
    config/         (fichiers de configuration)
    controllers/    (contrôleurs)
    lib/            (biblothéques, contiendra F3 et Twig par défaut)
    logs/           (fichiers de logs)
    model/          (modèle de données, entités)
    resources/      ([fichiers statiques](#fichiers-statiques))
    tmp/            (fichiers temporaires)
        cache/      (cache)
        uploads/    (fichiers uploadés)
    views/          (fichiers de vues)
    kernel.php      (contrôleur principal)
app.php             (bootstrap)
.htaccess           (gestion des redirections vers le contrôleur principal)
```

[Plus d'infos ici](http://fatfreeframework.com/framework-variables#do-it-yourself-directory-structures)

La racine du projet contiendra donc seulement le fichier index.php, qui va se charger d'amorcer le lancement de l'application, ainsi que le fichier .htaccess qui va se charger de rediriger les requêtes.

###Fichiers statiques

Une requête sur un fichier statique (i.e. un fichier de ressource comme une feuille de style, une image ou un script) doit être fait en utilisant l'url :
```
/chemin/vers/racine/public/chemin/vers/ressource.css
```
La particularité de ces fichiers est qu'ils ne seront pas traités par le contrôleur principal et seront servis directement, comme définit dans le fichier .htaccess . Ces fichiers devront être placés dans le dossier `/app/resources`. Par exemple, si on va sur l'URL http://example.com/CV/public/css/style.css, il faudra avoir le fichiers `style.css` dans le repertoire `/CV/app/resources/css` .

Toutes les autres requêtes seront dirigées vers le contrôleur principal, et devront être traitées sous peine de générer une erreur 404.

##Configuration

###Variables systeme

La configuration du projet se fait via le fichier `/app/config/config.ini`. Ce fichier permet de modifier toutes les variables systemes (qui comprennent les noms des dossiers pour la personnalisation de la structure de fichiers). Le site de *F3* dresse la [liste complète des variables systeme](http://fatfreeframework.com/quick-reference).

###Personnalisation des erreurs

Il est aussi possible de personnaliser les erreurs dans le fichier `/app/config/config.ini`. Pour cela, on ira simplement modifier la clé `ERROR_` suivie du numero du code d'erreur. On indiquera pour valeur le chemin vers la vue qui devra être chargée (chemin relatif depuis le dossier `app/views`).

> Actuellement, il est possible de personnaliser uniquement la vue.
> Une pesonnalisation du contrôleur sera possible dans les versions ultérieures.

Il n'est pas nécessaire de personnaliser toutes les erreurs, si l'une d'entre elle n'est pas spécifiée, la vue `ERROR_FALLBACK` sera chargée (il faut bien sur donner sa valeur dans le fichier `app/config/config.ini`).
Si aucune vue n'est spécifiée lorsqu'une erreur survient (cela signifie par exemple pour une erreur 500, que ni la clé `ERROR_500`, ni la clé `ERROR_FALLBACK` ne sont définies dans le fichier de configuration), alors l'application utilise un template minimaliste définit dans le code.

**Remarque :**
Les templates d'erreurs sont seulement utilisé en mode production ou en mode `DEBUG=0`. On peut passer en `DEBUG=0` grâce au fichier de configuration `/app/config/config.ini` et en mode production en modifiant le fichier `/app.php` :
``` php
$kernel = new Kernel($prefix, false); //debug = false : mode production
```

###Autres variables

Le fichier de configuration permet aussi de définir des variables personnalisées. Ces variables seront alors accessibles dans le code via :
``` php
$valeur = $f3->get('NOM_VARIABLE');
```




**Remarque importante :**
Ce fichier est en cours de rédaction. Il est donc incomplet et sujet à de fréquentes modifications.
