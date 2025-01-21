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
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );

    error_log('El plugin ha sido activado.');
}

// Cada vez que se abra una pagina, revisar si hay un formulario y anexarlo con la base de datos.
