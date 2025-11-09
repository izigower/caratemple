# Configuration CI/CD - CaraTemple

## Vue d'ensemble

Le projet CaraTemple utilise **GitHub Actions** pour l'intégration continue et le déploiement continu (CI/CD).

## Pipeline CI/CD

### 🔄 Déclencheurs

Le pipeline s'exécute automatiquement lors de :
- **Push** sur les branches `main` et `develop`
- **Pull Requests** vers `main` et `develop`

### 📋 Jobs

#### 1. **Lint & Test** (lint-and-test)

Vérifie la qualité du code et exécute les tests.

**Étapes :**
- ✅ Checkout du code
- ✅ Installation de PHP 8.1 avec extensions (mbstring, xml, pdo, pdo_mysql, mysqli)
- ✅ Installation de Node.js 20
- ✅ Installation des dépendances Composer
- ✅ Installation des dépendances npm
- ✅ Exécution de **PHPStan** (analyse statique PHP)
- ✅ Exécution d'**ESLint** (linting JavaScript)
- ✅ Exécution de **Stylelint** (linting CSS)
- ✅ Exécution des **tests PHPUnit**
- ✅ Upload des résultats de tests

**Services :**
- MySQL 8.0 pour les tests d'intégration

#### 2. **Security Audit** (security-check)

Vérifie les vulnérabilités de sécurité.

**Étapes :**
- ✅ Audit des dépendances Composer (`composer audit`)
- ✅ Audit des dépendances npm (`npm audit`)
- ✅ Détection des vulnérabilités connues

#### 3. **Code Quality** (code-quality)

Analyse approfondie de la qualité du code.

**Étapes :**
- ✅ PHPStan niveau 5 (analyse stricte)
- ✅ Vérification du style de code
- ✅ Conformité aux standards

#### 4. **Deploy** (deploy)

Déploiement automatique en production.

**Conditions :**
- ✅ Tous les jobs précédents réussis
- ✅ Push sur la branche `main` uniquement
- ✅ Pas de déploiement sur les Pull Requests

**Étapes :**
- ✅ Création d'un artifact de déploiement
- ✅ Compression du code source
- ✅ Upload de l'artifact (rétention 30 jours)
- ✅ Notification de déploiement

## Configuration Locale

### Prérequis

```bash
# PHP 8.1+
php --version

# Composer
composer --version

# Node.js 20+
node --version

# npm
npm --version
```

### Installation

```bash
# Installer les dépendances PHP
composer install

# Installer les dépendances Node
npm install
```

### Commandes de Vérification

```bash
# Lancer tous les checks localement
composer run phpstan       # Analyse statique PHP
npm run lint               # Linting JavaScript
npm run lint:css           # Linting CSS
composer run test          # Tests unitaires PHPUnit

# Vérifications de sécurité
composer audit
npm audit
```

## Badges de Statut

Ajoutez ces badges dans votre `README.md` :

```markdown
![CI/CD Pipeline](https://github.com/izigower/caratemple/workflows/CI%2FCD%20Pipeline/badge.svg)
![Tests](https://img.shields.io/badge/tests-passing-brightgreen)
![PHPStan](https://img.shields.io/badge/PHPStan-level%205-blue)
```

## Fichiers de Configuration

- `.github/workflows/ci.yml` - Configuration GitHub Actions
- `phpunit.xml` - Configuration PHPUnit
- `phpstan.neon` - Configuration PHPStan
- `.eslintrc.json` - Configuration ESLint
- `.stylelintrc.json` - Configuration Stylelint
- `composer.json` - Dépendances PHP et scripts
- `package.json` - Dépendances Node et scripts

## Workflow de Développement

### Branches

- `main` - Production (déploiement automatique)
- `develop` - Développement (tests uniquement)
- `feature/*` - Nouvelles fonctionnalités
- `bugfix/*` - Corrections de bugs

### Processus

1. **Créer une branche** depuis `develop`
   ```bash
   git checkout -b feature/ma-nouvelle-fonctionnalite
   ```

2. **Développer et tester localement**
   ```bash
   composer run phpstan
   npm run lint
   composer run test
   ```

3. **Commit et push**
   ```bash
   git add .
   git commit -m "feat: ajouter nouvelle fonctionnalité"
   git push origin feature/ma-nouvelle-fonctionnalite
   ```

4. **Créer une Pull Request** vers `develop`
   - Le pipeline CI/CD s'exécute automatiquement
   - Tous les checks doivent passer ✅

5. **Merge vers develop** après validation

6. **Merge vers main** pour déploiement en production

## Notifications

Le pipeline envoie des notifications :
- ✅ **Succès** : Tous les tests passent
- ❌ **Échec** : Un ou plusieurs checks échouent
- 🚀 **Déploiement** : Code déployé en production

## Monitoring

Les artifacts suivants sont conservés :
- Résultats des tests (30 jours)
- Packages de déploiement (30 jours)
- Logs d'exécution (90 jours)

## Optimisations

- ✅ **Cache npm** : Accélère l'installation des dépendances
- ✅ **Cache Composer** : Réduit le temps de build
- ✅ **Exécution parallèle** : Jobs indépendants s'exécutent en parallèle
- ✅ **Services MySQL** : Tests d'intégration avec base de données

## Dépannage

### Échec de PHPStan

```bash
# Vérifier localement
composer run phpstan

# Corriger les erreurs détectées
```

### Échec des tests

```bash
# Exécuter les tests localement
composer run test

# Voir les détails
vendor/bin/phpunit --testdox
```

### Échec de linting

```bash
# JavaScript
npm run lint:fix

# CSS
npm run lint:css:fix
```

---

**Dernière mise à jour** : 31 octobre 2025  
**Version** : 1.0.0
