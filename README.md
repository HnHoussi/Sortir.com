# 🎉 Sortir – Application de gestion d’événements entre étudiants ENI

Projet Symfony permettant aux anciens et nouveaux étudiants de l’ENI d’organiser, participer et gérer des sorties.

---

## 👥 Équipe

- Jassim
- Steve
- Houcine
- Aissa

---

## 🚀 Fonctionnalités principales

- **Connexion / Déconnexion** – ROLE_USER
- **Se souvenir de moi** – ROLE_USER
- **Mot de passe oublié** – ROLE_USER
- **Gestion du profil** – ROLE_USER
- **Photo de profil** – ROLE_USER
- **Afficher les sorties par site** – ROLE_USER
- **Créer une sortie** – ROLE_USER
- **S’inscrire à une sortie** – ROLE_USER
- **Se désister d’une sortie** – ROLE_USER
- **Clôturer les inscriptions** – ROLE_USER
- **Annuler une sortie** – ROLE_USER / ROLE_ADMIN
- **Archiver les sorties** – ROLE_ADMIN
- **Afficher le profil d’autres participants** – ROLE_USER
- **Gérer les villes et lieux** – ROLE_ADMIN
- **Inscrire des utilisateurs par fichier** – ROLE_ADMIN
- **Inscrire un utilisateur manuellement** – ROLE_ADMIN
- **Désactiver / Supprimer des utilisateurs** – ROLE_ADMIN
- **Notifications par e-mail** :
    - confirmation inscription/désistement
    - rappel 48h avant le début d’une sortie
- **Responsive design** – Utilisation sur smartphone et tablette

---

## ⚙️ Installation du projet

### 1️⃣ Cloner le projet

```bash
git clone https://github.com/HnHoussi/Sortir.com.git
cd Sortir.com
```

### 2️⃣ Installer les dépendances PHP

```bash
composer install
```

### 3️⃣ Créer le fichier `.env.local`

Copiez le fichier `.env` puis configurez votre connexion à la base de données :

```ini
DATABASE_URL="mysql://user:password@127.0.0.1:3306/sortir"
```

### 4️⃣ Créer la base de données

```bash
php bin/console doctrine:database:create
```

### 5️⃣ Exécuter les migrations

```bash
php bin/console doctrine:migrations:migrate
```

### 6️⃣ Lancer le serveur Symfony

```bash
symfony serve
```

👉 Application disponible sur [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## 🛠️ Technologies utilisées

- Symfony 7
- PHP 8.3
- Twig
- Doctrine ORM
- Bootstrap
- MySQL
- WampServer

---

## ✅ Bonnes pratiques

- Respect du MVC Symfony
- Sécurité avec gestion des rôles (`ROLE_USER`, `ROLE_ADMIN`)
- Validation des formulaires
- Protection CSRF pour les actions sensibles
