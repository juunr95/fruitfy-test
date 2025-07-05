<template>
  <div v-if="validMeta && meta.last_page > 1" class="flex items-center justify-between bg-white px-4 py-3 sm:px-6 border-t border-gray-200">
    <div class="flex flex-1 justify-between sm:hidden">
      <Link
        v-if="meta.current_page > 1 && links.prev"
        :href="links.prev"
        class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
      >
        Anterior
      </Link>
      <span
        v-else
        class="relative inline-flex items-center rounded-md border border-gray-300 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-400 cursor-not-allowed"
      >
        Anterior
      </span>
      
      <Link
        v-if="meta.current_page < meta.last_page && links.next"
        :href="links.next"
        class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
      >
        Próximo
      </Link>
      <span
        v-else
        class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-400 cursor-not-allowed"
      >
        Próximo
      </span>
    </div>
    
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
      <div v-if="meta.from && meta.to && meta.total">
        <p class="text-sm text-gray-700">
          Mostrando
          <span class="font-medium">{{ meta.from }}</span>
          até
          <span class="font-medium">{{ meta.to }}</span>
          de
          <span class="font-medium">{{ meta.total }}</span>
          resultados
        </p>
      </div>
      
      <div>
        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
          <!-- Previous Page Link -->
          <Link
            v-if="meta.current_page > 1 && links.prev"
            :href="links.prev"
            class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-10 focus:outline-offset-0"
          >
            <span class="sr-only">Anterior</span>
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
            </svg>
          </Link>
          <span
            v-else
            class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-300 ring-1 ring-inset ring-gray-300 cursor-not-allowed"
          >
            <span class="sr-only">Anterior</span>
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
            </svg>
          </span>
          
          <!-- Page Numbers -->
          <template v-for="page in pageNumbers" :key="page">
            <Link
              v-if="page === meta.current_page"
              :href="getPageUrl(page)"
              class="relative z-10 inline-flex items-center bg-blue-600 px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
            >
              {{ page }}
            </Link>
            <span
              v-else-if="page === '...'"
              class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 cursor-default"
            >
              ...
            </span>
            <Link
              v-else
              :href="getPageUrl(page)"
              class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-10 focus:outline-offset-0"
            >
              {{ page }}
            </Link>
          </template>
          
          <!-- Next Page Link -->
          <Link
            v-if="meta.current_page < meta.last_page && links.next"
            :href="links.next"
            class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-10 focus:outline-offset-0"
          >
            <span class="sr-only">Próximo</span>
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
            </svg>
          </Link>
          <span
            v-else
            class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-300 ring-1 ring-inset ring-gray-300 cursor-not-allowed"
          >
            <span class="sr-only">Próximo</span>
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
            </svg>
          </span>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  meta: {
    type: Object,
    required: true
  },
  links: {
    type: Object,
    required: true
  }
});

// Check if meta data is valid
const validMeta = computed(() => {
  return props.meta && 
         typeof props.meta.current_page === 'number' && 
         typeof props.meta.last_page === 'number' &&
         props.meta.current_page > 0 &&
         props.meta.last_page > 0;
});

const pageNumbers = computed(() => {
  if (!validMeta.value) return [];
  
  const current = props.meta.current_page;
  const last = props.meta.last_page;
  const delta = 2;
  
  // Handle single page case
  if (last === 1) return [1];
  
  const range = [];
  const rangeWithDots = [];
  
  for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
    range.push(i);
  }
  
  if (current - delta > 2) {
    rangeWithDots.push(1, '...');
  } else {
    rangeWithDots.push(1);
  }
  
  rangeWithDots.push(...range);
  
  if (current + delta < last - 1) {
    rangeWithDots.push('...', last);
  } else {
    rangeWithDots.push(last);
  }
  
  return rangeWithDots;
});

const getPageUrl = (page) => {
  if (page === '...') return '#';
  
  try {
    const url = new URL(window.location.href);
    url.searchParams.set('page', page);
    return url.toString();
  } catch (e) {
    console.error('Error generating page URL:', e);
    return '#';
  }
};
</script> 