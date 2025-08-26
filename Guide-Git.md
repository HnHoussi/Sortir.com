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

# Récupérer les dernières modifications de l’équipe depuis develop
git pull origin develop
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

## 🔄 **Mettre à jour ta branche avec develop**

Quand l’équipe a fusionné de nouvelles fonctionnalités dans `develop` :

```bash
git checkout feature/nom-de-ta-fonctionnalité
git pull origin develop
```

Résoudre les conflits si besoin.

---

## 🚀 **Quand ta fonctionnalité est prête**

Fusionner ta branche dans `develop` :

```bash
# Se placer sur develop
git checkout develop

# Mettre à jour develop depuis le remote
git pull origin develop

# Fusionner ta branche de fonctionnalité dans develop
git merge feature/nom-de-ta-fonctionnalité

# Pousser develop mis à jour
git push origin develop
```

Ou créer une merge request / pull request de `feature/nom-de-ta-fonctionnalité` → `develop`.

---

## ✅ **Résumé rapide (copier/coller)**

```bash
# Avant de commencer
git checkout feature/nom-de-ta-fonctionnalité
git pull
git pull origin develop

# Après avoir travaillé
git add .
git commit -m "description"
git push origin feature/nom-de-ta-fonctionnalité
```

```bash
# Quand la fonctionnalité est prête
git checkout develop
git pull origin develop
git merge feature/nom-de-ta-fonctionnalité
git push origin develop
```

---

## 📈 Schéma visuel

```
main
  |
  o---------o---------o   ← versions stables en production
            \
             develop
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

> Chaque fonctionnalité a sa propre branche. Quand la fonctionnalité est prête, on fusionne dans `develop`. Quand `develop` est stable, on fusionne dans `main`.

