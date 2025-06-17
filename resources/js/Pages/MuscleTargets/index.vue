<script setup>
defineOptions({ layout: DefaultLayout })
import DefaultLayout from '@/Layouts/DefaultLayout.vue'
import { Link, router } from '@inertiajs/vue3'

defineProps({
    muscleCategories: Array,
    uncategorizedMuscleTargets: Array
})

function deleteMuscleTarget(id) {
    if (confirm('Voulez-vous vraiment supprimer ce muscle ?')) {
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

        <!-- Muscles par catégorie -->
        <div v-for="category in muscleCategories" :key="category.id" class="mb-6">
            <h2 class="text-2xl font-semibold mb-2">{{ category.name }}</h2>

            <div
                v-if="category.muscle_targets.length"
                v-for="muscleTarget in category.muscle_targets"
                :key="muscleTarget.id"
                class="border p-4 mb-2 rounded"
            >
                <h3 class="text-lg font-semibold">{{ muscleTarget.name }}</h3>
                <div class="mt-2 flex space-x-2">
                    <Link
                        :href="route('muscleTargets.edit', muscleTarget.id)"
                        class="text-white bg-yellow-500 hover:bg-yellow-600 px-3 py-1 rounded text-sm"
                    >
                        Modifier
                    </Link>
                    <button
                        @click="deleteMuscleTarget(muscleTarget.id)"
                        class="text-white bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-sm"
                    >
                        Supprimer
                    </button>
                </div>
            </div>

            <div v-else class="text-gray-500 italic">Aucun muscle dans cette catégorie</div>
        </div>

        <!-- Muscles sans catégorie -->
        <div v-if="uncategorizedMuscleTargets.length" class="mb-6">
            <h2 class="text-2xl font-semibold mb-2">Autres / Sans catégorie</h2>

            <div
                v-for="muscleTarget in uncategorizedMuscleTargets"
                :key="muscleTarget.id"
                class="border p-4 mb-2 rounded"
            >
                <h3 class="text-lg font-semibold">{{ muscleTarget.name }}</h3>
                <div class="mt-2 flex space-x-2">
                    <Link
                        :href="route('muscleTargets.edit', muscleTarget.id)"
                        class="text-white bg-yellow-500 hover:bg-yellow-600 px-3 py-1 rounded text-sm"
                    >
                        Modifier
                    </Link>
                    <button
                        @click="deleteMuscleTarget(muscleTarget.id)"
                        class="text-white bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-sm"
                    >
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
