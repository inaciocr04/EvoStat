<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    likedExercises: Array,
});

const exercises = ref(props.likedExercises);

const toggleLike = async (exercise) => {
    try {
        const response = await axios.post(`/exercises/${exercise.id}/like`);
        
        // Supprimer l'exercice de la liste puisqu'il n'est plus liké
        exercises.value = exercises.value.filter(ex => ex.id !== exercise.id);
        
    } catch (error) {
        console.error('Erreur lors du toggle like:', error);
    }
};
</script>

<template>
    <Head title="Exercices Likés" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Mes Exercices Likés
                </h2>
                <Link :href="route('exercises.index')" 
                      class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Tous les exercices
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        
                        <div v-if="exercises.length === 0" class="text-center py-8">
                            <div class="text-gray-500 text-lg mb-4">
                                <i class="bi bi-heart text-4xl"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-700 mb-2">
                                Aucun exercice liké
                            </h3>
                            <p class="text-gray-500 mb-4">
                                Vous n'avez pas encore liké d'exercices.
                            </p>
                            <Link :href="route('exercises.index')" 
                                  class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Découvrir les exercices
                            </Link>
                        </div>

                        <div v-else>
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold text-gray-800">
                                    {{ exercises.length }} exercice{{ exercises.length > 1 ? 's' : '' }} liké{{ exercises.length > 1 ? 's' : '' }}
                                </h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div v-for="exercise in exercises" 
                                     :key="exercise.id"
                                     class="bg-white border border-gray-200 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                                    
                                    <div class="p-6">
                                        <div class="flex justify-between items-start mb-4">
                                            <h4 class="text-xl font-semibold text-gray-800">
                                                {{ exercise.name }}
                                            </h4>
                                            <button @click="toggleLike(exercise)"
                                                    class="text-red-500 hover:text-red-700 transition-colors duration-200">
                                                <i class="bi bi-heart-fill text-xl"></i>
                                            </button>
                                        </div>

                                        <p class="text-gray-600 mb-4 line-clamp-3">
                                            {{ exercise.description }}
                                        </p>

                                        <div v-if="exercise.muscle_targets && exercise.muscle_targets.length > 0" 
                                             class="mb-4">
                                            <div class="flex flex-wrap gap-2">
                                                <span v-for="target in exercise.muscle_targets" 
                                                      :key="target.id"
                                                      class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                                    {{ target.name }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="flex justify-between items-center text-sm text-gray-500">
                                            <span class="flex items-center">
                                                <i class="bi bi-heart-fill text-red-500 mr-1"></i>
                                                {{ exercise.likes_count }} like{{ exercise.likes_count > 1 ? 's' : '' }}
                                            </span>
                                            <span>
                                                Liké le {{ new Date(exercise.pivot?.created_at || exercise.created_at).toLocaleDateString('fr-FR') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
