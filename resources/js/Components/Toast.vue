<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0 transform translate-y-2 scale-95"
      enter-to-class="opacity-100 transform translate-y-0 scale-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100 transform translate-y-0 scale-100"
      leave-to-class="opacity-0 transform translate-y-2 scale-95"
    >
      <div
        v-if="show"
        :class="[
          'fixed top-4 right-4 max-w-sm w-full z-50 pointer-events-auto',
          'rounded-lg shadow-lg border',
          toastClasses
        ]"
      >
        <div class="p-4">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <component :is="iconComponent" :class="iconClasses" />
            </div>
            <div class="ml-3 w-0 flex-1">
              <p :class="titleClasses" v-if="title">{{ title }}</p>
              <p :class="messageClasses">{{ message }}</p>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
              <button
                @click="close"
                :class="buttonClasses"
                class="inline-flex rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2"
              >
                <span class="sr-only">Fechar</span>
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>
        </div>
        
        <!-- Progress bar for auto-hide -->
        <div v-if="autoHide && duration > 0" class="w-full bg-gray-200 h-1">
          <div 
            :class="progressBarClasses"
            class="h-full transition-all ease-linear"
            :style="{ width: `${progressWidth}%` }"
          ></div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  type: {
    type: String,
    default: 'success',
    validator: (value) => ['success', 'error', 'warning', 'info'].includes(value)
  },
  title: {
    type: String,
    default: ''
  },
  message: {
    type: String,
    required: true
  },
  autoHide: {
    type: Boolean,
    default: true
  },
  duration: {
    type: Number,
    default: 5000
  }
});

const emit = defineEmits(['close']);

const progressWidth = ref(100);
let timer = null;
let progressTimer = null;

const toastClasses = computed(() => {
  const baseClasses = 'bg-white';
  
  const typeClasses = {
    success: 'border-green-200',
    error: 'border-red-200',
    warning: 'border-yellow-200',
    info: 'border-blue-200'
  };
  
  return `${baseClasses} ${typeClasses[props.type]}`;
});

const iconComponent = computed(() => {
  const icons = {
    success: 'CheckCircleIcon',
    error: 'XCircleIcon',
    warning: 'ExclamationTriangleIcon',
    info: 'InformationCircleIcon'
  };
  
  return icons[props.type];
});

const iconClasses = computed(() => {
  const baseClasses = 'h-6 w-6';
  
  const typeClasses = {
    success: 'text-green-400',
    error: 'text-red-400',
    warning: 'text-yellow-400',
    info: 'text-blue-400'
  };
  
  return `${baseClasses} ${typeClasses[props.type]}`;
});

const titleClasses = computed(() => {
  const typeClasses = {
    success: 'text-green-800',
    error: 'text-red-800',
    warning: 'text-yellow-800',
    info: 'text-blue-800'
  };
  
  return `text-sm font-medium ${typeClasses[props.type]}`;
});

const messageClasses = computed(() => {
  const typeClasses = {
    success: 'text-green-700',
    error: 'text-red-700',
    warning: 'text-yellow-700',
    info: 'text-blue-700'
  };
  
  return `text-sm ${typeClasses[props.type]} ${props.title ? 'mt-1' : ''}`;
});

const buttonClasses = computed(() => {
  const typeClasses = {
    success: 'text-green-400 hover:text-green-500 focus:ring-green-500',
    error: 'text-red-400 hover:text-red-500 focus:ring-red-500',
    warning: 'text-yellow-400 hover:text-yellow-500 focus:ring-yellow-500',
    info: 'text-blue-400 hover:text-blue-500 focus:ring-blue-500'
  };
  
  return typeClasses[props.type];
});

const progressBarClasses = computed(() => {
  const typeClasses = {
    success: 'bg-green-400',
    error: 'bg-red-400',
    warning: 'bg-yellow-400',
    info: 'bg-blue-400'
  };
  
  return typeClasses[props.type];
});

const close = () => {
  clearTimers();
  emit('close');
};

const clearTimers = () => {
  if (timer) {
    clearTimeout(timer);
    timer = null;
  }
  if (progressTimer) {
    clearInterval(progressTimer);
    progressTimer = null;
  }
};

const startAutoHide = () => {
  if (!props.autoHide || props.duration <= 0) return;
  
  progressWidth.value = 100;
  
  // Start progress bar animation
  const interval = 50; // Update every 50ms
  const step = (interval / props.duration) * 100;
  
  progressTimer = setInterval(() => {
    progressWidth.value -= step;
    if (progressWidth.value <= 0) {
      progressWidth.value = 0;
      clearInterval(progressTimer);
    }
  }, interval);
  
  // Auto close after duration
  timer = setTimeout(() => {
    close();
  }, props.duration);
};

onMounted(() => {
  if (props.show) {
    startAutoHide();
  }
});

onUnmounted(() => {
  clearTimers();
});

// Restart timer when show prop changes
const restartTimer = () => {
  clearTimers();
  if (props.show) {
    startAutoHide();
  }
};

// Watch for show prop changes
import { watch } from 'vue';
watch(() => props.show, restartTimer);

// Icon components as inline SVGs
const CheckCircleIcon = {
  template: `
    <svg viewBox="0 0 20 20" fill="currentColor" class="w-6 h-6">
      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.236 4.53L7.53 10.22a.75.75 0 00-1.06 1.061l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
    </svg>
  `
};

const XCircleIcon = {
  template: `
    <svg viewBox="0 0 20 20" fill="currentColor" class="w-6 h-6">
      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
    </svg>
  `
};

const ExclamationTriangleIcon = {
  template: `
    <svg viewBox="0 0 20 20" fill="currentColor" class="w-6 h-6">
      <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.19-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
    </svg>
  `
};

const InformationCircleIcon = {
  template: `
    <svg viewBox="0 0 20 20" fill="currentColor" class="w-6 h-6">
      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
    </svg>
  `
};
</script> 