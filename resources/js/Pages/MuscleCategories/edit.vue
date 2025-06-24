<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import DefaultLayout from '@/Layouts/DefaultLayout.vue'

defineOptions({ layout: DefaultLayout })

const props = defineProps({
    muscleCategory: Object,
})

const form = useForm({
    name: props.muscleCategory.name,
    color: props.muscleCategory.color,
})

const bgClasses = {
    evochest: 'bg-evochest',
    evolegs: 'bg-evolegs',
    evoback: 'bg-evoback',
    evoarm: 'bg-evoarm',
}

function submit() {
    form.put(route('muscleCategories.update', props.muscleCategory.id))
}
</script>

<template>
    <Head title="Modifier le muscle" />

    <div class="max-w-xl mx-auto p-6 bg-white shadow rounded">
        <h1 class="text-2xl font-bold mb-4">Modifier la catégorie</h1>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block font-semibold mb-1">Nom</label>
                <input
                    v-model="form.name"
                    type="text"
                    class="w-full border border-gray-300 rounded px-3 py-2"
                />
                <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                    {{ form.errors.name }}
                </div>
            </div>

            <div>
                <label class="block font-semibold mb-1">Couleur</label>
                <select
                    v-model="form.color"
                    class="w-full border border-gray-300 rounded px-3 py-2"
                >
                    <option disabled value="">-- Sélectionner une couleur --</option>
                    <option value="evochest">Poitrine</option>
                    <option value="evolegs">Jambes</option>
                    <option value="evoback">Dos</option>
                    <option value="evoarm">Bras</option>
                </select>
                <div v-if="form.errors.color" class="text-red-500 text-sm mt-1">
                    {{ form.errors.color }}
                </div>
            </div>

            <div
                v-if="form.color"
                class="w-32 h-12 rounded mt-2 flex items-center justify-center text-white font-bold"
                :class="bgClasses[form.color]"
            >
                {{ form.color }}
            </div>

            <div class="flex justify-end">
                <button
                    type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                    :disabled="form.processing"
                >
                    Mettre à jour
                </button>
            </div>
        </form>
    </div>
</template>
