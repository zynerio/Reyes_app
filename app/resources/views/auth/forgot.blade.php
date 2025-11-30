<x-layout>
  <div class="max-w-md mx-auto bg-white p-6 rounded shadow mt-[25vh]">
    <h2 class="text-xl font-semibold mb-4">Recuperar contraseña</h2>
    <form id="forgotForm" class="space-y-3">
      <input class="w-full border rounded px-3 py-2" type="email" name="email" placeholder="Email" required>
      <button class="w-full px-3 py-2 rounded bg-blue-600 text-white">Generar token</button>
    </form>
    <p id="forgotMsg" class="mt-2 text-sm"></p>
    <h3 class="font-semibold mt-4">Usar token</h3>
    <form id="resetForm" class="space-y-3">
      <input class="w-full border rounded px-3 py-2" type="text" name="token" placeholder="Token" required>
      <input class="w-full border rounded px-3 py-2" type="password" name="password" placeholder="Nueva contraseña" required>
      <input class="w-full border rounded px-3 py-2" type="password" name="password_confirmation" placeholder="Confirmar" required>
      <button class="w-full px-3 py-2 rounded bg-green-600 text-white">Restablecer</button>
    </form>
    <p id="resetMsg" class="mt-2 text-sm"></p>
  </div>
  <script>
    document.getElementById('forgotForm').addEventListener('submit', async (e) => {
      e.preventDefault()
      const fd = new FormData(e.target)
      const payload = Object.fromEntries(fd.entries())
      try {
        const res = await api('/auth/password/forgot', { method: 'POST', body: JSON.stringify(payload) })
        document.getElementById('forgotMsg').textContent = 'Token: ' + res.token
      } catch (err) {
        document.getElementById('forgotMsg').textContent = err.message
      }
    })
    document.getElementById('resetForm').addEventListener('submit', async (e) => {
      e.preventDefault()
      const fd = new FormData(e.target)
      const payload = Object.fromEntries(fd.entries())
      try {
        await api('/auth/password/reset', { method: 'POST', body: JSON.stringify(payload) })
        document.getElementById('resetMsg').textContent = 'Contraseña restablecida'
      } catch (err) {
        document.getElementById('resetMsg').textContent = err.message
      }
    })
  </script>
</x-layout>

