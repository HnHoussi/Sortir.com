# 🛠 Guide Git Quotidien (Branches par fonctionnalité)

Ce guide explique quoi faire chaque jour : comment travailler sur ta branche de **fonctionnalité** et comment fusionner ta fonctionnalité dans `develop`.

---

## 📦 Branches
- `main` → code stable et prêt pour la production
- `develop` → branche d’intégration où chacun fusionne ses fonctionnalités terminées
- Branche par fonctionnalité → ex. `feature/login`, `feature/cart`, `feature/dashboard`

---

## ✅ **Le matin (avant de commencer)**

```bash
# Se placer sur ta branche de fonctionnalité
git checkout feature/nom-de-ta-fonctionnalité

# Mettre à jour ta branche locale depuis le remote (au cas où tu as déjà poussé hier)
git pull

# Récupérer les dernières modifications de l’équipe depuis dev
git pull origin dev
```

---

## ✏️ **Pendant que tu travailles**
- Modifier les fichiers et ajouter de nouvelles fonctionnalités.
- Sauvegarder tes changements localement :

```bash
git add .
git commit -m "message clair sur ce que tu as fait"
```

- Pousser ton travail pour que tes coéquipiers le voient :

```bash
git push origin feature/nom-de-ta-fonctionnalité
```

> 💡 De petits commits clairs sont mieux qu’un gros commit unique.

---

## 🔄 **Mettre à jour ta branche avec dev**

Quand l’équipe a fusionné de nouvelles fonctionnalités dans `dev` :

```bash
git checkout feature/nom-de-ta-fonctionnalité
git pull origin dev
```

Résoudre les conflits si besoin.

---

## 🚀 **Quand ta fonctionnalité est prête**

Fusionner ta branche dans `dev` :

```bash
# Se placer sur develop
git checkout dev

# Mettre à jour develop depuis le remote
git pull origin dev

# Fusionner ta branche de fonctionnalité dans develop
git merge feature/nom-de-ta-fonctionnalité

# Pousser develop mis à jour
git push origin dev
```

Ou créer une merge request / pull request de `feature/nom-de-ta-fonctionnalité` → `dev`.

---

## ✅ **Résumé rapide (copier/coller)**

```bash
# Avant de commencer
git checkout feature/nom-de-ta-fonctionnalité
git pull
git pull origin dev

# Après avoir travaillé
git add .
git commit -m "description"
git push origin feature/nom-de-ta-fonctionnalité
```

```bash
# Quand la fonctionnalité est prête
git checkout dev
git pull origin dev
git merge feature/nom-de-ta-fonctionnalité
git push origin dev
```

---

## 📈 Schéma visuel

```
main
  |
  o---------o---------o   ← versions stables en production
            \
             dev
              o----o----o----o   ← intégration des fonctionnalités
               \    \    \    \
                \    \    \    feature/login
                 \    \    o--o--o   ← branche de fonctionnalité
                  \    feature/cart
                   \    o--o--o
                    \
                     feature/dashboard
                      o--o--o
```

> Chaque fonctionnalité a sa propre branche. Quand la fonctionnalité est prête, on fusionne dans `dev`. Quand `dev` est stable, on fusionne dans `main`.

