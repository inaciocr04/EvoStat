<script setup>
import { useForm } from '@inertiajs/vue3'
import { defineProps } from 'vue'

const props = defineProps({
    categories: Array
})

const form = useForm({
    name: '',
    description: '',
    category_id: '',
})

function submit() {
    form.post(route('exercises.store'))
}
</script>

<template>
    <div class="max-w-xl mx-auto p-6 bg-white shadow rounded">
        <h1 class="text-2xl font-bold mb-4">Créer un exercice</h1>

        <form @submit.prevent="submit">
            <div class="mb-4">
                <label class="block font-semibold mb-1" for="name">Nom</label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="w-full border px-4 py-2 rounded"
                />
                <p v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</p>
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-1" for="description">Description</label>
                <textarea
                    id="description"
                    v-model="form.description"
                    class="w-full border px-4 py-2 rounded"
                ></textarea>
                <p v-if="form.errors.description" class="text-red-500 text-sm">{{ form.errors.description }}</p>
            </div>

            <button
                type="submit"
                class="bg-evoblue text-white px-4 py-2 rounded hover:bg-evogreen transition"
            >
                Créer
            </button>
        </form>
    </div>
</template>

