# Gestion HSE

Application web de gestion de la santé, sécurité et environnement (HSE).

## 📋 Description

Gestion HSE est une application web développée en PHP permettant de gérer les déclarations HSE, les actions correctives, les utilisateurs, les notifications et les indicateurs de suivi.

L'application propose plusieurs espaces selon le rôle de l'utilisateur : administrateur, responsable et employé.

## 🚀 Fonctionnalités

* 🔐 Authentification et gestion des utilisateurs
* 👤 Gestion des comptes utilisateurs
* 📝 Création et consultation des déclarations HSE
* 🔎 Consultation détaillée des déclarations
* ⚠️ Gestion des actions correctives
* 📊 Suivi des indicateurs HSE
* 🔔 Système de notifications
* 👨‍💼 Dashboard administrateur
* 👷 Dashboard employé
* 👨‍💼 Dashboard responsable
* 📁 Gestion des fichiers uploadés
* 🔄 Mise à jour du statut des actions et déclarations

## 🛠️ Technologies utilisées

* PHP
* MySQL
* HTML5
* CSS3
* JavaScript
* XAMPP
* Git / GitHub

## 📂 Structure du projet

```text
gestion_hse/
│
├── actions.php
├── admin_actions.php
├── admin_dashboard.php
├── admin_declarations.php
├── connexion.php
├── create_password.php
├── declaration_add.php
├── declaration_details.php
├── declarations.php
├── employee_dashboard.php
├── indicateurs.php
├── login.php
├── logout.php
├── mes_actions.php
├── mes_declarations.php
├── notifications.php
├── responsable_actions.php
├── responsable_dashboard.php
├── responsable_declarations.php
├── update_statut.php
├── utilisateurs.php
├── index.php
├── test.php
└── uploads/
```

## ⚙️ Installation

### 1. Cloner le projet

```bash
git clone https://github.com/OUA1234/gestion_hse.git
```

### 2. Placer le projet dans XAMPP

Copier le dossier `gestion_hse` dans :

```text
C:\xampp\htdocs\
```

### 3. Démarrer XAMPP

Lancer :

* Apache
* MySQL

### 4. Configurer la base de données

Créer la base de données MySQL nécessaire au projet et configurer les informations de connexion dans :

```text
connexion.php
```

### 5. Lancer l'application

Ouvrir dans le navigateur :

```text
http://localhost/gestion_hse/
```

## 🔐 Gestion des rôles

L'application prend en charge plusieurs profils :

* **Administrateur** : gestion globale de l'application et des utilisateurs.
* **Responsable** : suivi des déclarations et des actions.
* **Employé** : création et suivi de ses déclarations et actions.

## 🎯 Objectif

L'objectif de cette application est de faciliter la digitalisation du suivi HSE au sein d'une organisation en centralisant les déclarations, les actions correctives, les notifications et les indicateurs dans une seule plateforme.

## 👩‍💻 Auteur

**OUA1234**

Projet réalisé dans le cadre d'un projet de développement web.

## 📄 Licence

Ce projet est destiné à un usage académique et démonstratif.
