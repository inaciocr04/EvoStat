<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import DefaultLayout from '@/Layouts/DefaultLayout.vue'
import ApexCharts from 'vue3-apexcharts'
import dayjs from 'dayjs'

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
    exercises: Array,
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
        height: 300,
        toolbar: { show: false }
    },
    labels: props.muscleGroupStats?.map(m => m.category) || [],
    colors: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4'],
    legend: {
        position: 'bottom',
        show: true,
        formatter: function(seriesName, opts) {
            const dataIndex = opts.seriesIndex
            const volume = props.muscleGroupStats?.[dataIndex]?.total_volume || 0
            return `${seriesName}: ${formatNumber(parseFloat(volume))}`
        }
    },
    plotOptions: {
        pie: {
            donut: {
                size: '70%'
            }
        }
    },
    dataLabels: {
        enabled: false
    },
    responsive: [{
        breakpoint: 480,
        options: {
            chart: {
                width: 200
            },
            legend: {
                position: 'bottom'
            }
        }
    }]
}))

const muscleGroupSeries = computed(() => 
    props.muscleGroupStats?.map(m => parseFloat(m.total_volume)) || []
)

// Calculer les pourcentages pour l'affichage
const muscleGroupWithPercentages = computed(() => {
    if (!props.muscleGroupStats || props.muscleGroupStats.length === 0) return []
    
    const totalVolume = props.muscleGroupStats.reduce((sum, m) => sum + parseFloat(m.total_volume), 0)
    
    return props.muscleGroupStats.map(muscle => ({
        ...muscle,
        percentage: totalVolume > 0 ? ((parseFloat(muscle.total_volume) / totalVolume) * 100).toFixed(1) : '0.0'
    }))
})

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

// Variables pour le graphique de progression
const selectedExercise = ref('')
const progressionSeries = ref([])

// Les exercices sont déjà filtrés côté serveur
const availableExercises = computed(() => {
    return props.exercises || []
})

// Configuration du graphique de progression
const progressionChartOptions = computed(() => ({
    chart: {
        height: 380,
        type: 'line',
        animations: {
            enabled: true,
            easing: 'easeinout',
            speed: 700,
            animateGradually: {enabled: true, delay: 200},
            dynamicAnimation: {enabled: true, speed: 300}
        },
        toolbar: {
            show: true,
            tools: {
                download: true,
                zoom: true,
                zoomin: true,
                zoomout: true,
                pan: true,
                reset: true
            }
        },
        zoom: {
            enabled: true,
            type: 'x',
            autoScaleYaxis: true
        }
    },
    stroke: {
        curve: 'smooth',
        width: 3
    },
    markers: {
        size: 7,
        hover: {
            size: 10,
            sizeOffset: 3
        },
        shape: 'circle',
        strokeColors: '#1E90FF',
        strokeWidth: 2
    },
    xaxis: {
        type: 'datetime',
        labels: {
            rotate: -45,
            datetimeUTC: false,
            format: 'dd MMM HH:mm',
            style: {fontSize: '13px', fontWeight: 'bold'}
        },
        title: {
            text: 'Date',
            style: {fontSize: '14px', fontWeight: 'bold'}
        },
        tooltip: {enabled: false}
    },
    yaxis: {
        min: 0,
        tickAmount: 6,
        labels: {
            formatter: val => val.toFixed(1),
            style: {fontSize: '13px', fontWeight: 'bold'}
        },
        title: {
            text: 'Poids (kg)',
            style: {fontSize: '14px', fontWeight: 'bold'}
        }
    },
    tooltip: {
        enabled: true,
        shared: false,
        followCursor: true,
        custom: ({series, seriesIndex, dataPointIndex, w}) => {
            const point = w.config.series[seriesIndex].data[dataPointIndex]
            const date = dayjs(point.x).format('YYYY-MM-DD HH:mm')
            const reps = point.reps ?? '?'
            return `
                <div class="p-2 text-sm">
                    <strong>${date}</strong><br>
                    ${point.y} kg – ${reps} reps
                </div>
            `
        }
    },
    grid: {
        borderColor: '#e0e0e0',
        strokeDashArray: 5,
        xaxis: {lines: {show: true}},
        yaxis: {lines: {show: true}}
    },
    fill: {
        type: 'solid',
        opacity: 1
    }
}))

// Fonction pour générer des données de progression simulées
const generateProgressionData = (exerciseId) => {
    // Simulation de données de progression pour l'exercice sélectionné
    const baseWeight = 50 + (exerciseId % 5) * 10 // Poids de base variable selon l'exercice
    const data = []
    const now = new Date()
    
    for (let i = 30; i >= 0; i--) {
        const date = new Date(now.getTime() - (i * 24 * 60 * 60 * 1000))
        const progress = Math.sin(i / 10) * 5 + (30 - i) * 0.5 // Progression avec variation
        const weight = baseWeight + progress + (Math.random() - 0.5) * 2 // Ajout de variation aléatoire
        
        data.push({
            x: date.getTime(),
            y: Math.max(10, weight.toFixed(1)),
            reps: Math.floor(Math.random() * 10) + 8 // 8-18 reps
        })
    }
    
    return data
}

// Fonction pour mettre à jour le graphique
const updateProgressionChart = () => {
    if (!selectedExercise.value) {
        progressionSeries.value = []
        return
    }
    
    const weightData = generateProgressionData(selectedExercise.value)
    
    progressionSeries.value = [
        {
            name: 'Poids (kg)',
            type: 'line',
            data: weightData,
            color: '#1E90FF'
        }
    ]
}

// Fonction pour sélectionner un exercice
const selectExercise = (id) => {
    selectedExercise.value = id
    updateProgressionChart()
}

// Watcher pour mettre à jour le graphique
watch(() => selectedExercise.value, updateProgressionChart, {immediate: true})
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
                            <p class="text-2xl font-bold text-blue-600">{{ props.frequencyStats?.averagePerWeek || 0 }}</p>
                            <p class="text-sm text-gray-500">Séances/semaine</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-green-600">{{ props.frequencyStats?.averagePerMonth || 0 }}</p>
                            <p class="text-sm text-gray-500">Séances/mois</p>
                        </div>
                    </div>
                </div>

                <!-- Répartition par groupe musculaire -->
                <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">💪 Répartition musculaire</h3>
                    
                    <!-- Message si aucune donnée -->
                    <div v-if="!props.muscleGroupStats || props.muscleGroupStats.length === 0" 
                         class="text-center py-8 text-gray-500">
                        <p class="text-lg mb-2">📊 Aucune donnée de répartition musculaire disponible</p>
                        <p class="text-sm">Les exercices doivent être associés à des groupes musculaires pour afficher ce graphique.</p>
                    </div>
                    
                    <div v-else>
                        
                        <ApexCharts
                            type="donut"
                            :options="muscleGroupChartOptions"
                            :series="muscleGroupSeries"
                            height="300"
                        />
                        <div class="mt-4 space-y-2">
                            <div v-for="muscle in muscleGroupWithPercentages.slice(0, 5)" :key="muscle.category"
                                 class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">{{ muscle.category }}</span>
                                <div class="text-right">
                                    <span class="text-sm font-semibold">{{ formatNumber(muscle.total_volume) }}</span>
                                    <span class="text-xs text-gray-500 ml-2">({{ muscle.percentage }}%)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Records personnels -->
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 mb-8">
                <h3 class="text-xl font-bold text-gray-800 mb-6">🏆 Records Personnels</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="record in (props.personalRecords || []).slice(0, 9)" :key="record.exercise"
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
                    <div v-for="(exercise, index) in (props.topExercises || []).slice(0, 10)" :key="exercise.name"
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

            <!-- Graphique de progression -->
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 mb-8">
                <h3 class="text-xl font-bold text-gray-800 mb-6">📈 Progression des Exercices</h3>
                
                <div class="flex flex-col lg:flex-row gap-6">
                    <!-- Graphique -->
                    <div class="flex-1">
                        <div v-if="progressionSeries.length > 0">
                            <ApexCharts
                                type="line"
                                :options="progressionChartOptions"
                                :series="progressionSeries"
                                height="380"
                                width="100%"
                            />
                        </div>
                        <div v-else class="text-center py-12 text-gray-500">
                            <p class="text-lg mb-2">📊 Sélectionnez un exercice pour voir sa progression</p>
                            <p class="text-sm">Choisissez un exercice dans la liste à droite</p>
                        </div>
                    </div>

                    <!-- Liste des exercices -->
                    <div class="w-full lg:w-72 max-h-[400px] overflow-y-auto border border-gray-200 rounded-xl p-4 shadow-sm">
                        <h4 class="text-lg font-bold mb-4 text-gray-800">Exercices</h4>
                        <div class="space-y-2">
                            <div
                                v-for="ex in availableExercises"
                                :key="ex.id"
                                @click="selectExercise(ex.id)"
                                :class="[
                                    'cursor-pointer p-3 rounded-lg transition-all duration-200 border',
                                    selectedExercise && selectedExercise.toString() === ex.id.toString()
                                        ? 'bg-blue-500 text-white border-blue-500 font-bold shadow-md' 
                                        : 'hover:bg-gray-100 border-gray-200 hover:border-gray-300'
                                ]"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="text-sm">{{ ex.name }}</span>
                                    <div v-if="selectedExercise && selectedExercise.toString() === ex.id.toString()" 
                                         class="w-2 h-2 bg-white rounded-full"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div v-if="availableExercises.length === 0" class="text-center py-8 text-gray-500">
                            <p class="text-sm">Aucun exercice disponible</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistiques de durée -->
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100">
                <h3 class="text-xl font-bold text-gray-800 mb-6">⏱️ Durée des Séances</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="text-center">
                        <p class="text-3xl font-bold text-blue-600">{{ props.durationStats?.average_duration || 0 }}min</p>
                        <p class="text-sm text-gray-500">Durée moyenne</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-green-600">{{ props.durationStats?.longest_session || 0 }}min</p>
                        <p class="text-sm text-gray-500">Plus longue</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-orange-600">{{ props.durationStats?.shortest_session || 0 }}min</p>
                        <p class="text-sm text-gray-500">Plus courte</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-bold text-purple-600">{{ props.durationStats?.sessions_with_duration || 0 }}</p>
                        <p class="text-sm text-gray-500">Séances chronométrées</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
