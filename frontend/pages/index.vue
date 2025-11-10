<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
        APIs AEVO - Demo
      </h2>
      <p class="mt-2 text-center text-sm text-gray-600">
        Prueba de autenticación con OTP
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <!-- Formulario de Email -->
        <div v-if="!otpSent" class="space-y-6">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
              Email
            </label>
            <div class="mt-1">
              <input
                id="email"
                v-model="email"
                type="email"
                required
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                placeholder="tu@email.com"
              />
            </div>
          </div>

          <div>
            <button
              @click="handleSendOtp"
              :disabled="loading || !email"
              class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
            >
              {{ loading ? 'Enviando...' : 'Enviar Código OTP' }}
            </button>
          </div>
        </div>

        <!-- Formulario de OTP -->
        <div v-else class="space-y-6">
          <div>
            <label for="code" class="block text-sm font-medium text-gray-700">
              Código OTP
            </label>
            <div class="mt-1">
              <input
                id="code"
                v-model="code"
                type="text"
                maxlength="6"
                required
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-center text-2xl tracking-widest"
                placeholder="000000"
              />
            </div>
            <p class="mt-2 text-sm text-gray-500">
              Código enviado a: {{ email }}
            </p>
          </div>

          <div class="flex space-x-3">
            <button
              @click="handleVerifyOtp"
              :disabled="loading || code.length !== 6"
              class="flex-1 flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
            >
              {{ loading ? 'Verificando...' : 'Verificar' }}
            </button>
            
            <button
              @click="resetForm"
              class="flex-1 flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Cambiar Email
            </button>
          </div>
        </div>

        <!-- Mensajes -->
        <div v-if="message" class="mt-4">
          <div :class="[
            'rounded-md p-4',
            messageType === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'
          ]">
            {{ message }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const authStore = useAuthStore()
const router = useRouter()

const email = ref('')
const code = ref('')
const otpSent = ref(false)
const loading = ref(false)
const message = ref('')
const messageType = ref('success')

// Redirigir si ya está autenticado
onMounted(() => {
  authStore.initAuth()
  if (authStore.isAuthenticated) {
    router.push('/dashboard')
  }
})

const handleSendOtp = async () => {
  loading.value = true
  message.value = ''
  
  const result = await authStore.sendOtp(email.value)
  
  if (result.success) {
    otpSent.value = true
    messageType.value = 'success'
    message.value = result.message
  } else {
    messageType.value = 'error'
    message.value = result.message
  }
  
  loading.value = false
}

const handleVerifyOtp = async () => {
  loading.value = true
  message.value = ''
  
  const result = await authStore.verifyOtp(email.value, code.value)
  
  if (result.success) {
    messageType.value = 'success'
    message.value = 'Login exitoso, redirigiendo...'
    setTimeout(() => {
      router.push('/dashboard')
    }, 1000)
  } else {
    messageType.value = 'error'
    message.value = result.message
    code.value = ''
  }
  
  loading.value = false
}

const resetForm = () => {
  otpSent.value = false
  code.value = ''
  message.value = ''
}
</script>