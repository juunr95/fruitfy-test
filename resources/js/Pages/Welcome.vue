<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">
                Bem-vindo!
            </h1>
            <p class="text-gray-600 mb-6">
                Laravel + Inertia.js + Vue 3 + Tailwind CSS funcionando perfeitamente!
            </p>
            <div class="space-y-2">
                <p class="text-sm text-gray-500">
                    <strong>Framework:</strong> Laravel {{ laravelVersion }}
                </p>
                <p class="text-sm text-gray-500">
                    <strong>PHP:</strong> {{ phpVersion }}
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    laravelVersion: String,
    phpVersion: String,
});
</script> 