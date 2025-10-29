# CaraTemple

CaraTemple est un forum communautaire dédié aux passionnés de Carapuce. Ce dépôt contient l'intégralité du code source du projet, organisé selon le cahier des charges fourni.

## Structure actuelle

```
assets/
  css/
  images/
  js/
admin/
config/
db/
docs/
includes/
uploads/
views/
```

## Mise en route

1. Cloner le dépôt sur votre environnement local.
2. Vérifier les prérequis : PHP >= 8.1, MySQL >= 8.0, serveur HTTP (Apache ou Nginx).
3. Copier `config/config.php` et ajuster les constantes `DB_*` pour votre environnement.
4. Créer une base de données MySQL nommée `caratemple` et exécuter `db/schema.sql` pour générer les tables initiales.
5. Configurer votre hôte virtuel pour pointer vers le dossier racine du projet.

## Normes et sécurité

- Toutes les entrées utilisateur devront être filtrées et échappées.
- Les mots de passe seront gérés via `password_hash()` et `password_verify()`.
- Les requêtes SQL utiliseront des instructions préparées.
- La charte graphique respecte les maquettes fournies, avec des couleurs pastel et la police Inter.

## Fonctionnalités livrées

- Page d'accueil responsive inspirée de la maquette "Main - Non Connecté".
- Formulaires d'inscription et de connexion avec validation côté client et serveur.
- Gestion des utilisateurs (création, connexion, déconnexion) persistée en base MySQL.
- Messages flash pour informer l'utilisateur des actions réalisées.

## Traçabilité

Conservez les échanges IA dans `docs/prompts/` et détaillez chaque étape de développement via Git.

## Licence

Projet académique – utilisation interne uniquement.
