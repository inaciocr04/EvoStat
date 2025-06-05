<script setup>
defineOptions({ layout: DefaultLayout })
import DefaultLayout from '@/Layouts/DefaultLayout.vue'
import { Link, router } from '@inertiajs/vue3'

defineProps({
    exercises: Array,
    muscleTargets: Array
})

// Fonction pour supprimer un exercice
function deleteExercise(id) {
    if (confirm('Voulez-vous vraiment supprimer cet exercice ?')) {
        router.delete(route('exercises.destroy', id))
    }
}
</script>

<template>
    <div class="max-w-4xl mx-auto p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Liste des exercices</h1>
            <Link
                :href="route('exercises.create')"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition"
            >
                + Nouvel exercice
            </Link>
            <Link
                :href="route('muscleTargets.index')"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition"
            >
                Liste des muscles
            </Link>
        </div>

        <div
            v-for="exercise in exercises"
            :key="exercise.id"
            class="border p-4 mb-4 rounded"
        >
            <h2 class="text-xl font-semibold">{{ exercise.name }}</h2>
            <p class="text-gray-700">{{ exercise.description || 'Aucune description' }}</p>
            <div v-if="exercise.muscle_targets?.length">
                <p class="mt-2 font-semibold">Muscles ciblés :</p>
                <ul class="list-disc list-inside">
                    <li
                        v-for="muscle in exercise.muscle_targets"
                        :key="muscle.id"
                    >
                        {{ muscle.name }}
                    </li>
                </ul>
            </div>
            <div v-else>
                <p class="italic text-gray-500">Aucun muscle ciblé</p>
            </div>

            <div class="mt-4 flex space-x-2">
                <Link
                    :href="route('exercises.edit', exercise.id)"
                    class="text-white bg-yellow-500 hover:bg-yellow-600 px-3 py-1 rounded text-sm"
                >
                    Modifier
                </Link>
                <button
                    @click="deleteExercise(exercise.id)"
                    class="text-white bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-sm"
                >
                    Supprimer
                </button>
            </div>
        </div>
    </div>
</template>
