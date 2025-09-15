<script setup>
import {ref, watch, onMounted, onUnmounted, computed} from 'vue'
import {usePage, router, Head} from '@inertiajs/vue3'
import ApexCharts from 'vue3-apexcharts'
import DefaultLayout from '@/Layouts/DefaultLayout.vue'
import dayjs from 'dayjs'

defineOptions({layout: DefaultLayout})

const page = usePage()
const exercises = page.props.exercises
const stats = page.props.stats
const exerciseName = page.props.exerciseName
const exerciseId = page.props.exerciseId

// Variable pour la largeur de fenêtre (responsive)
const windowWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024)

const selectExercise = (id) => {
    selectedExercise.value = id
    onExerciseChange()
}

const selectedExercise = ref(exerciseId ?? '')

const series = ref([])

const allowedExerciseNames = ['rowing bar', 'curl biceps', 'extension triceps', 'developper couche barre']

const normalize = str =>
    str.trim().normalize('NFD').replace(/\p{Diacritic}/gu, '').toLowerCase()

const filteredExercises = computed(() => {
    return exercises.filter(ex => {
        const normName = normalize(ex.name)
        console.log('Exercise:', ex.name, 'Normalized:', normName, 'Allowed:', allowedExerciseNames.includes(normName))
        return allowedExerciseNames.includes(normName)
    })
})

console.log(filteredExercises)

const chartOptions = ref({
    chart: {
        height: windowWidth.value < 640 ? 300 : 380,
        type: 'line',
        animations: {
            enabled: true,
            easing: 'easeinout',
            speed: 700,
            animateGradually: {enabled: true, delay: 200},
            dynamicAnimation: {enabled: true, speed: 300}
        },
        toolbar: {
            show: windowWidth.value >= 640,
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
        width: windowWidth.value < 640 ? 2 : 3
    },
    markers: {
        size: windowWidth.value < 640 ? 5 : 7,
        hover: {
            size: windowWidth.value < 640 ? 8 : 10,
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
            style: {
                fontSize: windowWidth.value < 640 ? '10px' : '13px', 
                fontWeight: 'bold'
            }
        },
        title: {
            text: 'Date',
            style: {
                fontSize: windowWidth.value < 640 ? '12px' : '14px', 
                fontWeight: 'bold'
            }
        },
        tooltip: {enabled: false}
    },
    yaxis: {
        min: 0,
        tickAmount: windowWidth.value < 640 ? 4 : 6,
        labels: {
            formatter: val => val.toFixed(1),
            style: {
                fontSize: windowWidth.value < 640 ? '10px' : '13px', 
                fontWeight: 'bold'
            }
        },
        title: {
            text: 'Poids (kg)',
            style: {
                fontSize: windowWidth.value < 640 ? '12px' : '14px', 
                fontWeight: 'bold'
            }
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
    },
    responsive: [{
        breakpoint: 640,
        options: {
            chart: {
                height: 300,
                toolbar: {
                    show: false
                }
            },
            stroke: {
                width: 2
            },
            markers: {
                size: 5,
                hover: {
                    size: 8
                }
            },
            xaxis: {
                labels: {
                    style: {
                        fontSize: '10px'
                    }
                },
                title: {
                    style: {
                        fontSize: '12px'
                    }
                }
            },
            yaxis: {
                tickAmount: 4,
                labels: {
                    style: {
                        fontSize: '10px'
                    }
                },
                title: {
                    style: {
                        fontSize: '12px'
                    }
                }
            }
        }
    }, {
        breakpoint: 480,
        options: {
            chart: {
                height: 250
            },
            xaxis: {
                labels: {
                    style: {
                        fontSize: '9px'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: '9px'
                    }
                }
            }
        }
    }]
})

// 🔀 Jitter pour séparer les points trop proches
function jitterTimestamp(timestamp) {
    const jitterMs = (Math.random() - 0.5) * 10 * 60 * 1000
    return timestamp + jitterMs
}

const updateChart = () => {
    if (!stats.length) {
        series.value = []
        return
    }

    const weightData = stats.map(s => {
        const baseTimestamp = new Date(s.created_at).getTime()
        return {
            x: jitterTimestamp(baseTimestamp),
            y: s.weight,
            reps: s.reps
        }
    })

    series.value = [
        {
            name: 'Poids (kg)',
            type: 'line',
            data: weightData,
            color: '#1E90FF'
        }
    ]
}

watch(() => stats, updateChart, {immediate: true})

const onExerciseChange = () => {
    router.get('/stats', {exercise_id: selectedExercise.value}, {preserveScroll: true})
}

// Fonction pour mettre à jour la largeur de fenêtre
const updateWindowWidth = () => {
    windowWidth.value = window.innerWidth
}

onMounted(() => {
    updateChart()
    window.addEventListener('resize', updateWindowWidth)
})

onUnmounted(() => {
    window.removeEventListener('resize', updateWindowWidth)
})

</script>


<template>
    <Head title="Historique & stats"/>

    <div class="p-4 sm:p-6">
        <h1 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6">Statistiques de progression</h1>

        <div class="flex flex-col lg:flex-row gap-4 sm:gap-6">
            <!-- 📈 GRAPHIQUE -->
            <div class="flex-1">
                <div v-if="series.length > 0">
                    <h2 class="text-lg sm:text-xl font-semibold mb-3 sm:mb-4">Progression – {{ exerciseName }} {{ exerciseId }}</h2>
                    <div class="chart-container">
                        <ApexCharts
                            type="line"
                            :options="chartOptions"
                            :series="series"
                            :height="windowWidth.value < 640 ? 300 : 380"
                            width="100%"
                        />
                    </div>
                </div>
                <p v-else-if="selectedExercise" class="text-gray-500">Aucune donnée pour cet exercice.</p>
                <p v-else class="text-gray-500">Sélectionne un exercice dans la liste à droite.</p>
            </div>

            <!-- 📝 LISTE DES EXERCICES -->
            <div class="w-full lg:w-72 max-h-[400px] overflow-y-auto border border-gray-200 rounded p-3 sm:p-4 shadow-sm">
                <h3 class="text-base sm:text-lg font-bold mb-2">Exercices</h3>
                <ul class="space-y-2">
                    <li
                        v-for="ex in filteredExercises"
                        :key="ex.id"
                        @click="selectExercise(ex.id)"
                        :class="['cursor-pointer p-2 rounded transition-colors',
selectedExercise && selectedExercise.toString() === ex.id.toString()
 ? 'bg-evogradientleft text-white font-bold' : 'hover:bg-gray-100'
                        ]"

                    >
                        {{ ex.name }}
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

