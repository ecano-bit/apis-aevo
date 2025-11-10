<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          APIs AEVO - Demo
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Prueba de autenticación con OTP
        </p>
      </div>
      
      <div class="bg-white p-8 rounded-lg shadow">
        <!-- Formulario de Email -->
        <div v-if="!otpSent" class="space-y-6">
          <div>
            <label class="block text-sm font-medium text-gray-700">
              Email
            </label>
            <input
              v-model="email"
              type="email"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md"
              placeholder="tu@email.com"
            />
          </div>
          <button
            @click="sendOtp"
            :disabled="!email || loading"
            class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 disabled:opacity-50"
          >
            {{ loading ? 'Enviando...' : 'Enviar Código OTP' }}
          </button>
        </div>

        <!-- Formulario de OTP -->
        <div v-else class="space-y-6">
          <div>
            <label class="block text-sm font-medium text-gray-700">
              Código OTP
            </label>
            <input
              v-model="code"
              type="text"
              maxlength="6"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md text-center text-2xl"
              placeholder="000000"
            />
            <p class="mt-2 text-sm text-gray-500">
              Código enviado a: {{ email }}
            </p>
          </div>
          <div class="flex space-x-3">
            <button
              @click="verifyOtp"
              :disabled="code.length !== 6 || loading"
              class="flex-1 bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 disabled:opacity-50"
            >
              {{ loading ? 'Verificando...' : 'Verificar' }}
            </button>
            <button
              @click="resetForm"
              class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400"
            >
              Cambiar Email
            </button>
          </div>
        </div>

        <!-- Usuario autenticado -->
        <div v-if="user" class="space-y-4">
          <h3 class="text-lg font-medium text-green-600">¡Login exitoso!</h3>
          <div class="bg-gray-50 p-4 rounded">
            <p><strong>ID:</strong> {{ user.id }}</p>
            <p><strong>Nombre:</strong> {{ user.name }}</p>
            <p><strong>Email:</strong> {{ user.email }}</p>
          </div>
          <button
            @click="logout"
            class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700"
          >
            Cerrar Sesión
          </button>
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
const config = useRuntimeConfig()

const email = ref('')
const code = ref('')
const otpSent = ref(false)
const loading = ref(false)
const message = ref('')
const messageType = ref('success')
const user = ref(null)

const sendOtp = async () => {
  loading.value = true
  message.value = ''
  
  try {
    await $fetch(`${config.public.apiBase}/auth/send-otp`, {
      method: 'POST',
      body: { email: email.value }
    })
    
    otpSent.value = true
    messageType.value = 'success'
    message.value = 'Código enviado. Revisa los logs de Laravel para ver el código.'
  } catch (error) {
    messageType.value = 'error'
    message.value = error.data?.message || 'Error al enviar código'
  }
  
  loading.value = false
}

const verifyOtp = async () => {
  loading.value = true
  message.value = ''
  
  try {
    const data = await $fetch(`${config.public.apiBase}/auth/verify-otp`, {
      method: 'POST',
      body: { 
        email: email.value, 
        code: code.value 
      }
    })
    
    user.value = data.user
    messageType.value = 'success'
    message.value = 'Login exitoso'
  } catch (error) {
    messageType.value = 'error'
    message.value = error.data?.message || 'Código inválido'
    code.value = ''
  }
  
  loading.value = false
}

const logout = () => {
  user.value = null
  resetForm()
}

const resetForm = () => {
  otpSent.value = false
  code.value = ''
  message.value = ''
}
</script>