<script setup>
import { useForm } from '@inertiajs/vue3'
defineOptions({ layout: DefaultLayout })
import DefaultLayout from '@/Layouts/DefaultLayout.vue'

const form = useForm({
    name: '',
    color:'',
})

const bgClasses = {
    evochest: 'bg-evochest',
    evolegs: 'bg-evolegs',
    evoback: 'bg-evoback',
    evoarm: 'bg-evoarm',
    evoshoulder: 'bg-evoshoulder',
    evocardio: 'bg-evocardio',
}

function submit() {
    form.post(route('muscleCategories.store'))
}
</script>

<template>
    <div class="max-w-xl mx-auto p-6 bg-white shadow rounded">
        <h1 class="text-2xl font-bold mb-4">Créer une catégorie de muscle</h1>

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
                <label class="block font-semibold mb-1" for="color">Couleur</label>
                <select
                    id="color"
                    v-model="form.color"
                    class="w-full border px-4 py-2 rounded"
                >
                    <option disabled value="">-- Sélectionner une couleur --</option>
                    <option value="evochest">Poitrine</option>
                    <option value="evolegs">Jambes</option>
                    <option value="evoback">Dos</option>
                    <option value="evoarm">Bras</option>
                    <option value="evoshoulder">Epaules</option>
                    <option value="evocardio">Cardio</option>
                </select>
                <p v-if="form.errors.color" class="text-red-500 text-sm">{{ form.errors.color }}</p>
            </div>

            <div
                v-if="form.color"
                class="w-32 h-12 rounded mt-2 flex items-center justify-center text-white font-bold"
                :class="bgClasses[form.color]"
            >
                Couleur sélectionnée : {{ form.color }}
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

