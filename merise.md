================= MCD ================== 
┌──────────────┐         ┌──────────────┐
│    ADMIN     │         │   CONTACT    │
├──────────────┤         ├──────────────┤
│ identifiant  │         │ nom          │
│ mot_de_passe │         │ email        │
│              │         │ sujet        │
└──────────────┘         │ contenu      │
                         │ date_envoi   │
                         └──────────────┘

┌──────────────┐ 1,N   ┌──────────────────┐
│    PROJET    │◄──────┤    MEDIA         │
├──────────────┤ 1,1   ├──────────────────┤
│ titre        │       │ chemin_fichier   │
│ description  │       │ texte_alt        │
│ technologies │       │ ordre            │
│ url_git      │       └──────────────────┘
│ url_demo     │
│ image_couv   │ ← attribut interne (VARCHAR)
│ galerie      │ ← attribut interne (JSON)
│ date_creation│
└──────────────┘

Légende de la relation :

    Un PROJET peut contenir 0 à N IMAGES.
    Une IMAGE appartient à 1 et 1 seul PROJET.

================= MLD ================== 

ADMINS ( #id, identifiant, mot_de_passe )

CONTACTS ( #id, nom, email, sujet, message, date_envoi )

PROJECTS ( #id, slug, titre, description_courte, description, 
           technologies, image_couverture, url_github, url_demo,
           date_creation, flag_custom_assets, galerie_json )

PROJECT_IMAGES ( #id, >id_projet [ref: PROJECTS.id, CASCADE], 
                 chemin_image, texte_alt, ordre_affichage, date_ajout )
