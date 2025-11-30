<x-layout>
  <div class="max-w-2xl mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-xl font-semibold mb-4">Tu perfil</h2>
    <div class="flex items-center gap-4 mb-6">
      @php($u = auth()->user())
      @if($u->avatar_path)
        <img src="{{ asset('storage/'.$u->avatar_path) }}" alt="Avatar" class="h-16 w-16 rounded-full object-cover">
      @else
        @php($parts = explode(' ', $u->name))
        @php($initials = strtoupper(substr($parts[0] ?? '',0,1).substr($parts[1] ?? '',0,1)))
        <div class="h-16 w-16 rounded-full bg-gray-300 flex items-center justify-center text-lg font-bold">{{ $initials }}</div>
      @endif
      <div>
        <div class="font-medium">{{ $u->name }} <span class="text-gray-500">({{ $u->username }})</span></div>
        <div class="text-sm text-gray-500">{{ $u->email }}</div>
      </div>
    </div>

    <h3 class="font-semibold mb-2">Cambiar contraseña</h3>
    <form id="pwdForm" class="grid grid-cols-1 md:grid-cols-3 gap-2 mb-6">
      <input class="border rounded px-2 py-1" name="current" type="password" placeholder="Actual" required>
      <input class="border rounded px-2 py-1" name="password" type="password" placeholder="Nueva" required>
      <input class="border rounded px-2 py-1" name="password_confirmation" type="password" placeholder="Confirmar" required>
      <button class="px-3 py-1 rounded bg-blue-600 text-white md:col-span-3">Guardar</button>
    </form>
    <p id="pwdMsg" class="text-sm"></p>

    <h3 class="font-semibold mb-2">Avatar</h3>
    <form id="avatarForm" class="flex items-center gap-2 mb-2">
      <input type="file" id="avatarInput" accept="image/png, image/jpeg">
      <button class="px-3 py-1 rounded bg-green-600 text-white" type="submit">Subir</button>
      <button id="delAvatar" class="px-3 py-1 rounded bg-gray-300" type="button">Eliminar</button>
    </form>
    <img id="avatarPreview" class="h-24 w-24 rounded-full object-cover hidden" alt="Preview">
  </div>
  <script>
    document.getElementById('pwdForm').addEventListener('submit', async (e) => {
      e.preventDefault()
      const fd = new FormData(e.target)
      const payload = Object.fromEntries(fd.entries())
      try {
        await api('/profile/password', { method:'POST', body: JSON.stringify(payload) })
        document.getElementById('pwdMsg').textContent = 'Contraseña actualizada'
      } catch (err) {
        document.getElementById('pwdMsg').textContent = err.message
      }
    })
    const input = document.getElementById('avatarInput')
    input.addEventListener('change', () => {
      const file = input.files[0]
      if (!file) return
      const ok = ['image/png','image/jpeg'].includes(file.type)
      document.getElementById('avatarPreview').classList.toggle('hidden', !ok)
      if (ok) document.getElementById('avatarPreview').src = URL.createObjectURL(file)
    })
    document.getElementById('avatarForm').addEventListener('submit', async (e) => {
      e.preventDefault()
      const file = input.files[0]
      if (!file) return
      const fd = new FormData()
      fd.append('avatar', file)
      const res = await fetch(`${API_BASE}/profile/avatar`, { method:'POST', body: fd, credentials:'same-origin' })
      if (res.ok) location.reload()
    })
    document.getElementById('delAvatar').addEventListener('click', async () => {
      const res = await fetch(`${API_BASE}/profile/avatar`, { method:'DELETE', credentials:'same-origin' })
      if (res.ok) location.reload()
    })
  </script>
</x-layout>
