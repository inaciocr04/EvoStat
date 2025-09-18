<script setup>
import { router } from '@inertiajs/vue3'

const props = defineProps({
    workout: Object
})

function startWorkout() {
    router.post(route('planning.start', props.workout.id))
}

function skipWorkout() {
    if (confirm('Êtes-vous sûr de vouloir marquer cette séance comme ignorée ?')) {
        router.post(route('planning.skip', props.workout.id))
    }
}

function deleteWorkout() {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette séance programmée ?')) {
        router.delete(route('planning.destroy', props.workout.id))
    }
}

function getStatusColor(status) {
    const colors = {
        scheduled: 'bg-blue-100 text-blue-800 border-blue-200',
        completed: 'bg-green-100 text-green-800 border-green-200',
        skipped: 'bg-yellow-100 text-yellow-800 border-yellow-200',
        cancelled: 'bg-red-100 text-red-800 border-red-200',
    }
    return colors[status] || 'bg-gray-100 text-gray-800 border-gray-200'
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
</script>

<template>
    <div class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-3">
            <div class="flex-1">
                <h4 class="font-semibold text-gray-900 text-sm">{{ workout.workout_template.name }}</h4>
                <div class="flex items-center space-x-2 mt-1">
                    <span class="text-xs px-2 py-1 rounded-full border" :class="getStatusColor(workout.status)">
                        {{ getStatusIcon(workout.status) }} {{ workout.status }}
                    </span>
                    <span v-if="workout.scheduled_time" class="text-xs text-gray-500">
                        {{ workout.scheduled_time }}
                    </span>
                </div>
            </div>
        </div>
        
        <div v-if="workout.notes" class="text-xs text-gray-600 mb-3 bg-gray-50 p-2 rounded">
            {{ workout.notes }}
        </div>
        
        <!-- Actions rapides -->
        <div v-if="workout.status === 'scheduled'" class="flex space-x-2">
            <button
                @click="startWorkout"
                class="flex-1 px-3 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors text-xs font-medium"
            >
                🏋️ Démarrer
            </button>
            <button
                @click="skipWorkout"
                class="flex-1 px-3 py-2 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 transition-colors text-xs font-medium"
            >
                ⏭️ Ignorer
            </button>
            <button
                @click="deleteWorkout"
                class="px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-xs font-medium"
            >
                🗑️
            </button>
        </div>
        
        <div v-else class="text-xs text-gray-500 text-center">
            Séance {{ workout.status === 'completed' ? 'terminée' : 'ignorée' }}
        </div>
    </div>
</template>
