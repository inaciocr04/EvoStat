<template>
    <div>
        <h2 class="text-xl font-bold mb-4">Progression – {{ exerciseName }}</h2>
        <canvas ref="chart"></canvas>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import Chart from 'chart.js/auto'

const props = defineProps({
    stats: Array,
    exerciseName: String
})

const chart = ref(null)
let chartInstance = null

onMounted(() => {
    const labels = props.stats.map(item => item.date)
    const data = props.stats.map(item => item.max_weight)

    chartInstance = new Chart(chart.value, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Poids Max (kg)',
                data,
                borderColor: 'rgba(75, 192, 192, 1)',
                tension: 0.3,
                fill: false,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Poids (kg)'
                    }
                }
            }
        }
    })
})
</script>
