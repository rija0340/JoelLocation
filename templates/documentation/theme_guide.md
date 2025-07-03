# Guide du Système de Thème Joel Location

## Structure des Templates

### Hiérarchie des Templates
```
templates/
├── base.html.twig                    # Template de base principal
├── accueil/
│   ├── layouts/
│   │   └── vehicule_detail.html.twig # Template pour les pages de véhicules
│   ├── components/                   # Composants réutilisables
│   │   ├── section_title.html.twig
│   │   ├── vehicule_conditions.html.twig
│   │   ├── vehicule_caracteristiques.html.twig
│   │   ├── cta_button.html.twig
│   │   └── about_section.html.twig
│   └── [pages individuelles]
```

### CSS Thématique
```
public/css/theme/
├── variables.css    # Variables CSS (couleurs, espacements, etc.)
├── layout.css       # Styles de mise en page
├── components.css   # Styles des composants
└── utilities.css    # Classes utilitaires
```

## Utilisation des Composants

### 1. Titre de Section
```twig
{% include 'accueil/components/section_title.html.twig' with {
    'title': 'Mon Titre',
    'subtitle': 'Mon sous-titre (optionnel)',
    'color_class': 'red|white'
} %}
```

### 2. Conditions de Location
```twig
{% include 'accueil/components/vehicule_conditions.html.twig' with {
    'title': 'Conditions de location',
    'conditions': [
        'Kilométrage illimité',
        'Âge minimum : 21 ans',
        // ...
    ]
} %}
```

### 3. Caractéristiques de Véhicule
```twig
{% include 'accueil/components/vehicule_caracteristiques.html.twig' with {
    'title': 'Caractéristiques',
    'caracteristiques': [
        {'icon': 'fa fa-battery-full', 'text': 'Carburant : Essence'},
        // ...
    ]
} %}
```

### 4. Bouton d'Action
```twig
{% include 'accueil/components/cta_button.html.twig' with {
    'url': path('formulaire-contact'),
    'text': 'Demander un devis',
    'class': 'classe-optionnelle'
} %}
```

## Template de Véhicule

Pour créer une nouvelle page de véhicule, utilisez le template `vehicule_detail.html.twig` :

```twig
{% extends 'accueil/layouts/vehicule_detail.html.twig' %}

{% set vehicule_data = {
    'marque': 'renault',
    'modele': 'clio',
    'titre': 'Renault Clio IV',
    'image': 'images/clio.png',
    'description': 'Description du véhicule...',
    'conditions': [
        'Kilométrage illimité',
        'Âge minimum : 21 ans',
        // ...
    ],
    'caracteristiques': [
        {'icon': 'fa fa-battery-full', 'text': 'Carburant : Essence'},
        // ...
    ]
} %}
```

## Variables CSS Disponibles

### Couleurs
- `--primary-color`: #af0000 (rouge principal)
- `--primary-dark`: #8b0000
- `--primary-light`: #d32f2f
- `--text-color`: #333333
- `--text-white`: #ffffff

### Espacements
- `--spacing-xs`: 0.25rem
- `--spacing-sm`: 0.5rem
- `--spacing-md`: 1rem
- `--spacing-lg`: 1.5rem
- `--spacing-xl`: 2rem
- `--spacing-xxl`: 3rem

### Transitions
- `--transition-fast`: 0.15s ease-in-out
- `--transition-normal`: 0.3s ease-in-out
- `--transition-slow`: 0.5s ease-in-out

## Bonnes Pratiques

1. **Utilisez les composants** plutôt que de dupliquer le code
2. **Utilisez les variables CSS** pour maintenir la cohérence
3. **Évitez les styles inline** - utilisez les classes utilitaires
4. **Testez la responsivité** sur différentes tailles d'écran
5. **Maintenez la hiérarchie** des templates pour faciliter la maintenance

## Migration des Anciens Templates

Pour migrer un ancien template :
1. Identifiez les sections répétitives
2. Remplacez par les composants appropriés
3. Utilisez les variables CSS au lieu des valeurs codées en dur
4. Testez le rendu final
