# Pousser le projet sur GitHub

Dépôt : **https://github.com/Laabidi-Eya/clickngo-website.git**

---

## 1. Autoriser Git à utiliser ce dossier (une seule fois)

Si Git affiche "dubious ownership", exécutez :

```powershell
git config --global --add safe.directory C:/clickngo-website
```

---

## 2. Se placer à la racine du projet

```powershell
cd c:\clickngo-website
```

---

## 3. Vérifier le remote (ou l’ajouter)

Vérifier si le remote `origin` existe :

```powershell
git remote -v
```

- Si **aucun remote** ou pas le bon :
  ```powershell
  git remote add origin https://github.com/Laabidi-Eya/clickngo-website.git
  ```
- Si `origin` pointe ailleurs et que vous voulez le remplacer :
  ```powershell
  git remote set-url origin https://github.com/Laabidi-Eya/clickngo-website.git
  ```

---

## 4. Préparer et committer tout le code

```powershell
git add .
git status
git commit -m "Initial commit - ClickNGo site (activités, événements, produits, covoiturage, sponsors)"
```

*(Adaptez le message si vous préférez.)*

---

## 5. Branche et premier push

Le dépôt GitHub est vide. Choisir la branche (souvent `main`) puis pousser :

```powershell
git branch -M main
git push -u origin main
```

Si GitHub vous demande de vous connecter :
- Utilisez votre **compte GitHub** et un **Personal Access Token** (mot de passe) au lieu du mot de passe du compte.
- Création d’un token : GitHub → Settings → Developer settings → Personal access tokens → Generate new token (cocher au moins `repo`).

---

## Récapitulatif (copier-coller dans PowerShell)

```powershell
cd c:\clickngo-website
git config --global --add safe.directory C:/clickngo-website
git remote add origin https://github.com/Laabidi-Eya/clickngo-website.git
git add .
git commit -m "Initial commit - ClickNGo site"
git branch -M main
git push -u origin main
```

*(Si `origin` existe déjà, remplacez `git remote add` par `git remote set-url origin https://github.com/Laabidi-Eya/clickngo-website.git`.)*
