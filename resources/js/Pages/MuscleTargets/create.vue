<script setup>
import { useForm } from '@inertiajs/vue3'
import { defineProps } from 'vue'

const props = defineProps({
    muscleCategories: Array
})

const form = useForm({
    name: '',
    muscle_category_id: ''
})

function submit() {
    form.post(route('muscleTargets.store'))
}
</script>

<template>
    <div class="max-w-xl mx-auto p-6 bg-white shadow rounded">
        <h1 class="text-2xl font-bold mb-4">Créer un muscle</h1>

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
                <label>Catégorie du muscle</label>
                <select
                    v-model="form.muscle_category_id"
                    class="border rounded-md px-3 py-2 w-full text-gray-700"
                >
                    <option
                        v-for="muscleCategory in muscleCategories"
                        :key="muscleCategory.id"
                        :value="muscleCategory.id"
                    >
                        {{ muscleCategory.name }}
                    </option>
                </select>
                <p v-if="form.errors.muscleCategory_id" class="text-red-500 text-sm">
                    {{ form.errors.muscleCategory_id }}
                </p>

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

