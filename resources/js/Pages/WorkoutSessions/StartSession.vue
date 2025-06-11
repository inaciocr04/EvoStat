<script setup>
import {ref, watch} from 'vue'
import {router} from '@inertiajs/vue3'
import axios from 'axios'
import DefaultLayout from '@/Layouts/DefaultLayout.vue'

defineOptions({layout: DefaultLayout})

const props = defineProps({
    session: Object,
})

const exercices = ref([])

watch(() => props.session.session_exercises, (newVal) => {
    if (Array.isArray(newVal)) {
        exercices.value = newVal.map(ex => ({
            id: ex.id,
            name: ex.exercise?.name || 'Nom non défini',
            sets: [{
                reps: ex.notes?.default_reps || 8,
                weight: ex.notes?.default_weight || 0,
                rest_time: ex.notes?.default_rest_time || 60,
            }],
        }))
    } else {
        exercices.value = []
    }
}, {immediate: true})

console.log(props)
function addSet(exIndex) {
    exercices.value[exIndex].sets.push({
        reps: 0,
        weight: 0,
        rest_time: 60,
    })
}

function removeSet(exIndex, setIndex) {
    if (exercices.value[exIndex].sets.length > 1) {
        exercices.value[exIndex].sets.splice(setIndex, 1)
    }
}

function formatDate(dateStr) {
    if (!dateStr) return '...'
    const date = new Date(dateStr)
    return date.toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: 'numeric',
    })
}

async function go() {
    try {
        // Prépare le payload : un tableau de sets groupés par session_exercise_id
        const payload = exercices.value.flatMap(ex =>
            ex.sets.map(set => ({
                session_exercise_id: ex.id,
                weight: set.weight,
                reps: set.reps,
                rest_time: set.rest_time,
            }))
        );
        const selectedTemplateId = ref(null);


        console.log('Payload envoyé:', payload);

        // Envoi le payload sous la clé 'sets' (selon ta validation Laravel)
        await axios.post(`/sessions/${props.session.id}/sets`, {
            template_id: selectedTemplateId.value,
            sets: payload,
        });

        // Démarre la session
        await axios.post(`/sessions/${props.session.id}/start`, {
            template_id: selectedTemplateId.value,
        })
            .then(res => { /* succès */ })
            .catch(err => { /* gestion erreur */ });

        // Redirection vers la page en cours
        router.visit(route('sessions.inprogress', props.session.id));
    } catch (error) {
        if (error.response && error.response.status === 422) {
            console.error('Erreurs de validation:', error.response.data.errors);
        } else {
            console.error('Erreur lors du démarrage de la séance:', error);
        }
    }
}


</script>

<template>
    <div class="max-w-3xl mx-auto p-6 bg-white rounded-lg shadow-md">
        <header class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">
                Séance {{ session.workout_template?.name || '...' }} du {{ formatDate(session.created_at) }}
                <span class="text-sm font-normal text-gray-500">({{ session.status }})</span>
            </h2>
        </header>

        <section v-if="exercices.length === 0" class="text-center text-gray-500 italic py-12">
            Aucun exercice à afficher.
        </section>

        <section v-else>
            <div v-for="(exercice, exIndex) in exercices" :key="exercice.id" class="mb-8">
                <h3 class="text-xl font-semibold text-gray-700 mb-3">{{ exercice.name }}</h3>

                <div class="flex items-center gap-3 mb-2 text-gray-600 font-medium">
                    <div class="w-20 text-center">Reps</div>
                    <div class="w-28 text-center">Poids (kg)</div>
                    <div class="w-24 text-center">Repos (sec)</div>
                    <div class="w-10"></div>
                </div>

                <div v-for="(set, setIndex) in exercice.sets" :key="setIndex" class="flex items-center gap-3 mb-2">
                    <input type="number" v-model.number="set.reps"
                           class="w-20 text-center border rounded-md px-2 py-1"/>
                    <input type="number" v-model.number="set.weight"
                           class="w-28 text-center border rounded-md px-2 py-1"/>
                    <input type="number" v-model.number="set.rest_time"
                           class="w-24 text-center border rounded-md px-2 py-1"/>
                    <button
                        @click="removeSet(exIndex, setIndex)"
                        :disabled="exercice.sets.length === 1"
                        class="text-white bg-red-500 hover:bg-red-600 disabled:bg-red-300 rounded-md px-3 py-1 transition"
                        title="Supprimer cette série"
                    >
                        ✕
                    </button>
                </div>

                <button @click="addSet(exIndex)" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                    + Ajouter une série
                </button>

                <hr class="mt-4 border-gray-300"/>
            </div>
        </section>

        <div>
            <button
                @click="go"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-md transition"
            >
                GO
            </button>
        </div>
    </div>
</template>


