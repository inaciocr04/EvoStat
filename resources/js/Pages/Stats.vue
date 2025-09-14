<script setup>
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import DefaultLayout from '@/Layouts/DefaultLayout.vue'
import ApexCharts from 'vue3-apexcharts'

defineOptions({layout: DefaultLayout})

const props = defineProps({
    generalStats: Object,
    frequencyStats: Object,
    progressStats: Array,
    personalRecords: Array,
    muscleGroupStats: Array,
    durationStats: Object,
    topExercises: Array,
    recentStats: Object,
})

// Formatage des nombres
const formatNumber = (num) => {
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M'
    if (num >= 1000) return (num / 1000).toFixed(1) + 'K'
    return num.toString()
}

const formatDuration = (minutes) => {
    if (minutes < 60) return `${minutes}min`
    const hours = Math.floor(minutes / 60)
    const mins = Math.round(minutes % 60)
    return `${hours}h ${mins}min`
}

// Configuration des graphiques
const frequencyChartOptions = computed(() => ({
    chart: {
        type: 'line',
        height: 300,
        toolbar: { show: false }
    },
    stroke: {
        curve: 'smooth',
        width: 3,
        colors: ['#3B82F6']
    },
    xaxis: {
        categories: props.frequencyStats?.weekly?.map(w => w.date) || [],
        labels: { rotate: -45 }
    },
    yaxis: {
        min: 0,
        title: { text: 'Séances' }
    },
    colors: ['#3B82F6'],
    grid: {
        borderColor: '#e5e7eb',
        strokeDashArray: 3
    }
}))

const frequencySeries = computed(() => [{
    name: 'Séances par semaine',
    data: props.frequencyStats?.weekly?.map(w => w.sessions) || []
}])

const muscleGroupChartOptions = computed(() => ({
    chart: {
        type: 'donut',
        height: 300
    },
    labels: props.muscleGroupStats?.map(m => m.category) || [],
    colors: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4'],
    legend: {
        position: 'bottom'
    },
    plotOptions: {
        pie: {
            donut: {
                size: '70%'
            }
        }
    }
}))

const muscleGroupSeries = computed(() => 
    props.muscleGroupStats?.map(m => m.total_volume) || []
)

// Statistiques calculées
const statsCards = computed(() => [
    {
        title: 'Séances totales',
        value: props.generalStats?.totalSessions || 0,
        icon: '🏋️',
        color: 'bg-blue-500',
        subtitle: 'Séances complétées'
    },
    {
        title: 'Volume total',
        value: formatNumber(props.generalStats?.totalVolume || 0),
        icon: '⚖️',
        color: 'bg-green-500',
        subtitle: 'kg × reps'
    },
    {
        title: 'Séries totales',
        value: props.generalStats?.totalSets || 0,
        icon: '📊',
        color: 'bg-purple-500',
        subtitle: 'Séries effectuées'
    },
    {
        title: 'Temps total',
        value: formatDuration((props.durationStats?.total_time || 0) * 60),
        icon: '⏱️',
        color: 'bg-orange-500',
        subtitle: 'Heures d\'entraînement'
    }
])

const recentCards = computed(() => [
    {
        title: 'Séances (30j)',
        value: props.recentStats?.sessions_last_30_days || 0,
        icon: '📅',
        color: 'bg-indigo-500'
    },
    {
        title: 'Volume (30j)',
        value: formatNumber(props.recentStats?.volume_last_30_days || 0),
        icon: '💪',
        color: 'bg-emerald-500'
    },
    {
        title: 'Fréquence',
        value: `${props.recentStats?.average_sessions_per_week || 0}/sem`,
        icon: '📈',
        color: 'bg-rose-500'
    }
])
</script>

<template>
    <Head title="Statistiques" />

    <div class="min-h-screen bg-white p-6">
        <div class="max-w-7xl mx-auto">
            <!-- En-tête -->
            <div class="mb-12 text-center">
                <h1 class="text-5xl font-bold text-gray-900 mb-4">📊 Mes Statistiques</h1>
                <p class="text-xl text-gray-600">Suivez votre progression et vos performances</p>
                <div class="w-24 h-1 bg-gradient-to-r from-blue-500 to-purple-500 mx-auto mt-4 rounded-full"></div>
            </div>

            <!-- Cartes principales -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div v-for="card in statsCards" :key="card.title" 
                     class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 hover:shadow-xl hover:border-gray-200 transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">{{ card.title }}</p>
                            <p class="text-3xl font-bold text-gray-800">{{ card.value }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ card.subtitle }}</p>
                        </div>
                        <div :class="[card.color, 'w-12 h-12 rounded-full flex items-center justify-center text-2xl']">
                            {{ card.icon }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistiques récentes -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div v-for="card in recentCards" :key="card.title"
                     class="bg-white rounded-xl p-4 shadow-md border border-gray-100 hover:shadow-lg hover:border-gray-200 transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">{{ card.title }}</p>
                            <p class="text-xl font-bold text-gray-800">{{ card.value }}</p>
                        </div>
                        <div :class="[card.color, 'w-10 h-10 rounded-full flex items-center justify-center text-lg']">
                            {{ card.icon }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Graphique de fréquence -->
                <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">📈 Fréquence d'entraînement</h3>
                    <ApexCharts
                        type="line"
                        :options="frequencyChartOptions"
                        :series="frequencySeries"
                        height="300"
                    />
                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-blue-600">{{ frequencyStats?.averagePerWeek || 0 }}</p>
                            <p class="text-sm text-gray-500">Séances/semaine</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-green-600">{{ frequencyStats?.averagePerMonth || 0 }}</p>
                            <p class="text-sm text-gray-500">Séances/mois</p>
                        </div>
                    </div>
                </div>

                <!-- Répartition par groupe musculaire -->
                <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">💪 Répartition musculaire</h3>
                    <ApexCharts
                        type="donut"
                        :options="muscleGroupChartOptions"
                        :series="muscleGroupSeries"
                        height="300"
                    />
                    <div class="mt-4 space-y-2">
                        <div v-for="muscle in (muscleGroupStats || []).slice(0, 5)" :key="muscle.category"
                             class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">{{ muscle.category }}</span>
                            <span class="text-sm font-semibold">{{ formatNumber(muscle.total_volume) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Records personnels -->
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 mb-8">
                <h3 class="text-xl font-bold text-gray-800 mb-6">🏆 Records Personnels</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="record in (personalRecords || []).slice(0, 9)" :key="record.exercise"
                         class="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl p-4 border border-yellow-200">
                        <h4 class="font-bold text-gray-800 mb-2">{{ record.exercise }}</h4>
                        <div class="space-y-1">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Max poids:</span>
                                <span class="font-semibold">{{ record.max_weight }}kg</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Max reps:</span>
                                <span class="font-semibold">{{ record.max_reps }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Max volume:</span>
                                <span class="font-semibold">{{ formatNumber(record.max_volume) }}</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-2">
                                Dernier: {{ record.last_achieved }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top exercices -->
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 mb-8">
                <h3 class="text-xl font-bold text-gray-800 mb-6">⭐ Exercices Favoris</h3>
                <div class="space-y-3">
                    <div v-for="(exercise, index) in (topExercises || []).slice(0, 10)" :key="exercise.name"
                         class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-200 hover:bg-gray-100 transition-colors duration-200">
                        <div class="flex items-center space-x-4">
                            <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold">
                                {{ index + 1 }}
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800">{{ exercise.name }}</h4>
                                <p class="text-sm text-gray-500">{{ exercise.sets }} séries • {{ exercise.sessions }} séances</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-gray-800">{{ formatNumber(exercise.total_volume) }}</p>
                            <p class="text-sm text-gray-500">Volume total</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistiques de durée -->
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100">
                <h3 class="text-xl font-bold text-gray-800 mb-6">⏱️ Durée des Séances</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="text-center">
                        <p class="text-3xl font-bold text-blue-600">{{ durationStats?.average_duration || 0 }}min</p>
                        <p class="text-sm text-gray-500">Durée moyenne</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-green-600">{{ durationStats?.longest_session || 0 }}min</p>
                        <p class="text-sm text-gray-500">Plus longue</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-orange-600">{{ durationStats?.shortest_session || 0 }}min</p>
                        <p class="text-sm text-gray-500">Plus courte</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-purple-600">{{ durationStats?.sessions_with_duration || 0 }}</p>
                        <p class="text-sm text-gray-500">Séances chronométrées</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
