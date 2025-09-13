<script setup>
defineOptions({ layout: DefaultLayout })
import DefaultLayout from '@/Layouts/DefaultLayout.vue'
import { Link, router } from '@inertiajs/vue3'

defineProps({
    muscleCategories: Array
})

// Fonction pour supprimer un exercice
function deletemuscleCategory(id) {
    if (confirm('Voulez-vous vraiment supprimer cet exercice ?')) {
        router.delete(route('muscleCategories.destroy', id))
    }
}

const bgClasses = {
    evochest: 'bg-evochest',
    evolegs: 'bg-evolegs',
    evoback: 'bg-evoback',
    evoarm: 'bg-evoarm',
    evoshoulder: 'bg-evoshoulder',
    evocardio: 'bg-evocardio',
}
</script>

<template>
    <div class="max-w-4xl mx-auto p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Liste des muscles</h1>
            <Link
                :href="route('muscleCategories.create')"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition"
            >
                + Nouveau Muscle
            </Link>
        </div>

        <div
            v-for="muscleCategory in muscleCategories"
            :key="muscleCategory.id"
            class="border p-4 mb-4 rounded"
            :class="muscleCategory.color ? bgClasses[muscleCategory.color] : 'bg-gray-500'"
        >
            <h2 class="text-xl font-semibold">{{ muscleCategory.name }}</h2>

            <div class="mt-4 flex space-x-2">
                <Link
                    :href="route('muscleCategories.edit', muscleCategory.id)"
                    class="text-white bg-yellow-500 hover:bg-yellow-600 px-3 py-1 rounded text-sm"
                >
                    Modifier
                </Link>
                <button
                    @click="deletemuscleCategory(muscleCategory.id)"
                    class="text-white bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-sm"
                >
                    Supprimer
                </button>
            </div>
        </div>
    </div>
</template>
