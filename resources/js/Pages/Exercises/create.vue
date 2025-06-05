<script setup>
import { useForm } from '@inertiajs/vue3'
import { defineProps } from 'vue'

const props = defineProps({
    muscleTargets: Array,
})

const form = useForm({
    name: '',
    description: '',
    muscle_targets: props.exercise?.muscleTargets?.map(m => m.id) ?? []

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
            <div class="mb-4">
                <label>Muscles ciblés</label>
                <select
                    v-model="form.muscle_targets"
                    multiple
                    class="border rounded-md px-3 py-2 w-full text-gray-700"
                >
                    <option
                        v-for="muscleTarget in muscleTargets"
                        :key="muscleTarget.id"
                        :value="muscleTarget.id"
                    >
                        {{ muscleTarget.name }}
                    </option>
                </select>
                <p v-if="form.errors.muscle_targets" class="text-red-500 text-sm">
                    {{ form.errors.muscle_targets }}
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

