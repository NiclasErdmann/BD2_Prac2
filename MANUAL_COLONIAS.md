# Manual de Usuario - Gestión de Colonias Felinas

## Tabla de Contenidos
1. [Introducción](#introducción)
2. [Acceso a Colonias](#acceso-a-colonias)
3. [Listar Colonias](#listar-colonias)
4. [Crear una Nueva Colonia](#crear-una-nueva-colonia)
5. [Ver Detalles de una Colonia](#ver-detalles-de-una-colonia)
6. [Editar una Colonia](#editar-una-colonia)
7. [Asignar Grupo de Trabajo](#asignar-grupo-de-trabajo)
8. [Permisos y Roles](#permisos-y-roles)

---

## Introducción

El sistema de gestión de colonias felinas permite registrar, organizar y controlar todas las colonias de gatos en tu ayuntamiento. Cada colonia puede tener varios gatos asociados y ser asignada a un grupo de trabajo específico para su cuidado y mantenimiento.

---

## Acceso a Colonias

Para acceder al módulo de colonias:

1. **Inicia sesión** en el sistema con tu usuario y contraseña
2. En el **menú principal**, selecciona la opción **"Ver Colonias"**
3. Se abrirá la página de listado de colonias de tu ayuntamiento

---

## Listar Colonias

### Pantalla Principal de Colonias

La página de listado muestra:

- **Encabezado**: "Mis Colonias (Ayuntamiento de [Nombre])"
- **Tabla con columnas**:
  - **Nombre**: Identificación de la colonia (clickeable para ver detalles)
  - **Ubicación**: Lugar de referencia donde se encuentra la colonia
  - **Grupo de Trabajo**: Grupo responsable de gestionar esta colonia
  - **Número de Gatos**: Cantidad estimada de gatos en la colonia

### Funcionalidades

- **Ver Detalles**: Haz clic en el **nombre de la colonia** para ver toda su información
- **Ver Grupo**: Haz clic en el **nombre del grupo** para ver los detalles del grupo asignado
- **Crear Nueva Colonia**: Si tienes permisos de "Modificar Colonias", aparecerá un botón **"Crear Nueva Colonia"**

### Información Importante

> ⚠️ **Solo se muestran colonias del tu ayuntamiento**. Si tu ayuntamiento no tiene colonias registradas, verás el mensaje: "No hay colonias registradas en tu ayuntamiento"

---

## Crear una Nueva Colonia

### Requisitos
- Debes tener el permiso **"Modificar Colonias"**
- Debes estar registrado en un ayuntamiento

### Pasos para Crear

1. En la página de listado, haz clic en el botón **"Crear Nueva Colonia"**
2. Se abrirá un **formulario con los siguientes campos**:

| Campo | Tipo | Descripción | Obligatorio |
|-------|------|-------------|-----------|
| **Nombre de la Colonia** | Texto | Identificación única de la colonia (ej: "Colonia Centro", "Colonia Parque") | ✅ Sí |
| **Lugar de Referencia** | Texto | Ubicación descriptiva (ej: "Plaza Mayor", "Parque Central") | ❌ No |
| **Coordenadas (GPS)** | Texto | Coordenadas geográficas en formato lat,lon (ej: "40.0735,-88.2535") | ❌ No |
| **Número de Gatos** | Número | Cantidad estimada de gatos en la colonia | ❌ No (default: 0) |
| **Asignar Grupo de Trabajo** | Desplegable | Selecciona el grupo responsable de la colonia | ❌ No |
| **Descripción** | Texto largo | Notas sobre la colonia, zona de actuación, características especiales | ❌ No |

3. **Completa los campos** necesarios (al menos el nombre)
4. Haz clic en **"Guardar Colonia"**
5. **Confirmación**: Se te redirigirá a la página de detalles de la nueva colonia

---

## Ver Detalles de una Colonia

### Acceso
- Desde el listado, haz clic en el **nombre de la colonia**
- O accede directamente si tienes la URL

### Información Mostrada

#### Sección: Datos Generales
- **Lugar de referencia**: Ubicación descriptiva
- **Coordenadas**: Ubicación GPS (si está registrada)
- **Número de gatos**: Cantidad estimada

#### Sección: Descripción
Notas y comentarios sobre la colonia

#### Sección: Grupo de Trabajo Asignado
Muestra el grupo responsable de gestionar la colonia:
- Si **tiene grupo asignado**: Muestra el nombre (clickeable para ver detalles del grupo)
- Si **NO tiene grupo**: Muestra "⚠ Esta colonia no tiene grupo asignado"

#### Sección: Gatos en Esta Colonia
Lista todos los gatos registrados actualmente en la colonia:
- Número XIP (identificación del gato)
- Descripción breve del gato
- Enlace para ver detalles del gato

Si no hay gatos registrados, muestra: "No hay gatos registrados actualmente en esta colonia"

### Acciones Disponibles
Si tienes permiso de **"Modificar Colonias"**, aparecerá un botón:
- **"✏ Editar colonia"**: Para modificar la información

---

## Editar una Colonia

### Requisitos
- Debes tener el permiso **"Modificar Colonias"**

### Pasos para Editar

1. En la página de detalles de la colonia, haz clic en **"✏ Editar colonia"**
2. Se abrirá un **formulario con todos los campos** (igual que en crear):
   - El formulario **precarga los datos actuales** de la colonia
   - Puedes modificar cualquier campo
3. Realiza los cambios necesarios
4. Haz clic en **"Actualizar Colonia"**
5. **Confirmación**: Se guardarán los cambios y serás redirigido a la página de detalles

### Cambios Permitidos
- ✅ Nombre
- ✅ Ubicación
- ✅ Coordenadas GPS
- ✅ Número de gatos
- ✅ Asignación de grupo de trabajo
- ✅ Descripción

---

## Asignar Grupo de Trabajo

### ¿Qué es un Grupo de Trabajo?
Un grupo de trabajo es un equipo de voluntarios responsables de gestionar y cuidar una colonia específica.

### Cómo Asignar Grupo

**Opción 1: Al Crear la Colonia**
1. En el formulario de creación, ve al campo **"Asignar Grupo de Trabajo"**
2. Selecciona un grupo del desplegable
3. Guarda la colonia

**Opción 2: Editando una Colonia Existente**
1. En la página de detalles, haz clic en **"Editar colonia"**
2. Ve al campo **"Asignar Grupo de Trabajo"**
3. Selecciona o cambia el grupo
4. Actualiza la colonia

### Cambiar de Grupo
Si una colonia ya está asignada a un grupo y necesitas cambiarla:
1. Abre la colonia para editar
2. Selecciona un **grupo diferente** en el desplegable
3. Actualiza
4. La colonia se **reasignará automáticamente** al nuevo grupo

### Desasignar Grupo
Para quitar el grupo de una colonia:
1. Abre la colonia para editar
2. Selecciona **"-- Ninguno --"** en el desplegable
3. Actualiza
4. La colonia quedará **sin grupo asignado**

---

## Permisos y Roles

### Permisos Necesarios

| Acción | Permiso Requerido | Rol Típico |
|--------|------------------|-----------|
| Ver listado de colonias | "Ver Colonias" | Responsable de Grupo, Admin |
| Ver detalles de colonia | "Ver Colonias" | Responsable de Grupo, Admin |
| Crear colonia | "Modificar Colonias" | Admin de Ayuntamiento |
| Editar colonia | "Modificar Colonias" | Admin de Ayuntamiento |
| Asignar grupo a colonia | "Modificar Colonias" | Admin de Ayuntamiento |

### Roles y Funciones

**Admin de Ayuntamiento**
- ✅ Ver todas las colonias del ayuntamiento
- ✅ Crear nuevas colonias
- ✅ Editar colonias
- ✅ Asignar/cambiar grupos de trabajo

**Responsable de Grupo**
- ✅ Ver colonias asignadas a su grupo
- ❌ Crear colonias
- ❌ Editar colonias
- ❌ Asignar grupos

---

## Flujo de Trabajo Típico

### Scenario 1: Registrar una Nueva Colonia

```
1. Admin inicia sesión
2. Selecciona "Ver Colonias" en el menú
3. Haz clic en "Crear Nueva Colonia"
4. Completa los datos:
   - Nombre: "Colonia Parque Norte"
   - Ubicación: "Parque Municipal entrada norte"
   - Coordenadas: "40.0750,-88.2540"
   - Gatos: 18
   - Grupo: "Grupo Parques"
   - Descripción: "Colonia activa, colaborativa con vecinos"
5. Haz clic en "Guardar Colonia"
6. Sistema confirma y muestra la página de detalles
```

### Scenario 2: Actualizar Información de Colonia

```
1. Admin ve el listado de colonias
2. Haz clic en "Colonia Centro" para ver detalles
3. Haz clic en "✏ Editar colonia"
4. Modifica el número de gatos (de 15 a 17)
5. Actualiza la descripción
6. Haz clic en "Actualizar Colonia"
7. Los cambios se guardan automáticamente
```

### Scenario 3: Reasignar Colonia a Otro Grupo

```
1. Admin ve el listado
2. Haz clic en una colonia
3. Nota que está asignada a "Grupo Centro"
4. Haz clic en "Editar colonia"
5. Cambia el grupo a "Grupo Parques"
6. Haz clic en "Actualizar Colonia"
7. La colonia ahora pertenece a otro grupo
```

---

## Notas Importantes

### Validaciones
- ⚠️ El **nombre de la colonia es obligatorio**. Sin nombre no puedes guardar
- ⚠️ Las **coordenadas deben estar en formato lat,lon** (ej: 40.0735,-88.2535)
- ⚠️ Solo se muestran **colonias de tu ayuntamiento**

### Consejos
- 💡 Usa nombres descriptivos que faciliten la búsqueda
- 💡 Registra las coordenadas GPS para mejor localización
- 💡 Actualiza el número de gatos regularmente
- 💡 Asigna siempre un grupo responsable
- 💡 Usa la descripción para notas importantes (ej: "Acceso difícil", "Gatos agresivos", "Requiere esterilización")

### Relaciones
- Una colonia puede tener **múltiples gatos**
- Una colonia está asignada a **un grupo de trabajo**
- Un grupo de trabajo gestiona **múltiples colonias**

---

## Solución de Problemas

### "No hay colonias registradas en tu ayuntamiento"
**Causa**: No existen colonias registradas aún
**Solución**: Haz clic en "Crear Nueva Colonia" para crear la primera

### "Esta colonia no tiene grupo asignado"
**Causa**: La colonia fue creada sin grupo o fue desasignada
**Solución**: Edita la colonia y asigna un grupo de trabajo

### "No ves el botón Crear Nueva Colonia"
**Causa**: No tienes permiso de "Modificar Colonias"
**Solución**: Contacta al administrador del sistema para solicitar permisos

### "No ves el botón Editar"
**Causa**: No tienes permiso de "Modificar Colonias"
**Solución**: Contacta al administrador del sistema para solicitar permisos

---

## Contacto y Soporte

Si tienes preguntas o encuentras problemas:
- Contacta al administrador del sistema
- Verifica que tu usuario tiene los permisos necesarios
- Asegúrate de estar registrado en el ayuntamiento correcto

---

**Versión del Manual**: 1.0  
**Última actualización**: 14 de Diciembre de 2025  
**Estado**: Completo
