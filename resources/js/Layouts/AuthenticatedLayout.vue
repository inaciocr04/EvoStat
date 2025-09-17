<script setup>
import { ref } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';
import ApplicationLogoNav from "@/Components/ApplicationLogoNav.vue";

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div>
        <!-- Navigation moderne et responsive -->
        <nav class="bg-white shadow-lg border-b border-gray-100 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16 sm:h-20">
                    <!-- Logo avec animation -->
                    <div class="flex-shrink-0">
                        <Link :href="route('profils')" class="flex items-center gap-2 sm:gap-3 group">
                            <ApplicationLogoNav
                                class="block h-8 sm:h-10 w-auto fill-current text-gray-800 group-hover:text-blue-600 transition-colors duration-300"
                            />
                            <span class="text-lg sm:text-xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                                EvoStat
                            </span>
                        </Link>
                    </div>
                    
                    <!-- Navigation desktop -->
                    <div class="hidden md:flex items-center space-x-1 lg:space-x-2">
                        <NavLink
                            :href="route('exercises.index')"
                            :active="route().current('exercises.*')"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-blue-50 hover:text-blue-600"
                        >
                            💪 Exercices
                        </NavLink>
                        <NavLink
                            :href="route('workout-templates.index')"
                            :active="route().current('workout-templates.*')"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-green-50 hover:text-green-600"
                        >
                            🏋️ Séances
                        </NavLink>
                        <NavLink
                            :href="route('planning.index')"
                            :active="route().current('planning.*')"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-indigo-50 hover:text-indigo-600"
                        >
                            📅 Planning
                        </NavLink>
                        <NavLink
                            :href="route('statistics')"
                            :active="route().current('statistics')"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-purple-50 hover:text-purple-600"
                        >
                            📊 Statistiques
                        </NavLink>
                        <NavLink
                            :href="route('profils')"
                            :active="route().current('profils')"
                            class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-orange-50 hover:text-orange-600"
                        >
                            👤 Profils
                        </NavLink>
                    </div>

                    <!-- Menu utilisateur desktop -->
                    <div class="hidden md:flex items-center space-x-4">
                        <div class="relative">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button
                                        type="button"
                                        class="flex items-center space-x-2 px-4 py-2 rounded-lg bg-gradient-to-r from-blue-500 to-purple-600 text-white text-sm font-medium hover:from-blue-600 hover:to-purple-700 transition-all duration-200 shadow-md hover:shadow-lg"
                                    >
                                        <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                            <span class="text-sm font-bold">{{ $page.props.auth.user.pseudo.charAt(0).toUpperCase() }}</span>
                                        </div>
                                        <span>{{ $page.props.auth.user.pseudo }}</span>
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </template>

                                <template #content>
                                    <DropdownLink :href="route('profile.edit')" class="flex items-center space-x-2">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                        </svg>
                                        <span>Profil</span>
                                    </DropdownLink>
                                    <DropdownLink
                                        :href="route('logout')"
                                        method="post"
                                        as="button"
                                        class="flex items-center space-x-2 text-red-600 hover:bg-red-50"
                                    >
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd" />
                                        </svg>
                                        <span>Déconnexion</span>
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>

                    <!-- Bouton hamburger mobile -->
                    <div class="md:hidden">
                        <button
                            @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="inline-flex items-center justify-center p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500 transition-all duration-200"
                        >
                            <svg
                                class="h-6 w-6"
                                :class="{ 'hidden': showingNavigationDropdown, 'block': !showingNavigationDropdown }"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <svg
                                class="h-6 w-6"
                                :class="{ 'hidden': !showingNavigationDropdown, 'block': showingNavigationDropdown }"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Menu mobile -->
            <div
                :class="{ 'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown }"
                class="md:hidden bg-white border-t border-gray-200 shadow-lg"
            >
                <div class="px-4 py-2 space-y-1">
                    <ResponsiveNavLink
                        :href="route('exercises.index')"
                        :active="route().current('exercises.*')"
                        class="flex items-center space-x-3 px-4 py-3 rounded-lg text-base font-medium hover:bg-blue-50 hover:text-blue-600 transition-all duration-200"
                    >
                        <span class="text-xl">💪</span>
                        <span>Exercices</span>
                    </ResponsiveNavLink>
                    <ResponsiveNavLink
                        :href="route('workout-templates.index')"
                        :active="route().current('workout-templates.*')"
                        class="flex items-center space-x-3 px-4 py-3 rounded-lg text-base font-medium hover:bg-green-50 hover:text-green-600 transition-all duration-200"
                    >
                        <span class="text-xl">🏋️</span>
                        <span>Séances</span>
                    </ResponsiveNavLink>
                    <ResponsiveNavLink
                        :href="route('planning.index')"
                        :active="route().current('planning.*')"
                        class="flex items-center space-x-3 px-4 py-3 rounded-lg text-base font-medium hover:bg-indigo-50 hover:text-indigo-600 transition-all duration-200"
                    >
                        <span class="text-xl">📅</span>
                        <span>Planning</span>
                    </ResponsiveNavLink>
                    <ResponsiveNavLink
                        :href="route('statistics')"
                        :active="route().current('statistics')"
                        class="flex items-center space-x-3 px-4 py-3 rounded-lg text-base font-medium hover:bg-purple-50 hover:text-purple-600 transition-all duration-200"
                    >
                        <span class="text-xl">📊</span>
                        <span>Statistiques</span>
                    </ResponsiveNavLink>
                    <ResponsiveNavLink
                        :href="route('profils')"
                        :active="route().current('profils')"
                        class="flex items-center space-x-3 px-4 py-3 rounded-lg text-base font-medium hover:bg-orange-50 hover:text-orange-600 transition-all duration-200"
                    >
                        <span class="text-xl">👤</span>
                        <span>Profils</span>
                    </ResponsiveNavLink>
                </div>

                <!-- Section utilisateur mobile -->
                <div class="border-t border-gray-200 px-4 py-4">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                            <span class="text-white font-bold text-lg">{{ $page.props.auth.user.pseudo.charAt(0).toUpperCase() }}</span>
                        </div>
                        <div>
                            <div class="text-base font-medium text-gray-900">{{ $page.props.auth.user.pseudo }}</div>
                            <div class="text-sm text-gray-500">{{ $page.props.auth.user.email }}</div>
                        </div>
                    </div>
                    
                    <div class="space-y-1">
                        <ResponsiveNavLink :href="route('profile.edit')" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-base font-medium hover:bg-gray-50 transition-all duration-200">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                            <span>Profil</span>
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="flex items-center space-x-3 px-4 py-3 rounded-lg text-base font-medium text-red-600 hover:bg-red-50 transition-all duration-200 w-full text-left"
                        >
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd" />
                            </svg>
                            <span>Déconnexion</span>
                        </ResponsiveNavLink>
                    </div>
                </div>
            </div>
        </nav>
    </div>
</template>
