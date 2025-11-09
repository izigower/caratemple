# Guide d'Installation - CaraTemple sur Windows (WAMP)

## 📋 Prérequis

### Logiciels Requis

1. **WAMP Server** (ou XAMPP)
   - Télécharger : https://www.wampserver.com/
   - Version recommandée : WAMP 3.3.0+ (PHP 8.1+, MySQL 8.0+)

2. **Visual Studio Code**
   - Télécharger : https://code.visualstudio.com/
   - Extensions recommandées :
     - PHP Intelephense
     - ESLint
     - Stylelint
     - GitLens

3. **Git for Windows**
   - Télécharger : https://git-scm.com/download/win

4. **Node.js** (pour les outils de développement)
   - Télécharger : https://nodejs.org/
   - Version recommandée : LTS (20.x)

5. **Composer** (gestionnaire de dépendances PHP)
   - Télécharger : https://getcomposer.org/download/

---

## 🚀 Installation Étape par Étape

### Étape 1 : Installer WAMP Server

1. **Télécharger et installer WAMP**
   - Lancer l'installateur
   - Choisir le répertoire d'installation (par défaut : `C:\wamp64`)
   - Attendre la fin de l'installation

2. **Démarrer WAMP**
   - Lancer WAMP depuis le menu Démarrer
   - L'icône WAMP doit devenir **verte** (tous les services actifs)
   - Si l'icône reste orange/rouge :
     - Vérifier que le port 80 n'est pas utilisé (Skype, IIS, etc.)
     - Clic droit sur l'icône WAMP → Tools → Use a port other than 80

3. **Vérifier l'installation**
   - Ouvrir le navigateur : http://localhost/
   - Vous devriez voir la page d'accueil de WAMP

---

### Étape 2 : Cloner le Projet depuis GitHub

1. **Ouvrir Git Bash** (ou PowerShell/CMD)

2. **Naviguer vers le répertoire www de WAMP**
   ```bash
   cd C:\wamp64\www
   ```

3. **Cloner le dépôt**
   ```bash
   git clone https://github.com/izigower/caratemple.git
   cd caratemple
   ```

---

### Étape 3 : Configurer la Base de Données

1. **Ouvrir PHPMyAdmin**
   - Naviguer vers : http://localhost/phpmyadmin/
   - Identifiants par défaut :
     - Utilisateur : `root`
     - Mot de passe : *(vide)*

2. **Créer la base de données**
   - Cliquer sur "Nouvelle base de données"
   - Nom : `caratemple`
   - Interclassement : `utf8mb4_general_ci`
   - Cliquer sur "Créer"

3. **Importer le schéma SQL**
   - Sélectionner la base `caratemple`
   - Onglet "Importer"
   - Choisir le fichier : `db/schema.sql`
   - Cliquer sur "Exécuter"

4. **Créer un utilisateur MySQL** (optionnel mais recommandé)
   - Onglet "Comptes d'utilisateurs"
   - Cliquer sur "Ajouter un compte d'utilisateur"
   - Nom d'utilisateur : `caratemple_user`
   - Nom d'hôte : `localhost`
   - Mot de passe : `caratemple_password` (ou un mot de passe fort)
   - Cocher "Créer une base portant son nom et donner à cet utilisateur tous les privilèges sur cette base"
   - Cliquer sur "Exécuter"

---

### Étape 4 : Configurer le Projet

1. **Ouvrir le projet dans VS Code**
   ```bash
   code .
   ```

2. **Modifier le fichier de configuration**
   - Ouvrir `config/config.php`
   - Modifier les constantes de base de données :

   ```php
   // Base de données
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'caratemple');
   define('DB_USER', 'root');              // ou 'caratemple_user'
   define('DB_PASS', '');                  // ou 'caratemple_password'
   define('DB_CHARSET', 'utf8mb4');

   // URL de base (IMPORTANT : modifier selon votre configuration)
   define('BASE_URL', 'http://localhost/caratemple');
   ```

   **⚠️ IMPORTANT** : Si vous avez changé le port de WAMP (ex: 8080), utilisez :
   ```php
   define('BASE_URL', 'http://localhost:8080/caratemple');
   ```

---

### Étape 5 : Installer les Dépendances

1. **Ouvrir un terminal dans VS Code** (Ctrl + ù ou Terminal → New Terminal)

2. **Installer les dépendances PHP avec Composer**
   ```bash
   composer install
   ```

3. **Installer les dépendances Node.js**
   ```bash
   npm install
   ```

---

### Étape 6 : Tester l'Installation

1. **Ouvrir le site dans le navigateur**
   - URL : http://localhost/caratemple/
   - Vous devriez voir la page d'accueil de CaraTemple

2. **Créer un compte de test**
   - Cliquer sur "Rejoindre le Temple"
   - Remplir le formulaire d'inscription
   - Se connecter

3. **Tester les fonctionnalités**
   - ✅ Créer une discussion
   - ✅ Poster une réponse
   - ✅ Liker un post
   - ✅ Rechercher une discussion
   - ✅ Accéder à l'administration (si compte admin)

---

## 🔧 Outils de Développement

### Linting et Qualité de Code

```bash
# Vérifier le code JavaScript
npm run lint

# Corriger automatiquement le JavaScript
npm run lint:fix

# Vérifier le CSS
npm run lint:css

# Corriger automatiquement le CSS
npm run lint:css:fix

# Analyser le code PHP avec PHPStan
composer run phpstan
```

### Tests

```bash
# Exécuter les tests unitaires
composer run test

# Exécuter les tests avec détails
vendor/bin/phpunit --testdox

# Générer un rapport de couverture
composer run test:coverage
```

### Optimisation

```bash
# Optimiser les fichiers SVG
npm run optimize:svg
```

---

## 🐛 Résolution de Problèmes

### Problème : Page blanche ou erreur 500

**Solution :**
1. Activer l'affichage des erreurs dans `config/config.php` :
   ```php
   define('DISPLAY_ERRORS', true);
   ```
2. Vérifier les logs Apache : `C:\wamp64\logs\apache_error.log`
3. Vérifier les logs PHP : `C:\wamp64\logs\php_error.log`

### Problème : Erreur de connexion à la base de données

**Solution :**
1. Vérifier que MySQL est démarré (icône WAMP verte)
2. Vérifier les identifiants dans `config/config.php`
3. Tester la connexion dans PHPMyAdmin

### Problème : CSS/JS ne se chargent pas

**Solution :**
1. Vérifier que `BASE_URL` est correct dans `config/config.php`
2. Vider le cache du navigateur (Ctrl + F5)
3. Vérifier que les fichiers existent dans `assets/css/` et `assets/js/`

### Problème : Port 80 déjà utilisé

**Solution :**
1. Clic droit sur l'icône WAMP → Tools → Use a port other than 80
2. Choisir le port 8080
3. Modifier `BASE_URL` dans `config/config.php` :
   ```php
   define('BASE_URL', 'http://localhost:8080/caratemple');
   ```

### Problème : Composer ou npm non reconnu

**Solution :**
1. Vérifier que Composer et Node.js sont installés
2. Redémarrer le terminal/VS Code
3. Ajouter Composer et Node.js au PATH Windows :
   - Panneau de configuration → Système → Paramètres système avancés
   - Variables d'environnement → Path → Modifier
   - Ajouter : `C:\ProgramData\ComposerSetup\bin` et `C:\Program Files\nodejs\`

---

## 📁 Structure du Projet

```
caratemple/
├── admin/              # Interface d'administration
├── api/                # Endpoints Ajax
│   ├── like.php
│   ├── admin_delete.php
│   ├── search.php
│   └── post_reply.php
├── assets/             # Ressources statiques
│   ├── css/
│   ├── js/
│   └── images/
├── config/             # Configuration
│   └── config.php      # ⚠️ À MODIFIER
├── db/                 # Base de données
│   └── schema.sql
├── docs/               # Documentation
├── includes/           # Fonctions PHP
├── tests/              # Tests unitaires
├── views/              # Pages du site
├── composer.json       # Dépendances PHP
├── package.json        # Dépendances Node.js
└── index.php           # Page d'accueil
```

---

## 🔐 Créer un Compte Administrateur

### Méthode 1 : Via PHPMyAdmin

1. Ouvrir PHPMyAdmin : http://localhost/phpmyadmin/
2. Sélectionner la base `caratemple`
3. Onglet "SQL"
4. Exécuter cette requête (remplacer les valeurs) :

```sql
-- Créer un utilisateur admin
INSERT INTO users (username, email, password, is_admin, created_at)
VALUES (
    'admin',
    'admin@caratemple.local',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: 'password'
    1,
    NOW()
);
```

**Identifiants par défaut :**
- Username : `admin`
- Password : `password`

**⚠️ IMPORTANT** : Changer le mot de passe après la première connexion !

### Méthode 2 : Promouvoir un utilisateur existant

```sql
-- Rendre un utilisateur admin
UPDATE users SET is_admin = 1 WHERE username = 'votre_username';
```

---

## 🚀 Accès Rapide

- **Site** : http://localhost/caratemple/
- **Administration** : http://localhost/caratemple/admin/
- **PHPMyAdmin** : http://localhost/phpmyadmin/
- **WAMP** : http://localhost/

---

## 📚 Ressources

- **Documentation PHP** : https://www.php.net/
- **Documentation MySQL** : https://dev.mysql.com/doc/
- **WAMP Documentation** : https://www.wampserver.com/en/
- **Composer** : https://getcomposer.org/doc/
- **npm** : https://docs.npmjs.com/

---

## ✅ Checklist d'Installation

- [ ] WAMP installé et démarré (icône verte)
- [ ] Git installé
- [ ] Node.js et npm installés
- [ ] Composer installé
- [ ] Projet cloné dans `C:\wamp64\www\caratemple`
- [ ] Base de données `caratemple` créée
- [ ] Schéma SQL importé
- [ ] `config/config.php` modifié avec les bons paramètres
- [ ] `composer install` exécuté
- [ ] `npm install` exécuté
- [ ] Site accessible sur http://localhost/caratemple/
- [ ] Compte admin créé
- [ ] Tests fonctionnels OK

---

**Besoin d'aide ?**
- Consulter les logs : `C:\wamp64\logs\`
- Vérifier la configuration : `config/config.php`
- Tester la connexion DB dans PHPMyAdmin

**Bon développement ! 🚀**
