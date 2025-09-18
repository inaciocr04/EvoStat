<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès Refusé - EvoStat</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
        <div class="mb-6">
            <!-- Logo EvoStat -->
            <div class="mb-4">
                <img src="/img/evostat_grand.png" alt="EvoStat Logo" class="h-16 mx-auto">
            </div>
            
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Accès Refusé</h1>
            <p class="text-gray-600 mb-6">
                Vous n'avez pas les permissions nécessaires pour accéder à cette page.
            </p>
        </div>
        
        <div class="space-y-3">
            <a href="{{ url()->previous() }}" 
               class="block w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                ← Retour à la page précédente
            </a>
            
            <a href="{{ route('exercises.index') }}" 
               class="block w-full bg-gray-200 text-gray-800 py-2 px-4 rounded-lg hover:bg-gray-300 transition-colors">
                🏋️ Aller aux exercices
            </a>
            
            @auth
                <a href="{{ route('planning.index') }}" 
                   class="block w-full bg-gray-200 text-gray-800 py-2 px-4 rounded-lg hover:bg-gray-300 transition-colors">
                    📅 Aller au planning
                </a>
            @endauth
        </div>
        
        <div class="mt-6 text-sm text-gray-500">
            <p>Si vous pensez qu'il s'agit d'une erreur, contactez l'administrateur.</p>
        </div>
    </div>
</body>
</html>