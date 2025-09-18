<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Mail;

// Configuration pour tester l'email
$config = [
    'mail' => [
        'default' => 'smtp',
        'mailers' => [
            'smtp' => [
                'transport' => 'smtp',
                'host' => env('MAIL_HOST', 'smtp.gmail.com'),
                'port' => env('MAIL_PORT', 587),
                'encryption' => env('MAIL_ENCRYPTION', 'tls'),
                'username' => env('MAIL_USERNAME'),
                'password' => env('MAIL_PASSWORD'),
            ],
        ],
        'from' => [
            'address' => env('MAIL_FROM_ADDRESS', 'noreply@evostat.com'),
            'name' => env('MAIL_FROM_NAME', 'EvoStat'),
        ],
    ],
];

echo "🧪 Test de Configuration Email EvoStat\n";
echo "=====================================\n\n";

// Vérifier la configuration
echo "📋 Configuration actuelle :\n";
echo "MAIL_HOST: " . (env('MAIL_HOST') ?: 'Non configuré') . "\n";
echo "MAIL_PORT: " . (env('MAIL_PORT') ?: 'Non configuré') . "\n";
echo "MAIL_USERNAME: " . (env('MAIL_USERNAME') ?: 'Non configuré') . "\n";
echo "MAIL_PASSWORD: " . (env('MAIL_PASSWORD') ? 'Configuré' : 'Non configuré') . "\n";
echo "MAIL_FROM_ADDRESS: " . (env('MAIL_FROM_ADDRESS') ?: 'Non configuré') . "\n\n";

// Test d'envoi
if (env('MAIL_USERNAME') && env('MAIL_PASSWORD')) {
    echo "✅ Configuration détectée !\n";
    echo "📧 Pour tester l'envoi, utilisez :\n";
    echo "php artisan tinker\n";
    echo ">>> Mail::raw('Test email EvoStat', function(\$msg) { \$msg->to('votre-email@test.com')->subject('Test EvoStat'); });\n\n";
} else {
    echo "❌ Configuration manquante !\n";
    echo "📝 Ajoutez ces lignes à votre fichier .env :\n\n";
    echo "MAIL_MAILER=smtp\n";
    echo "MAIL_HOST=smtp.gmail.com\n";
    echo "MAIL_PORT=587\n";
    echo "MAIL_USERNAME=votre-email@gmail.com\n";
    echo "MAIL_PASSWORD=votre-mot-de-passe-app\n";
    echo "MAIL_ENCRYPTION=tls\n";
    echo "MAIL_FROM_ADDRESS=\"noreply@evostat.com\"\n";
    echo "MAIL_FROM_NAME=\"EvoStat\"\n\n";
}

echo "🔗 Pour Gmail, créez un mot de passe d'application :\n";
echo "https://myaccount.google.com/apppasswords\n\n";

echo "📚 Documentation complète : CONFIGURATION_SECURITE.md\n";
