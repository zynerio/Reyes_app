<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reyes</title>
    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('img/kings_M.png') }}">
    <link rel="apple-touch-icon" sizes="100x100" href="{{ asset('img/kings_G.png') }}">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="color-scheme" content="light">
    <style>
      :root { color-scheme: light !important; }
      html { background-color: #ffffff; }
      body { background-color: #f3f4f6; color: #111827; }
      @media (prefers-color-scheme: dark) { :root { color-scheme: light !important; } }
      [x-cloak] { display: none !important; }
    </style>

        <script>
            const APP_BASE = '{{ rtrim(url('/'), '/') }}'
            const API_BASE = APP_BASE + '/api'
            async function api(path, options = {}) {
            const res = await fetch(`${API_BASE}${path}`, {
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                credentials: 'same-origin',
                ...options,
            })
            let data = null
            try { data = await res.json() } catch { data = null }
            if (!res.ok) {
                const msg = (data && (data.message || JSON.stringify(data))) || 'Error'
                throw new Error(msg)
            }
            return data
        }
        window.api = api
        window.APP_BASE = APP_BASE
        window.DEFAULT_GIFT_IMG = '{{ asset('img/Gift.png') }}'
        </script>
</head>
@php($isAuth = request()->is('login') || request()->is('register') || request()->is('password/*'))
<body class="min-h-screen text-gray-900" style="{{ $isAuth ? 'background-image:url('.asset('img/wall_A.jpg').');background-size:cover;background-position:center' : '' }}">
    @if($isAuth)
    <div class="fixed inset-0 bg-black/35"></div>
    @endif
    <div class="max-w-5xl mx-auto p-4 relative">
        <div x-data="{show:false,text:'',type:'success',modal:false,modalTitle:'',modalText:'',modalImgSrc:'',modalImageGiftId:null,modalLinkUrl:'',confirmShow:false,confirmTitle:'',confirmText:'',okLabel:'Aceptar',cancelLabel:'Cancelar',confirmResolve:null}"
             x-init="window.notify=(t,k='success')=>{if(!t)return;text=t;type=k;show=true;setTimeout(()=>show=false,3000)};window.showModal=(title,txt='',img=null,giftId=null,link=null)=>{modalTitle=title;modalText=txt;modalImgSrc=img||window.DEFAULT_GIFT_IMG;modalImageGiftId=giftId;modalLinkUrl=link||'';modal=true};window.confirmModal=(title,txt,opts={})=>new Promise((resolve)=>{confirmTitle=title;confirmText=txt;okLabel=opts.ok||'Aceptar';cancelLabel=opts.cancel||'Cancelar';confirmResolve=resolve;confirmShow=true});window.changeGiftImage=(id)=>{const inp=document.querySelector('[data-gift-file="'+id+'"]');if(inp){inp.click()}}">
            <div x-show="show" x-cloak x-transition class="fixed top-4 left-1/2 -translate-x-1/2 px-4 py-2 rounded shadow z-50" :class="type==='success'?'bg-green-600 text-white':'bg-red-600 text-white'"><span x-text="text"></span></div>
            <div x-show="modal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="absolute inset-0 bg-black/40" @click="modal=false"></div>
                <div class="bg-white rounded shadow p-4 z-10 w-96">
                    <h3 class="text-lg font-semibold mb-2" x-text="modalTitle"></h3>
                    <div>
                        <img :src="modalImgSrc || window.DEFAULT_GIFT_IMG" alt="Imagen" class="mb-4 rounded w-full">
                        <p class="mb-2" x-text="modalText"></p>
                        <div class="flex justify-between items-center mb-2">
                            <button class="px-3 py-1 rounded bg-gray-200" @click="window.changeGiftImage(modalImageGiftId)">Cambiar imagen</button>
                            <template x-if="modalLinkUrl">
                                <a :href="modalLinkUrl" target="_blank" class="px-3 py-1 rounded text-white" style="background-color:#8e44ad">Abrir enlace</a>
                            </template>
                        </div>
                    </div>
                    <div class="text-right"><button class="px-3 py-1 rounded bg-blue-600 text-white" @click="modal=false">Cerrar</button></div>
                </div>
            </div>
            <div x-show="confirmShow" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
                <div class="absolute inset-0 bg-black/40" @click="confirmShow=false;confirmResolve(false)"></div>
                <div class="bg-white rounded shadow p-4 z-10 w-96">
                    <h3 class="text-lg font-semibold mb-2" x-text="confirmTitle"></h3>
                    <p class="mb-4" x-text="confirmText"></p>
                    <div class="flex justify-end gap-2">
                        <button class="px-3 py-1 rounded bg-gray-300" @click="confirmShow=false;confirmResolve(false)" x-text="cancelLabel"></button>
                        <button class="px-3 py-1 rounded bg-red-600 text-white" @click="confirmShow=false;confirmResolve(true)" x-text="okLabel"></button>
                    </div>
                </div>
            </div>
        </div>
        @php($isAuth = request()->is('login') || request()->is('register'))
        @unless($isAuth)
        <header class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <img src="{{ asset('img/kings_M.png') }}" alt="Reyes" class="h-8 w-8">
                <h1 class="text-2xl font-bold">Reyes</h1>
                <a href="{{ url('/') }}" class="text-sm text-gray-700 pt-1">Listas</a>
                <a href="{{ url('/people') }}" class="text-sm text-gray-700 pt-1">Personas</a>
                @if(auth()->check() && auth()->user()->role === 'admin')
                  <a href="{{ url('/admin/users') }}" class="text-sm text-gray-700 pt-1">Usuarios</a>
                @endif
            </div>
            <div class="flex items-center gap-3">
              @php($u = auth()->user())
              <a href="{{ url('/profile') }}" class="flex items-center gap-2">
                @if($u && $u->avatar_path)
                  <img src="{{ asset('storage/'.$u->avatar_path) }}" alt="Avatar" class="h-8 w-8 rounded-full object-cover">
                @elseif($u)
                  @php($parts = explode(' ', $u->name))
                  @php($initials = strtoupper(substr($parts[0] ?? '',0,1).substr($parts[1] ?? '',0,1)))
                  <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center text-sm font-bold">{{ $initials }}</div>
                @else
                  <div class="h-8 w-8 rounded-full bg-gray-200"></div>
                @endif
                
              </a>
              <button id="logoutBtn" class="px-3 py-1 rounded bg-gray-200">Salir</button>
            </div>
        </header>
        @endunless
        {{ $slot }}
    </div>
    <script>
        const logoutBtn = document.getElementById('logoutBtn')
        if (logoutBtn) {
            logoutBtn.addEventListener('click', async () => {
                try { await api('/auth/logout', { method:'POST' }) } catch {}
                location.href = '{{ url('/login') }}'
            })
        }
    </script>
</body>
</html>
