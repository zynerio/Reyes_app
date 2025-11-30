<x-layout>
  <div class="bg-white p-6 rounded shadow">
    <h2 class="text-xl font-semibold mb-4">Gestión de usuarios</h2>
    <form id="createUserForm" class="mb-4 grid grid-cols-1 md:grid-cols-5 gap-2">
      <input name="name" class="border rounded px-2 py-1" placeholder="Nombre" required>
      <input name="email" type="email" class="border rounded px-2 py-1" placeholder="Email" required>
      <input name="username" class="border rounded px-2 py-1" placeholder="Usuario (opcional)">
      <input name="password" type="password" class="border rounded px-2 py-1" placeholder="Contraseña" required>
      <select name="role" class="border rounded px-2 py-1">
        <option value="user">user</option>
        <option value="admin">admin</option>
      </select>
      <div class="md:col-span-5 text-right">
        <button class="px-3 py-1 rounded bg-green-600 text-white" type="submit">Crear usuario</button>
      </div>
    </form>
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-sm min-w-[720px]">
        <thead>
          <tr class="text-left">
            <th class="p-2">ID</th><th class="p-2">Nombre</th><th class="p-2">Email</th><th class="p-2">Usuario</th><th class="p-2">Rol</th><th class="p-2">Acciones</th>
          </tr>
        </thead>
        <tbody id="rows"></tbody>
      </table>
    </div>
    <div id="cards" class="md:hidden space-y-3"></div>
  </div>
  <script>
    document.getElementById('createUserForm').addEventListener('submit', async (e) => {
      e.preventDefault()
      const fd = new FormData(e.target)
      const payload = Object.fromEntries(fd.entries())
      try {
        const u = await api('/admin/users', { method:'POST', body: JSON.stringify(payload) })
        e.target.reset()
        if (window.notify) window.notify(`Usuario creado: ${u.username}`, 'success')
        load()
      } catch (err) {
        if (window.notify) window.notify(err.message, 'error')
      }
    })
    async function load() {
      const users = await api('/admin/users')
      const me = (await api('/profile')).user
      const tbody = document.getElementById('rows')
      const cards = document.getElementById('cards')
      tbody.innerHTML = ''
      cards.innerHTML = ''
      users.forEach(u => {
        const tr = document.createElement('tr')
        tr.innerHTML = `
          <td class="p-2">${u.id}</td>
          <td class="p-2"><input class="border rounded px-2 py-1 w-full" value="${u.name}" data-id="${u.id}" data-field="name"></td>
          <td class="p-2"><input class="border rounded px-2 py-1 w-full" value="${u.email}" data-id="${u.id}" data-field="email"></td>
          <td class="p-2"><input class="border rounded px-2 py-1 w-full" value="${u.username||''}" data-id="${u.id}" data-field="username"></td>
          <td class="p-2">
            <select class="border rounded px-2 py-1 w-full" data-id="${u.id}" data-field="role">
              <option ${u.role==='user'?'selected':''}>user</option>
              <option ${u.role==='admin'?'selected':''}>admin</option>
            </select>
          </td>
          <td class="p-2">
            <div class="flex flex-wrap gap-2">
              <button class="px-2 py-1 rounded bg-blue-600 text-white" data-action="save" data-id="${u.id}">Guardar</button>
              ${u.id!==me.id ? `<button class="px-2 py-1 rounded bg-red-600 text-white" data-action="del" data-id="${u.id}">Eliminar</button>` : '<span class="text-gray-400">No puedes eliminarte</span>'}
            </div>
          </td>
        `
        tbody.appendChild(tr)
        const card = document.createElement('div')
        card.className = 'p-3 bg-white rounded shadow'
        card.innerHTML = `
          <div class="flex justify-end mb-2">
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs ${u.role==='admin'?'bg-purple-100 text-purple-800':'bg-gray-100 text-gray-800'}">${u.role}</span>
          </div>
          <div class="grid grid-cols-1 gap-2">
            <input class="border rounded px-2 py-1" value="${u.name}" data-id="${u.id}" data-field="name" placeholder="Nombre">
            <input class="border rounded px-2 py-1" value="${u.email}" data-id="${u.id}" data-field="email" placeholder="Email">
            <input class="border rounded px-2 py-1" value="${u.username||''}" data-id="${u.id}" data-field="username" placeholder="Usuario">
            <select class="border rounded px-2 py-1" data-id="${u.id}" data-field="role">
              <option ${u.role==='user'?'selected':''}>user</option>
              <option ${u.role==='admin'?'selected':''}>admin</option>
            </select>
            <div class="flex flex-wrap gap-2 mt-1">
              <button class="px-2 py-1 rounded bg-blue-600 text-white" data-action="save" data-id="${u.id}">Guardar</button>
              ${u.id!==me.id ? `<button class="px-2 py-1 rounded bg-red-600 text-white" data-action="del" data-id="${u.id}">Eliminar</button>` : '<span class="text-gray-400">No puedes eliminarte</span>'}
            </div>
          </div>
        `
        cards.appendChild(card)
      })
      function collectPayload(container, id) {
        const payload = {}
        container.querySelectorAll(`[data-id="${id}"]`).forEach(el => payload[el.dataset.field]=el.value)
        return payload
      }
      tbody.querySelectorAll('button').forEach(btn => {
        btn.addEventListener('click', async () => {
          const id = btn.dataset.id
          const act = btn.dataset.action
          if (act==='save') {
            const payload = collectPayload(tbody, id)
            try {
              await api(`/admin/users/${id}`, { method:'PUT', body: JSON.stringify(payload) })
              if (window.notify) window.notify('Usuario actualizado', 'success')
            } catch (err) {
              if (window.notify) window.notify(err.message, 'error')
            }
          } else if (act==='del') {
            try {
              await api(`/admin/users/${id}`, { method:'DELETE' })
              if (window.notify) window.notify('Usuario eliminado', 'success')
              load()
            } catch (err) {
              if (window.notify) window.notify(err.message, 'error')
            }
          }
        })
      })
      cards.querySelectorAll('button').forEach(btn => {
        btn.addEventListener('click', async () => {
          const id = btn.dataset.id
          const act = btn.dataset.action
          if (act==='save') {
            const payload = collectPayload(cards, id)
            try {
              await api(`/admin/users/${id}`, { method:'PUT', body: JSON.stringify(payload) })
              if (window.notify) window.notify('Usuario actualizado', 'success')
            } catch (err) {
              if (window.notify) window.notify(err.message, 'error')
            }
          } else if (act==='del') {
            try {
              await api(`/admin/users/${id}`, { method:'DELETE' })
              if (window.notify) window.notify('Usuario eliminado', 'success')
              load()
            } catch (err) {
              if (window.notify) window.notify(err.message, 'error')
            }
          }
        })
      })
    }
    load()
  </script>
</x-layout>
