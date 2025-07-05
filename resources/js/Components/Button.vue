<template>
  <component 
    :is="computedTag" 
    :class="buttonClasses"
    :disabled="isDisabled"
    :type="computedType"
    :href="href"
    v-bind="$attrs"
  >
    <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <slot />
  </component>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'danger', 'success', 'outline'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  disabled: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  type: {
    type: String,
    default: 'button'
  },
  tag: {
    type: String,
    default: null
  },
  href: {
    type: String,
    default: null
  }
});

const computedTag = computed(() => {
  // If href is provided, render as link
  if (props.href) {
    return 'a';
  }
  
  // If tag is explicitly provided, use it
  if (props.tag) {
    return props.tag;
  }
  
  // Default to button
  return 'button';
});

const computedType = computed(() => {
  // Don't set type for links
  if (computedTag.value === 'a') {
    return undefined;
  }
  
  return props.type;
});

const isDisabled = computed(() => {
  return props.disabled || props.loading;
});

const buttonClasses = computed(() => {
  const baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
  
  const variantClasses = {
    primary: 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
    secondary: 'bg-gray-200 text-gray-900 hover:bg-gray-300 focus:ring-gray-500',
    danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
    success: 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
    outline: 'border border-gray-300 text-gray-700 hover:bg-gray-50 focus:ring-blue-500'
  };
  
  const sizeClasses = {
    sm: 'px-3 py-2 text-sm',
    md: 'px-4 py-2 text-sm',
    lg: 'px-6 py-3 text-base'
  };
  
  const classes = [
    baseClasses,
    variantClasses[props.variant],
    sizeClasses[props.size]
  ];
  
  // Add disabled styles for links
  if (computedTag.value === 'a' && isDisabled.value) {
    classes.push('pointer-events-none');
  }
  
  return classes.join(' ');
});
</script> 