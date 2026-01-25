# Plugin Rédacteurs (writers) pour Dotclear 2 - Gestion des utilisateurs pour les administrateurs de blog

[![Release](https://img.shields.io/github/v/release/franck-paul/writers)](https://github.com/franck-paul/writers/releases)
[![Date](https://img.shields.io/github/release-date/franck-paul/writers)](https://github.com/franck-paul/writers/releases)
[![Issues](https://img.shields.io/github/issues/franck-paul/writers)](https://github.com/franck-paul/writers/issues)
[![License](https://img.shields.io/github/license/franck-paul/writers)](https://github.com/franck-paul/writers/blob/master/LICENSE)

## Description

Ce plugin permet, lorsqu'on est simple administrateur, d'accorder des droits pour son blog à des utilisateurs inscrits sur la plateforme.

Exemple :

- Une installation possède trois blogs, **A**, **B** et **C**.
- **Bernard** est super-adminstrateur de la plateforme.
- **Alice** est administratrice du blog **C**.
- **Roger** est rédacteur sur les blogs **A** et **B** mais pas sur **C**.

Actuellement si **Alice** souhaite que **Roger** puisse contribuer sur le blog **C** elle doit faire une requête à **Bernard**.

Une fois ce plugin installé, **Alice** pourra ajouter **Roger** et lui affecter les droits de rédaction sur le blog **C** sans avoir besoin de solliciter **Bernard**.

## Réglages

Ce plugin possède une page de réglage, accessible via le menu (item Rédacteur dans la section Blog) qui permet de gérer les utilisateurs et leurs droits.

Notez que l'accès à cette page, quand on est super-administrateur, renvoie sur la page de gestion classique des utilisateurs du blog.
