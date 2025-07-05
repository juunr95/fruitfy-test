<template>
  <AppLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Editar Contato</h1>
            <p class="text-gray-600">Edite as informações do contato: {{ contact.name }}</p>
          </div>
          <div class="flex space-x-3">
            <Button
              tag="a"
              :href="route('contacts.show', contact.id)"
              variant="outline"
            >
              <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
              </svg>
              Cancelar
            </Button>
            <Button
              tag="a"
              :href="route('contacts.index')"
              variant="secondary"
            >
              <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
              </svg>
              Todos os Contatos
            </Button>
          </div>
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
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
              <Button
                type="button"
                variant="outline"
                @click="resetForm"
              >
                Reverter
              </Button>
              <Button
                @click="submit"
                :disabled="form.processing || !hasChanges"
                :loading="form.processing"
                size="lg"
              >
                Salvar Alterações
              </Button>
            </div>
          </form>
        </div>
      </div>

      <!-- Changes Preview -->
      <div v-if="hasChanges" class="mt-6 bg-yellow-50 rounded-lg border border-yellow-200">
        <div class="px-6 py-4">
          <h3 class="text-lg font-semibold text-yellow-800 mb-4">
            <svg class="inline h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
            Alterações Pendentes
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div v-if="form.name !== originalData.name" class="bg-white rounded-lg p-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Nome</label>
              <p class="text-sm text-gray-500 line-through">{{ originalData.name }}</p>
              <p class="text-yellow-800 font-medium">{{ form.name }}</p>
            </div>
            
            <div v-if="form.email !== originalData.email" class="bg-white rounded-lg p-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
              <p class="text-sm text-gray-500 line-through">{{ originalData.email }}</p>
              <p class="text-yellow-800 font-medium">{{ form.email }}</p>
            </div>
            
            <div v-if="normalizePhone(form.phone) !== normalizePhone(originalData.phone)" class="bg-white rounded-lg p-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
              <p class="text-sm text-gray-500 line-through">{{ formatPhone(originalData.phone) }}</p>
              <p class="text-yellow-800 font-medium">{{ form.phone }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Current Data -->
      <div class="mt-6 bg-gray-50 rounded-lg border border-gray-200">
        <div class="px-6 py-4">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Dados Atuais</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg p-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Nome</label>
              <p class="text-gray-900">{{ contact.name }}</p>
            </div>
            
            <div class="bg-white rounded-lg p-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
              <p class="text-gray-900">{{ contact.email }}</p>
            </div>
            
            <div class="bg-white rounded-lg p-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
              <p class="text-gray-900">{{ formatPhone(contact.phone) }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive, ref, computed, watch, inject } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Pages/Layouts/AppLayout.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';

const route = inject('route');

const props = defineProps({
  contact: {
    type: Object,
    required: true
  }
});

const form = useForm({
  name: props.contact.name,
  email: props.contact.email,
  phone: props.contact.phone
});

const originalData = {
  name: props.contact.name,
  email: props.contact.email,
  phone: props.contact.phone
};

const errors = reactive({
  name: '',
  email: '',
  phone: ''
});

const hasChanges = computed(() => {
  return form.name !== originalData.name || 
         form.email !== originalData.email || 
         normalizePhone(form.phone) !== normalizePhone(originalData.phone);
});

const changes = computed(() => {
  const changesList = [];
  
  if (form.name !== originalData.name) {
    changesList.push({
      field: 'Nome',
      old: originalData.name,
      new: form.name
    });
  }
  
  if (form.email !== originalData.email) {
    changesList.push({
      field: 'Email',
      old: originalData.email,
      new: form.email
    });
  }
  
  if (normalizePhone(form.phone) !== normalizePhone(originalData.phone)) {
    changesList.push({
      field: 'Telefone',
      old: formatPhone(originalData.phone),
      new: formatPhone(form.phone)
    });
  }
  
  return changesList;
});

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

const normalizePhone = (phone) => {
  if (!phone) return '';
  // Remove all non-digits to compare raw numbers
  return phone.replace(/\D/g, '');
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
};

const submit = () => {
  if (validateForm()) {
    form.put(route('contacts.update', props.contact.id), {
      onSuccess: () => {
        // Success message will be handled by the controller
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
  form.name = originalData.name;
  form.email = originalData.email;
  form.phone = originalData.phone;
  
  Object.keys(errors).forEach(key => {
    errors[key] = '';
  });
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

// Watch for form changes and validate
watch([() => form.name, () => form.email, () => form.phone], () => {
  validateForm();
}, { deep: true });
</script> 