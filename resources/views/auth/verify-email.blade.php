<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification Email - EvoStat</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
        <div class="mb-6">
            <!-- Logo EvoStat -->
            <div class="mb-4">
                <img src="/img/evostat_grand.png" alt="EvoStat Logo" class="h-16 mx-auto">
            </div>
            
            <div class="mx-auto w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Vérifiez votre email</h1>
            <p class="text-gray-600 mb-6">
                Nous avons envoyé un lien de vérification à votre adresse email. 
                Cliquez sur le lien pour activer votre compte.
            </p>
        </div>
        
        <div class="space-y-3">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" 
                        class="block w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                    📧 Renvoyer l'email de vérification
                </button>
            </form>
            
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" 
                        class="block w-full bg-gray-200 text-gray-800 py-2 px-4 rounded-lg hover:bg-gray-300 transition-colors">
                    🚪 Se déconnecter
                </button>
            </form>
        </div>
        
        <div class="mt-6 text-sm text-gray-500">
            <p>Vérifiez aussi votre dossier spam si vous ne recevez pas l'email.</p>
        </div>
        
        <!-- Instructions -->
        <div class="mt-8 p-4 bg-gray-50 rounded-lg">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Instructions :</h3>
            <div class="text-xs text-gray-600 space-y-1">
                <p>• Ouvrez votre boîte email</p>
                <p>• Cherchez l'email d'EvoStat</p>
                <p>• Cliquez sur le lien de vérification</p>
                <p>• Revenez sur EvoStat</p>
            </div>
        </div>
    </div>
</body>
</html>