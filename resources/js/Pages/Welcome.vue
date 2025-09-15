<script setup>
import {Head, Link} from '@inertiajs/vue3';
import WavingHand from "@/Components/WavingHand.vue";
import FooterBar from "@/Components/FooterBar.vue";

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
</script>

<template>
    <Head title="Welcome"/>
    <header class="">

        <nav v-if="canLogin" class="flex flex-1 justify-between items-center px-4 sm:px-6 py-8 sm:py-12">
            <div class="flex items-center gap-2">
                <img src="/img/logo_evostat.png" alt="Logo EvoStat" class="h-8 sm:h-10 w-auto" loading="lazy">
                <h1 class="text-xs sm:text-sm">EvoStat</h1>
            </div>
            <Link
                v-if="$page.props.auth.user"
                :href="route('profils')"
                class="rounded-md px-2 sm:px-3 py-1 sm:py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white text-sm sm:text-base"
            >
                Profils
            </Link>

            <div class="space-x-1 sm:space-x-3">
                <Link
                    :href="route('login')"
                    class="text-white bg-evogradientleft py-1 sm:py-2 px-4 sm:px-8 rounded-xl sm:rounded-2xl text-sm sm:text-base"
                >
                    Log in
                </Link>

                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="text-white bg-evogradientleft py-1 sm:py-2 px-4 sm:px-8 rounded-xl sm:rounded-2xl text-sm sm:text-base"
                >
                    Register
                </Link>
            </div>
        </nav>
        <div class="flex flex-col lg:flex-row items-center justify-around px-4 sm:px-8 md:px-16 lg:px-24 py-16 sm:py-24 lg:py-36 gap-8 lg:gap-0">
            <div class="space-y-4 sm:space-y-6 text-center lg:text-left">
                <h1 class="max-w-3xl text-4xl sm:text-5xl md:text-6xl lg:text-7xl xl:text-8xl text-evogray font-bold">Évolue, séance après séance.</h1>
                <p class="max-w-2xl text-lg sm:text-xl lg:text-2xl">Avec EvoStat, chaque séance vous rapproche de votre meilleure version :
                    suivez vos stats, explorez de
                    nouveaux exercices, évoluez.
                </p>
            </div>
            <img src="/img/evostat_grand.png" alt="Logo EvoStat" class="h-48 sm:h-64 md:h-80 lg:h-96 w-auto" loading="lazy">
        </div>
    </header>
    <main>
        <div class="max-w-screen-xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 my-8 sm:my-12 px-4 sm:px-6 lg:px-8">
            <div
                class="flex justify-center items-center flex-col gap-4 sm:gap-6 bg-white shadow-evoShadow px-4 sm:px-6 pt-6 sm:pt-10 max-w-xl rounded-mainRounded">
                <div class="flex items-center gap-3 sm:gap-6">
                    <img src="/img/icon_recherche.png" alt="" class="w-8 sm:w-12 h-auto">
                    <h2 class="text-evogray text-2xl sm:text-3xl lg:text-4xl font-bold">Trouve de nouveaux exercices !</h2>
                </div>
                <p class="text-lg sm:text-xl lg:text-2xl text-evogray max-w-2xs text-center">Plus d'une centaine d'exos expliqué et détailler</p>
                <div class="shadow-evoShadow p-4 sm:p-6 rounded-t-mainRounded max-w-sm">
                    <p class="font-bold text-evogray text-lg sm:text-xl">Découvrir plus...</p>
                    <div class="flex gap-1">
                        <div>
                            <img src="/img/home_bench.png" alt="" class="w-16 sm:w-20 h-auto">
                            <img src="/img/home_curl.png" alt="" class="w-16 sm:w-20 h-auto">

                        </div>
                        <div>
                            <img src="/img/home_traction.png" alt="" class="w-16 sm:w-20 h-auto">
                            <img src="/img/home_squat.png" alt="" class="w-16 sm:w-20 h-auto">
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="flex justify-center items-center flex-col gap-4 sm:gap-6 bg-white shadow-evoShadow px-4 sm:px-6 pt-6 sm:pt-10 max-w-xl rounded-mainRounded">
                <div class="flex items-center gap-3 sm:gap-6">
                    <img src="/img/icon_graphique.png" alt="" class="w-8 sm:w-12 h-auto">
                    <h2 class="text-evogray text-2xl sm:text-3xl lg:text-4xl font-bold">Mesure tes performances</h2>
                </div>
                <p class="text-lg sm:text-xl lg:text-2xl text-evogray max-w-2xs text-center">Des graphiques mis à jours après chaque séances pour suivre
                    ton évolution</p>
                <div class="shadow-evoShadow p-4 sm:p-6 rounded-t-mainRounded max-w-sm">
                    <p class="font-bold text-evogray text-lg sm:text-xl">Découvrir plus...</p>
                    <div class="flex gap-1">
                        <div>
                            <img src="/img/home_bench.png" alt="" class="w-16 sm:w-20 h-auto">
                            <img src="/img/home_curl.png" alt="" class="w-16 sm:w-20 h-auto">

                        </div>
                        <div>
                            <img src="/img/home_traction.png" alt="" class="w-16 sm:w-20 h-auto">
                            <img src="/img/home_squat.png" alt="" class="w-16 sm:w-20 h-auto">
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="flex justify-center items-center flex-col gap-4 sm:gap-6 bg-white shadow-evoShadow px-4 sm:px-6 pt-6 sm:pt-10 max-w-xl rounded-mainRounded">
                <div class="flex items-center gap-3 sm:gap-6">
                    <img src="/img/icon_chrono.png" alt="" class="w-8 sm:w-12 h-auto">
                    <h2 class="text-evogray text-2xl sm:text-3xl lg:text-4xl font-bold">Retrouve toutes tes ancienne séances</h2>
                </div>
                <p class="text-lg sm:text-xl lg:text-2xl text-evogray max-w-2xs text-center">Retrouve toutes tes séances et performance au même
                    endroit</p>
                <div class="shadow-evoShadow p-4 sm:p-6 rounded-t-mainRounded max-w-sm">
                    <p class="font-bold text-evogray text-lg sm:text-xl">Découvrir plus...</p>
                    <div class="flex gap-1">
                        <div>
                            <img src="/img/home_bench.png" alt="" class="w-16 sm:w-20 h-auto">
                            <img src="/img/home_curl.png" alt="" class="w-16 sm:w-20 h-auto">

                        </div>
                        <div>
                            <img src="/img/home_traction.png" alt="" class="w-16 sm:w-20 h-auto">
                            <img src="/img/home_squat.png" alt="" class="w-16 sm:w-20 h-auto">
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="flex justify-center items-center flex-col gap-4 sm:gap-6 bg-white shadow-evoShadow px-4 sm:px-6 pt-6 sm:pt-10 max-w-xl rounded-mainRounded">
                <div class="flex items-center gap-3 sm:gap-6">
                    <img src="/img/icon_podium.png" alt="" class="w-8 sm:w-12 h-auto">
                    <h2 class="text-evogray text-2xl sm:text-3xl lg:text-4xl font-bold">Augmente ton rank grâce a tes perf !</h2>
                </div>
                <p class="text-lg sm:text-xl lg:text-2xl text-evogray max-w-2xs text-center">Découvre un système de badge unique a débloquer grâce a ton
                    évolution </p>
                <div class="shadow-evoShadow p-4 sm:p-6 rounded-t-mainRounded max-w-sm">
                    <p class="font-bold text-evogray text-lg sm:text-xl">Découvrir plus...</p>
                    <div class="flex gap-1">
                        <div>
                            <img src="/img/home_bench.png" alt="" class="w-16 sm:w-20 h-auto">
                            <img src="/img/home_curl.png" alt="" class="w-16 sm:w-20 h-auto">

                        </div>
                        <div>
                            <img src="/img/home_traction.png" alt="" class="w-16 sm:w-20 h-auto">
                            <img src="/img/home_squat.png" alt="" class="w-16 sm:w-20 h-auto">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-evogray px-4 sm:px-8 md:px-16 lg:px-24 py-8 sm:py-12 lg:py-14 text-white font-bold space-y-6 sm:space-y-8 lg:space-y-10">
            <div class="flex items-center gap-3 sm:gap-6">
                <WavingHand/>
                <h3 class="text-2xl sm:text-3xl lg:text-4xl">Hey, bienvenue sur EvoStat !</h3>
            </div>
            <div class="text-lg sm:text-xl lg:text-2xl space-y-4 sm:space-y-6 lg:space-y-8 pr-0 sm:pr-8 md:pr-16 lg:pr-44">
                <p>
                    Déjà, merci d'être là. Si tu lis ça, c'est que t'es à deux clics de rejoindre l'aventure. En
                    t'inscrivant, tu découvres la toute première version d'EvoStat. Alors oui, tout n'est pas encore
                    parfait, mais c'est normal : on démarre, on teste, on avance. Et surtout, on évolue avec toi.
                </p>
                <p>
                    On prévoit plein de mises à jour, des améliorations non-stop, et ton avis compte à fond. Dis-nous ce
                    qui te plaît (ou pas), et on s'en occupe.
                    Notre mission ? Rendre tes séances de sport plus simples, plus claires, plus motivantes.
                </p>
                <p>
                    Tout est dispo sur le web, et si ça cartonne, une app mobile verra peut-être le jour 👀
                    Encore un grand merci à toi, futur utilisateur d'EvoStat.
                </p>
                <p>Evoluons -- ensemble</p>

            </div>
        </div>
    </main>
    <FooterBar/>
</template>
