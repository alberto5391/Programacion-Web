El proyecto PLAGUE, desarrollado para la clase del maestro Javier Romero Castro, ha sido actualizado para funcionar con la arquitectura **MVC (Modelo-Vista-Controlador)**. Esta arquitectura organiza el código separando las diferentes responsabilidades del sistema en módulos independientes, lo que hace que sea más fácil de mantener, escalar y asegurar.

### Estructura de Directorios

```text
plague/
├── directorios.php
├── index.php
├── controllers/
│   ├── controller.login.php
│   ├── admin/
│   │   ├── controller.bitacora.php
│   │   ├── controller.campana.php
│   │   ├── controller.control_acceso.php
│   │   ├── controller.dashboard.php
│   │   ├── controller.estadistica.php
│   │   ├── controller.evidencia.php
│   │   ├── controller.gps.php
│   │   ├── controller.informacion_historica.php
│   │   ├── controller.login.php
│   │   ├── controller.logout.php
│   │   ├── controller.notificacion.php
│   │   ├── controller.plaga.php
│   │   ├── controller.producto.php
│   │   ├── controller.solicitante.php
│   │   ├── controller.tecnico.php
│   │   ├── controller.usuario.php
│   │   └── controller.zona.php
│   ├── super/
│   │   ├── controller.estado_campana.php
│   │   ├── controller.graficas.php
│   │   ├── controller.graficas_incidencias.php
│   │   ├── controller.graficas_plagas.php
│   │   ├── controller.graficas_productos.php
│   │   ├── controller.graficas_zonas.php
│   │   ├── controller.historial_zonas.php
│   │   ├── controller.incidencias.php
│   │   ├── controller.notificacion_exportar.php
│   │   ├── controller.registro_campanas.php
│   │   ├── controller.reporte_aplicaciones.php
│   │   ├── controller.reporte_campana.php
│   │   ├── controller.reporte_inspecciones.php
│   │   ├── controller.seguimiento_posterior.php
│   │   ├── controller.super_dashboard.php
│   │   └── controller.zonas_prioritarias.php
│   └── tec/
│       └── controller.tecnico.php
├── models/
│   ├── model.usuario.php
│   └── admin/
│       ├── model.base.php
│       ├── model.bitacora.php
│       ├── model.campana.php
│       ├── model.evidencia.php
│       ├── model.gps.php
│       ├── model.notificacion.php
│       ├── model.plaga.php
│       ├── model.producto.php
│       ├── model.rol.php
│       ├── model.solicitante.php
│       ├── model.tecnico.php
│       └── model.zona.php
└── resources/
    ├── class/
    │   └── class.connection.php
    └── img/
        ├── Estadisticas.png
        ├── evidencia_001.jpg
        ├── evidencia_002.jpg
        └── evidencia_003.jpeg

```

### Aquí está la explicación del proyecto y cómo funciona:
* **`index.php` (Punto único de entrada):**
* Funciona como el "Front Controller" o enrutador de toda la aplicación.
* Se encarga de iniciar la sesión del usuario y de establecer la conexión con la base de datos para que esté disponible en todo el sistema.
* Captura el módulo que el usuario quiere ver mediante la URL (por ejemplo, `index.php?modulo=login`) y, mediante un ciclo de tipo `switch`, decide a qué controlador o vista debe enviar al usuario.

### Aquí está la explicación de los php directorio:
* **`directorios.php` (Gestor de rutas):**
* Define las constantes de las rutas absolutas de todas las carpetas principales (como `CONTROLLER_PATH`, `MODEL_PATH`, `VIEW_PATH` y `RESOURCE_PATH`).
* Sirve para evitar problemas de vinculación al usar `require_once` cuando se llama a un archivo desde diferentes niveles de carpetas.

### Aquí está la explicación de los php controller:
* **`controllers/` (Los Controladores):**
* Son el "cerebro" lógico de cada módulo. Reciben las peticiones del `index.php`, procesan la información, le piden datos a los modelos y finalmente cargan la vista correcta.
* En tu proyecto, los controladores están inteligentemente divididos en carpetas según el rol del usuario: `admin/` (administradores), `super/` (supervisores) y `tec/` (técnicos).

### Aquí está la explicación de los php modelos:
* **`models/` (Los Modelos):**
* Son la capa encargada exclusivamente de interactuar con la base de datos.
* Archivos como `model.campana.php` contienen las sentencias SQL (como `INSERT`, `SELECT`, `UPDATE` y `DELETE`) para guardar, buscar o actualizar información. Los controladores mandan a llamar a estos modelos cuando necesitan datos.

### Aquí está la explicación de los php vistas:
* **Las Vistas (Views):**
* Son los archivos que contienen las interfaces gráficas y el diseño (HTML, Bootstrap, CSS) que el usuario ve en pantalla.
* Los controladores se encargan de validar permisos y luego cargar las vistas correspondientes mediante `require_once VIEW_PATH ...`.

### Aquí está la explicación de como funciona el login:
* **Los Login (`controller.login.php`):**
* Es el controlador responsable de la autenticación del sistema.
* Recibe el correo y la contraseña enviados por el usuario, solicita a `model.usuario.php` que valide las credenciales y, si son correctas, genera las variables de sesión.
* Dependiendo del rol del usuario (Administrador, Supervisor o Técnico), el login redirige automáticamente al panel (dashboard) correspondiente de cada perfil.

### Aquí está la explicación de como funciona los recursos y cosas extras que necesita el proyecto:
* **Directorios extra (`resources/`):**
* Contiene recursos generales, como la carpeta `img/` para las fotografías y logos.
* Almacena clases fundamentales como `class.connection.php`, la cual establece la conexión directa a la base de datos `plagueweb_db` utilizando el puerto 3307.

### ⚠️ Nota: Módulo de Recuperación de Contraseña

En el **Dashboard Técnico**, la vista del módulo `Recuperación de contraseña` actualmente se encuentra únicamente en HTML plano. 

Si alguien cuenta con el controlador/archivo PHP correspondiente a las vistas y la lógica de este módulo, por favor súbanlo a la rama de desarrollo (o ábranse un PR), ya que el archivo original no se encuentra localmente y pudo haberse extraviado por error.