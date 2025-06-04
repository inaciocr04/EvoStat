<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import DefaultLayout from '@/Layouts/DefaultLayout.vue'

defineOptions({ layout: DefaultLayout })

const props = defineProps({
    exercise: Object,
})

const form = useForm({
    name: props.exercise.name,
    description: props.exercise.description,
    category_id: props.exercise.category_id,
})

function submit() {
    form.put(route('exercises.update', props.exercise.id))
}
</script>

<template>
    <Head title="Modifier l'exercice" />

    <div class="max-w-xl mx-auto p-6 bg-white shadow rounded">
        <h1 class="text-2xl font-bold mb-4">Modifier l'exercice</h1>

        <form @submit.prevent="submit" class="space-y-4">
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

            <div>
                <label class="block font-semibold">Description</label>
                <textarea
                    v-model="form.description"
                    class="w-full border border-gray-300 rounded px-3 py-2"
                ></textarea>
                <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">
                    {{ form.errors.description }}
                </div>
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
