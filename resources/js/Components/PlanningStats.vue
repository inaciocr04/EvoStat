<script setup>
import { computed } from 'vue'
import { format, parseISO, startOfWeek, endOfWeek, eachWeekOfInterval, isWithinInterval } from 'date-fns'
import { fr } from 'date-fns/locale'

const props = defineProps({
    stats: Object,
    currentMonth: String,
    completedSessions: Array
})

const totalSessions = computed(() => {
    return props.stats.total_completed_sessions || 0
})

const weeklyStats = computed(() => {
    if (!props.completedSessions || props.completedSessions.length === 0) return []
    
    const sessions = props.completedSessions.map(session => ({
        ...session,
        date: parseISO(session.completed_at)
    }))
    
    const firstSession = sessions[sessions.length - 1]
    const lastSession = sessions[0]
    
    const weeks = eachWeekOfInterval({
        start: startOfWeek(firstSession.date),
        end: endOfWeek(lastSession.date)
    })
    
    return weeks.map(weekStart => {
        const weekEnd = endOfWeek(weekStart)
        const weekSessions = sessions.filter(session => 
            isWithinInterval(session.date, { start: weekStart, end: weekEnd })
        )
        
        return {
            weekStart,
            weekEnd,
            sessions: weekSessions,
            count: weekSessions.length
        }
    }).reverse().slice(0, 4) // 4 dernières semaines
})

const averagePerWeek = computed(() => {
    if (weeklyStats.value.length === 0) return 0
    const totalWeeks = weeklyStats.value.length
    const totalSessions = weeklyStats.value.reduce((sum, week) => sum + week.count, 0)
    return Math.round((totalSessions / totalWeeks) * 10) / 10
})

const streakDays = computed(() => {
    if (!props.completedSessions || props.completedSessions.length === 0) return 0
    
    const sessions = props.completedSessions
        .map(s => parseISO(s.completed_at))
        .sort((a, b) => b - a)
    
    let streak = 0
    let currentDate = new Date()
    currentDate.setHours(0, 0, 0, 0)
    
    for (let i = 0; i < sessions.length; i++) {
        const sessionDate = new Date(sessions[i])
        sessionDate.setHours(0, 0, 0, 0)
        
        if (sessionDate.getTime() === currentDate.getTime()) {
            streak++
            currentDate.setDate(currentDate.getDate() - 1)
        } else if (sessionDate.getTime() === currentDate.getTime() - 86400000) {
            streak++
            currentDate.setDate(currentDate.getDate() - 1)
        } else {
            break
        }
    }
    
    return streak
})
</script>

<template>
    <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl p-6 border border-blue-100">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">📊 Tableau de bord</h3>
            <span class="text-sm text-gray-500">{{ currentMonth }}</span>
        </div>
        
        <!-- Statistiques principales -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-white rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ totalSessions }}</div>
                <div class="text-sm text-gray-600">Séances ce mois</div>
            </div>
            <div class="bg-white rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-green-600">{{ streakDays }}</div>
                <div class="text-sm text-gray-600">Jours consécutifs</div>
            </div>
        </div>
        
        <!-- Progression hebdomadaire -->
        <div v-if="weeklyStats.length > 0">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm font-medium text-gray-700">Progression récente</h4>
                <span class="text-sm text-gray-500">{{ averagePerWeek }} séances/semaine</span>
            </div>
            
            <div class="space-y-2">
                <div
                    v-for="week in weeklyStats"
                    :key="week.weekStart.toISOString()"
                    class="flex items-center justify-between text-sm"
                >
                    <span class="text-gray-600">
                        Sem. du {{ format(week.weekStart, 'd MMM', { locale: fr }) }}
                    </span>
                    <div class="flex items-center space-x-2">
                        <div class="w-16 bg-gray-200 rounded-full h-2">
                            <div 
                                class="bg-gradient-to-r from-green-500 to-blue-500 h-2 rounded-full transition-all duration-300"
                                :style="{ width: `${Math.min((week.count / 7) * 100, 100)}%` }"
                            ></div>
                        </div>
                        <span class="font-medium text-gray-900 w-6 text-right">{{ week.count }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Message si aucune donnée -->
        <div v-else class="text-center py-4">
            <div class="text-gray-400 text-2xl mb-2">🏋️‍♂️</div>
            <p class="text-sm text-gray-500">Commencez vos séances pour voir vos statistiques !</p>
        </div>
    </div>
</template>
