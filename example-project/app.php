<?php

$prefix = 'app/'; //Cette variable designe le prefixe entre le repertoire contenant ce fichier et le repertoire contenant le fichier kernel.php
require_once $prefix . 'kernel.php';

$kernel = new Kernel($prefix, true); //L'objet Kernel est une couche ajoutée a FatFree, il permet de charger tous les composants et les parametres
//Le parametre 1 correspond au prefixe, il doit etre correcte sous peine d'erreurs de chargement
//Le parametre 2 correspond au mode debug. Si debug vaut false, on n'affichera que le minimum sur les erreurs quelle que soit la valeur DEBUG dans les fichiers de conf

//Permet de traiter la route
$kernel->run();
