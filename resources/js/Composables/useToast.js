import { reactive, ref } from 'vue';

const toasts = reactive([]);
let toastId = 0;

export function useToast() {
  const addToast = (options) => {
    const id = ++toastId;
    const toast = {
      id,
      show: true,
      type: options.type || 'success',
      title: options.title || '',
      message: options.message || '',
      autoHide: options.autoHide !== false,
      duration: options.duration || 5000,
      ...options
    };
    
    toasts.push(toast);
    
    return id;
  };

  const removeToast = (id) => {
    const index = toasts.findIndex(toast => toast.id === id);
    if (index > -1) {
      toasts.splice(index, 1);
    }
  };

  const success = (message, options = {}) => {
    return addToast({
      type: 'success',
      message,
      ...options
    });
  };

  const error = (message, options = {}) => {
    return addToast({
      type: 'error',
      message,
      autoHide: false, // Error messages should persist
      ...options
    });
  };

  const warning = (message, options = {}) => {
    return addToast({
      type: 'warning',
      message,
      ...options
    });
  };

  const info = (message, options = {}) => {
    return addToast({
      type: 'info',
      message,
      ...options
    });
  };

  const clear = () => {
    toasts.splice(0, toasts.length);
  };

  return {
    toasts,
    addToast,
    removeToast,
    success,
    error,
    warning,
    info,
    clear
  };
} 