# Helpdesk — Mini application PHP

## Membres du groupe
- ...
- ...

## Lancer le projet

```bash
cd helpdesk
php -S localhost:8000
```

Puis ouvrir : http://localhost:8000/login.php

## Comptes de test

| Utilisateur  | Mot de passe | Rôle     |
|--------------|--------------|----------|
| `etudiant1`  | `password`   | Étudiant |
| `etudiant2`  | `password`   | Étudiant |
| `tuteur1`    | `password`   | Tuteur   |

## Choix techniques

- **Langage** : PHP 8.1+ sans framework
- **Stockage** : Fichiers JSON dans `/data/` (Option 1)
- **Sessions** : `$_SESSION` PHP natif + `session_regenerate_id()` à la connexion
- **Sécurité** : `htmlspecialchars()` systématique via `h()`, validation côté serveur, vérification des rôles par page, protection contre l'accès inter-utilisateurs

## Structure des fichiers

```
helpdesk/
├── login.php              # Page de connexion
├── logout.php             # Déconnexion (détruit la session)
├── index.php              # Liste des tickets + filtres + stats tuteur
├── nouveau_ticket.php     # Création de ticket (étudiant uniquement)
├── ticket.php             # Détail + commentaires + changement statut
│
├── css/
│   └── style.css          # Feuille de style complète
│
├── data/                  # Stockage JSON (jamais exposé publiquement)
│   ├── users.json         # Utilisateurs et mots de passe (bcrypt)
│   ├── tickets.json       # Tickets
│   └── commentaires.json  # Commentaires
│
└── includes/              # Logique PHP partagée
    ├── config.php         # Constantes (chemins) + démarrage session
    ├── auth.php           # requireLogin(), requireRole(), authenticate()
    ├── data.php           # CRUD JSON (lire/écrire tickets et commentaires)
    ├── helpers.php        # h(), getParam(), flash messages, formatDate()…
    └── layout.php         # renderHeader() / renderFooter() HTML commun
```

## Fonctionnalités implémentées

- [x] Authentification par login/mot de passe (bcrypt)
- [x] Gestion de sessions avec `$_SESSION`
- [x] Deux rôles : étudiant et tuteur
- [x] Protection des pages internes (redirection si non connecté)
- [x] Création de ticket avec titre, description, catégorie, priorité
- [x] Liste des tickets filtrée selon le rôle (étudiant = ses tickets, tuteur = tous)
- [x] Filtres par statut, priorité, catégorie et recherche texte
- [x] Statistiques (tableau de bord tuteur)
- [x] Consultation du détail d'un ticket
- [x] Changement de statut par le tuteur
- [x] Ajout de commentaires (tous les utilisateurs connectés)
- [x] Protection XSS via `htmlspecialchars()`
- [x] Validation côté serveur sur tous les formulaires
- [x] Messages flash (succès/erreur)
- [x] Refus d'accès à un ticket d'un autre étudiant (403)
