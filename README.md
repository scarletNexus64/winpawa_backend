# 🎮 WINPAWA - Plateforme Casino Gaming

![WINPAWA](https://img.shields.io/badge/WINPAWA-Casino%20Gaming-d946ef?style=for-the-badge&logo=data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBkPSJNMTIgMkw0IDEyTDEyIDIyTDIwIDEyTDEyIDJaIiBmaWxsPSIjZDk0NmVmIi8+PC9zdmc+)
![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Filament](https://img.shields.io/badge/Filament-3.2-FBBF24?style=for-the-badge)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)

Plateforme de casino mobile et web avec jeux virtuels, système d'affiliation, et bonus pour le marché camerounais.

---

## ✨ Fonctionnalités

### 🎰 Jeux Casino
- **Roulette virtuelle / Apple of Fortune** - RTP 75-85%
- **Cartes à gratter (Scratch Card)** - RTP 70-80%
- **Pile ou face** - RTP 70-85%
- **Dés / Lancer de dés** - RTP 70-85%
- **Pierre-Papier-Ciseaux** - RTP 70-85%
- **Choix de coffre / boîte** - RTP 70-85%
- **Nombre aléatoire** - RTP 70-85%
- **Jackpot simplifié** - RTP 70-80%
- **Jeu de Penalty / Tir au but** - RTP 70-85%
- **Ludo simplifié / Course de pions** - RTP 70-85%
- **Mini quiz chance** - RTP 70-85%
- **Roulette de couleurs** - RTP 70-85%

### ⚽ Virtual Match
- Matchs de football virtuels 24h/24
- Génération automatique toutes les X minutes
- Durées : 1, 3 ou 5 minutes
- Résultats par RNG algorithme

### 💰 Système Financier
- **Dépôt minimum** : 200 FCFA
- **Retrait minimum** : 1 000 FCFA
- **Bonus d'inscription** : 50% sur premier dépôt
- **Condition de retrait** : pari total minimum = 5× dépôt
- Paiements : MTN Mobile Money, Orange Money

### 👥 Affiliation (1 niveau)
- Commission 5% sur dépôts des filleuls
- Commission 25% sur pertes nettes des filleuls
- Seuil de retrait affilié : 5 000 FCFA
- Dashboard affilié complet

### 🛡️ Sécurité
- RNG côté serveur pour tous les jeux
- Logs horodatés pour audit
- Authentification Laravel Sanctum
- Permissions et rôles avec Spatie

---

## 🎨 Page de Login Admin - Ultra Gaming Theme

La page de connexion administrateur a été conçue avec un **thème gaming dark ultra moderne** :

### Caractéristiques visuelles :
- ✅ **Fond dégradé ultra dark** : noir profond → violet → magenta
- ✅ **Grille animée** en arrière-plan avec effet de défilement
- ✅ **4 orbes flottants** multicolores avec animations fluides (magenta, violet, cyan, or)
- ✅ **8 particules lumineuses** montantes avec effet de brillance
- ✅ **Effet scanline** gaming traversant l'écran
- ✅ **Logo 3D animé** avec rotation perspective et pulse lumineux
- ✅ **Coins cyberpunk** style futuriste
- ✅ **Titre avec dégradé animé** "WINPAWA"
- ✅ **Badge "SÉCURISÉ"** avec indicateur live vert pulsant
- ✅ **Carte glassmorphism** avec glow rotatif
- ✅ **Champs input néon** avec effet glow au focus
- ✅ **Bouton dégradé** avec effet vague lumineuse au survol

### Polices :
- **Orbitron** : Titres et éléments futuristes
- **Outfit** : Texte et formulaires

### Palette de couleurs :
- 🟣 **Primary** : #d946ef (Magenta)
- 🟣 **Secondary** : #8b5cf6 (Violet)
- 🔵 **Accent** : #06b6d4 (Cyan)
- 🟡 **Gold** : #fbbf24 (Or)
- 🟢 **Success** : #10b981 (Vert)

---

## 🚀 Installation

### Prérequis
- PHP 8.2 ou supérieur
- Composer
- SQLite ou MySQL
- Node.js & NPM (pour Vite)

### Étapes d'installation

1. **Cloner le projet**
```bash
git clone https://github.com/votre-repo/winpawa.git
cd winpawa
```

2. **Installer les dépendances**
```bash
composer install
npm install
```

3. **Configuration de l'environnement**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configurer la base de données**

   Pour **SQLite** (développement) :
   ```bash
   touch database/database.sqlite
   ```

   Ou modifier `.env` pour **MySQL** :
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=winpawa
   DB_USERNAME=votre_user
   DB_PASSWORD=votre_password
   ```

5. **Exécuter les migrations et seeders**
```bash
php artisan migrate:fresh --seed
```

6. **Créer les liens symboliques**
```bash
php artisan storage:link
```

7. **Compiler les assets**
```bash
npm run dev
```

8. **Lancer le serveur**
```bash
php artisan serve
```

Le site sera accessible sur : `http://localhost:8000`

---

## 🔑 Accès Admin

### URL de connexion
```
http://localhost:8000/admin/login
```

### Identifiants par défaut
- **Email** : `admin@winpawa.com`
- **Mot de passe** : `password`

⚠️ **Important** : Changez ces identifiants en production !

---

## 📁 Structure du Projet

```
winpawa/
├── app/
│   ├── Enums/              # Énumérations (GameType, BetStatus, etc.)
│   ├── Filament/           # Admin Panel Filament
│   │   ├── Pages/
│   │   │   └── Auth/
│   │   │       └── Login.php  # Page login personnalisée
│   │   ├── Resources/      # Ressources CRUD
│   │   └── Widgets/        # Widgets dashboard
│   ├── Http/
│   │   └── Controllers/
│   │       └── Api/        # Controllers API mobile
│   ├── Models/             # Modèles Eloquent
│   └── Services/           # Services (RNG, Payment)
│
├── config/
│   └── winpawa.php         # Configuration WINPAWA
│
├── database/
│   ├── migrations/         # Migrations
│   └── seeders/           # Seeders (Admin, Games)
│
├── resources/
│   └── views/
│       └── filament/
│           ├── custom-head.blade.php        # Styles gaming globaux
│           ├── custom-scripts.blade.php     # Scripts personnalisés
│           └── pages/
│               └── auth/
│                   └── login.blade.php      # Vue login ultra gaming
│
└── routes/
    ├── api.php            # Routes API
    └── web.php            # Routes web
```

---

## 🎮 Cahiers des Charges

Consultez les documents de référence dans le dossier `Cahier Charge/` :

1. **CAHIER DES CHARGES – PLATEFORME CASINO MOBILE & WEB.pdf**
   - Présentation générale
   - Liste complète des jeux
   - Module bonus et affiliation
   - Spécifications techniques

2. **CAHIER DES CHARGES VIRTUAL MATCH.pdf**
   - Fonctionnalités Virtual Match
   - Logique de génération
   - Calcul des gains
   - Administration

---

## 🔧 Configuration WINPAWA

Les paramètres principaux sont dans `.env` :

```env
# Configuration WINPAWA
WINPAWA_MIN_DEPOSIT=200              # Dépôt minimum (FCFA)
WINPAWA_MIN_WITHDRAWAL=1000          # Retrait minimum (FCFA)
WINPAWA_SIGNUP_BONUS_PERCENT=50      # Bonus inscription (%)
WINPAWA_WAGERING_REQUIREMENT=5       # Exigence de pari (×)
WINPAWA_AFFILIATE_COMMISSION_DEPOSIT=5   # Commission dépôt (%)
WINPAWA_AFFILIATE_COMMISSION_LOSS=25     # Commission pertes (%)
WINPAWA_AFFILIATE_MIN_WITHDRAWAL=5000    # Retrait min affilié (FCFA)
```

---

## 🎨 Personnalisation du Thème

### Modifier les couleurs gaming

Éditez [resources/views/filament/custom-head.blade.php](resources/views/filament/custom-head.blade.php:1-290) :

```css
:root {
    --gaming-primary: #d946ef;      /* Magenta */
    --gaming-secondary: #8b5cf6;    /* Violet */
    --gaming-accent: #06b6d4;       /* Cyan */
    --gaming-gold: #fbbf24;         /* Or */
    /* ... */
}
```

### Modifier le logo

Remplacez le fichier dans `public/images/logo.svg` et mettez à jour [app/Providers/Filament/AdminPanelProvider.php](app/Providers/Filament/AdminPanelProvider.php:31).

---

## 📱 API Routes

### Authentification
- `POST /api/register` - Inscription utilisateur
- `POST /api/login` - Connexion
- `POST /api/logout` - Déconnexion

### Jeux
- `GET /api/games` - Liste des jeux
- `POST /api/games/{game}/play` - Jouer à un jeu
- `GET /api/bets/history` - Historique des paris

### Wallet
- `GET /api/wallet/balance` - Solde du wallet
- `POST /api/wallet/deposit` - Effectuer un dépôt
- `POST /api/wallet/withdraw` - Demander un retrait
- `GET /api/transactions` - Historique des transactions

### Affiliation
- `GET /api/affiliate/dashboard` - Dashboard affilié
- `GET /api/affiliate/referrals` - Liste des filleuls
- `POST /api/affiliate/withdraw` - Retrait commission

---

## 🧪 Tests

```bash
# Tests unitaires
php artisan test

# Tests avec couverture
php artisan test --coverage
```

---

## 📊 Technologies Utilisées

| Technologie | Usage |
|-------------|-------|
| **Laravel 11** | Framework PHP backend |
| **Filament 3.2** | Admin panel & CRUD |
| **Spatie Permission** | Gestion rôles & permissions |
| **Spatie Activity Log** | Logs d'activité |
| **Laravel Sanctum** | Authentification API |
| **TailwindCSS** | Framework CSS utility-first |
| **AlpineJS** | Interactivité JavaScript légère |

---

## 🔐 Sécurité

- ✅ Validation stricte des entrées
- ✅ Protection CSRF
- ✅ Authentification sécurisée (bcrypt)
- ✅ RNG côté serveur uniquement
- ✅ Logs d'audit complets
- ✅ Rate limiting sur API
- ✅ Sanitisation des données

---

## 📝 License

Proprietary - © 2024 WINPAWA. Tous droits réservés.

---

## 👥 Support

Pour toute question ou assistance :

- **Email** : support@winpawa.com
- **Documentation** : [docs.winpawa.com](https://docs.winpawa.com)

---

## 🎯 Roadmap

### Phase 1 - ✅ Terminé
- [x] Design UI + architecture backend
- [x] Page de login admin ultra gaming
- [x] Système d'authentification
- [x] Dashboard admin Filament

### Phase 2 - 🔄 En cours
- [ ] Intégration jeux casino
- [ ] Module Virtual Match
- [ ] Paramètres RNG, RTP, multiplicateurs

### Phase 3 - 📋 À venir
- [ ] Module bonus inscription
- [ ] Module affiliation
- [ ] Paiements MTN & Orange Money

### Phase 4 - 🚀 Future
- [ ] Application mobile (React Native)
- [ ] WebSocket temps réel
- [ ] Système de chat support
- [ ] Tournois et défis

---

<div align="center">

**Fait avec ❤️ pour le marché africain**

🎮 **WINPAWA** - La révolution du gaming en Afrique 🎮

</div>
