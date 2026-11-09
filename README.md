El proyecto PLAGUE, desarrollado para la clase del maestro Javier Romero Castro, ha sido actualizado para funcionar con la arquitectura **MVC (Modelo-Vista-Controlador)**. Esta arquitectura organiza el código separando las diferentes responsabilidades del sistema en módulos independientes, lo que hace que sea más fácil de mantener, escalar y asegurar.

Basado en la estructura y los archivos que compartiste, aquí tienes la explicación de cómo funciona cada parte fundamental del proyecto:

* **`index.php` (Punto único de entrada):**
* Funciona como el "Front Controller" o enrutador de toda la aplicación.


* Se encarga de iniciar la sesión del usuario y de establecer la conexión con la base de datos para que esté disponible en todo el sistema.


* Captura el módulo que el usuario quiere ver mediante la URL (por ejemplo, `index.php?modulo=login`) y, mediante un ciclo de tipo `switch`, decide a qué controlador o vista debe enviar al usuario.




* **`directorios.php` (Gestor de rutas):**
* Define las constantes de las rutas absolutas de todas las carpetas principales (como `CONTROLLER_PATH`, `MODEL_PATH`, `VIEW_PATH` y `RESOURCE_PATH`).


* Sirve para evitar problemas de vinculación al usar `require_once` cuando se llama a un archivo desde diferentes niveles de carpetas.




* **`controllers/` (Los Controladores):**
* Son el "cerebro" lógico de cada módulo. Reciben las peticiones del `index.php`, procesan la información, le piden datos a los modelos y finalmente cargan la vista correcta.


* En tu proyecto, los controladores están inteligentemente divididos en carpetas según el rol del usuario: `admin/` (administradores), `super/` (supervisores) y `tec/` (técnicos).




* **`models/` (Los Modelos):**
* Son la capa encargada exclusivamente de interactuar con la base de datos.


* Archivos como `model.campana.php` contienen las sentencias SQL (como `INSERT`, `SELECT`, `UPDATE` y `DELETE`) para guardar, buscar o actualizar información. Los controladores mandan a llamar a estos modelos cuando necesitan datos.




* **Las Vistas (Views):**
* Son los archivos que contienen las interfaces gráficas y el diseño (HTML, Bootstrap, CSS) que el usuario ve en pantalla.


* Los controladores se encargan de validar permisos y luego cargar las vistas correspondientes mediante `require_once VIEW_PATH ...`.




* **Los Login (`controller.login.php`):**
* Es el controlador responsable de la autenticación del sistema.


* Recibe el correo y la contraseña enviados por el usuario, solicita a `model.usuario.php` que valide las credenciales y, si son correctas, genera las variables de sesión.


* Dependiendo del rol del usuario (Administrador, Supervisor o Técnico), el login redirige automáticamente al panel (dashboard) correspondiente de cada perfil.




* **Directorios extra (`resources/`):**
* Contiene recursos generales, como la carpeta `img/` para las fotografías y logos.


* Almacena clases fundamentales como `class.connection.php`, la cual establece la conexión directa a la base de datos `plagueweb_db` utilizando el puerto 3307.