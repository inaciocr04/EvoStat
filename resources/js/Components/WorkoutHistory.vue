<script setup>
import { computed } from 'vue'
import { format, parseISO, differenceInDays } from 'date-fns'
import { fr } from 'date-fns/locale'

const props = defineProps({
    historyWorkouts: Array
})

const groupedHistory = computed(() => {
    const groups = {}
    
    props.historyWorkouts.forEach(workout => {
        const date = parseISO(workout.scheduled_date)
        const dateKey = format(date, 'yyyy-MM-dd')
        
        if (!groups[dateKey]) {
            groups[dateKey] = {
                date: date,
                workouts: []
            }
        }
        
        groups[dateKey].workouts.push(workout)
    })
    
    // Trier par date décroissante
    return Object.keys(groups)
        .sort((a, b) => new Date(b) - new Date(a))
        .map(dateKey => groups[dateKey])
})

function getWorkoutDuration(workout) {
    if (workout.workout_session && workout.workout_session.total_duration) {
        const minutes = Math.floor(workout.workout_session.total_duration / 60)
        const seconds = workout.workout_session.total_duration % 60
        return `${minutes}:${seconds.toString().padStart(2, '0')}`
    }
    return 'N/A'
}

function getDaysAgo(date) {
    const days = differenceInDays(new Date(), date)
    if (days === 0) return "Aujourd'hui"
    if (days === 1) return "Hier"
    if (days < 7) return `Il y a ${days} jours`
    if (days < 30) return `Il y a ${Math.floor(days / 7)} semaine${Math.floor(days / 7) > 1 ? 's' : ''}`
    return `Il y a ${Math.floor(days / 30)} mois`
}

function getTotalExercises(workout) {
    if (workout.workout_session && workout.workout_session.session_exercises) {
        return workout.workout_session.session_exercises.length
    }
    return 'N/A'
}

function getTotalSets(workout) {
    if (workout.workout_session && workout.workout_session.session_exercises) {
        return workout.workout_session.session_exercises.reduce((total, exercise) => {
            return total + (exercise.sets ? exercise.sets.length : 0)
        }, 0)
    }
    return 'N/A'
}
</script>

<template>
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <span class="text-xl mr-2">📚</span>
                    Historique des séances
                </h3>
                <span class="text-sm text-gray-500">{{ historyWorkouts.length }} séances</span>
            </div>
        </div>
        
        <div class="p-6">
            <div v-if="groupedHistory.length === 0" class="text-center py-8">
                <div class="text-gray-400 text-4xl mb-4">🏋️‍♂️</div>
                <p class="text-gray-500">Aucune séance terminée pour le moment</p>
                <p class="text-sm text-gray-400 mt-2">Commencez par programmer et réaliser vos premières séances !</p>
            </div>
            
            <div v-else class="space-y-6">
                <div
                    v-for="group in groupedHistory"
                    :key="group.date.toISOString()"
                    class="border border-gray-200 rounded-lg p-4"
                >
                    <!-- En-tête de la date -->
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-blue-500 rounded-full flex items-center justify-center">
                                <span class="text-white font-bold text-sm">
                                    {{ format(group.date, 'd') }}
                                </span>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">
                                    {{ format(group.date, 'EEEE d MMMM yyyy', { locale: fr }) }}
                                </h4>
                                <p class="text-sm text-gray-500">{{ getDaysAgo(group.date) }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-500">{{ group.workouts.length }} séance{{ group.workouts.length > 1 ? 's' : '' }}</div>
                        </div>
                    </div>
                    
                    <!-- Séances du jour -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div
                            v-for="workout in group.workouts"
                            :key="workout.id"
                            class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors"
                        >
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <h5 class="font-medium text-gray-900 text-sm">{{ workout.workout_template.name }}</h5>
                                    <div class="flex items-center space-x-2 mt-1">
                                        <span class="text-xs px-2 py-1 bg-green-100 text-green-800 rounded-full">
                                            ✅ Terminée
                                        </span>
                                        <span v-if="workout.scheduled_time" class="text-xs text-gray-500">
                                            {{ workout.scheduled_time }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Statistiques de la séance -->
                            <div class="grid grid-cols-3 gap-3 text-center">
                                <div class="bg-white rounded-lg p-2">
                                    <div class="text-lg font-bold text-blue-600">{{ getTotalExercises(workout) }}</div>
                                    <div class="text-xs text-gray-500">Exercices</div>
                                </div>
                                <div class="bg-white rounded-lg p-2">
                                    <div class="text-lg font-bold text-green-600">{{ getTotalSets(workout) }}</div>
                                    <div class="text-xs text-gray-500">Séries</div>
                                </div>
                                <div class="bg-white rounded-lg p-2">
                                    <div class="text-lg font-bold text-purple-600">{{ getWorkoutDuration(workout) }}</div>
                                    <div class="text-xs text-gray-500">Durée</div>
                                </div>
                            </div>
                            
                            <!-- Notes si présentes -->
                            <div v-if="workout.notes" class="mt-3 p-2 bg-white rounded text-xs text-gray-600">
                                💬 {{ workout.notes }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
