Fonctionnalités attendues :
Page d’accueil (dashboard) :
#
Vue globale des tickets avec des statistiques (nombre total de tickets, tickets par statut, etc.).
Graphiques représentant la répartition des tickets par statut, priorité, ou date de création (périodique).
Gestion des tickets :
#
Création et édition d’un ticket avec les champs : titre, description, statut, priorité, date de création, date limite (deadline) et assignation à un utilisateur spécifique (parmi les utilisateurs de type support).
Changement du statut avec historique des modifications (les changements de statut doivent être enregistrés avec la date et l’utilisateur ayant effectué le changement).
Filtre avancé des tickets : possibilité de filtrer par priorité, statut, utilisateur assigné, et date limite.
Affectation automatique : Lorsque la création d’un ticket dépasse une certaine limite, il est automatiquement assigné à un utilisateur disponible.
#
Authentification et rôles :

Utilisateurs : Les utilisateurs doivent se connecter pour accéder à l'application.
Rôles :
Administrateur : Gestion complète des tickets, des utilisateurs et accès à des statistiques globales.
Technicien support : Peut consulter et gérer les tickets qui lui sont assignés.
Utilisateur classique : Peut créer des tickets et consulter ceux qu’il a créés.
Gestion des rôles par l'administrateur (modification/suppression d'utilisateurs, attribution des rôles).
#
Gestion des utilisateurs :

Chaque utilisateur a son propre tableau de bord avec la liste des tickets qui lui sont assignés ou qu’il a créés.
Reporting :

Tableau de bord des statistiques : Graphiques et rapports sur les tickets créés par mois, les tickets par statut, les tickets en retard, etc.

#
Schéma de la base de données :
Table user :

id (int, auto-increment)
email (string, unique)
password (string)
role (string, par exemple ROLE_ADMIN, ROLE_SUPPORT, ROLE_USER)
created_at (datetime)
Table ticket :

id (int, auto-increment)
title (string)
description (text)
status (enum : Ouvert, En cours, Résolu, Fermé)
priority (enum : Basse, Moyenne, Haute)
assigned_to (foreign key vers users, nullable)
deadline (datetime)
created_at (datetime)
updated_at (datetime)
Table ticket_status_history (pour conserver l’historique des statuts) :

id (int, auto-increment)
ticket_id (foreign key vers tickets)
status (enum)
changed_by (foreign key vers users)
changed_at (datetime)
#
Maquette de l’interface utilisateur :
Barre supérieure : Titre de l’application (« Gestion de Tickets »), un logo et un lien vers le tableau de bord.
Sidebar gauche :
Lien vers la page d'accueil (Liste des tickets).
Lien pour créer un nouveau ticket.
Lien vers la gestion des utilisateurs (administrateurs seulement).
Lien vers la page des statistiques (administrateurs seulement).

Zone centrale :
La zone où les contenus de chaque page seront affichés (liste des tickets, formulaire de création/édition de ticket, tableau de bord, etc.).
L'interface se devra d'être responsive
#
Consignes de développement :
Installation et configuration de Symfony : Créez un projet Symfony avec les bundles nécessaires.
Entités et base de données : Implémentez les entités User, Ticket, TicketStatusHistory.
Système de sécurité : Mettez en place l'authentification et la gestion des rôles.
Affichage et gestion des formulaires : Utilisez Twig pour les vues et les formulaires Symfony pour la gestion des tickets et utilisateurs.
Validation des données : Assurez-vous que toutes les données saisies dans les formulaires soient correctement validées.
Fonctionnalités avancées : Implémentez les fonctionnalités de reporting (graphique).
Bonus : Mettez en place une recherche avancée avec des filtres multi-critères et un système de pagination pour les tickets.
#
Livrables attendus :
Code source de l'application Symfony.
Base de données SQLite (fichier .sql).
