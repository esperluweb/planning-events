<?php
/**
 * Plugin Name: Planning Events
 * Plugin URI: https://github.com/esperluweb/planning-events
 * Description: Une extension pour gérer et afficher un planning d'événements
 * Version: 1.0.0
 * Author: EsperluWeb
 * Author URI: https://esperlweb.com
 * Text Domain: planning-events
 * License: GPL2
 */

// Sécurité : Empêcher l'accès direct au fichier
if (!defined('ABSPATH')) {
    exit;
}

// Définition des constantes
define('PLANNING_EVENTS_VERSION', '1.0.0');
define('PLANNING_EVENTS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PLANNING_EVENTS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Inclure les fichiers nécessaires
require_once PLANNING_EVENTS_PLUGIN_DIR . 'includes/class-planning-events.php';
require_once PLANNING_EVENTS_PLUGIN_DIR . 'includes/class-planning-events-post-type.php';
require_once PLANNING_EVENTS_PLUGIN_DIR . 'includes/class-planning-events-shortcode.php';
require_once PLANNING_EVENTS_PLUGIN_DIR . 'includes/class-planning-events-settings.php';

// Initialiser le plugin
function planning_events_init() {
    $plugin = new Planning_Events();
    $plugin->run();
    
    // Initialiser les paramètres
    new Planning_Events_Settings();
}
add_action('plugins_loaded', 'planning_events_init');

// Activation du plugin
register_activation_hook(__FILE__, 'planning_events_activate');
function planning_events_activate() {
    // Mettre à jour les permaliens pour que les CPT fonctionnent correctement
    flush_rewrite_rules();
}

// Désactivation du plugin
register_deactivation_hook(__FILE__, 'planning_events_deactivate');
function planning_events_deactivate() {
    // Nettoyage si nécessaire
    flush_rewrite_rules();
}
