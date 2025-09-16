<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import DefaultLayout from '@/Layouts/DefaultLayout.vue'
import { ref, onMounted, watch } from 'vue'

defineOptions({ layout: DefaultLayout })

const props = defineProps({
    exercise: Object,
    muscleTargets: Array,
})

const form = useForm({
    name: '',
    description: '',
    primary_muscle_target: '',
    secondary_muscle_targets: []
})

// Variables réactives pour les muscles
const primaryMuscle = ref('')
const secondaryMuscles = ref([])

// Fonction pour initialiser le formulaire
function initializeForm() {
    if (props.exercise) {
        form.name = props.exercise.name
        form.description = props.exercise.description
        
        // Utiliser muscle_targets (avec underscore) comme dans la modal
        const muscleTargets = props.exercise?.muscle_targets || props.exercise?.muscleTargets
        
        // Trouver le muscle principal (gérer les valeurs 1/0 et true/false)
        const primaryMuscleData = muscleTargets?.find(m => m.pivot.is_primary === true || m.pivot.is_primary === 1)
        primaryMuscle.value = primaryMuscleData ? Number(primaryMuscleData.id) : ''
        form.primary_muscle_target = primaryMuscle.value
        
        // Trouver les muscles secondaires (gérer les valeurs 1/0 et true/false)
        const secondaryMusclesData = muscleTargets?.filter(m => m.pivot.is_primary === false || m.pivot.is_primary === 0) || []
        secondaryMuscles.value = secondaryMusclesData.map(m => Number(m.id))
        form.secondary_muscle_targets = [...secondaryMuscles.value]
        
    }
}

// Initialiser le formulaire après le montage
onMounted(() => {
    initializeForm()
})

// Surveiller les changements de props.exercise
watch(() => props.exercise, () => {
    initializeForm()
}, { deep: true })

function submit() {
    form.put(route('exercises.update', props.exercise.id))
}

function getMuscleName(muscleId) {
    const muscle = props.muscleTargets.find(m => Number(m.id) === Number(muscleId))
    return muscle ? muscle.name : ''
}

function removeSecondaryMuscle(muscleId) {
    const index = secondaryMuscles.value.indexOf(muscleId)
    if (index > -1) {
        secondaryMuscles.value.splice(index, 1)
        form.secondary_muscle_targets = [...secondaryMuscles.value]
    }
}

function toggleMuscle(muscleId) {
    // Si c'est le muscle principal actuel
    if (Number(muscleId) === Number(primaryMuscle.value)) {
        // Décocher le muscle principal
        primaryMuscle.value = ''
        form.primary_muscle_target = ''
    } else {
        // Si c'est un muscle secondaire
        const secondaryIndex = secondaryMuscles.value.indexOf(muscleId)
        if (secondaryIndex > -1) {
            // Vérifier si ce muscle est déjà choisi comme muscle principal
            if (Number(muscleId) === Number(primaryMuscle.value)) {
                return // Empêcher la désélection
            }
            // Retirer des muscles secondaires
            secondaryMuscles.value.splice(secondaryIndex, 1)
        } else {
            // Vérifier si ce muscle est déjà choisi comme muscle principal
            if (Number(muscleId) === Number(primaryMuscle.value)) {
                return // Empêcher l'ajout
            }
            // Ajouter aux muscles secondaires
            secondaryMuscles.value.push(muscleId)
        }
        
        // Synchroniser avec le formulaire
        form.secondary_muscle_targets = [...secondaryMuscles.value]
    }
}

function toggleSecondaryMuscle(muscleId) {
    const index = secondaryMuscles.value.indexOf(muscleId)
    if (index > -1) {
        // Retirer le muscle
        secondaryMuscles.value.splice(index, 1)
    } else {
        // Ajouter le muscle
        secondaryMuscles.value.push(muscleId)
    }
    
    // Synchroniser avec le formulaire
    form.secondary_muscle_targets = [...secondaryMuscles.value]
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
                <p v-if="form.errors.primary_muscle_target" class="text-red-500 text-sm mt-1">
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
                            'bg-gray-100': Number(muscleTarget.id) === Number(primaryMuscle),
                            'bg-blue-50': secondaryMuscles.includes(Number(muscleTarget.id))
                        }"
                    >
                        <input
                            type="checkbox"
                            :checked="secondaryMuscles.includes(Number(muscleTarget.id)) || Number(muscleTarget.id) === Number(primaryMuscle)"
                            :disabled="Number(muscleTarget.id) === Number(primaryMuscle)"
                            @change="toggleMuscle(Number(muscleTarget.id))"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            :class="{
                                'opacity-50 cursor-not-allowed': Number(muscleTarget.id) === Number(primaryMuscle)
                            }"
                        />
                        <span 
                            class="text-sm"
                            :class="{
                                'font-medium': secondaryMuscles.includes(Number(muscleTarget.id)) || Number(muscleTarget.id) === Number(primaryMuscle)
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
