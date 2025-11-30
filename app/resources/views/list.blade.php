<x-layout>
    <div class="mb-4">
        <h2 class="text-xl font-semibold">{{ $list->title }}</h2>
        <p class="text-sm text-gray-600">{{ $list->description }}</p>
        <div class="mt-2 flex gap-2">
            @if(!$list->finalized_at)
                <button id="finalize" class="px-3 py-1 rounded bg-amber-600 text-white">Finalizar</button>
            @else
                <span class="px-3 py-1 rounded bg-gray-300">Finalizada</span>
            @endif
            <a href="{{ url('/') }}" class="px-3 py-1 rounded text-white" style="background-color:#8e44ad">Volver</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <section>
            <div class="flex items-center justify-between mb-2">
                <h3 class="font-semibold">Regalos</h3>
                <div class="flex items-center gap-4">
                    <label class="inline-flex items-center gap-2"><input type="checkbox" id="usePeopleSwitch" {{ $list->uses_people ? 'checked' : '' }}> Usar personas</label>
                    <label class="inline-flex items-center gap-2"><input type="checkbox" id="showSublistSwitch" {{ $list->show_sublist_in_main ? 'checked' : '' }}> Mostrar sublistas en general</label>
                </div>
            </div>
            <form id="newSimpleGift" class="grid grid-cols-2 md:grid-cols-6 gap-2 mb-3">
                <input class="border rounded px-2 py-1 md:col-span-2" name="name" placeholder="Nombre" required>
                <input class="border rounded px-2 py-1 w-full md:col-span-2" name="price" type="number" step="0.01" min="0" placeholder="Precio" required>
                <input class="border rounded px-2 py-1 w-full md:col-span-2" name="link_url" placeholder="Enlace (opcional)">
                <label class="inline-flex items-center gap-1 text-sm"><input type="checkbox" name="is_ordered"> Pedido</label>
                <label class="inline-flex items-center gap-1 text-sm"><input type="checkbox" name="is_received"> Recibido</label>
                <button class="px-3 py-1 rounded bg-green-600 text-white">Añadir</button>
            </form>
            <ul class="space-y-2" id="simpleGiftsList"></ul>
            <div class="mt-2 text-right"><span class="font-semibold">Total:</span> <span id="simpleTotal">0.00 €</span></div>
            <div class="mt-4">
                <label class="block text-sm font-semibold mb-1">Notas de la lista</label>
                <textarea id="listNotes" rows="4" class="border rounded px-3 py-2 w-full" placeholder="Escribe tus notas...">{{ $list->notes }}</textarea>
                <div class="flex items-center justify-between mt-2">
                    <span id="notesStatus" class="text-sm text-gray-600"></span>
                    <button id="saveNotes" class="px-3 py-1 rounded bg-blue-600 text-white">Guardar notas</button>
                </div>
            </div>
        </section>
        <section id="peopleSection">
            <h3 class="font-semibold mb-2">Personas</h3>
            <form id="attachPerson" class="flex gap-2 mb-3">
                <select class="border rounded px-3 py-2 w-full md:w-64" id="peopleSelect"></select>
                <button class="px-3 py-1 rounded bg-blue-600 text-white">Añadir a lista</button>
            </form>
            <ul class="space-y-2" id="participantsList">
                @foreach($list->participants as $p)
                    <li class="p-2 bg-white rounded shadow flex items-center justify-between">
                        <button class="text-left" data-select-participant="{{ $p->id }}">{{ $p->name }}</button>
                        <button class="px-2 py-1 rounded bg-gray-300" data-person-id="{{ $p->person_id ?? '' }}" data-participant-id="{{ $p->id }}">Quitar</button>
                    </li>
                @endforeach
            </ul>
            <p class="text-sm mt-2">Gestiona tus personas en <a class="text-blue-700" href="{{ url('/people') }}">Personas</a></p>
            <div id="sublistWrap" class="mt-4 hidden">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-semibold" id="sublistTitle">Sublista</h3>
                    <button id="emptySublist" class="px-2 py-1 rounded bg-red-600 text-white">Vaciar sublista</button>
                </div>
                <form id="newPersonGift" class="grid grid-cols-2 md:grid-cols-6 gap-2 mb-3">
                    <input class="border rounded px-2 py-1 md:col-span-2" name="name" placeholder="Nombre" required>
                    <input class="border rounded px-2 py-1 w-full md:col-span-2" name="price" type="number" step="0.01" min="0" placeholder="Precio" required>
                    <input class="border rounded px-2 py-1 w-full md:col-span-2" name="link_url" placeholder="Enlace (opcional)">
                    <label class="inline-flex items-center gap-1 text-sm"><input type="checkbox" name="is_ordered"> Pedido</label>
                    <label class="inline-flex items-center gap-1 text-sm"><input type="checkbox" name="is_received"> Recibido</label>
                    <button class="px-3 py-1 rounded bg-green-600 text-white">Añadir</button>
                </form>
                <ul class="space-y-2" id="personGiftsList"></ul>
                <div class="mt-2 text-right" id="personTotalWrap"><span class="font-semibold">Total persona:</span> <span id="personTotal">0.00 €</span></div>
            </div>
        </section>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
        const listId = {{ $list->id }}
        const DEFAULT_GIFT_IMG = '{{ asset('img/Gift.png') }}'
        let selectedParticipant = null
        function togglePeopleUI(show) {
            const peopleSec = document.getElementById('peopleSection')
            peopleSec.style.display = show ? '' : 'none'
            document.getElementById('sublistWrap').classList.toggle('hidden', !selectedParticipant)
        }
        async function loadPeople() {
            let people = []
            try {
                people = await api('/people')
            } catch (e) {
                console.warn('No autenticado para /people:', e.message)
                return
            }
            const sel = document.getElementById('peopleSelect')
            sel.innerHTML = ''
            const byName = {}
            people.forEach(p => {
                const opt = document.createElement('option')
                opt.value = p.id
                opt.textContent = p.name
                sel.appendChild(opt)
                byName[p.name] = p.color_key
            })
            window.peopleColorsByName = byName
        }
        loadPeople()
        const participantsMap = {}
        const participantsColorKey = {}
        @foreach($list->participants as $p)
            participantsMap[{{ $p->id }}] = '{{ addslashes($p->name) }}'
            participantsColorKey[{{ $p->id }}] = '{{ $p->person?->color_key }}'
        @endforeach
        window.participantsMap = participantsMap
        window.participantsColorKey = participantsColorKey
        const badgePalette = [
            ['bg-rose-200','text-rose-800'],
            ['bg-amber-200','text-amber-800'],
            ['bg-blue-200','text-blue-800'],
            ['bg-green-200','text-green-800'],
            ['bg-purple-200','text-purple-800'],
            ['bg-cyan-200','text-cyan-800'],
            ['bg-fuchsia-200','text-fuchsia-800'],
            ['bg-indigo-200','text-indigo-800'],
            ['bg-lime-200','text-lime-800'],
            ['bg-teal-200','text-teal-800'],
        ]
        function badgeClass(id) {
            const key = window.participantsColorKey[id]
            const map = {
                rose: ['bg-rose-200','text-rose-800'],
                amber: ['bg-amber-200','text-amber-800'],
                blue: ['bg-blue-200','text-blue-800'],
                green: ['bg-green-200','text-green-800'],
                purple: ['bg-purple-200','text-purple-800'],
                cyan: ['bg-cyan-200','text-cyan-800'],
                fuchsia: ['bg-fuchsia-200','text-fuchsia-800'],
                indigo: ['bg-indigo-200','text-indigo-800'],
                lime: ['bg-lime-200','text-lime-800'],
                teal: ['bg-teal-200','text-teal-800'],
            }
            if (key && map[key]) {
                return map[key].join(' ')
            }
            const name = window.participantsMap && window.participantsMap[id] ? window.participantsMap[id] : ''
            const keyByName = window.peopleColorsByName && window.peopleColorsByName[name]
            if (keyByName && map[keyByName]) {
                return map[keyByName].join(' ')
            }
            let hash = 0
            for (let i = 0; i < name.length; i++) {
                hash = ((hash << 5) - hash) + name.charCodeAt(i)
                hash |= 0
            }
            const idx = Math.abs(hash) % badgePalette.length
            return badgePalette[idx].join(' ')
        }
        document.getElementById('usePeopleSwitch').addEventListener('change', async (e) => {
            const uses_people = e.target.checked
            await api(`/lists/${listId}`, { method:'PUT', body: JSON.stringify({ uses_people }) })
            togglePeopleUI(uses_people)
        })
        const showSwitch = document.getElementById('showSublistSwitch')
        if (showSwitch) {
            showSwitch.addEventListener('change', async (e) => {
                const show_sublist_in_main = e.target.checked
                await api(`/lists/${listId}`, { method:'PUT', body: JSON.stringify({ show_sublist_in_main }) })
                loadSimpleGifts()
            })
        }
        togglePeopleUI({{ $list->uses_people ? 'true' : 'false' }})
        let notesSaveTimer = null
        let notesSaving = false
        const notesStatus = document.getElementById('notesStatus')
        const notesEl = document.getElementById('listNotes')
        function setNotesStatus(state) {
            if (!notesStatus) return
            if (!state) { notesStatus.innerHTML = ''; return }
            if (state === 'saving') {
                notesStatus.innerHTML = '<span class="inline-flex items-center gap-1 text-gray-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-opacity="0.25" stroke-width="4"/><path d="M22 12a10 10 0 00-10-10" stroke-width="4" stroke-linecap="round"/></svg>Guardando...</span>'
                return
            }
            if (state === 'saved') {
                notesStatus.innerHTML = '<span class="inline-flex items-center gap-1 text-green-700"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-7.5 7.5a1 1 0 01-1.414 0l-3-3a1 1 0 111.414-1.414L8.5 12.086l6.793-6.793a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Guardado</span>'
                return
            }
            if (state === 'error') {
                notesStatus.innerHTML = '<span class="inline-flex items-center gap-1 text-red-700"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10A8 8 0 11.001 10 8 8 0 0118 10zm-9 4a1 1 0 100-2 1 1 0 000 2zM9 6a1 1 0 011 1v3a1 1 0 11-2 0V7a1 1 0 011-1z" clip-rule="evenodd"/></svg>Error al guardar</span><button id="notesRetry" class="ml-2 px-2 py-0.5 rounded bg-red-100 text-red-700">Reintentar</button>'
                return
            }
            notesStatus.textContent = state
        }
        async function performNotesSave() {
            if (notesSaving) return
            notesSaving = true
            setNotesStatus('saving')
            const notes = notesEl ? notesEl.value : ''
            try {
                await api(`/lists/${listId}`, { method:'PUT', body: JSON.stringify({ notes }) })
                notesSaving = false
                setNotesStatus('saved')
                setTimeout(() => setNotesStatus(''), 2000)
            } catch (e) {
                notesSaving = false
                setNotesStatus('error')
                if (window.notify) window.notify(e && e.message ? e.message : 'Error al guardar notas', 'error')
            }
        }
        function scheduleNotesSave(immediate = false) {
            if (immediate) {
                if (notesSaveTimer) { clearTimeout(notesSaveTimer); notesSaveTimer = null }
                performNotesSave()
                return
            }
            if (notesSaveTimer) clearTimeout(notesSaveTimer)
            notesSaveTimer = setTimeout(() => { notesSaveTimer = null; performNotesSave() }, 600)
        }
        const saveNotesBtn = document.getElementById('saveNotes')
        if (saveNotesBtn) saveNotesBtn.addEventListener('click', () => scheduleNotesSave(true))
        if (notesEl) notesEl.addEventListener('blur', () => scheduleNotesSave(false))
        const notesStatusEl = document.getElementById('notesStatus')
        if (notesStatusEl) notesStatusEl.addEventListener('click', (e) => {
            if (e.target && e.target.id === 'notesRetry') {
                e.target.disabled = true
                e.target.innerHTML = '<span class="inline-flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-opacity="0.25" stroke-width="4"/><path d="M22 12a10 10 0 00-10-10" stroke-width="4" stroke-linecap="round"/></svg>Reintentando...</span>'
                scheduleNotesSave(true)
            }
        })
        document.getElementById('attachPerson').addEventListener('submit', async (e) => {
            e.preventDefault()
            const person_id = document.getElementById('peopleSelect').value
            await api(`/lists/${listId}/people`, { method:'POST', body: JSON.stringify({ person_id }) })
            location.reload()
        })
        document.querySelectorAll('#participantsList button').forEach(btn => {
            btn.addEventListener('click', async () => {
                const personId = btn.dataset.personId
                if (!personId) return
                await api(`/lists/${listId}/people/${personId}`, { method:'DELETE' })
                location.reload()
            })
        })
        document.querySelectorAll('[data-select-participant]').forEach(btn => {
            btn.addEventListener('click', async () => {
                const clickedId = btn.dataset.selectParticipant
                const wrap = document.getElementById('sublistWrap')
                const isSame = selectedParticipant === clickedId
                document.querySelectorAll('#participantsList li').forEach(li => li.classList.remove('ring-2','ring-blue-400'))
                if (isSame) {
                    selectedParticipant = null
                    wrap.classList.add('hidden')
                    return
                }
                selectedParticipant = clickedId
                document.getElementById('sublistTitle').textContent = 'Sublista de ' + btn.textContent
                btn.closest('li').classList.add('ring-2','ring-blue-400')
                await loadPersonGifts()
                togglePeopleUI(true)
            })
        })
        
        async function loadSimpleGifts() {
            const onlyMain = document.getElementById('showSublistSwitch') && !document.getElementById('showSublistSwitch').checked
            const allItems = await api(`/lists/${listId}/simple-gifts`)
            const items = onlyMain ? allItems.filter(it => !it.participant_id) : allItems
            const ul = document.getElementById('simpleGiftsList')
            ul.innerHTML = ''
            let total = 0
            allItems.forEach(it => {
                total += parseFloat(it.price)
                const li = document.createElement('li')
                li.className = 'p-3 bg-white rounded shadow relative'
                const personBadge = (window.participantsMap && it.participant_id && window.participantsMap[it.participant_id])
                  ? `<div class="absolute top-2 right-2"><span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs ${badgeClass(it.participant_id)}">${window.participantsMap[it.participant_id]}</span></div>`
                  : ''
                li.innerHTML = `
                  <div class="grid grid-cols-1 md:grid-cols-2 items-start gap-3">
                    ${personBadge}
                    <div class="md:col-span-1">
                      <img src="${it.image_path ? '{{ asset('storage') }}/'+it.image_path : DEFAULT_GIFT_IMG}" data-gift-id="${it.id}" class="w-full max-w-[500px] h-auto max-h-[500px] md:w-[200px] md:h-[200px] md:max-w-none md:max-h-none rounded object-contain">
                      <input type="file" data-gift-file="${it.id}" class="hidden" accept="image/png,image/jpeg">
                    </div>
                    <div class="md:col-span-1 flex flex-col gap-2 md:mt-6">
                      <input class="border rounded px-2 py-1" value="${it.name}" data-id="${it.id}" data-field="name">
                      <input class="border rounded px-2 py-1 w-full" type="number" step="0.01" min="0" value="${it.price}" data-id="${it.id}" data-field="price">
                      <input class="border rounded px-2 py-1 w-full" placeholder="Enlace (opcional)" value="${it.link_url||''}" data-id="${it.id}" data-field="link_url">
                      <div class="flex items-center gap-8">
                        <label class="inline-flex items-center gap-1 text-sm"><input type="checkbox" ${it.is_ordered?'checked':''} data-id="${it.id}" data-field="is_ordered" class="toggleSimple"> Pedido</label>
                        <label class="inline-flex items-center gap-1 text-sm"><input type="checkbox" ${it.is_received?'checked':''} data-id="${it.id}" data-field="is_received" class="toggleSimple"> Recibido</label>
                      </div>
                    </div>
                    <div class="mt-3 pt-2 border-t md:col-span-2 flex flex-wrap gap-2 justify-end">
                      <button class="px-2 py-1 rounded bg-blue-600 text-white" data-action="save" data-id="${it.id}">Guardar</button>
                      <button class="px-2 py-1 rounded bg-red-600 text-white" data-action="del" data-id="${it.id}">Eliminar</button>
                      ${it.link_url ? `<a class="inline-block no-underline px-2 py-1 rounded text-white" target="_blank" href="${it.link_url}" style="background-color:#8e44ad">Enlace</a>` : ''}
                      <button class="px-2 py-1 rounded bg-gray-700 text-white" data-change-image="${it.id}">Cambiar imagen</button>
                    </div>
                  </div>
                `
                if (!onlyMain || (onlyMain && !it.participant_id)) {
                    ul.appendChild(li)
                }
            })
            document.getElementById('simpleTotal').textContent = `${total.toFixed(2)} €`
            ul.querySelectorAll('button[data-change-image]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.dataset.changeImage
                    const inp = ul.querySelector(`input[type="file"][data-gift-file="${id}"]`)
                    if (inp) inp.click()
                })
            })
            ul.querySelectorAll('input[type="file"][data-gift-file]').forEach(inp => {
                inp.addEventListener('change', async () => {
                    const id = inp.dataset.giftFile
                    const f = inp.files[0]
                    if (!f) return
                    const fd = new FormData()
                    fd.append('image', f)
                    const res = await fetch(`${API_BASE}/simple-gifts/${id}/image`, { method:'POST', body: fd, credentials:'same-origin' })
                    if (res.ok) {
                        const it = await res.json()
                        const img = ul.querySelector(`img[data-gift-id="${id}"]`)
                        img.src = '{{ asset('storage') }}/'+it.image_path
                        if (window.notify) window.notify('Imagen actualizada', 'success')
                    }
                })
            })
            ul.querySelectorAll('button').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const id = btn.dataset.id
                    const act = btn.dataset.action
                    if (act==='save') {
                        const payload = {}
                        ul.querySelectorAll(`[data-id="${id}"]`).forEach(el => {
                            let val = el.type==='checkbox' ? el.checked : el.value
                            if (el.dataset.field==='price') val = parseFloat(val)
                            payload[el.dataset.field] = val
                        })
                        await api(`/simple-gifts/${id}`, { method:'PUT', body: JSON.stringify(payload) })
                        loadSimpleGifts()
                    } else if (act==='del') {
                        await api(`/simple-gifts/${id}`, { method:'DELETE' })
                        loadSimpleGifts()
                    }
                })
            })
            ul.querySelectorAll('.toggleSimple').forEach(el => {
                el.addEventListener('change', async () => {
                    const id = el.dataset.id
                    const field = el.dataset.field
                    const body = {}
                    body[field] = el.checked
                    await api(`/simple-gifts/${id}`, { method:'PUT', body: JSON.stringify(body) })
                })
            })
        }
        loadSimpleGifts()
        async function loadPersonGifts() {
            if (!selectedParticipant) return
            const items = await api(`/lists/${listId}/simple-gifts?participant=${selectedParticipant}`)
            const ul = document.getElementById('personGiftsList')
            ul.innerHTML = ''
            let total = 0
            items.forEach(it => {
                total += parseFloat(it.price)
                const li = document.createElement('li')
                li.className = 'p-3 bg-white rounded shadow'
                li.innerHTML = `
                  <div class="grid grid-cols-1 md:grid-cols-2 items-start gap-3">
                    <div class="md:col-span-1">
                      <img src="${it.image_path ? '{{ asset('storage') }}/'+it.image_path : DEFAULT_GIFT_IMG}" data-gift-id="${it.id}" class="w-full max-w-[500px] h-auto max-h-[500px] md:w-[200px] md:h-[200px] md:max-w-none md:max-h-none rounded object-contain">
                      <input type="file" data-gift-file="${it.id}" class="hidden" accept="image/png,image/jpeg">
                    </div>
                    <div class="md:col-span-1 flex flex-col gap-2">
                      <input class="border rounded px-2 py-1" value="${it.name}" data-id="${it.id}" data-field="name">
                      <input class="border rounded px-2 py-1 w-full" type="number" step="0.01" min="0" value="${it.price}" data-id="${it.id}" data-field="price">
                      <input class="border rounded px-2 py-1 w-full" placeholder="Enlace (opcional)" value="${it.link_url||''}" data-id="${it.id}" data-field="link_url">
                      <div class="flex items-center gap-8">
                        <label class="inline-flex items-center gap-1 text-sm"><input type="checkbox" ${it.is_ordered?'checked':''} data-id="${it.id}" data-field="is_ordered" class="togglePersonSimple"> Pedido</label>
                        <label class="inline-flex items-center gap-1 text-sm"><input type="checkbox" ${it.is_received?'checked':''} data-id="${it.id}" data-field="is_received" class="togglePersonSimple"> Recibido</label>
                      </div>
                    </div>
                    <div class="mt-3 pt-2 border-t md:col-span-2 flex flex-wrap gap-2 justify-end">
                      <button class="px-2 py-1 rounded bg-blue-600 text-white" data-action="save" data-id="${it.id}">Guardar</button>
                      <button class="px-2 py-1 rounded bg-red-600 text-white" data-action="del" data-id="${it.id}">Eliminar</button>
                      ${it.link_url ? `<a class="inline-block no-underline px-2 py-1 rounded text-white" target="_blank" href="${it.link_url}" style="background-color:#8e44ad">Enlace</a>` : ''}
                      <button class="px-2 py-1 rounded bg-gray-700 text-white" data-change-image="${it.id}">Cambiar imagen</button>
                    </div>
                  </div>
                `
                ul.appendChild(li)
            })
            ul.querySelectorAll('button[data-change-image]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.dataset.changeImage
                    const inp = ul.querySelector(`input[type="file"][data-gift-file="${id}"]`)
                    if (inp) inp.click()
                })
            })
            ul.querySelectorAll('input[type="file"][data-gift-file]').forEach(inp => {
                inp.addEventListener('change', async () => {
                    const id = inp.dataset.giftFile
                    const f = inp.files[0]
                    if (!f) return
                    const fd = new FormData()
                    fd.append('image', f)
                    const res = await fetch(`${API_BASE}/simple-gifts/${id}/image`, { method:'POST', body: fd, credentials:'same-origin' })
                    if (res.ok) {
                        const it = await res.json()
                        const img = ul.querySelector(`img[data-gift-id="${id}"]`)
                        img.src = '{{ asset('storage') }}/'+it.image_path
                        if (window.notify) window.notify('Imagen actualizada', 'success')
                    }
                })
            })
            document.getElementById('personTotal').textContent = `${total.toFixed(2)} €`
            ul.querySelectorAll('button').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const id = btn.dataset.id
                    const act = btn.dataset.action
                    if (act==='save') {
                        const payload = {}
                        ul.querySelectorAll(`[data-id="${id}"]`).forEach(el => {
                            let val = el.type==='checkbox' ? el.checked : el.value
                            if (el.dataset.field==='price') val = parseFloat(val)
                            payload[el.dataset.field] = val
                        })
                        await api(`/simple-gifts/${id}`, { method:'PUT', body: JSON.stringify(payload) })
                        await loadPersonGifts()
                        await loadSimpleGifts()
                    } else if (act==='del') {
                        await api(`/simple-gifts/${id}`, { method:'DELETE' })
                        await loadPersonGifts()
                        await loadSimpleGifts()
                    }
                })
            })
            ul.querySelectorAll('.togglePersonSimple').forEach(el => {
                el.addEventListener('change', async () => {
                    const id = el.dataset.id
                    const field = el.dataset.field
                    const body = {}
                    body[field] = el.checked
                    await api(`/simple-gifts/${id}`, { method:'PUT', body: JSON.stringify(body) })
                })
            })
        }
        document.getElementById('newPersonGift').addEventListener('submit', async (e) => {
            e.preventDefault()
            if (!selectedParticipant) return
            const fd = new FormData(e.target)
            const payload = Object.fromEntries(fd.entries())
            payload.price = parseFloat(payload.price)
            payload.is_ordered = !!payload.is_ordered
            payload.is_received = !!payload.is_received
            payload.participant_id = selectedParticipant
            await api(`/lists/${listId}/simple-gifts`, { method: 'POST', body: JSON.stringify(payload) })
            e.target.reset()
            await loadPersonGifts()
            await loadSimpleGifts()
        })
        document.getElementById('emptySublist').addEventListener('click', async () => {
            if (!selectedParticipant) return
            const ok = await confirmModal('Vaciar sublista', '¿Vaciar sublista de esta persona?')
            if (!ok) return
            await api(`/lists/${listId}/simple-gifts/by-participant/${selectedParticipant}`, { method:'DELETE' })
            await loadPersonGifts()
            await loadSimpleGifts()
        })
        document.getElementById('newSimpleGift').addEventListener('submit', async (e) => {
            e.preventDefault()
            const fd = new FormData(e.target)
            const payload = Object.fromEntries(fd.entries())
            payload.price = parseFloat(payload.price)
            payload.is_ordered = !!payload.is_ordered
            payload.is_received = !!payload.is_received
            await api(`/lists/${listId}/simple-gifts`, { method: 'POST', body: JSON.stringify(payload) })
            e.target.reset()
            loadSimpleGifts()
        })
        const finalizeBtn = document.getElementById('finalize')
        if (finalizeBtn) finalizeBtn.addEventListener('click', async () => {
            await api(`/lists/${listId}/finalize`, { method: 'POST' })
            location.reload()
        })
        })
</script>
</x-layout>
