<?php
/**
* Plugin Name: Form-Page-View
**/

// La interfaz del plugin debe tener:
    // - Un select de los usuarios. Para escoger al usuario que se le mostrará la página.
    //---------------------- si el usuario escogido está conectado al wordpress panel -------------
    // - un botón a la página creada dinamicamente (esta pagina no está visibile en la lista de paginas del sitio)


// Cada vez que se cargue una pagina, busque si existe algún formulario
// si existe algún formulario, le va a crear un lissener para el botón de envio.
// Si alguíen envía la información del formulario, se hace una query a la DB para retribuir la información y colocarla en una página especifica.

// Seguridad
if (!defined ('ABSPATH')){
    echo 'No puedo hacer nada cuando me llaman directamente :(';
    die;
}

// Cuando se activa el plugin crea-registra un tabla nueva en la base de datos
register_activation_hook(__FILE__, 'database_creation');

function database_creation() {
    // Crear la tabla
    global $wpdb;

    $table_name = $wpdb->prefix . 'tabla_pagina_unica';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (

        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        data LONGTEXT NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );

    error_log('El plugin ha sido activado.');
}

// Cada vez que se abra una pagina, revisar si hay un formulario y anexarlo con la base de datos.

function detectar_envio_formulario() {
    ?>
    <script>


        document.addEventListener("DOMContentLoaded", function () {

            let _all_forms = document.querySelectorAll("form input[type=submit]");

            _all_forms.forEach(form => {
                form.addEventListener("click", async function(e){
                    e.preventDefault(); // Evita que el formulario se envíe y recargue la página

                    // Prevenir el evento de submit
                    let formData = new FormData(form.closest('form')); // Usamos closest para asegurar que el formulario correcto se seleccione

                    try {
                        // Hacer la solicitud de forma asíncrona
                        let response = await fetch("<?php echo admin_url('admin-ajax.php'); ?>", {
                            method: "POST",
                            headers: { "Content-Type": "application/x-www-form-urlencoded" },
                            body: new URLSearchParams({
                                action: "capturar_formulario",
                                ...Object.fromEntries(formData),
                            }),
                        });

                        // Esperar respuesta y procesarla
                        let data = await response.json();
                        console.log("Formulario capturado:", data);

                        // Si todo salió bien, envía el formulario después de capturar la información
                        form.closest('form').submit();

                    } catch (error) {
                        console.error("Error al capturar el formulario:", error);
                    }
                });
            });

        });




    </script>
    <?php
}
add_action('wp_footer', 'detectar_envio_formulario');

//Guardamos los Datos en la Base de Datos con PHP
function capturar_formulario() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tabla_pagina_unica';

    $data = array_map('sanitize_text_field', $_POST); // Limpia los datos

    if (!empty($data)) {
        $wpdb->insert(
            $table_name,
            array('name' => json_encode($data)), // Guarda como JSON
            array('%s')
        );

        wp_send_json_success(['message' => 'Formulario capturado y almacenado.']);
    } else {
        wp_send_json_error(['message' => 'No se recibió información.']);
    }
}
add_action('wp_ajax_capturar_formulario', 'capturar_formulario');
add_action('wp_ajax_nopriv_capturar_formulario', 'capturar_formulario');