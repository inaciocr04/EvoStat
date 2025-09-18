<script setup>
import { computed } from 'vue'
import { format, parseISO, startOfWeek, endOfWeek, eachWeekOfInterval, isWithinInterval } from 'date-fns'
import { fr } from 'date-fns/locale'

const props = defineProps({
    historyWorkouts: Array
})

const totalWorkouts = computed(() => props.historyWorkouts.length)

const totalDuration = computed(() => {
    return props.historyWorkouts.reduce((total, workout) => {
        if (workout.workout_session && workout.workout_session.total_duration) {
            return total + workout.workout_session.total_duration
        }
        return total
    }, 0)
})

const totalExercises = computed(() => {
    return props.historyWorkouts.reduce((total, workout) => {
        if (workout.workout_session && workout.workout_session.session_exercises) {
            return total + workout.workout_session.session_exercises.length
        }
        return total
    }, 0)
})

const totalSets = computed(() => {
    return props.historyWorkouts.reduce((total, workout) => {
        if (workout.workout_session && workout.workout_session.session_exercises) {
            return workout.workout_session.session_exercises.reduce((exerciseTotal, exercise) => {
                return exerciseTotal + (exercise.sets ? exercise.sets.length : 0)
            }, 0)
        }
        return total
    }, 0)
})

const weeklyStats = computed(() => {
    if (props.historyWorkouts.length === 0) return []
    
    const workouts = props.historyWorkouts.map(workout => ({
        ...workout,
        date: parseISO(workout.scheduled_date)
    }))
    
    const firstWorkout = workouts[workouts.length - 1]
    const lastWorkout = workouts[0]
    
    const weeks = eachWeekOfInterval({
        start: startOfWeek(firstWorkout.date),
        end: endOfWeek(lastWorkout.date)
    })
    
    return weeks.map(weekStart => {
        const weekEnd = endOfWeek(weekStart)
        const weekWorkouts = workouts.filter(workout => 
            isWithinInterval(workout.date, { start: weekStart, end: weekEnd })
        )
        
        return {
            weekStart,
            weekEnd,
            workouts: weekWorkouts,
            count: weekWorkouts.length
        }
    }).reverse() // Plus récent en premier
})

const averagePerWeek = computed(() => {
    if (weeklyStats.value.length === 0) return 0
    const totalWeeks = weeklyStats.value.length
    const totalWorkouts = weeklyStats.value.reduce((sum, week) => sum + week.count, 0)
    return Math.round((totalWorkouts / totalWeeks) * 10) / 10
})

const monthlyStats = computed(() => {
    if (props.historyWorkouts.length === 0) return []
    
    const workouts = props.historyWorkouts.map(workout => ({
        ...workout,
        date: parseISO(workout.scheduled_date)
    }))
    
    const groups = {}
    
    workouts.forEach(workout => {
        const monthKey = format(workout.date, 'yyyy-MM')
        const monthName = format(workout.date, 'MMMM yyyy', { locale: fr })
        
        if (!groups[monthKey]) {
            groups[monthKey] = {
                monthKey,
                monthName,
                count: 0,
                workouts: []
            }
        }
        
        groups[monthKey].count++
        groups[monthKey].workouts.push(workout)
    })
    
    return Object.keys(groups)
        .sort((a, b) => b.localeCompare(a)) // Plus récent en premier
        .map(monthKey => groups[monthKey])
        .slice(0, 12) // 12 derniers mois
})

function formatDuration(seconds) {
    const hours = Math.floor(seconds / 3600)
    const minutes = Math.floor((seconds % 3600) / 60)
    
    if (hours > 0) {
        return `${hours}h ${minutes}min`
    }
    return `${minutes}min`
}
</script>

<template>
    <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-xl p-6 border border-green-100 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <span class="text-xl mr-2">🏆</span>
                Statistiques globales
            </h3>
            <span class="text-sm text-gray-500">{{ totalWorkouts }} séances terminées</span>
        </div>
        
        <!-- Statistiques principales -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ totalWorkouts }}</div>
                <div class="text-sm text-gray-600">Séances</div>
            </div>
            <div class="bg-white rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-green-600">{{ totalExercises }}</div>
                <div class="text-sm text-gray-600">Exercices</div>
            </div>
            <div class="bg-white rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-purple-600">{{ totalSets }}</div>
                <div class="text-sm text-gray-600">Séries</div>
            </div>
            <div class="bg-white rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-orange-600">{{ formatDuration(totalDuration) }}</div>
                <div class="text-sm text-gray-600">Temps total</div>
            </div>
        </div>
        
        <!-- Statistiques mensuelles -->
        <div v-if="monthlyStats.length > 0" class="mb-6">
            <h4 class="text-sm font-medium text-gray-700 mb-3">Progression mensuelle</h4>
            <div class="bg-white rounded-lg p-4">
                <div class="space-y-3">
                    <div
                        v-for="month in monthlyStats"
                        :key="month.monthKey"
                        class="flex items-center justify-between text-sm"
                    >
                        <span class="text-gray-600 font-medium">{{ month.monthName }}</span>
                        <div class="flex items-center space-x-3">
                            <div class="w-24 bg-gray-200 rounded-full h-3">
                                <div 
                                    class="bg-gradient-to-r from-blue-500 to-purple-500 h-3 rounded-full transition-all duration-300"
                                    :style="{ width: `${Math.min((month.count / 30) * 100, 100)}%` }"
                                ></div>
                            </div>
                            <span class="font-bold text-gray-900 w-8 text-right">{{ month.count }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistiques hebdomadaires -->
        <div v-if="weeklyStats.length > 0">
            <h4 class="text-sm font-medium text-gray-700 mb-3">Progression hebdomadaire</h4>
            <div class="bg-white rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm text-gray-600">Moyenne par semaine</span>
                    <span class="font-semibold text-gray-900">{{ averagePerWeek }} séances</span>
                </div>
                
                <div class="space-y-2">
                    <div
                        v-for="week in weeklyStats.slice(0, 8)"
                        :key="week.weekStart.toISOString()"
                        class="flex items-center justify-between text-sm"
                    >
                        <span class="text-gray-600">
                            Semaine du {{ format(week.weekStart, 'd MMM', { locale: fr }) }}
                        </span>
                        <div class="flex items-center space-x-2">
                            <div class="w-20 bg-gray-200 rounded-full h-2">
                                <div 
                                    class="bg-gradient-to-r from-green-500 to-blue-500 h-2 rounded-full transition-all duration-300"
                                    :style="{ width: `${Math.min((week.count / 7) * 100, 100)}%` }"
                                ></div>
                            </div>
                            <span class="font-medium text-gray-900 w-8 text-right">{{ week.count }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
