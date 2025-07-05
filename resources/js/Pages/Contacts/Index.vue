<template>
  <AppLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Contatos</h1>
            <p class="text-gray-600">Gerencie seus contatos</p>
          </div>
          <Button 
            v-if="$page.props.features.contacts.can_create"
            variant="primary" 
            :href="route('contacts.create')"
            class="flex items-center"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Novo Contato
          </Button>
          <div 
            v-else 
            class="flex items-center text-gray-500"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.664-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            Criação de contatos desabilitada
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="mb-6 bg-gray-50 rounded-lg p-4">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Filtros</h3>
        <form @submit.prevent="applyFilters" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <Input
              v-model="form.search"
              label="Busca geral"
              placeholder="Pesquisar em todos os campos..."
              help="Pesquisa por nome, email ou telefone"
            />
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">Ordenar por</label>
              <select
                v-model="form.sort_by"
                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Padrão (mais recente)</option>
                <option value="name">Nome</option>
                <option value="email">Email</option>
                <option value="phone">Telefone</option>
                <option value="created_at">Data de criação</option>
                <option value="updated_at">Data de atualização</option>
              </select>
            </div>
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">Direção</label>
              <select
                v-model="form.sort_direction"
                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="asc">Crescente</option>
                <option value="desc">Decrescente</option>
              </select>
            </div>
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">Itens por página</label>
              <select
                v-model="form.per_page"
                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="10">10 itens</option>
                <option value="25">25 itens</option>
                <option value="50">50 itens</option>
                <option value="100">100 itens</option>
              </select>
            </div>
          </div>
          
          <div class="flex justify-end space-x-2">
            <Button type="submit">
              Aplicar Filtros
            </Button>
            <Button type="button" variant="outline" @click="clearFilters">
              Limpar
            </Button>
          </div>
        </form>
      </div>

      <!-- Results -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <!-- Table Header -->
        <div class="px-6 py-3 border-b border-gray-200 bg-gray-50">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">
              {{ contactsMeta.total }} {{ contactsMeta.total === 1 ? 'contato' : 'contatos' }}
            </h3>
            <div class="text-sm text-gray-500" v-if="contactsMeta.from && contactsMeta.to">
              {{ contactsMeta.from }} - {{ contactsMeta.to }} de {{ contactsMeta.total }}
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <button @click="sortBy('name')" class="flex items-center hover:text-gray-700">
                    Nome
                    <svg v-if="form.sort_by === 'name'" class="ml-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path v-if="form.sort_direction === 'asc'" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                      <path v-else d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"/>
                    </svg>
                  </button>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <button @click="sortBy('email')" class="flex items-center hover:text-gray-700">
                    Email
                    <svg v-if="form.sort_by === 'email'" class="ml-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path v-if="form.sort_direction === 'asc'" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                      <path v-else d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"/>
                    </svg>
                  </button>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <button @click="sortBy('phone')" class="flex items-center hover:text-gray-700">
                    Telefone
                    <svg v-if="form.sort_by === 'phone'" class="ml-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path v-if="form.sort_direction === 'asc'" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                      <path v-else d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"/>
                    </svg>
                  </button>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <button @click="sortBy('created_at')" class="flex items-center hover:text-gray-700">
                    Criado em
                    <svg v-if="form.sort_by === 'created_at'" class="ml-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path v-if="form.sort_direction === 'asc'" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/>
                      <path v-else d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"/>
                    </svg>
                  </button>
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Ações
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="contactsData.length === 0">
                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                  <div class="flex flex-col items-center space-y-2">
                    <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <p class="text-lg font-medium">Nenhum contato encontrado</p>
                    <p class="text-sm">Tente ajustar os filtros ou criar um novo contato</p>
                  </div>
                </td>
              </tr>
              
              <tr v-for="contact in contactsData" :key="contact.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center">
                        <span class="text-white font-medium text-sm">
                          {{ contact.name.charAt(0).toUpperCase() }}
                        </span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">
                        {{ contact.name }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ contact.email }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ formatPhone(contact.phone) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ formatDate(contact.created_at) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex justify-end space-x-2">
                    <Button 
                      variant="outline" 
                      size="sm" 
                      :href="route('contacts.show', contact.id)"
                      class="flex items-center"
                    >
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                      </svg>
                      Ver
                    </Button>
                    
                    <Button 
                      v-if="$page.props.features.contacts.can_update"
                      variant="secondary" 
                      size="sm" 
                      :href="route('contacts.edit', contact.id)"
                      class="flex items-center"
                    >
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                      </svg>
                      Editar
                    </Button>
                    
                    <Button 
                      v-if="$page.props.features.contacts.can_delete"
                      variant="danger" 
                      size="sm"
                      @click="confirmDelete(contact)"
                      class="flex items-center"
                    >
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                      </svg>
                      Excluir
                    </Button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <Pagination
          v-if="contactsMeta.last_page > 1"
          :meta="contactsMeta"
          :links="contactsLinks"
        />
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <Modal
      :show="showDeleteModal"
      type="danger"
      title="Confirmar exclusão"
      :message="`Tem certeza que deseja excluir o contato ${contactToDelete?.name}? Esta ação não pode ser desfeita.`"
      confirm-text="Excluir"
      @close="showDeleteModal = false"
      @confirm="deleteContact"
    />
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { inject } from 'vue';
import AppLayout from '@/Pages/Layouts/AppLayout.vue';
import Button from '@/Components/Button.vue';
import Input from '@/Components/Input.vue';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';

const route = inject('route');

const props = defineProps({
    "contacts": {
        "required": true
    },
    "filters": {}
});

// Computed properties with defensive checks
const contactsData = computed(() => {
  return props.contacts?.data || [];
});

const contactsMeta = computed(() => {
  return {
    total: props.contacts?.total || 0,
    from: props.contacts?.from || 0,
    to: props.contacts?.to || 0,
    current_page: props.contacts?.current_page || 1,
    last_page: props.contacts?.last_page || 1,
    per_page: props.contacts?.per_page || 10
  };
});

const contactsLinks = computed(() => {
  if (!props.contacts?.links) {
    return { prev: null, next: null };
  }
  
  return {
    prev: props.contacts.prev_page_url,
    next: props.contacts.next_page_url
  };
});

const form = reactive({
  search: props.filters.search || '',
  sort_by: props.filters.sort_by || '',
  sort_direction: props.filters.sort_direction || 'desc',
  per_page: props.filters.per_page || 10
});

const showDeleteModal = ref(false);
const contactToDelete = ref(null);

const applyFilters = () => {
  const filters = {};
  
  // Only add non-empty filters
  Object.keys(form).forEach(key => {
    if (form[key] && form[key].toString().trim() !== '') {
      filters[key] = form[key];
    }
  });

  router.get(route('contacts.index'), filters, {
    preserveState: true,
    replace: true
  });
};

const clearFilters = () => {
  Object.keys(form).forEach(key => {
    if (key === 'per_page') {
      form[key] = 10;
    } else if (key === 'sort_direction') {
      form[key] = 'desc';
    } else {
      form[key] = '';
    }
  });
  
  router.get(route('contacts.index'), { per_page: 10, sort_direction: 'desc' }, {
    preserveState: true,
    replace: true
  });
};

const sortBy = (field) => {
  if (form.sort_by === field) {
    // Toggle direction if same field
    form.sort_direction = form.sort_direction === 'asc' ? 'desc' : 'asc';
  } else {
    // Set new field with default direction
    form.sort_by = field;
    form.sort_direction = 'asc';
  }
  
  applyFilters();
};

const confirmDelete = (contact) => {
  contactToDelete.value = contact;
  showDeleteModal.value = true;
};

const deleteContact = () => {
  if (contactToDelete.value) {
    router.delete(route('contacts.destroy', contactToDelete.value.id), {
      onSuccess: () => {
        showDeleteModal.value = false;
        contactToDelete.value = null;
      }
    });
  }
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

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  });
};

// Watch for per_page changes and apply automatically
watch(() => form.per_page, () => {
  applyFilters();
});

// Watch for sort changes and apply automatically
watch(() => [form.sort_by, form.sort_direction], () => {
  if (form.sort_by) {
    applyFilters();
  }
});
</script> 