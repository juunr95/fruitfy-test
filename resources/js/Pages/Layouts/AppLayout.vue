<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center">
            <Link href="/" class="text-xl font-semibold text-gray-900 hover:text-blue-600 transition-colors">
              Sistema de Contatos
            </Link>
          </div>
          <nav class="flex space-x-4">
            <Link 
              href="/contacts" 
              class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors"
            >
              Contatos
            </Link>
            <Link 
              href="/contacts/create" 
              class="bg-blue-600 text-white hover:bg-blue-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors"
            >
              Novo Contato
            </Link>
          </nav>
        </div>
      </div>
    </header>

    <!-- Flash Messages Display -->
    <div v-if="$page.props.flash?.message" class="bg-green-50 border-l-4 border-green-400 p-4 m-4">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.236 4.53L7.53 10.22a.75.75 0 00-1.06 1.061l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <p class="text-sm text-green-700">
            {{ $page.props.flash.message }}
          </p>
        </div>
      </div>
    </div>

    <div v-if="$page.props.flash?.error" class="bg-red-50 border-l-4 border-red-400 p-4 m-4">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <p class="text-sm text-red-700">
            {{ $page.props.flash.error }}
          </p>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Content -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <slot />
      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-auto">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="text-center text-sm text-gray-500">
          © {{ new Date().getFullYear() }} Sistema de Contatos. Todos os direitos reservados.
        </div>
      </div>
    </footer>

    <!-- Toast Notifications Container -->
    <div class="fixed top-4 right-4 z-50 space-y-2">
      <Toast
        v-for="toast in toasts"
        :key="toast.id"
        :show="toast.show"
        :type="toast.type"
        :title="toast.title"
        :message="toast.message"
        :auto-hide="toast.autoHide"
        :duration="toast.duration"
        @close="removeToast(toast.id)"
      />
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Toast from '@/Components/Toast.vue';
import { useToast } from '@/Composables/useToast.js';

const page = usePage();
const { toasts, removeToast, success, error, warning, info } = useToast();

// Handle flash messages from the backend
const handleFlashMessages = () => {
  const flash = page.props.flash || {};
  
  if (flash.success) {
    success(flash.success);
  }
  
  if (flash.error) {
    error(flash.error);
  }
  
  if (flash.warning) {
    warning(flash.warning);
  }
  
  if (flash.info) {
    info(flash.info);
  }
};

// Watch for flash message changes
watch(() => page.props.flash, handleFlashMessages, { deep: true, immediate: true });

onMounted(() => {
  handleFlashMessages();
});
</script> 