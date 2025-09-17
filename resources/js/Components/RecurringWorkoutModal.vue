<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    workoutTemplates: Array
})

const showModal = ref(false)
const form = ref({
    workout_template_id: '',
    start_date: '',
    end_date: '',
    indefinite_end: false,
    days_of_week: [],
    scheduled_time: '',
    notes: '',
})

const daysOfWeek = [
    { value: 1, label: 'Lundi', short: 'L' },
    { value: 2, label: 'Mardi', short: 'M' },
    { value: 3, label: 'Mercredi', short: 'M' },
    { value: 4, label: 'Jeudi', short: 'J' },
    { value: 5, label: 'Vendredi', short: 'V' },
    { value: 6, label: 'Samedi', short: 'S' },
    { value: 7, label: 'Dimanche', short: 'D' },
]

const selectedDays = computed(() => {
    return daysOfWeek.filter(day => form.value.days_of_week.includes(day.value))
})

function openModal() {
    // Définir les dates par défaut (semaine prochaine)
    const nextWeek = new Date()
    nextWeek.setDate(nextWeek.getDate() + 7)
    const endWeek = new Date(nextWeek)
    endWeek.setDate(endWeek.getDate() + 14) // 2 semaines plus tard
    
    form.value.start_date = nextWeek.toISOString().split('T')[0]
    form.value.end_date = endWeek.toISOString().split('T')[0]
    showModal.value = true
}

function closeModal() {
    showModal.value = false
    form.value = {
        workout_template_id: '',
        start_date: '',
        end_date: '',
        days_of_week: [],
        scheduled_time: '',
        notes: '',
    }
}

function toggleDay(dayValue) {
    const index = form.value.days_of_week.indexOf(dayValue)
    if (index > -1) {
        form.value.days_of_week.splice(index, 1)
    } else {
        form.value.days_of_week.push(dayValue)
    }
}

function submitForm() {
    const formData = { ...form.value }
    
    // Si indéfini, on ne passe pas de date de fin
    if (formData.indefinite_end) {
        delete formData.end_date
    }
    
    router.post(route('planning.recurring'), formData, {
        onSuccess: () => {
            closeModal()
        }
    })
}

function getPreviewDates() {
    if (!form.value.start_date || form.value.days_of_week.length === 0) {
        return []
    }
    
    const startDate = new Date(form.value.start_date)
    let endDate
    
    if (form.value.indefinite_end) {
        // Pour l'aperçu, on limite à 3 mois si indéfini
        endDate = new Date(startDate)
        endDate.setMonth(endDate.getMonth() + 3)
    } else if (form.value.end_date) {
        endDate = new Date(form.value.end_date)
    } else {
        return []
    }
    
    const dates = []
    
    let currentDate = new Date(startDate)
    while (currentDate <= endDate && dates.length < 20) { // Limiter à 20 dates pour l'aperçu
        const dayOfWeek = currentDate.getDay() === 0 ? 7 : currentDate.getDay() // Convertir dimanche de 0 à 7
        
        if (form.value.days_of_week.includes(dayOfWeek)) {
            dates.push(new Date(currentDate))
        }
        
        currentDate.setDate(currentDate.getDate() + 1)
    }
    
    return dates.slice(0, 10) // Limiter à 10 dates pour l'aperçu
}
</script>

<template>
    <div>
        <!-- Bouton pour ouvrir le modal -->
        <button
            @click="openModal"
            class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-gradient-to-r from-green-500 to-blue-600 text-white font-medium rounded-lg hover:from-green-600 hover:to-blue-700 transition-all duration-200 shadow-md hover:shadow-lg touch-manipulation"
        >
            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span class="hidden sm:inline">Programmer récurrent</span>
            <span class="sm:hidden">Récurrent</span>
        </button>

        <!-- Modal pour programmer des séances récurrentes -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-2 sm:p-4 z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[95vh] sm:max-h-[90vh] overflow-y-auto">
                <div class="p-4 sm:p-6">
                    <div class="flex items-center justify-between mb-4 sm:mb-6">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 flex items-center">
                            <span class="text-lg sm:text-xl mr-1 sm:mr-2">🔄</span>
                            <span class="hidden sm:inline">Programmer des séances récurrentes</span>
                            <span class="sm:hidden">Séances récurrentes</span>
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

                    <form @submit.prevent="submitForm">
                        <div class="space-y-6">
                            <!-- Template de séance -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Séance à répéter
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

                            <!-- Période -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Date de début
                                    </label>
                                    <input
                                        v-model="form.start_date"
                                        type="date"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Date de fin
                                    </label>
                                    <div class="space-y-3">
                                        <div class="flex items-center">
                                            <input
                                                v-model="form.indefinite_end"
                                                type="checkbox"
                                                id="indefinite_end"
                                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                            />
                                            <label for="indefinite_end" class="ml-2 text-sm text-gray-700">
                                                Indéfini (pas de date de fin)
                                            </label>
                                        </div>
                                        <input
                                            v-model="form.end_date"
                                            type="date"
                                            :required="!form.indefinite_end"
                                            :disabled="form.indefinite_end"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:text-gray-500"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Jours de la semaine -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Jours de la semaine
                                </label>
                                <div class="grid grid-cols-7 gap-2">
                                    <button
                                        v-for="day in daysOfWeek"
                                        :key="day.value"
                                        type="button"
                                        @click="toggleDay(day.value)"
                                        :class="[
                                            'p-3 rounded-lg border-2 transition-all duration-200 text-sm font-medium',
                                            form.days_of_week.includes(day.value)
                                                ? 'bg-blue-500 text-white border-blue-500'
                                                : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100'
                                        ]"
                                    >
                                        <div class="text-center">
                                            <div class="text-lg font-bold">{{ day.short }}</div>
                                            <div class="text-xs">{{ day.label }}</div>
                                        </div>
                                    </button>
                                </div>
                                <div v-if="selectedDays.length > 0" class="mt-2 text-sm text-gray-600">
                                    Sélectionné : {{ selectedDays.map(d => d.label).join(', ') }}
                                </div>
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
                                    placeholder="Ajoutez des notes pour ces séances..."
                                ></textarea>
                            </div>

                            <!-- Aperçu des dates -->
                            <div v-if="getPreviewDates().length > 0">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Aperçu des dates 
                                    <span v-if="form.indefinite_end" class="text-blue-600">(indéfini - {{ getPreviewDates().length }} premières dates)</span>
                                    <span v-else>({{ getPreviewDates().length }} premières dates)</span>
                                </label>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-sm">
                                        <div
                                            v-for="date in getPreviewDates()"
                                            :key="date.toISOString()"
                                            class="bg-white rounded p-2 text-center border"
                                        >
                                            {{ date.toLocaleDateString('fr-FR', { weekday: 'short', day: 'numeric', month: 'short' }) }}
                                        </div>
                                    </div>
                                    <div v-if="form.indefinite_end" class="text-xs text-blue-600 mt-2 text-center font-medium">
                                        🔄 Séances récurrentes sans fin
                                    </div>
                                    <div v-else-if="getPreviewDates().length === 10" class="text-xs text-gray-500 mt-2 text-center">
                                        ... et d'autres dates
                                    </div>
                                </div>
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
                                :disabled="form.days_of_week.length === 0"
                                class="flex-1 px-3 sm:px-4 py-2 bg-gradient-to-r from-green-500 to-blue-600 text-white rounded-lg hover:from-green-600 hover:to-blue-700 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed touch-manipulation"
                            >
                                <span class="hidden sm:inline">Programmer les séances</span>
                                <span class="sm:hidden">Programmer</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
