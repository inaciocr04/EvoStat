# 🔒 Configuration de Sécurité EvoStat

## 📧 Configuration Email

Pour que la vérification d'email fonctionne, ajoutez ces lignes à votre fichier `.env` :

```env
# Configuration Email - IMPORTANT pour la vérification d'email
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre-email@gmail.com
MAIL_PASSWORD=votre-mot-de-passe-app
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@evostat.com"
MAIL_FROM_NAME="EvoStat"
```

### Configuration Gmail :
1. Activez l'authentification à 2 facteurs sur votre compte Gmail
2. Générez un "Mot de passe d'application" dans les paramètres de sécurité
3. Utilisez ce mot de passe dans `MAIL_PASSWORD`

### Configuration SMTP Autre :
- **Outlook/Hotmail** : `smtp-mail.outlook.com:587`
- **Yahoo** : `smtp.mail.yahoo.com:587`
- **Serveur personnalisé** : Adaptez selon votre fournisseur

## 🤖 Configuration CAPTCHA Google reCAPTCHA

### 1. Obtenir les clés :
1. Allez sur [Google reCAPTCHA](https://www.google.com/recaptcha/admin)
2. Créez un nouveau site
3. Choisissez "reCAPTCHA v2" → "Je ne suis pas un robot"
4. Ajoutez votre domaine (ex: `localhost`, `votre-domaine.com`)

### 2. Configuration dans `.env` :
```env
# Configuration CAPTCHA Google reCAPTCHA
RECAPTCHA_SITE_KEY=votre-site-key-ici
RECAPTCHA_SECRET_KEY=votre-secret-key-ici
```

### 3. Test en développement :
- En mode `local`, le CAPTCHA est optionnel si les clés ne sont pas configurées
- En production, le CAPTCHA est obligatoire

## 🛡️ Sécurités Actives

### ✅ Inscription :
- **Rate Limiting** : 5 tentatives par minute
- **CAPTCHA** : Protection contre les bots
- **Validation stricte** : Regex pour noms, pseudo, etc.
- **Vérification email** : Obligatoire avant accès
- **CSRF Protection** : Tokens anti-falsification

### ✅ Routes Admin :
- **Middleware Role** : Seuls les admins peuvent accéder
- **Vérification utilisateur** : Rôle défini et valide
- **Pages d'erreur** : 403, 404, 500 personnalisées

### ✅ Base de données :
- **Hachage sécurisé** : bcrypt pour les mots de passe
- **Unicité** : Email et pseudo uniques
- **Rôle forcé** : Toujours 'user' à l'inscription

## 🚀 Commandes Utiles

```bash
# Créer un utilisateur admin
php artisan db:seed --class=AdminUserSeeder

# Vider le cache
php artisan cache:clear
php artisan config:clear

# Tester l'envoi d'email
php artisan tinker
>>> Mail::raw('Test email', function($msg) { $msg->to('test@example.com')->subject('Test'); });
```

## ⚠️ Important

1. **Changez le mot de passe admin** après la première connexion
2. **Configurez l'email** avant la mise en production
3. **Ajoutez les clés CAPTCHA** pour la sécurité maximale
4. **Testez** toutes les fonctionnalités après configuration

## 📞 Support

En cas de problème :
1. Vérifiez les logs : `storage/logs/laravel.log`
2. Testez la configuration email
3. Vérifiez les clés CAPTCHA
4. Consultez la documentation Laravel
