<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import DefaultLayout from '@/Layouts/DefaultLayout.vue'
import PlanningStats from '@/Components/PlanningStats.vue'
import WorkoutQuickActions from '@/Components/WorkoutQuickActions.vue'
import WorkoutHistory from '@/Components/WorkoutHistory.vue'
import HistoryStats from '@/Components/HistoryStats.vue'
import RecurringWorkoutModal from '@/Components/RecurringWorkoutModal.vue'
import PlanningInfo from '@/Components/PlanningInfo.vue'
import WorkoutSummary from '@/Components/WorkoutSummary.vue'
import DaySessionsModal from '@/Components/DaySessionsModal.vue'
import { format, startOfMonth, endOfMonth, eachDayOfInterval, isSameMonth, isSameDay, addMonths, subMonths, parseISO, isToday } from 'date-fns'
import { fr } from 'date-fns/locale'

defineOptions({ layout: DefaultLayout })

const props = defineProps({
    scheduledWorkouts: Array,
    completedSessions: Array,
    workoutTemplates: Array,
    currentMonth: String,
    stats: Object,
    historyWorkouts: Array,
})

const showModal = ref(false)
const selectedDate = ref(null)
const selectedWorkout = ref(null)
const selectedSession = ref(null)
const showSummaryModal = ref(false)
const selectedDay = ref(null)
const showDayModal = ref(false)
const activeTab = ref('calendar') // 'calendar' ou 'history'

// Variables pour suppression en masse
const showBulkDeleteModal = ref(false)
const selectedWorkoutsForDelete = ref([])
const selectAllWorkouts = ref(false)
const form = ref({
    workout_template_id: '',
    scheduled_date: '',
    scheduled_time: '',
    notes: '',
})

const currentDate = computed(() => parseISO(props.currentMonth + '-01'))
const monthStart = computed(() => startOfMonth(currentDate.value))
const monthEnd = computed(() => endOfMonth(currentDate.value))
const calendarDays = computed(() => {
    const days = eachDayOfInterval({ start: monthStart.value, end: monthEnd.value })
    return days.map(day => ({
        date: day,
        workouts: props.scheduledWorkouts.filter(workout => 
            isSameDay(parseISO(workout.scheduled_date), day)
        ),
        completedSessions: props.completedSessions.filter(session => 
            session.completed_at && isSameDay(parseISO(session.completed_at), day)
        )
    }))
})

const monthName = computed(() => format(currentDate.value, 'MMMM yyyy', { locale: fr }))

const todayWorkouts = computed(() => {
    const today = new Date()
    return props.scheduledWorkouts.filter(workout => 
        isSameDay(parseISO(workout.scheduled_date), today)
    )
})

function openModal(date = null) {
    selectedDate.value = date
    form.value.scheduled_date = date ? format(date, 'yyyy-MM-dd') : ''
    selectedWorkout.value = null // S'assurer qu'on est en mode création
    showModal.value = true
}

function closeModal() {
    showModal.value = false
    selectedWorkout.value = null
    form.value = {
        workout_template_id: '',
        scheduled_date: '',
        scheduled_time: '',
        notes: '',
    }
}

function submitForm() {
    router.post(route('planning.store'), form.value, {
        onSuccess: () => {
            closeModal()
        }
    })
}

function editWorkout(workout) {
    selectedWorkout.value = workout
    console.log('Editing workout:', workout) // Debug
    
    // Gérer différents formats de date
    let formattedDate = ''
    if (workout.scheduled_date) {
        try {
            // Si c'est déjà au format YYYY-MM-DD
            if (workout.scheduled_date.match(/^\d{4}-\d{2}-\d{2}$/)) {
                formattedDate = workout.scheduled_date
            } else {
                // Sinon, parser et formater
                formattedDate = format(parseISO(workout.scheduled_date), 'yyyy-MM-dd')
            }
        } catch (error) {
            console.error('Date parsing error:', error)
            formattedDate = workout.scheduled_date
        }
    }
    
    form.value = {
        workout_template_id: workout.workout_template_id,
        scheduled_date: formattedDate,
        scheduled_time: workout.scheduled_time || '',
        notes: workout.notes || '',
    }
    console.log('Form data:', form.value) // Debug
    showModal.value = true
}

function updateWorkout() {
    router.put(route('planning.update', selectedWorkout.value.id), form.value, {
        onSuccess: () => {
            closeModal()
        }
    })
}

function deleteWorkout(workout) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette séance programmée ?')) {
        router.delete(route('planning.destroy', workout.id))
    }
}

function startWorkout(workout) {
    router.post(route('planning.start', workout.id))
}

function skipWorkout(workout) {
    router.post(route('planning.skip', workout.id))
}

function navigateMonth(direction) {
    const newMonth = direction === 'next' 
        ? addMonths(currentDate.value, 1)
        : subMonths(currentDate.value, 1)
    
    router.get(route('planning.index'), { month: format(newMonth, 'yyyy-MM') })
}

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

function showSessionSummary(session) {
    selectedSession.value = session
    showSummaryModal.value = true
}

function closeSummaryModal() {
    showSummaryModal.value = false
    selectedSession.value = null
}

function getDaySessions(day) {
    const sessions = []
    
    // Ajouter les séances programmées
    day.workouts.forEach(workout => {
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
    day.completedSessions.forEach(session => {
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
}

function showDayDetails(day) {
    selectedDay.value = day
    showDayModal.value = true
}

function closeDayModal() {
    showDayModal.value = false
    selectedDay.value = null
}

// Fonctions pour suppression en masse
function openBulkDeleteModal() {
    selectedWorkoutsForDelete.value = []
    selectAllWorkouts.value = false
    showBulkDeleteModal.value = true
}

function closeBulkDeleteModal() {
    showBulkDeleteModal.value = false
    selectedWorkoutsForDelete.value = []
    selectAllWorkouts.value = false
}

function toggleWorkoutSelection(workoutId) {
    const index = selectedWorkoutsForDelete.value.indexOf(workoutId)
    if (index > -1) {
        selectedWorkoutsForDelete.value.splice(index, 1)
    } else {
        selectedWorkoutsForDelete.value.push(workoutId)
    }
}

function toggleSelectAll() {
    if (selectAllWorkouts.value) {
        selectedWorkoutsForDelete.value = props.scheduledWorkouts
            .filter(workout => workout.status === 'scheduled')
            .map(workout => workout.id)
    } else {
        selectedWorkoutsForDelete.value = []
    }
}

function confirmBulkDelete() {
    if (selectedWorkoutsForDelete.value.length === 0) return
    
    router.post(route('planning.bulk-delete'), {
        workout_ids: selectedWorkoutsForDelete.value
    }, {
        onSuccess: () => {
            closeBulkDeleteModal()
        }
    })
}

const scheduledWorkoutsOnly = computed(() => {
    return props.scheduledWorkouts.filter(workout => workout.status === 'scheduled')
})
</script>

<template>
    <Head title="Planning" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header avec statistiques -->
        <div class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">📅 Planning</h1>
                        <p class="text-gray-600 mt-1">Organisez vos séances d'entraînement</p>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                        <button
                            @click="openModal()"
                            class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-600 text-white font-medium rounded-lg hover:from-blue-600 hover:to-purple-700 transition-all duration-200 shadow-md hover:shadow-lg touch-manipulation"
                        >
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            <span class="hidden sm:inline">Programmer une séance</span>
                            <span class="sm:hidden">Programmer</span>
                        </button>
                        
                        <!-- Bouton pour séances récurrentes -->
                        <RecurringWorkoutModal :workout-templates="workoutTemplates" />
                        
                        <!-- Bouton suppression en masse -->
                        <button
                            v-if="scheduledWorkoutsOnly.length > 0"
                            @click="openBulkDeleteModal"
                            class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-all duration-200 shadow-md hover:shadow-lg touch-manipulation"
                        >
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <span class="hidden sm:inline">Supprimer en masse</span>
                            <span class="sm:hidden">Supprimer</span>
                        </button>
                    </div>
                </div>

                <!-- Statistiques du mois -->
                <div class="mt-6">
                    <PlanningStats :stats="stats" :current-month="currentMonth" :completed-sessions="completedSessions" />
                </div>
            </div>
        </div>

        <!-- Séances d'aujourd'hui -->
        <div v-if="todayWorkouts.length > 0" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <span class="text-xl mr-2">🏋️</span>
                    Séances d'aujourd'hui
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <WorkoutQuickActions
                        v-for="workout in todayWorkouts"
                        :key="workout.id"
                        :workout="workout"
                    />
                </div>
            </div>
        </div>

        <!-- Onglets -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="bg-white rounded-lg shadow-sm border">
                <div class="flex border-b border-gray-200">
                    <button
                        @click="activeTab = 'calendar'"
                        :class="[
                            'flex-1 px-3 sm:px-6 py-3 sm:py-4 text-sm font-medium transition-colors touch-manipulation',
                            activeTab === 'calendar' 
                                ? 'text-blue-600 border-b-2 border-blue-600 bg-blue-50' 
                                : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'
                        ]"
                    >
                        <span class="mr-1 sm:mr-2">📅</span>
                        <span class="hidden sm:inline">Calendrier</span>
                        <span class="sm:hidden">Cal.</span>
                    </button>
                    <button
                        @click="activeTab = 'history'"
                        :class="[
                            'flex-1 px-3 sm:px-6 py-3 sm:py-4 text-sm font-medium transition-colors touch-manipulation',
                            activeTab === 'history' 
                                ? 'text-blue-600 border-b-2 border-blue-600 bg-blue-50' 
                                : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'
                        ]"
                    >
                        <span class="mr-1 sm:mr-2">📚</span>
                        <span class="hidden sm:inline">Historique</span>
                        <span class="sm:hidden">Hist.</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Contenu des onglets -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
            <!-- Info sur le planning -->
            <PlanningInfo />
            
            <!-- Calendrier -->
            <div v-if="activeTab === 'calendar'" class="bg-white rounded-lg shadow-sm border">
                <!-- Navigation du calendrier -->
                <div class="flex items-center justify-between p-3 sm:p-6 border-b">
                    <button
                        @click="navigateMonth('prev')"
                        class="p-2 sm:p-2 rounded-lg hover:bg-gray-100 transition-colors touch-manipulation"
                    >
                        <svg class="w-5 h-5 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    
                    <div class="text-center">
                        <h2 class="text-lg sm:text-xl font-semibold text-gray-900 capitalize">{{ monthName }}</h2>
                        <!-- Afficher le mois suivant sur mobile -->
                        <div class="sm:hidden text-sm text-gray-500 mt-1">
                            {{ format(addMonths(currentDate, 1), 'MMM yyyy', { locale: fr }) }}
                        </div>
                    </div>
                    
                    <button
                        @click="navigateMonth('next')"
                        class="p-2 sm:p-2 rounded-lg hover:bg-gray-100 transition-colors touch-manipulation"
                    >
                        <svg class="w-5 h-5 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                <!-- Grille du calendrier -->
                <div class="p-3 sm:p-6">
                    <!-- En-têtes des jours -->
                    <div class="grid grid-cols-7 gap-1 mb-2 sm:mb-4">
                        <div class="text-center text-xs sm:text-sm font-medium text-gray-500 py-1 sm:py-2">L</div>
                        <div class="text-center text-xs sm:text-sm font-medium text-gray-500 py-1 sm:py-2">M</div>
                        <div class="text-center text-xs sm:text-sm font-medium text-gray-500 py-1 sm:py-2">M</div>
                        <div class="text-center text-xs sm:text-sm font-medium text-gray-500 py-1 sm:py-2">J</div>
                        <div class="text-center text-xs sm:text-sm font-medium text-gray-500 py-1 sm:py-2">V</div>
                        <div class="text-center text-xs sm:text-sm font-medium text-gray-500 py-1 sm:py-2">S</div>
                        <div class="text-center text-xs sm:text-sm font-medium text-gray-500 py-1 sm:py-2">D</div>
                    </div>

                    <!-- Jours du mois -->
                    <div class="grid grid-cols-7 gap-1">
                        <div
                            v-for="day in calendarDays"
                            :key="day.date.toISOString()"
                            class="min-h-[80px] sm:min-h-[120px] border border-gray-200 rounded-lg p-1 sm:p-2 hover:bg-gray-50 transition-colors cursor-pointer touch-manipulation"
                            @click="openModal(day.date)"
                        >
                            <div class="text-xs sm:text-sm font-medium text-gray-900 mb-1 sm:mb-2">
                                {{ format(day.date, 'd') }}
                            </div>
                            
                            <!-- Séances du jour -->
                            <div class="flex justify-center items-center mt-1">
                                <!-- Style mobile : cercle avec checkmark -->
                                <template v-if="getDaySessions(day).length > 0">
                                    <div
                                        class="w-6 h-6 sm:w-8 sm:h-8 rounded-full flex items-center justify-center cursor-pointer touch-manipulation transition-all duration-200 hover:scale-110"
                                        :class="getDaySessions(day).some(s => s.status === 'completed') ? 'bg-green-500' : 'bg-blue-500'"
                                        @click.stop="showDayDetails(day)"
                                    >
                                        <span class="text-white text-xs sm:text-sm font-bold">
                                            {{ getDaySessions(day).some(s => s.status === 'completed') ? '✓' : '○' }}
                                        </span>
                                    </div>
                                </template>
                                
                                <!-- Style desktop : affichage détaillé -->
                                <template v-if="getDaySessions(day).length > 0" class="hidden sm:block">
                                    <div class="hidden sm:block space-y-1 w-full">
                                        <div
                                            v-for="(session, index) in getDaySessions(day).slice(0, 1)"
                                            :key="session.id"
                                            class="text-xs p-1 rounded cursor-pointer hover:shadow-sm transition-shadow"
                                            :class="session.type === 'workout' ? getStatusColor(session.status) : 'bg-green-100 text-green-800'"
                                            @click.stop="session.type === 'workout' ? editWorkout(session) : showSessionSummary(session)"
                                        >
                                            <div class="flex items-center justify-between">
                                                <span class="truncate">{{ session.name }}</span>
                                                <span>{{ session.type === 'workout' ? getStatusIcon(session.status) : '✅' }}</span>
                                            </div>
                                            <div class="text-xs opacity-75">
                                                {{ session.time }}
                                            </div>
                                        </div>
                                        
                                        <!-- Indicateur "+X" s'il y a plus de séances -->
                                        <div
                                            v-if="getDaySessions(day).length > 1"
                                            class="text-xs p-1 rounded cursor-pointer hover:shadow-sm transition-shadow bg-gray-100 text-gray-600 text-center"
                                            @click.stop="showDayDetails(day)"
                                        >
                                            <div class="flex items-center justify-center">
                                                <span>+{{ getDaySessions(day).length - 1 }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Historique -->
            <div v-if="activeTab === 'history'">
                <HistoryStats :history-workouts="historyWorkouts" />
                <WorkoutHistory :history-workouts="historyWorkouts" />
            </div>
        </div>

        <!-- Modal pour programmer/modifier une séance -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-2 sm:p-4 z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[95vh] sm:max-h-[90vh] overflow-y-auto">
                <div class="p-4 sm:p-6">
                    <div class="flex items-center justify-between mb-4 sm:mb-6">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                            {{ selectedWorkout ? 'Modifier la séance' : 'Programmer une séance' }}
                        </h3>
                        <button
                            @click="closeModal"
                            class="text-gray-400 hover:text-gray-600 transition-colors touch-manipulation p-1"
                        >
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="selectedWorkout ? updateWorkout() : submitForm()">
                        <div class="space-y-4">
                            <!-- Template de séance -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Séance
                                </label>
                                <select
                                    v-model="form.workout_template_id"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="">Sélectionner une séance</option>
                                    <option
                                        v-for="template in workoutTemplates"
                                        :key="template.id"
                                        :value="template.id"
                                    >
                                        {{ template.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Date -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Date
                                </label>
                                <input
                                    v-model="form.scheduled_date"
                                    type="date"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>

                            <!-- Heure -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Heure (optionnel)
                                </label>
                                <input
                                    v-model="form.scheduled_time"
                                    type="time"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>

                            <!-- Notes -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Notes (optionnel)
                                </label>
                                <textarea
                                    v-model="form.notes"
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Ajoutez des notes pour cette séance..."
                                ></textarea>
                            </div>
                        </div>

                        <div class="flex space-x-3 mt-4 sm:mt-6">
                            <button
                                type="button"
                                @click="closeModal"
                                class="flex-1 px-3 sm:px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors touch-manipulation"
                            >
                                Annuler
                            </button>
                            <button
                                type="submit"
                                class="flex-1 px-3 sm:px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg hover:from-blue-600 hover:to-purple-700 transition-all duration-200 touch-manipulation"
                            >
                                {{ selectedWorkout ? 'Mettre à jour' : 'Programmer' }}
                            </button>
                        </div>
                    </form>

                    <!-- Actions pour une séance existante -->
                    <div v-if="selectedWorkout" class="mt-6 pt-6 border-t">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Actions</h4>
                        <div class="space-y-2">
                            <button
                                v-if="selectedWorkout.status === 'scheduled'"
                                @click="startWorkout(selectedWorkout)"
                                class="w-full px-3 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors text-sm"
                            >
                                🏋️ Démarrer la séance
                            </button>
                            <button
                                v-if="selectedWorkout.status === 'scheduled'"
                                @click="skipWorkout(selectedWorkout)"
                                class="w-full px-3 py-2 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 transition-colors text-sm"
                            >
                                ⏭️ Marquer comme ignorée
                            </button>
                            <button
                                @click="deleteWorkout(selectedWorkout)"
                                class="w-full px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-sm"
                            >
                                🗑️ Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal de résumé de séance -->
        <WorkoutSummary 
            v-if="selectedSession"
            :session="selectedSession"
            :is-open="showSummaryModal"
            @close="closeSummaryModal"
        />
        
        <!-- Modal des séances du jour -->
        <DaySessionsModal 
            v-if="selectedDay"
            :day="selectedDay"
            :is-open="showDayModal"
            @close="closeDayModal"
            @edit-workout="editWorkout"
            @show-session-summary="showSessionSummary"
        />
        
        <!-- Modal de suppression en masse -->
        <div v-if="showBulkDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-2 sm:p-4 z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[95vh] sm:max-h-[90vh] overflow-y-auto">
                <div class="p-4 sm:p-6">
                    <div class="flex items-center justify-between mb-4 sm:mb-6">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 flex items-center">
                            <span class="text-lg sm:text-xl mr-1 sm:mr-2">🗑️</span>
                            <span class="hidden sm:inline">Supprimer des séances programmées</span>
                            <span class="sm:hidden">Supprimer séances</span>
                        </h3>
                        <button
                            @click="closeBulkDeleteModal"
                            class="text-gray-400 hover:text-gray-600 transition-colors touch-manipulation p-1"
                        >
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <div class="mb-4">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 space-y-2 sm:space-y-0">
                            <p class="text-sm text-gray-600">
                                <span class="hidden sm:inline">Sélectionnez les séances programmées à supprimer</span>
                                <span class="sm:hidden">Sélectionnez les séances</span>
                            </p>
                            <div class="flex items-center">
                                <input
                                    v-model="selectAllWorkouts"
                                    @change="toggleSelectAll"
                                    type="checkbox"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded touch-manipulation"
                                />
                                <label class="ml-2 text-sm text-gray-700">Tout sélectionner</label>
                            </div>
                        </div>
                        
                        <div class="max-h-48 sm:max-h-60 overflow-y-auto border border-gray-200 rounded-lg">
                            <div
                                v-for="workout in scheduledWorkoutsOnly"
                                :key="workout.id"
                                class="flex items-center p-2 sm:p-3 border-b border-gray-100 hover:bg-gray-50 touch-manipulation"
                            >
                                <input
                                    :checked="selectedWorkoutsForDelete.includes(workout.id)"
                                    @change="toggleWorkoutSelection(workout.id)"
                                    type="checkbox"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded touch-manipulation"
                                />
                                <div class="ml-2 sm:ml-3 flex-1">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ workout.workout_template.name }}
                                    </div>
                                    <div class="text-xs sm:text-sm text-gray-500">
                                        <span class="hidden sm:inline">{{ format(parseISO(workout.scheduled_date), 'EEEE d MMMM yyyy', { locale: fr }) }}</span>
                                        <span class="sm:hidden">{{ format(parseISO(workout.scheduled_date), 'd MMM', { locale: fr }) }}</span>
                                        <span v-if="workout.scheduled_time">
                                            à {{ workout.scheduled_time }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button
                            @click="closeBulkDeleteModal"
                            class="flex-1 px-3 sm:px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors touch-manipulation"
                        >
                            Annuler
                        </button>
                        <button
                            @click="confirmBulkDelete"
                            :disabled="selectedWorkoutsForDelete.length === 0"
                            class="flex-1 px-3 sm:px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed touch-manipulation"
                        >
                            <span class="hidden sm:inline">Supprimer {{ selectedWorkoutsForDelete.length }} séance{{ selectedWorkoutsForDelete.length > 1 ? 's' : '' }}</span>
                            <span class="sm:hidden">Supprimer {{ selectedWorkoutsForDelete.length }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
