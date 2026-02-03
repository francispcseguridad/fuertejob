# API de Búsqueda de Localidades

## Ruta
```
GET /api/localidades/search
```

## Descripción
Esta ruta permite buscar ciudades en las Islas Canarias para funcionalidad de autocompletado. Busca coincidencias en ciudad, isla y provincia.

## Parámetros

| Parámetro | Tipo   | Requerido | Descripción                           |
|-----------|--------|-----------|---------------------------------------|
| q         | string | Sí        | Término de búsqueda (mínimo 2 caracteres) |
| provider  | string | No        | `auto` (default), `local` o `locationiq` |
| limit     | int    | No        | Límite de resultados (1–20, default 10) |

## Respuesta

La API devuelve un array JSON con hasta 10 resultados ordenados por nombre de ciudad.

### Estructura de Respuesta
```json
[
  {
    "city": "Las Palmas de Gran Canaria",
    "island": "Gran Canaria",
    "province": "Las Palmas",
    "country": "España"
  },
  {
    "city": "Santa Cruz de Tenerife",
    "island": "Tenerife",
    "province": "Santa Cruz de Tenerife",
    "country": "España"
  }
]
```

## Ejemplos de Uso

### 1. Con JavaScript Fetch
```javascript
async function buscarCiudad(termino) {
  try {
    const response = await fetch(`/api/localidades/search?q=${encodeURIComponent(termino)}`);
    const localidades = await response.json();
    console.log(localidades);
    return localidades;
  } catch (error) {
    console.error('Error al buscar localidades:', error);
    return [];
  }
}

// Uso
buscarCiudad('Las Palmas');
```

### 2. Con jQuery
```javascript
$.ajax({
  url: '/api/localidades/search',
  method: 'GET',
  data: { q: 'Santa Cruz' },
  success: function(data) {
    console.log('Resultados:', data);
  },
  error: function(error) {
    console.error('Error:', error);
  }
});
```

### 3. Con Axios
```javascript
axios.get('/api/localidades/search', {
  params: { q: 'Tenerife' }
})
.then(response => {
  console.log('Localidades encontradas:', response.data);
})
.catch(error => {
  console.error('Error:', error);
});
```

### 4. Ejemplo de Autocompletado con HTML/JavaScript Vanilla
```html
<input type="text" id="ciudad" placeholder="Buscar ciudad..." />
<ul id="resultados"></ul>

<script>
const inputCiudad = document.getElementById('ciudad');
const listaResultados = document.getElementById('resultados');

let timeoutId;

inputCiudad.addEventListener('input', function() {
  clearTimeout(timeoutId);
  
  const query = this.value;
  
  if (query.length < 2) {
    listaResultados.innerHTML = '';
    return;
  }
  
  timeoutId = setTimeout(async () => {
    try {
      const response = await fetch(`/api/localidades/search?q=${encodeURIComponent(query)}`);
      const localidades = await response.json();
      
      listaResultados.innerHTML = '';
      
      localidades.forEach(loc => {
        const li = document.createElement('li');
        li.textContent = `${loc.city}, ${loc.island}`;
        li.style.cursor = 'pointer';
        li.onclick = () => {
          inputCiudad.value = loc.city;
          listaResultados.innerHTML = '';
        };
        listaResultados.appendChild(li);
      });
    } catch (error) {
      console.error('Error:', error);
    }
  }, 300); // Espera 300ms después de que el usuario deje de escribir
});
</script>
```

### 5. Con cURL (para pruebas)
```bash
curl "http://localhost:8000/api/localidades/search?q=Las+Palmas"
```

## Notas

- La búsqueda requiere al menos 2 caracteres
- Si se envía menos de 2 caracteres, se devuelve un array vacío
- La búsqueda es insensible a mayúsculas y minúsculas
- Se busca en los campos: `city`, `island` y `province`
- Los resultados están limitados por `limit` (default 10)
- La ruta es pública y no requiere autenticación
- Si existe `LOCATIONIQ_API_KEY` en el `.env`, el modo `auto` combina resultados locales + LocationIQ

## Integración Recomendada

Para mejorar la experiencia de usuario:
1. Implementar debouncing (esperar unos milisegundos después del último tecleo)
2. Mostrar un indicador de carga mientras se busca
3. Manejar correctamente los errores de red
4. Limpiar resultados cuando el input esté vacío
