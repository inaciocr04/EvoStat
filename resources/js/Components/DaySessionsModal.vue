<script setup>
import { computed } from 'vue'
import { format, parseISO } from 'date-fns'
import { fr } from 'date-fns/locale'

const props = defineProps({
    day: Object,
    isOpen: Boolean
})

const emit = defineEmits(['close', 'editWorkout', 'showSessionSummary'])

const daySessions = computed(() => {
    const sessions = []
    
    // Ajouter les séances programmées
    props.day.workouts.forEach(workout => {
        sessions.push({
            id: 'workout-' + workout.id,
            type: 'workout',
            name: workout.workout_template.name,
            time: workout.scheduled_time || '',
            status: workout.status,
            data: workout
        })
    })
    
    // Ajouter les séances réalisées
    props.day.completedSessions.forEach(session => {
        sessions.push({
            id: 'session-' + session.id,
            type: 'session',
            name: session.workout_template?.name || 'Séance',
            time: format(parseISO(session.completed_at), 'HH:mm'),
            data: session
        })
    })
    
    // Trier par heure si disponible
    return sessions.sort((a, b) => {
        if (a.time && b.time) {
            return a.time.localeCompare(b.time)
        }
        return 0
    })
})

const dayName = computed(() => {
    return format(props.day.date, 'EEEE d MMMM yyyy', { locale: fr })
})

function getStatusColor(status) {
    const colors = {
        scheduled: 'bg-blue-100 text-blue-800',
        completed: 'bg-green-100 text-green-800',
        skipped: 'bg-yellow-100 text-yellow-800',
        cancelled: 'bg-red-100 text-red-800',
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}

function getStatusIcon(status) {
    const icons = {
        scheduled: '📅',
        completed: '✅',
        skipped: '⏭️',
        cancelled: '❌',
    }
    return icons[status] || '❓'
}

function handleSessionClick(session) {
    if (session.type === 'workout') {
        emit('editWorkout', session.data)
    } else {
        emit('showSessionSummary', session.data)
    }
    emit('close')
}
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <span class="text-xl mr-2">📅</span>
                        Séances du {{ dayName }}
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

                <!-- Liste des séances -->
                <div class="space-y-3">
                    <div
                        v-for="session in daySessions"
                        :key="session.id"
                        class="p-4 rounded-lg border border-gray-200 hover:shadow-md transition-shadow cursor-pointer"
                        :class="session.type === 'workout' ? getStatusColor(session.status) : 'bg-green-50 border-green-200'"
                        @click="handleSessionClick(session)"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900">{{ session.name }}</h4>
                                <div class="flex items-center space-x-3 mt-1">
                                    <span class="text-sm px-2 py-1 rounded-full border" :class="session.type === 'workout' ? getStatusColor(session.status) : 'bg-green-100 text-green-800'">
                                        {{ session.type === 'workout' ? getStatusIcon(session.status) : '✅' }} 
                                        {{ session.type === 'workout' ? session.status : 'Terminée' }}
                                    </span>
                                    <span v-if="session.time" class="text-sm text-gray-600">
                                        {{ session.time }}
                                    </span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Message si aucune séance -->
                <div v-if="daySessions.length === 0" class="text-center py-8">
                    <div class="text-gray-400 text-4xl mb-4">📅</div>
                    <p class="text-gray-500">Aucune séance ce jour</p>
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
