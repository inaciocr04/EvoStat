<script setup>
import { useForm } from '@inertiajs/vue3'
import { defineProps } from 'vue'

const props = defineProps({
    muscleTargets: Array,
})

const form = useForm({
    name: '',
    description: '',
    primary_muscle_target: '',
    secondary_muscle_targets: []
})

function submit() {
    form.post(route('exercises.store'))
}

function getMuscleName(muscleId) {
    const muscle = muscleTargets.find(m => Number(m.id) === Number(muscleId))
    return muscle ? muscle.name : ''
}

function removeSecondaryMuscle(muscleId) {
    const index = form.secondary_muscle_targets.indexOf(muscleId)
    if (index > -1) {
        form.secondary_muscle_targets.splice(index, 1)
    }
}

function toggleMuscle(muscleId) {
    console.log('Toggle muscle:', muscleId, 'Current primary:', form.primary_muscle_target, 'Current secondary:', form.secondary_muscle_targets)
    
    // Si c'est le muscle principal actuel
    if (Number(muscleId) === Number(form.primary_muscle_target)) {
        // Décocher le muscle principal
        form.primary_muscle_target = ''
        console.log('Unchecked primary muscle')
    } else {
        // Si c'est un muscle secondaire
        const secondaryIndex = form.secondary_muscle_targets.indexOf(muscleId)
        if (secondaryIndex > -1) {
            // Retirer des muscles secondaires
            form.secondary_muscle_targets.splice(secondaryIndex, 1)
            console.log('Removed from secondary:', form.secondary_muscle_targets)
        } else {
            // Ajouter aux muscles secondaires
            form.secondary_muscle_targets.push(muscleId)
            console.log('Added to secondary:', form.secondary_muscle_targets)
        }
    }
}

function toggleSecondaryMuscle(muscleId) {
    const index = form.secondary_muscle_targets.indexOf(muscleId)
    if (index > -1) {
        // Retirer le muscle
        form.secondary_muscle_targets.splice(index, 1)
    } else {
        // Ajouter le muscle
        form.secondary_muscle_targets.push(muscleId)
    }
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
                <label class="block font-semibold mb-1" for="primary_muscle_target">Muscle ciblé principal</label>
                <select
                    id="primary_muscle_target"
                    v-model="form.primary_muscle_target"
                    class="w-full border px-4 py-2 rounded"
                >
                    <option value="">Sélectionnez un muscle principal</option>
                    <option
                        v-for="muscleTarget in muscleTargets"
                        :key="muscleTarget.id"
                        :value="muscleTarget.id"
                    >
                        {{ muscleTarget.name }}
                    </option>
                </select>
                <p v-if="form.errors.primary_muscle_target" class="text-red-500 text-sm">
                    {{ form.errors.primary_muscle_target }}
                </p>
            </div>

            <div class="mb-4">
                <label class="block font-semibold mb-2" for="secondary_muscle_targets">Muscles ciblés secondaires</label>
                
                <!-- Affichage des muscles sélectionnés -->
                <div v-if="form.secondary_muscle_targets.length > 0" class="mb-3">
                    <div class="flex flex-wrap gap-2">
                        <span 
                            v-for="muscleId in form.secondary_muscle_targets" 
                            :key="muscleId"
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800"
                        >
                            {{ getMuscleName(muscleId) }}
                            <button 
                                type="button"
                                @click="removeSecondaryMuscle(muscleId)"
                                class="ml-2 text-blue-600 hover:text-blue-800"
                            >
                                ×
                            </button>
                        </span>
                    </div>
                </div>

                <!-- Sélecteur de muscles secondaires -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    <label 
                        v-for="muscleTarget in muscleTargets" 
                        :key="muscleTarget.id"
                        class="flex items-center space-x-2 p-2 border rounded cursor-pointer hover:bg-gray-50"
                        :class="{
                            'bg-gray-100': Number(muscleTarget.id) === Number(form.primary_muscle_target),
                            'bg-blue-50': form.secondary_muscle_targets.includes(Number(muscleTarget.id))
                        }"
                    >
                        <input
                            type="checkbox"
                            :checked="form.secondary_muscle_targets.includes(Number(muscleTarget.id)) || Number(muscleTarget.id) === Number(form.primary_muscle_target)"
                            @change="toggleMuscle(Number(muscleTarget.id))"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        />
                        <span 
                            class="text-sm"
                            :class="{
                                'font-medium': form.secondary_muscle_targets.includes(Number(muscleTarget.id)) || Number(muscleTarget.id) === Number(form.primary_muscle_target)
                            }"
                        >
                            {{ muscleTarget.name }}
                        </span>
                    </label>
                </div>
                
                <p v-if="form.errors.secondary_muscle_targets" class="text-red-500 text-sm mt-1">
                    {{ form.errors.secondary_muscle_targets }}
                </p>
                <p class="text-gray-600 text-sm mt-1">
                    Sélectionnez les muscles secondaires (optionnel)
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

