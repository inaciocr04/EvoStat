<script setup>
import { computed } from 'vue'
import { format, parseISO } from 'date-fns'
import { fr } from 'date-fns/locale'

const props = defineProps({
    session: Object,
    isOpen: Boolean
})

const emit = defineEmits(['close'])

const sessionDate = computed(() => {
    if (props.session.completed_at) {
        return format(parseISO(props.session.completed_at), 'EEEE d MMMM yyyy', { locale: fr })
    }
    return 'Date non disponible'
})

const sessionTime = computed(() => {
    if (props.session.completed_at) {
        return format(parseISO(props.session.completed_at), 'HH:mm')
    }
    return 'N/A'
})

const sessionDuration = computed(() => {
    if (props.session.total_duration) {
        const minutes = Math.floor(props.session.total_duration / 60)
        const seconds = props.session.total_duration % 60
        return `${minutes}:${seconds.toString().padStart(2, '0')}`
    }
    return 'N/A'
})

const totalExercises = computed(() => {
    return props.session.session_exercises ? props.session.session_exercises.length : 0
})

const totalSets = computed(() => {
    if (!props.session.session_exercises) return 0
    
    return props.session.session_exercises.reduce((total, exercise) => {
        return total + (exercise.sets ? exercise.sets.length : 0)
    }, 0)
})

const totalReps = computed(() => {
    if (!props.session.session_exercises) return 0
    
    return props.session.session_exercises.reduce((total, exercise) => {
        if (exercise.sets) {
            return total + exercise.sets.reduce((exerciseTotal, set) => {
                return exerciseTotal + (set.reps || 0)
            }, 0)
        }
        return total
    }, 0)
})

const averageWeight = computed(() => {
    if (!props.session.session_exercises) return 0
    
    let totalWeight = 0
    let weightCount = 0
    
    props.session.session_exercises.forEach(exercise => {
        if (exercise.sets) {
            exercise.sets.forEach(set => {
                if (set.weight && set.weight > 0) {
                    totalWeight += set.weight
                    weightCount++
                }
            })
        }
    })
    
    return weightCount > 0 ? Math.round(totalWeight / weightCount) : 0
})
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <span class="text-xl mr-2">🏋️</span>
                        Résumé de la séance
                    </h3>
                    <button
                        @click="$emit('close')"
                        class="text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Informations de la séance -->
                <div class="space-y-4">
                    <!-- Nom et date -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900 text-lg">{{ session.workout_template?.name || 'Séance sans nom' }}</h4>
                        <div class="flex items-center space-x-4 mt-2 text-sm text-gray-600">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ sessionDate }}
                            </span>
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ sessionTime }}
                            </span>
                        </div>
                    </div>

                    <!-- Statistiques principales -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-blue-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-blue-600">{{ totalExercises }}</div>
                            <div class="text-sm text-blue-600">Exercices</div>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-green-600">{{ totalSets }}</div>
                            <div class="text-sm text-green-600">Séries</div>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-purple-600">{{ totalReps }}</div>
                            <div class="text-sm text-purple-600">Répétitions</div>
                        </div>
                        <div class="bg-orange-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-orange-600">{{ sessionDuration }}</div>
                            <div class="text-sm text-orange-600">Durée</div>
                        </div>
                    </div>

                    <!-- Poids moyen si disponible -->
                    <div v-if="averageWeight > 0" class="bg-gray-50 rounded-lg p-4 text-center">
                        <div class="text-lg font-semibold text-gray-900">Poids moyen</div>
                        <div class="text-2xl font-bold text-gray-700">{{ averageWeight }} kg</div>
                    </div>

                    <!-- Liste des exercices -->
                    <div v-if="session.session_exercises && session.session_exercises.length > 0">
                        <h5 class="font-semibold text-gray-900 mb-3">Exercices réalisés</h5>
                        <div class="space-y-2">
                            <div
                                v-for="exercise in session.session_exercises"
                                :key="exercise.id"
                                class="bg-white border border-gray-200 rounded-lg p-3"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-900">{{ exercise.exercise?.name || 'Exercice' }}</span>
                                    <span class="text-sm text-gray-500">{{ exercise.sets?.length || 0 }} séries</span>
                                </div>
                                <div v-if="exercise.sets && exercise.sets.length > 0" class="mt-2 text-xs text-gray-600">
                                    <div class="flex flex-wrap gap-1">
                                        <span
                                            v-for="(set, index) in exercise.sets"
                                            :key="index"
                                            class="bg-gray-100 px-2 py-1 rounded"
                                        >
                                            {{ set.reps || 0 }}×{{ set.weight || 0 }}kg
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bouton de fermeture -->
                <div class="mt-6">
                    <button
                        @click="$emit('close')"
                        class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
                    >
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
