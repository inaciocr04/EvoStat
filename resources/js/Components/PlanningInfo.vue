<script setup>
// Pas de logique nécessaire pour ce composant informatif
</script>

<template>
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-100 mb-6">
        <div class="flex items-start space-x-4">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
                    <span class="text-white text-xl">💡</span>
                </div>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Comment utiliser le Planning ?</h3>
                <div class="space-y-3 text-sm text-gray-700">
                    <div class="flex items-start space-x-2">
                        <span class="text-blue-600 font-bold">📅 Planning :</span>
                        <span>Programmez vos séances à l'avance pour organiser vos semaines. Utilisez "Programmer récurrent" pour créer des routines hebdomadaires.</span>
                    </div>
                    <div class="flex items-start space-x-2">
                        <span class="text-green-600 font-bold">📚 Historique :</span>
                        <span>Toutes vos séances réalisées (programmées ou non). Consultez vos statistiques et votre progression.</span>
                    </div>
                    <div class="flex items-start space-x-2">
                        <span class="text-purple-600 font-bold">💡 Astuce :</span>
                        <span>Vous pouvez faire des séances spontanées sans les programmer. Elles apparaîtront dans l'historique !</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
