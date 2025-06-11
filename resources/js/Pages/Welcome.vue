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

        <nav v-if="canLogin" class="flex flex-1 justify-between items-center px-6 py-12">
            <div class="flex items-center gap-2">
                <img src="/img/logo_evostat.png" alt="Logo EvoStat" class="h-10 w-auto" loading="lazy">
                <h1 class="text-sm">EvoStat</h1>
            </div>
            <Link
                v-if="$page.props.auth.user"
                :href="route('profils')"
                class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
            >
                Profils
            </Link>

            <div class="space-x-3">
                <Link
                    :href="route('login')"
                    class="text-white bg-evogradientleft py-2 px-8 rounded-2xl"
                >
                    Log in
                </Link>

                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="text-white bg-evogradientleft py-2 px-8 rounded-2xl"
                >
                    Register
                </Link>
            </div>
        </nav>
        <div class="flex items-center justify-around px-24 py-36">
            <div class="space-y-6">
                <h1 class="max-w-3xl text-8xl text-evogray font-bold">Évolue, séance après séance.</h1>
                <p class="max-w-2xl text-2xl">Avec EvoStat, chaque séance vous rapproche de votre meilleure version :
                    suivez vos stats, explorez de
                    nouveaux exercices, évoluez.
                </p>
            </div>
            <img src="/img/evostat_grand.png" alt="Logo EvoStat" class="h-96 w-auto" loading="lazy">
        </div>
    </header>
    <main>
        <div class="max-w-screen-xl mx-auto grid grid-cols-2 gap-8 my-12">
            <div
                class="flex justify-center items-center flex-col gap-6 bg-white shadow-evoShadow px-6 pt-10 max-w-xl rounded-mainRounded">
                <div class="flex items-center gap-6">
                    <img src="/img/icon_recherche.png" alt="">
                    <h2 class="text-evogray text-4xl font-bold">Trouve de nouveaux exercices !</h2>
                </div>
                <p class="text-2xl text-evogray maw-w-2xs">Plus d’une centaine d’exos expliqué et détailler</p>
                <div class="shadow-evoShadow p-6 rounded-t-mainRounded max-w-sm">
                    <p class="font-bold text-evogray text-xl">Découvrir plus...</p>
                    <div class="flex gap-1">
                        <div>
                            <img src="/img/home_bench.png" alt="">
                            <img src="/img/home_curl.png" alt="">

                        </div>
                        <div>
                            <img src="/img/home_traction.png" alt="">
                            <img src="/img/home_squat.png" alt="">
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="flex justify-center items-center flex-col gap-6 bg-white shadow-evoShadow px-6 pt-10 max-w-xl rounded-mainRounded">
                <div class="flex items-center gap-6">
                    <img src="/img/icon_graphique.png" alt="">
                    <h2 class="text-evogray text-4xl font-bold">Mesure tes performances</h2>
                </div>
                <p class="text-2xl text-evogray maw-w-2xs">Des graphiques mis à jours après chaque séances pour suivre
                    ton évolution</p>
                <div class="shadow-evoShadow p-6 rounded-t-mainRounded max-w-sm">
                    <p class="font-bold text-evogray text-xl">Découvrir plus...</p>
                    <div class="flex gap-1">
                        <div>
                            <img src="/img/home_bench.png" alt="">
                            <img src="/img/home_curl.png" alt="">

                        </div>
                        <div>
                            <img src="/img/home_traction.png" alt="">
                            <img src="/img/home_squat.png" alt="">
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="flex justify-center items-center flex-col gap-6 bg-white shadow-evoShadow px-6 pt-10 max-w-xl rounded-mainRounded">
                <div class="flex items-center gap-6">
                    <img src="/img/icon_chrono.png" alt="">
                    <h2 class="text-evogray text-4xl font-bold">Retrouve toutes tes ancienne séances</h2>
                </div>
                <p class="text-2xl text-evogray maw-w-2xs">Retrouve toutes tes séances et performance au même
                    endroit</p>
                <div class="shadow-evoShadow p-6 rounded-t-mainRounded max-w-sm">
                    <p class="font-bold text-evogray text-xl">Découvrir plus...</p>
                    <div class="flex gap-1">
                        <div>
                            <img src="/img/home_bench.png" alt="">
                            <img src="/img/home_curl.png" alt="">

                        </div>
                        <div>
                            <img src="/img/home_traction.png" alt="">
                            <img src="/img/home_squat.png" alt="">
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="flex justify-center items-center flex-col gap-6 bg-white shadow-evoShadow px-6 pt-10 max-w-xl rounded-mainRounded">
                <div class="flex items-center gap-6">
                    <img src="/img/icon_podium.png" alt="">
                    <h2 class="text-evogray text-4xl font-bold">Augmente ton rank grâce a tes perf !</h2>
                </div>
                <p class="text-2xl text-evogray maw-w-2xs">Découvre un système de badge unique a débloquer grâce a ton
                    évolution </p>
                <div class="shadow-evoShadow p-6 rounded-t-mainRounded max-w-sm">
                    <p class="font-bold text-evogray text-xl">Découvrir plus...</p>
                    <div class="flex gap-1">
                        <div>
                            <img src="/img/home_bench.png" alt="">
                            <img src="/img/home_curl.png" alt="">

                        </div>
                        <div>
                            <img src="/img/home_traction.png" alt="">
                            <img src="/img/home_squat.png" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-evogray px-24 py-14 text-white font-bold space-y-10">
            <div class="flex items-center gap-6">
                <WavingHand/>
                <h3 class="text-4xl">Hey, bienvenue sur EvoStat !</h3>
            </div>
            <div class="text-2xl space-y-8 pr-44">
                <p>
                    Déjà, merci d’être là. Si tu lis ça, c’est que t’es à deux clics de rejoindre l’aventure. En
                    t’inscrivant, tu découvres la toute première version d’EvoStat. Alors oui, tout n’est pas encore
                    parfait, mais c’est normal : on démarre, on teste, on avance. Et surtout, on évolue avec toi.
                </p>
                <p>
                    On prévoit plein de mises à jour, des améliorations non-stop, et ton avis compte à fond. Dis-nous ce
                    qui te plaît (ou pas), et on s’en occupe.
                    Notre mission ? Rendre tes séances de sport plus simples, plus claires, plus motivantes.
                </p>
                <p>
                    Tout est dispo sur le web, et si ça cartonne, une app mobile verra peut-être le jour 👀
                    Encore un grand merci à toi, futur utilisateur d’EvoStat.
                </p>
                <p>Evoluons -- ensemble</p>

            </div>
        </div>
    </main>
    <FooterBar/>
</template>
