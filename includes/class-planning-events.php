<?php
/**
 * Classe principale du plugin Planning Events
 */
class Planning_Events {
    /**
     * Constructeur
     */
    public function __construct() {
        $this->load_dependencies();
    }

    /**
     * Chargement des dépendances
     */
    private function load_dependencies() {
        // Les classes sont chargées automatiquement via le fichier principal
    }

    /**
     * Exécution du plugin
     */
    public function run() {
        // Initialiser les hooks
        $this->define_admin_hooks();
        $this->define_public_hooks();

        // Initialiser le type de contenu personnalisé
        $post_type = new Planning_Events_Post_Type();
        $post_type->init();

        // Initialiser le shortcode
        $shortcode = new Planning_Events_Shortcode();
        $shortcode->init();
    }

    /**
     * Définition des hooks pour l'administration
     */
    private function define_admin_hooks() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    /**
     * Définition des hooks pour le frontend
     */
    private function define_public_hooks() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_scripts'));
    }

    /**
     * Chargement des scripts et styles pour l'administration
     */
    public function enqueue_admin_scripts() {
        wp_enqueue_style(
            'planning-events-admin',
            PLANNING_EVENTS_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            PLANNING_EVENTS_VERSION
        );

        wp_enqueue_script(
            'planning-events-admin',
            PLANNING_EVENTS_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'jquery-ui-datepicker'),
            PLANNING_EVENTS_VERSION,
            true
        );

        // Ajouter le style du datepicker
        wp_enqueue_style(
            'jquery-ui-style',
            '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css',
            array(),
            '1.12.1'
        );
    }

    /**
     * Chargement des scripts et styles pour le frontend
     */
    public function enqueue_public_scripts() {
        wp_enqueue_style(
            'planning-events',
            PLANNING_EVENTS_PLUGIN_URL . 'assets/css/planning-events.css',
            array(),
            PLANNING_EVENTS_VERSION
        );

        wp_enqueue_script(
            'planning-events',
            PLANNING_EVENTS_PLUGIN_URL . 'assets/js/planning-events.js',
            array('jquery'),
            PLANNING_EVENTS_VERSION,
            true
        );
    }
}
