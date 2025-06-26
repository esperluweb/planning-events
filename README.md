# Planning Events - Extension WordPress

Une extension WordPress pour gérer et afficher un planning d'événements avec un design moderne et réactif.

## Fonctionnalités

- Création et gestion d'événements avec date, heure, lieu et description
- Affichage des événements à venir dans un format de liste attrayant
- Shortcode pour insérer le planning n'importe où sur votre site
- Interface d'administration intuitive
- Design responsive qui s'adapte à tous les écrans

## Installation

1. Téléchargez le dossier `planning-events` et placez-le dans le répertoire `/wp-content/plugins/` de votre installation WordPress
2. Activez l'extension via le menu 'Extensions' dans WordPress

## Utilisation

### Ajouter un nouvel événement

1. Allez dans "Planning Événements" > "Ajouter un événement"
2. Remplissez les détails de l'événement (titre, description, date, heure, lieu)
3. Publiez l'événement

### Afficher le planning

Pour afficher le planning sur une page ou un article, utilisez le shortcode suivant :

```
[planning_events]
```

#### Paramètres optionnels

- `limit` : Nombre d'événements à afficher (par défaut : -1 pour tous)
- `order` : Ordre de tri (ASC pour croissant, DESC pour décroissant, par défaut : ASC)

Exemple :
```
[planning_events limit="5" order="ASC"]
```

## Personnalisation

Vous pouvez personnaliser l'apparence du planning en ajoutant des règles CSS dans le fichier `planning-events.css` ou en utilisant le personnalisateur de thème WordPress.

## Compatibilité

Testé avec WordPress 5.0 et versions ultérieures.

## Licence

GPL v2 ou ultérieure

## Auteur

Votre nom ou société
