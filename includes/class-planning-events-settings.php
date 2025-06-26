<?php
/**
 * Gestion des paramètres du plugin
 */
class Planning_Events_Settings {
    /**
     * L'identifiant de la page de paramètres
     *
     * @var string
     */
    private $options_page;

    /**
     * Initialisation de la classe
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'settings_init'));
    }

    /**
     * Ajout du menu d'administration
     */
    public function add_admin_menu() {
        $this->options_page = add_submenu_page(
            'edit.php?post_type=planning_event',
            __('Paramètres du planning', 'planning-events'),
            __('Paramètres', 'planning-events'),
            'manage_options',
            'planning-events-settings',
            array($this, 'options_page')
        );
    }

    /**
     * Initialisation des paramètres
     */
    /**
     * Validation des paramètres
     */
    public function validate_settings($input) {
        $output = array();
        
        // Validation de la couleur principale
        if (isset($input['primary_color'])) {
            $output['primary_color'] = sanitize_hex_color($input['primary_color']);
        }
        
        // Validation de la couleur de survol
        if (isset($input['hover_color'])) {
            $output['hover_color'] = sanitize_hex_color($input['hover_color']);
        }
        
        return $output;
    }

    public function settings_init() {
        // Enregistrement des paramètres avec fonction de validation
        register_setting(
            'planning_events',
            'planning_events_settings',
            array(
                'type' => 'array',
                'sanitize_callback' => array($this, 'validate_settings'),
                'default' => array()
            )
        );

        // Ajout d'une section
        add_settings_section(
            'planning_events_settings_section',
            __('Paramètres d\'affichage', 'planning-events'),
            array($this, 'settings_section_callback'),
            'planning_events'
        );

        // Champ pour la couleur principale
        add_settings_field(
            'primary_color',
            __('Couleur principale', 'planning-events'),
            array($this, 'color_field_render'),
            'planning_events',
            'planning_events_settings_section',
            array(
                'label_for' => 'primary_color',
                'default' => '#2c3e50',
                'description' => __('Couleur utilisée pour les bordures et les en-têtes des événements', 'planning-events')
            )
        );

        // Champ pour la couleur de survol
        add_settings_field(
            'hover_color',
            __('Couleur au survol', 'planning-events'),
            array($this, 'color_field_render'),
            'planning_events',
            'planning_events_settings_section',
            array(
                'label_for' => 'hover_color',
                'default' => '#1a252f',
                'description' => __('Couleur utilisée au survol des événements', 'planning-events')
            )
        );
    }

    /**
     * Rendu du champ couleur
     */
    public function color_field_render($args) {
        $options = get_option('planning_events_settings');
        $value = isset($options[$args['label_for']]) ? $options[$args['label_for']] : $args['default'];
        ?>
        <div style="display: flex; align-items: center; gap: 10px;">
            <input 
                type="color" 
                name="planning_events_settings[<?php echo esc_attr($args['label_for']); ?>]" 
                value="<?php echo esc_attr($value); ?>"
                aria-label="<?php echo esc_attr($args['description']); ?>"
            >
            <span><?php echo esc_html($value); ?></span>
        </div>
        <?php if (!empty($args['description'])) : ?>
            <p class="description" style="margin-top: 8px; margin-bottom: 0;"><?php echo esc_html($args['description']); ?></p>
        <?php endif; ?>
        <?php
    }

    /**
     * Callback de la section
     */
    public function settings_section_callback() {
        echo '<p class="description">' . __('Personnalisez les couleurs du planning d\'événements. Les modifications seront visibles sur le front-end.', 'planning-events') . '</p>';
    }

    /**
     * Affichage de la page de paramètres
     */
    public function options_page() {
        // Vérification des droits
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap planning-events-settings-wrap">
            <h1><span class="dashicons dashicons-calendar-alt" style="margin-right: 10px;"></span> <?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="notice notice-info">
                <p><?php _e('Personnalisez l\'apparence du planning d\'événements. Utilisez le shortcode <code>[planning_events]</code> pour afficher le planning sur vos pages.', 'planning-events'); ?></p>
            </div>
            
            <div class="card">
                <form action="options.php" method="post">
                    <?php
                    // Affichage des champs
                    settings_fields('planning_events');
                    do_settings_sections('planning_events');
                    submit_button(__('Enregistrer les modifications', 'planning-events'), 'primary', 'submit', false);
                    ?>
                </form>
            </div>
            
            <div class="card" style="margin-top: 20px;">
                <h2><?php _e('Aide', 'planning-events'); ?></h2>
                <p><?php _e('Pour afficher le planning sur une page ou un article, utilisez le shortcode suivant :', 'planning-events'); ?></p>
                <code>[planning_events]</code>
                
                <h3 style="margin-top: 20px;"><?php _e('Paramètres optionnels', 'planning-events'); ?></h3>
                <ul style="list-style-type: disc; margin-left: 20px;">
                    <li><code>limit</code>: <?php _e('Nombre d\'événements à afficher (par défaut: -1 pour tous)', 'planning-events'); ?></li>
                    <li><code>order</code>: <?php _e('Ordre de tri (ASC ou DESC, par défaut: ASC)', 'planning-events'); ?></li>
                </ul>
                
                <p style="margin-top: 20px;">
                    <strong><?php _e('Exemple :', 'planning-events'); ?></strong><br>
                    <code>[planning_events limit="5" order="ASC"]</code>
                </p>
            </div>
        </div>
        <?php
    }

    /**
     * Récupère la valeur d'une option
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get_option($key, $default = '') {
        $options = get_option('planning_events_settings', array());
        return isset($options[$key]) ? $options[$key] : $default;
    }
}
