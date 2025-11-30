<x-layout>
  <div class="max-w-md mx-auto bg-white p-6 rounded shadow mt-[25vh]">
    <img src="{{ asset('img/kings_G.png') }}" alt="Reyes" class="h-24 w-24 mx-auto mb-4">
    <h2 class="text-xl font-semibold mb-4">Acceder</h2>
    <form id="loginForm" class="space-y-3">
      <input class="w-full border rounded px-3 py-2" type="text" name="login" placeholder="Email o usuario" required>
      <input class="w-full border rounded px-3 py-2" type="password" name="password" placeholder="Contraseña" required>
      <button class="w-full px-3 py-2 rounded bg-blue-600 text-white">Entrar</button>
    </form>
    <p id="loginError" class="mt-2 text-sm text-red-600"></p>
    <p class="mt-3 text-sm">¿No tienes cuenta? <a class="text-blue-700" href="{{ url('/register') }}">Regístrate</a> · <a class="text-blue-700" href="{{ url('/password/forgot') }}">¿Olvidaste tu contraseña?</a></p>
  </div>
  <script>
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
      e.preventDefault()
      const fd = new FormData(e.target)
      const payload = Object.fromEntries(fd.entries())
      try {
        const res = await api('/auth/login', { method: 'POST', body: JSON.stringify(payload) })
        if (res && res.user) location.href = '{{ url('/') }}'
      } catch (err) {
        document.getElementById('loginError').textContent = err.message
      }
    })
  </script>
</x-layout>
