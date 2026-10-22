# Photo Academy — landing page

Page française sans framework ni dépendance, adaptée à une campagne Meta Ads. HTML sémantique, CSS responsive isolé sous `#photo-academy`, JavaScript natif. Aucun site WordPress existant n'a été modifié.

## Voir le résultat

Depuis ce dossier : `npm run dev`, puis ouvrir http://localhost:4173. Ou ouvrir directement `index.html` pour consulter le design (la collecte requiert un serveur). Aucun build nécessaire. Pour un hébergement statique, publier index.html, config.js et assets/. Ne pas lancer de campagne avant la recette de collecte.

## Contenus et identité

Le site officiel https://formation-photo.ma/ n'a pas pu être consulté : le proxy réseau de l'environnement est indisponible. Les informations de formation proviennent exclusivement du brief. Palette ivoire, encre, orange et vert olive : proposition créative, pas une charte officielle vérifiée. Logo typographique provisoire. Ne pas déduire d'autres informations sur le centre.

La photo de couverture est une photographie d'ambiance externe Unsplash (photo-1452780212940-6f5c0d14d848), clairement légendée ; ce n'est pas une réalisation d'élève. Son chargement et ses droits d'utilisation sont à vérifier avant publication. Pour supprimer toute dépendance externe, remplacer l'URL dans index.html par votre fichier local autorisé assets/hero.webp, créer des variantes de largeur 640/1000/1400 et ajouter srcset/sizes. Préférer une photo authentique de cours ou de terrain en format vertical. Aucun visuel généré n'a été employé.

Les trois blocs de galerie et les deux témoignages sont des espaces réservés explicitement identifiés. Remplacer uniquement avec de vrais travaux et avis autorisés. Les blocs vidéo ne sont volontairement pas de faux lecteurs. Ajouter une vraie vidéo avec controls, poster et sous-titres une fois disponible. Ne pas inventer de date de session, d'adresse ou de preuve sociale. La mention « places limitées » est celle du brief ; confirmer sa pertinence avant lancement.

## Modifier

- Textes, CTA, programme, FAQ : index.html. Les CTA ciblent tous #inscription.
- Identité : variables --paper, --ink, --accent, --muted dans assets/style.css.
- Photographies : remplacer les figures .placeholder par des images avec alt pertinent, width/height, loading="lazy" et légende auteur. Conserver les mentions réservées tant que les contenus manquent.
- Comportement et messages : assets/app.js.
- Collecte statique : config.js, endpoint HTTPS + privacyUrl. Aucun secret côté navigateur.

## Collecte réelle

En aperçu, endpoint et privacyUrl sont vides. Le formulaire valide les champs mais refuse l'envoi et explique qu'aucune donnée n'a été transmise. Il n'affiche jamais une fausse réussite.

Un endpoint compatible reçoit JSON {fullName,phone,email,level,goal,priceConsent,contactConsent,website} et doit valider, limiter les tentatives, stocker durablement, puis répondre 2xx avec {"success":true,"id":"identifiant-unique"}. Les erreurs, les délais dépassés et les réponses sans identifiant ne déclenchent aucun succès. Pour un autre domaine, configurer CORS sur une liste explicite d'origines. Utiliser HTTPS ; le serveur doit contrôler les consentements et les valeurs autorisées. Ne jamais envoyer les données personnelles dans GA4 ou Meta.

## Intégrer à WordPress

1. Sur une copie de préproduction, sauvegarder le site et lancer `npm run package:wordpress`.
2. Extensions > Ajouter > Téléverser : importer photo-academy-wordpress.zip et activer.
3. Créer une page avec modèle pleine largeur/sans en-tête du thème. Ajouter un bloc Code court : `[photo_academy]`.
4. Configurer une politique réelle dans Réglages > Confidentialité. Compléter responsable, contact pour les droits, durée de conservation, destinataires et obligations locales applicables. La collecte refuse de fonctionner sans URL de politique.
5. Le shortcode injecte la route REST et son nonce. Les demandes sont enregistrées dans l'administration « Demandes Photo Academy », privées et réservées aux administrateurs. Aucune notification e-mail n'est configurée : consulter cette liste ou ajouter une notification serveur après stockage. Restreindre l'accès aux personnes habilitées et organiser suppression et conservation.
6. Exclure cette page du cache HTML long : le nonce expire. Recharger la page en cas d'erreur 403. Configurer les protections antiabus au niveau hébergement ; la limite intégrée est de 5 tentatives par IP sur 10 minutes. Les visiteurs derrière la même IP peuvent partager cette limite. Le nonce public n'est pas une protection antibot.
7. Tester une demande réelle en préproduction : confirmer son apparition en administration avant mise en ligne. Vérifier champ manquant, e-mail invalide, consentement absent, panne réseau, limite de tentatives et absence de faux succès. Le module n'a pas été exécuté dans un WordPress réel dans cet environnement.
8. Ajouter titre et description via votre extension SEO, URL canonique du domaine définitif et Open Graph avec une photo autorisée. Le fichier autonome fournit déjà title, description, langue et viewport. Éviter le double h1 et les styles du thème en choisissant un modèle vierge.
9. Recette mobile/tablette/desktop et navigation clavier, puis publier sur l'URL choisie. Le site existant n'est pas modifié automatiquement.

## Mesure GA4 / Meta

Aucun tracker n'est installé par défaut. Configurer votre CMP puis charger GA4/Meta selon les consentements applicables. Lier le choix de consentement à `window.PHOTO_ACADEMY_CONFIG.trackingConsent` (false par défaut). Sur stockage confirmé seulement : dataLayer reçoit `generate_lead`, form_id et lead_id ; Meta reçoit Lead si fbq est chargé. Dans GTM, créer un déclencheur événement personnalisé generate_lead et une balise GA4 du même nom. Ne pas ajouter en parallèle une conversion sur clic ou sur soumission native. Pour Meta CAPI, réutiliser eventID pour dédupliquer. Ne pas enregistrer de conversion lors d'une visite, d'un clic CTA, d'une erreur ou d'une collecte non configurée.

## Validation et limites

`npm run check` vérifie la syntaxe JavaScript. L'archive WordPress est produite sans dépendance. Vérifier les navigateurs réels, les dimensions 360/390/768/1440, l'absence de débordement, les contrastes et le chargement de la photo avant lancement. Animations réduites avec prefers-reduced-motion, champs associés aux labels, groupes radio avec légende, statut annoncé, CTA mobile fixe et place réservée en pied. Aucune session de navigateur ni WordPress n'est fournie ici : aucun score Lighthouse ou résultat de recette visuelle n'est revendiqué.
