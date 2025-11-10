<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-sm text-gray-500">APIs AEVO - Demo</p>
          </div>
          <button
            @click="handleLogout"
            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium"
          >
            Cerrar Sesión
          </button>
        </div>
      </div>
    </header>

    <!-- Content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <!-- User Info Card -->
        <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
              Información del Usuario
            </h3>
            <div v-if="authStore.user" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div>
                <dt class="text-sm font-medium text-gray-500">ID</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ authStore.user.id }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ authStore.user.name }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Email</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ authStore.user.email }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">2FA Habilitado</dt>
                <dd class="mt-1 text-sm text-gray-900">
                  <span :class="[
                    'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                    authStore.user.two_factor_enabled 
                      ? 'bg-green-100 text-green-800' 
                      : 'bg-red-100 text-red-800'
                  ]">
                    {{ authStore.user.two_factor_enabled ? 'Sí' : 'No' }}
                  </span>
                </dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Creado</dt>
                <dd class="mt-1 text-sm text-gray-900">
                  {{ new Date(authStore.user.created_at).toLocaleDateString() }}
                </dd>
              </div>
            </div>
          </div>
        </div>

        <!-- API Test Card -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
              Pruebas de API
            </h3>
            
            <div class="space-y-4">
              <button
                @click="testHealthEndpoint"
                class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium"
              >
                Probar Health Check
              </button>
              
              <button
                @click="refreshUserData"
                class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium ml-0 sm:ml-3"
              >
                Actualizar Datos de Usuario
              </button>
            </div>

            <!-- Results -->
            <div v-if="apiResult" class="mt-4">
              <h4 class="text-sm font-medium text-gray-900 mb-2">Resultado:</h4>
              <pre class="bg-gray-100 p-3 rounded-md text-xs overflow-x-auto">{{ JSON.stringify(apiResult, null, 2) }}</pre>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
const authStore = useAuthStore()
const router = useRouter()
const config = useRuntimeConfig()

const apiResult = ref(null)

// Middleware de autenticación
onMounted(() => {
  authStore.initAuth()
  if (!authStore.isAuthenticated) {
    router.push('/')
  }
})

const handleLogout = async () => {
  await authStore.logout()
  router.push('/')
}

const testHealthEndpoint = async () => {
  try {
    const data = await $fetch(`${config.public.apiBase}/health`)
    apiResult.value = data
  } catch (error) {
    apiResult.value = { error: error.message }
  }
}

const refreshUserData = async () => {
  try {
    const userData = await authStore.fetchUser()
    apiResult.value = userData
  } catch (error) {
    apiResult.value = { error: error.message }
  }
}
</script>