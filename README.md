# 🎁 Reyes App Store - ZimaOS/CasaOS

Tienda de aplicaciones personalizada para ZimaOS y CasaOS.

## 📦 Apps Disponibles

### Reyes - Gift List Manager
Sistema de gestión de listas de regalos para familias y eventos.

**Características:**
- ✅ Instalación completamente automatizada
- ✅ Base de datos SQLite incluida (sin dependencias externas)
- ✅ Primer usuario es administrador automático
- 🔧 Puerto web: 9050
- 💾 Datos persistentes en volúmenes Docker

**Docker Hub:** [zynerio/reyesapp](https://hub.docker.com/r/zynerio/reyesapp)

---

## 🚀 Cómo usar esta tienda en ZimaOS/CasaOS

### Opción 1: Importar App Individual

1. Abre ZimaOS/CasaOS App Store
2. Click en **"Custom Install"** o **"+"**
3. Copia y pega el contenido de `Apps/Reyes/appdata.json`
4. Click **"Install"**

### Opción 2: Agregar Tienda Completa (próximamente)

```
URL: https://github.com/zynerio/Reyes_app
```

---

## 📋 Instalación Manual con Docker

```bash
docker run -d --name reyesapp -p 9050:80 \
  -v reyesapp-storage:/var/www/html/storage \
  -v reyesapp-database:/var/www/html/database \
  zynerio/reyesapp:latest
```

Accede a: `http://localhost:9050`

---

## 🛠️ Estructura del Repositorio

```
.
├── Apps/
│   └── Reyes/
│       ├── appdata.json       # Manifiesto para ZimaOS/CasaOS
│       ├── docker-compose.yml # Configuración Docker Compose
│       └── icon.png           # Icono de la aplicación
├── img/                       # Recursos gráficos
└── README.md                  # Este archivo
```

---

## 📝 Licencia

MIT License - Libre uso y modificación

---

## 👨‍💻 Autor

**Zynerio**
- Docker Hub: [zynerio](https://hub.docker.com/u/zynerio)
- GitHub: [zynerio](https://github.com/zynerio)
