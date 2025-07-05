<template>
  <AppLayout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Detalhes do Contato</h1>
            <p class="text-gray-600">Visualize as informações do contato</p>
          </div>
          <div class="flex justify-end space-x-3">
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

            <Button
              v-if="$page.props.features.contacts.can_update"
              variant="secondary"
              size="sm"
              :href="route('contacts.edit', contact.id)"
              class="flex items-center"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
              Editar
            </Button>

            <Button
              v-if="$page.props.features.contacts.can_delete"
              variant="danger"
              size="sm"
              @click="confirmDelete"
              class="flex items-center"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
              Excluir
            </Button>
          </div>
        </div>
      </div>

      <!-- Contact Card -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4">
          <!-- Contact Avatar and Name -->
          <div class="flex items-center mb-8">
            <div class="flex-shrink-0 h-20 w-20">
              <div class="h-20 w-20 rounded-full bg-blue-500 flex items-center justify-center">
                <span class="text-white font-bold text-2xl">
                  {{ contact.name.charAt(0).toUpperCase() }}
                </span>
              </div>
            </div>
            <div class="ml-6">
              <h2 class="text-3xl font-bold text-gray-900">{{ contact.name }}</h2>
              <p class="text-lg text-gray-600">{{ contact.email }}</p>
            </div>
          </div>

          <!-- Contact Details -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Personal Information -->
            <div class="space-y-6">
              <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações Pessoais</h3>
                <div class="space-y-4">
                  <div class="bg-gray-50 rounded-lg p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Nome Completo
                    </label>
                    <p class="text-lg text-gray-900">{{ contact.name }}</p>
                  </div>
                  
                  <div class="bg-gray-50 rounded-lg p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Email
                    </label>
                    <p class="text-lg text-gray-900">
                      <a :href="`mailto:${contact.email}`" class="text-blue-600 hover:text-blue-800 underline">
                        {{ contact.email }}
                      </a>
                    </p>
                  </div>
                  
                  <div class="bg-gray-50 rounded-lg p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Telefone
                    </label>
                    <p class="text-lg text-gray-900">
                      <a :href="`tel:${contact.phone}`" class="text-blue-600 hover:text-blue-800 underline">
                        {{ formatPhone(contact.phone) }}
                      </a>
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- System Information -->
            <div class="space-y-6">
              <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações do Sistema</h3>
                <div class="space-y-4">
                  <div class="bg-gray-50 rounded-lg p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      ID do Contato
                    </label>
                    <p class="text-lg text-gray-900 font-mono">#{{ contact.id }}</p>
                  </div>
                  
                  <div class="bg-gray-50 rounded-lg p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Criado em
                    </label>
                    <p class="text-lg text-gray-900">{{ formatDate(contact.created_at) }}</p>
                  </div>
                  
                  <div class="bg-gray-50 rounded-lg p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Última atualização
                    </label>
                    <p class="text-lg text-gray-900">{{ formatDate(contact.updated_at) }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="mt-8 pt-6 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Ações Rápidas</h3>
            <div class="flex flex-wrap gap-4">
              <Button
                tag="a"
                :href="`mailto:${contact.email}`"
                variant="outline"
                size="lg"
              >
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                </svg>
                Enviar Email
              </Button>
              
              <Button
                tag="a"
                :href="`tel:${contact.phone}`"
                variant="outline"
                size="lg"
              >
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                </svg>
                Ligar
              </Button>
              
              <Button
                tag="a"
                :href="`https://wa.me/${contact.phone.replace(/\D/g, '')}`"
                target="_blank"
                variant="success"
                size="lg"
              >
                <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                </svg>
                WhatsApp
              </Button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <Modal
      :show="showDeleteModal"
      type="danger"
      title="Confirmar exclusão"
      :message="`Tem certeza que deseja excluir o contato ${contact.name}? Esta ação não pode ser desfeita.`"
      confirm-text="Excluir"
      @close="showDeleteModal = false"
      @confirm="deleteContact"
    />
  </AppLayout>
</template>

<script setup>
import { ref, inject } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Pages/Layouts/AppLayout.vue';
import Button from '@/Components/Button.vue';
import Modal from '@/Components/Modal.vue';

const route = inject('route');

const props = defineProps({
  contact: {
    type: Object,
    required: true
  }
});

const showDeleteModal = ref(false);

const confirmDelete = () => {
  showDeleteModal.value = true;
};

const deleteContact = () => {
  router.delete(route('contacts.destroy', props.contact.id), {
    onSuccess: () => {
      showDeleteModal.value = false;
    }
  });
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
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const openEmailClient = (email) => {
  window.location.href = `mailto:${email}`;
};

const callPhone = (phone) => {
  const cleanPhone = phone.replace(/\D/g, '');
  window.location.href = `tel:+55${cleanPhone}`;
};

const openWhatsApp = (phone) => {
  const cleanPhone = phone.replace(/\D/g, '');
  const whatsappUrl = `https://wa.me/55${cleanPhone}`;
  window.open(whatsappUrl, '_blank');
};
</script> 