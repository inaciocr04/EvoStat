<script setup>
defineOptions({ layout: DefaultLayout })
import DefaultLayout from '@/Layouts/DefaultLayout.vue'
import { Link, router } from '@inertiajs/vue3'

defineProps({
    muscleTargets: Array
})

// Fonction pour supprimer un exercice
function deletemuscleTarget(id) {
    if (confirm('Voulez-vous vraiment supprimer cet exercice ?')) {
        router.delete(route('muscleTargets.destroy', id))
    }
}
</script>

<template>
    <div class="max-w-4xl mx-auto p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Liste des muscles</h1>
            <Link
                :href="route('muscleTargets.create')"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition"
            >
                + Nouveau Muscle
            </Link>
        </div>

        <div
            v-for="muscleTarget in muscleTargets"
            :key="muscleTarget.id"
            class="border p-4 mb-4 rounded"
        >
            <h2 class="text-xl font-semibold">{{ muscleTarget.name }}</h2>

            <div class="mt-4 flex space-x-2">
                <Link
                    :href="route('muscleTargets.edit', muscleTarget.id)"
                    class="text-white bg-yellow-500 hover:bg-yellow-600 px-3 py-1 rounded text-sm"
                >
                    Modifier
                </Link>
                <button
                    @click="deletemuscleTarget(muscleTarget.id)"
                    class="text-white bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-sm"
                >
                    Supprimer
                </button>
            </div>
        </div>
    </div>
</template>
