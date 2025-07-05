<template>
  <AppLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Criar Contato</h1>
            <p class="text-gray-600">
              <span v-if="$page.props.features.contacts.can_create">
                Preencha as informações abaixo para criar um novo contato
              </span>
              <span v-else class="text-orange-600">
                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.664-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                A criação de contatos está temporariamente desabilitada
              </span>
            </p>
          </div>
          <Button
            variant="outline"
            size="sm"
            :href="route('contacts.index')"
            class="flex items-center"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Voltar
          </Button>
        </div>
      </div>

      <!-- Form -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4">
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Contact Information -->
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações do Contato</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                  <Input
                    id="name"
                    name="name"
                    v-model="form.name"
                    label="Nome Completo"
                    placeholder="Digite o nome completo"
                    required
                    :error="errors.name"
                    help="Mínimo de 3 caracteres"
                  />
                </div>
                
                <Input
                  id="email"
                  name="email"
                  type="email"
                  v-model="form.email"
                  label="Email"
                  placeholder="exemplo@dominio.com"
                  required
                  :error="errors.email"
                  help="Insira um email válido"
                />
                
                <Input
                  id="phone"
                  name="phone"
                  v-model="form.phone"
                  label="Telefone"
                  placeholder="(11) 99999-9999"
                  required
                  :error="errors.phone"
                  help="Formato: (XX) XXXXX-XXXX"
                  @input="formatPhoneInput"
                />
              </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3">
              <Button
                variant="outline"
                size="lg"
                @click="resetForm"
                :disabled="form.processing"
              >
                Limpar
              </Button>
              <Button
                variant="primary"
                size="lg"
                @click="submit"
                :disabled="form.processing || !$page.props.features.contacts.can_create"
                :loading="form.processing"
              >
                Salvar Contato
              </Button>
            </div>
          </form>
        </div>
      </div>

      <!-- Form Preview -->
      <div v-if="showPreview" class="mt-6 bg-gray-50 rounded-lg border border-gray-200">
        <div class="px-6 py-4">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Pré-visualização</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div v-if="preview.name" class="bg-white rounded-lg p-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Nome</label>
              <p class="text-gray-900">{{ preview.name }}</p>
            </div>
            
            <div v-if="preview.email" class="bg-white rounded-lg p-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
              <p class="text-gray-900">{{ preview.email }}</p>
            </div>
            
            <div v-if="preview.phone" class="bg-white rounded-lg p-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
              <p class="text-gray-900">{{ preview.phone }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, ref, inject } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Pages/Layouts/AppLayout.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';

const route = inject('route');

const form = useForm({
  name: '',
  email: '',
  phone: ''
});

const preview = reactive({
  name: '',
  email: '',
  phone: ''
});

const errors = reactive({
  name: '',
  email: '',
  phone: ''
});

const showPreview = ref(false);

// Real-time validation
const validateForm = () => {
  const newErrors = {};
  
  if (!form.name.trim()) {
    newErrors.name = 'O nome é obrigatório';
  } else if (form.name.length < 2) {
    newErrors.name = 'O nome deve ter pelo menos 2 caracteres';
  }
  
  if (!form.email.trim()) {
    newErrors.email = 'O email é obrigatório';
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    newErrors.email = 'O email deve ter um formato válido';
  }
  
  if (!form.phone.trim()) {
    newErrors.phone = 'O telefone é obrigatório';
  } else if (!/^(\+55\s?)?(\(?\d{2}\)?[\s\-]?)?\d{4,5}[\s\-]?\d{4}$/.test(form.phone)) {
    newErrors.phone = 'O telefone deve ter um formato válido';
  }
  
  Object.keys(errors).forEach(key => {
    errors[key] = newErrors[key] || '';
  });
  
  return Object.keys(newErrors).length === 0;
};

const updatePreview = () => {
  preview.name = form.name;
  preview.email = form.email;
  preview.phone = formatPhone(form.phone);
  showPreview.value = form.name || form.email || form.phone;
};

const formatPhone = (phone) => {
  if (!phone) return '';
  
  // Remove all non-digits
  const cleaned = phone.replace(/\D/g, '');
  
  // Format based on length
  if (cleaned.length === 11) {
    return cleaned.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
  } else if (cleaned.length === 10) {
    return cleaned.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
  }
  
  return phone;
};

const formatPhoneInput = (event) => {
  const value = event.target.value;
  form.phone = formatPhone(value);
  updatePreview();
};

const submit = () => {
  if (validateForm()) {
    form.post(route('contacts.store'), {
      onSuccess: () => {
        form.reset();
        Object.keys(errors).forEach(key => {
          errors[key] = '';
        });
        showPreview.value = false;
      },
      onError: (serverErrors) => {
        Object.keys(serverErrors).forEach(key => {
          if (errors.hasOwnProperty(key)) {
            errors[key] = serverErrors[key];
          }
        });
      }
    });
  }
};

const resetForm = () => {
  form.reset();
  Object.keys(errors).forEach(key => {
    errors[key] = '';
  });
  showPreview.value = false;
};

// Watch for form changes
const handleInputChange = (field) => {
  if (field === 'phone') {
    // Let formatPhoneInput handle phone formatting
    return;
  }
  
  // Validate on input change
  setTimeout(() => {
    validateForm();
    updatePreview();
  }, 300);
};
</script> 