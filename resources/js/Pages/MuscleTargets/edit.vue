<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import DefaultLayout from '@/Layouts/DefaultLayout.vue'

defineOptions({ layout: DefaultLayout })

const props = defineProps({
    muscleTarget: Object,
    muscleCategories: Array
})

const form = useForm({
    name: props.muscleTarget.name,
    muscle_category_id: props.muscleTarget.muscle_category_id ?? ''
})

function submit() {
    form.put(route('muscleTargets.update', props.muscleTarget.id))
}
</script>

<template>
    <Head title="Modifier le muscle" />

    <div class="max-w-xl mx-auto p-6 bg-white shadow rounded">
        <h1 class="text-2xl font-bold mb-4">Modifier le muscle</h1>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Nom -->
            <div>
                <label class="block font-semibold">Nom</label>
                <input
                    v-model="form.name"
                    type="text"
                    class="w-full border border-gray-300 rounded px-3 py-2"
                />
                <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                    {{ form.errors.name }}
                </div>
            </div>

            <!-- Catégorie -->
            <div>
                <label class="block font-semibold">Catégorie du muscle</label>
                <select
                    v-model="form.muscle_category_id"
                    class="w-full border border-gray-300 rounded px-3 py-2"
                >
                    <option value="">-- Aucune --</option>
                    <option
                        v-for="category in muscleCategories"
                        :key="category.id"
                        :value="category.id"
                    >
                        {{ category.name }}
                    </option>
                </select>
                <div v-if="form.errors.muscle_category_id" class="text-red-500 text-sm mt-1">
                    {{ form.errors.muscle_category_id }}
                </div>
            </div>

            <!-- Submit -->
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
