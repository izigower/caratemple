# CaraTemple

CaraTemple est un forum communautaire complet dédié aux passionnés de Carapuce. Le projet respecte les contraintes pédagogiques du cahier des charges « Projet PHP – Mickaël Martin Nevot – V8.4.0 » ainsi que la maquette CaraTemple.

## Aperçu fonctionnel

- Page d’accueil responsive reprenant la grille « Main – Non Connecté » avec navigation burger, filtres et cartes discussions.
- Espace membre sécurisé : inscription, connexion, déconnexion, validation JavaScript en direct, récupération des erreurs serveur.
- Forum dynamique : création de sujets, réponses hiérarchisées, likes, vues, suppression par auteur, compteur de participation.
- Administration restreinte : tableau de bord (utilisateurs/messages/discussions), suppression ciblée, indicateurs d’activité.
- Accessibilité renforcée : skip-link, attributs aria, labels/alt exhaustifs, structure sémantique conforme WCAG 2.1 niveau AA.

## Stack & prérequis

- **Langages** : PHP 8.1+, MySQL 8+, HTML5, CSS3, JavaScript (ES2021).
- **Extensions PHP** : PDO MySQL, intl (formatage dates), session.
- **Outils optionnels** : [Clean-CSS CLI](https://github.com/jakubpawlowicz/clean-css-cli), [SVGO](https://github.com/svg/svgo), [html5validator](https://github.com/svenkreiss/html5validator).

## Installation

1. Cloner le dépôt.
2. Copier `config/config.php` en ajustant vos identifiants MySQL et l’URL de base.
3. Créer la base `caratemple` puis exécuter `db/schema.sql` pour initialiser les tables (utilisateurs, discussions, messages, likes…).
4. Configurer votre hôte virtuel ou lancer `php -S 0.0.0.0:8000 -t .` pour un test local.
5. (Facultatif) Créer un compte puis passer sa colonne `is_admin` à `1` pour accéder au panneau `/admin/index.php`.

## Pipeline front & optimisation

- Les styles sources résident dans `assets/css/main.css`. Le fichier de production minifié est `assets/css/main.min.css` et est référencé par l’entête partagé.
- Regénérer le CSS minifié après modification :

  ```bash
  npx clean-css-cli -o assets/css/main.min.css assets/css/main.css
  ```

- Les SVG sont optimisés avec `svgo`. Relancer la passe d’optimisation si vous modifiez une ressource :

  ```bash
  npx svgo assets/images/<fichier>.svg --output assets/images/<fichier>.svg
  ```

- Le fichier `.browserslistrc` cible les 2 dernières versions stables de Chrome, Firefox, Safari et Edge.

## Qualité & validation

- **HTML/CSS W3C** : `html5validator --root . --also-check-css` (aucune erreur restante).
- **Accessibilité** : audit WCAG 2.1 AA via Pa11y (le lancement via Puppeteer peut nécessiter les librairies système listées par [Puppeteer Troubleshooting](https://pptr.dev/troubleshooting)).
- **Sécurité** : toutes les requêtes utilisent PDO préparé, CSRF sur formulaires sensibles, mots de passe hachés via `password_hash()`.

## Structure du dépôt

```
assets/
  css/            # main.css (source), main.min.css (build)
  images/         # Illustrations optimisées SVG
  js/             # Scripts front (menu, formulaires)
admin/            # Interface d'administration sécurisée
config/           # Configuration application & PDO
db/               # Schéma SQL complet
docs/             # Documentation projet + historique prompts + guide utilisateur
includes/         # Composants PHP réutilisables (auth, header, helpers…)
uploads/          # Point d'entrée futur pour médias utilisateurs
views/            # Pages secondaires (authentification, discussions…)
```

## Administration

- Accès restreint à `admin/index.php` (sessions admin obligatoires).
- Opérations destructives protégées par CSRF + confirmation JS.
- Les compteurs affichent utilisateurs, discussions, messages, likes et vues.

## Traçabilité & support

- Historique des instructions IA : `docs/prompts/README.md`.
- Guide utilisateur (front/back) : `docs/USER_GUIDE.md`.
- Signalez tout bug via issues Git en décrivant l’environnement et les étapes de reproduction.

## Licence

Projet académique – utilisation interne uniquement.
