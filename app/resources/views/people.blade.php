<x-layout>
  <div class="bg-white p-6 rounded shadow">
    <h2 class="text-xl font-semibold mb-4">Personas</h2>
    <form id="newPerson" class="grid grid-cols-1 md:grid-cols-4 gap-2 mb-4">
      <input class="border rounded px-2 py-1" name="name" placeholder="Nombre" required>
      <input class="border rounded px-2 py-1" name="contact" placeholder="Contacto">
      <input class="border rounded px-2 py-1" name="note" placeholder="Nota">
      <button class="px-3 py-1 rounded bg-blue-600 text-white">Crear</button>
    </form>
    <p id="peopleMsg" class="text-sm mb-2"></p>
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-sm min-w-[720px]">
        <thead><tr class="text-left"><th class="p-2">Nombre</th><th class="p-2">Contacto</th><th class="p-2">Nota</th><th class="p-2">Color</th><th class="p-2">Acciones</th></tr></thead>
        <tbody id="peopleRows"></tbody>
      </table>
    </div>
    <div id="peopleCards" class="md:hidden space-y-3"></div>
    <div class="mt-6">
      <h3 class="font-semibold mb-2">Listas donde participa</h3>
      <ul id="personLists" class="space-y-2"></ul>
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
    async function loadPeople() {
      const people = await api('/people')
      const tb = document.getElementById('peopleRows')
      const cards = document.getElementById('peopleCards')
      tb.innerHTML = ''
      cards.innerHTML = ''
      const palette = ['rose','amber','blue','green','purple','cyan','fuchsia','indigo','lime','teal']
      function optionsHTML(selected) {
        return palette.map(k => `<option value="${k}" ${selected===k?'selected':''}>${k}</option>`).join('')
      }
      people.forEach(p => {
        const tr = document.createElement('tr')
        tr.innerHTML = `
          <td class="p-2">
            <div class="flex items-center gap-2">
              <span data-color-square class="inline-block h-4 w-4 rounded ${colorClass(p.color_key)}"></span>
              <input class="border rounded px-2 py-1 flex-1" value="${p.name}" data-id="${p.id}" data-field="name">
            </div>
          </td>
          <td class="p-2"><input class="border rounded px-2 py-1 w-full" value="${p.contact||''}" data-id="${p.id}" data-field="contact"></td>
          <td class="p-2"><input class="border rounded px-2 py-1 w-full" value="${p.note||''}" data-id="${p.id}" data-field="note"></td>
          <td class="p-2">
            <select class="border rounded px-2 py-1 w-full" data-id="${p.id}" data-field="color_key">${optionsHTML(p.color_key||'')}</select>
          </td>
          <td class="p-2">
            <div class="flex flex-wrap gap-2">
              <button class="px-2 py-1 rounded bg-green-600 text-white" data-action="save" data-id="${p.id}">Guardar</button>
              <button class="px-2 py-1 rounded bg-red-600 text-white" data-action="del" data-id="${p.id}">Eliminar</button>
              <button class="px-2 py-1 rounded bg-gray-300" data-action="lists" data-id="${p.id}">Ver listas</button>
            </div>
          </td>
        `
        tb.appendChild(tr)

        const card = document.createElement('div')
        card.className = 'p-3 bg-white rounded shadow'
        card.innerHTML = `
          <div class="flex items-center gap-2 mb-2">
            <span data-color-square class="inline-block h-4 w-4 rounded ${colorClass(p.color_key)}"></span>
            <input class="border rounded px-2 py-1 flex-1" value="${p.name}" data-id="${p.id}" data-field="name" placeholder="Nombre">
          </div>
          <div class="grid grid-cols-1 gap-2">
            <input class="border rounded px-2 py-1" value="${p.contact||''}" data-id="${p.id}" data-field="contact" placeholder="Contacto">
            <input class="border rounded px-2 py-1" value="${p.note||''}" data-id="${p.id}" data-field="note" placeholder="Nota">
            <select class="border rounded px-2 py-1" data-id="${p.id}" data-field="color_key">${optionsHTML(p.color_key||'')}</select>
            <div class="flex flex-wrap gap-2 mt-1">
              <button class="px-2 py-1 rounded bg-green-600 text-white" data-action="save" data-id="${p.id}">Guardar</button>
              <button class="px-2 py-1 rounded bg-red-600 text-white" data-action="del" data-id="${p.id}">Eliminar</button>
              <button class="px-2 py-1 rounded bg-gray-300" data-action="lists" data-id="${p.id}">Ver listas</button>
            </div>
          </div>
        `
        cards.appendChild(card)
      })
      function bindColor(container) {
        container.querySelectorAll('select[data-field="color_key"]').forEach(sel => {
          sel.addEventListener('change', () => {
            const wrap = sel.closest(container===tb ? 'tr' : 'div')
            const square = wrap && wrap.querySelector('[data-color-square]')
            if (square) square.className = `inline-block h-4 w-4 rounded ${colorClass(sel.value)}`
          })
        })
      }
      bindColor(tb)
      bindColor(cards)
      function colorClass(key) {
        const map = {
          rose: 'bg-rose-500', amber: 'bg-amber-500', blue: 'bg-blue-500', green: 'bg-green-500',
          purple: 'bg-purple-500', cyan: 'bg-cyan-500', fuchsia: 'bg-fuchsia-500', indigo: 'bg-indigo-500',
          lime: 'bg-lime-500', teal: 'bg-teal-500'
        }
        return map[key] || 'bg-gray-400'
      }
      function collectPayload(container, id) {
        const payload = {}
        container.querySelectorAll(`[data-id="${id}"]`).forEach(el => payload[el.dataset.field]=el.value)
        return payload
      }
      tb.querySelectorAll('button').forEach(btn => {
        btn.addEventListener('click', async () => {
          const id = btn.dataset.id
          const act = btn.dataset.action
          if (act==='save') {
            const payload = collectPayload(tb, id)
            await api(`/people/${id}`, { method:'PUT', body: JSON.stringify(payload) })
            loadPeople()
            document.getElementById('peopleMsg').textContent = 'Cambios guardados'
          } else if (act==='del') {
            const ok = await confirmModal('Eliminar persona', '¿Eliminar persona?')
            if (!ok) return
            await api(`/people/${id}`, { method:'DELETE' })
            loadPeople()
            document.getElementById('peopleMsg').textContent = 'Persona eliminada'
          } else if (act==='lists') {
            const lists = await api(`/people/${id}/lists`)
            const ul = document.getElementById('personLists')
            ul.innerHTML = ''
            if (lists.length === 0) {
              const li = document.createElement('li')
              li.className = 'text-gray-500'
              li.textContent = 'No participa en ninguna lista'
              ul.appendChild(li)
            } else {
              lists.forEach(l => {
                const li = document.createElement('li')
                li.innerHTML = `<a class="text-blue-700" href="{{ url('/lists') }}/${l.id}">${l.title}</a>`
                ul.appendChild(li)
              })
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
            await api(`/people/${id}`, { method:'PUT', body: JSON.stringify(payload) })
            loadPeople()
            document.getElementById('peopleMsg').textContent = 'Cambios guardados'
          } else if (act==='del') {
            const ok = await confirmModal('Eliminar persona', '¿Eliminar persona?')
            if (!ok) return
            await api(`/people/${id}`, { method:'DELETE' })
            loadPeople()
            document.getElementById('peopleMsg').textContent = 'Persona eliminada'
          } else if (act==='lists') {
            const lists = await api(`/people/${id}/lists`)
            const ul = document.getElementById('personLists')
            ul.innerHTML = ''
            if (lists.length === 0) {
              const li = document.createElement('li')
              li.className = 'text-gray-500'
              li.textContent = 'No participa en ninguna lista'
              ul.appendChild(li)
            } else {
              lists.forEach(l => {
                const li = document.createElement('li')
                li.innerHTML = `<a class="text-blue-700" href="{{ url('/lists') }}/${l.id}">${l.title}</a>`
                ul.appendChild(li)
              })
            }
          }
        })
      })
    }
    loadPeople()
    document.getElementById('newPerson').addEventListener('submit', async (e) => {
      e.preventDefault()
      const fd = new FormData(e.target)
      const payload = Object.fromEntries(fd.entries())
      await api('/people', { method:'POST', body: JSON.stringify(payload) })
      e.target.reset()
      loadPeople()
    })
    })
  </script>
</x-layout>
