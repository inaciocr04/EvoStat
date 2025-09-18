<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur Serveur - EvoStat</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
        <div class="mb-6">
            <div class="mb-4">
                <img src="/img/evostat_grand.png" alt="EvoStat Logo" class="h-16 mx-auto">
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-2">Erreur Serveur</h1>
            <p class="text-gray-600 mb-6">
                Une erreur interne s'est produite. Notre équipe a été notifiée et travaille à résoudre le problème.
            </p>
        </div>
        
        <div class="space-y-3">
            <a href="{{ route('exercises.index') }}" 
               class="block w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                🏋️ Retour aux exercices
            </a>
            
            @auth
                <a href="{{ route('planning.index') }}" 
                   class="block w-full bg-gray-200 text-gray-800 py-2 px-4 rounded-lg hover:bg-gray-300 transition-colors">
                    📅 Retour au planning
                </a>
            @endauth
            
            <button onclick="window.location.reload()" 
                    class="block w-full bg-gray-200 text-gray-800 py-2 px-4 rounded-lg hover:bg-gray-300 transition-colors">
                🔄 Réessayer
            </button>
        </div>
        
        <div class="mt-6 text-sm text-gray-500">
            <p>Code d'erreur : 500</p>
            <p class="mt-1">Temps : {{ now()->format('d/m/Y H:i') }}</p>
        </div>
        
        <!-- Actions recommandées -->
        <div class="mt-8 p-4 bg-gray-50 rounded-lg">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Que faire :</h3>
            <div class="text-xs text-gray-600 space-y-1">
                <p>• Attendez quelques minutes et réessayez</p>
                <p>• Vérifiez votre connexion internet</p>
                <p>• Contactez le support si le problème persiste</p>
            </div>
        </div>
    </div>
</body>
</html>