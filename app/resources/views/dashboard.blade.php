<x-layout>
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold">Tus listas</h2>
        <form id="newList" class="flex gap-2">
            <input class="border rounded px-2 py-1" name="title" placeholder="Nueva lista" required>
            <button class="px-3 py-1 rounded bg-blue-600 text-white">Crear</button>
        </form>
    </div>
    <ul class="space-y-2">
        @forelse($lists as $l)
            <li class="p-3 bg-white rounded shadow flex items-center justify-between">
                <a href="{{ url('/lists/'.$l->id) }}" class="font-medium">{{ $l->title }}</a>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-500">{{ $l->finalized_at ? 'Finalizada' : 'Activa' }}</span>
                    <button class="px-2 py-1 rounded bg-red-600 text-white" data-id="{{ $l->id }}">Eliminar</button>
                </div>
            </li>
        @empty
            <li class="text-gray-500">Aún no tienes listas</li>
        @endforelse
    </ul>
    <script>
        document.getElementById('newList').addEventListener('submit', async (e) => {
            e.preventDefault()
            const title = e.target.title.value
            const res = await api('/lists', { method: 'POST', body: JSON.stringify({ title }) })
            location.reload()
        })
        document.querySelectorAll('button[data-id]').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.dataset.id
                const ok = await confirmModal('Eliminar lista', '¿Eliminar lista?')
                if (!ok) return
                await api(`/lists/${id}`, { method:'DELETE' })
                location.reload()
            })
        })
    </script>
</x-layout>
