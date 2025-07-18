<?php
/**
 * Gestion du type de contenu personnalisé pour les événements
 */
class Planning_Events_Post_Type {
    /**
     * Initialisation du type de contenu
     */
    public function init() {
        add_action('init', array($this, 'register_post_type'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post_planning_event', array($this, 'save_meta_box_data'));
        add_action('save_post', array($this, 'save_meta_box_data_fallback'), 10, 2);

        
        // Ajouter les colonnes personnalisées
        // add_filter('manage_planning_event_posts_columns', array($this, 'add_event_columns'));
        // add_action('manage_planning_event_posts_custom_column', array($this, 'display_event_columns'), 10, 2);
        
        // Trier les colonnes
        // add_filter('manage_edit-planning_event_sortable_columns', array($this, 'sortable_columns'));
        
        // Masquer le lien 'Voir' dans la liste des événements
        // add_filter('post_row_actions', array($this, 'remove_view_link'), 10, 2);
    }

    public function save_meta_box_data_fallback($post_id, $post) {
        if ($post->post_type !== 'planning_event') {
            return;
        }
    
        $this->save_meta_box_data($post_id);
    }
    

    /**
     * Enregistrement du type de contenu personnalisé
     */
    public function register_post_type() {
        $labels = array(
            'name'               => __('Événements', 'planning-events'),
            'singular_name'      => __('Événement', 'planning-events'),
            'menu_name'          => __('Planning Événements', 'planning-events'),
            'add_new'            => __('Ajouter un événement', 'planning-events'),
            'add_new_item'       => __('Ajouter un nouvel événement', 'planning-events'),
            'edit_item'          => __('Modifier l\'événement', 'planning-events'),
            'new_item'           => __('Nouvel événement', 'planning-events'),
            'view_item'          => __('Voir l\'événement', 'planning-events'),
            'search_items'       => __('Rechercher des événements', 'planning-events'),
            'not_found'          => __('Aucun événement trouvé', 'planning-events'),
            'not_found_in_trash' => __('Aucun événement dans la corbeille', 'planning-events')
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false, // Pas de front
            'show_ui'            => true,  // Interface admin OK
            'show_in_menu'       => true,
            'publicly_queryable' => false,
            'query_var'          => false,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => 20,
            'menu_icon'          => 'dashicons-calendar-alt',
            'supports'           => array('title', 'editor', 'excerpt', 'thumbnail'),
            'show_in_rest'       => false // pas de Gutenberg
        );

        register_post_type('planning_event', $args);
    }

    /**
     * Ajout des meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'planning_event_meta',
            __('Détails de l\'événement', 'planning-events'),
            array($this, 'render_meta_box'),
            'planning_event',
            'normal',
            'high'
        );
    }

    /**
     * Affichage de la meta box
     */
    public function render_meta_box($post) {
        // Ajouter un nonce pour la vérification
        wp_nonce_field('planning_event_meta_box', 'planning_event_meta_box_nonce');

        // Récupérer les valeurs existantes
        $start_date = get_post_meta($post->ID, '_start_date', true);
        $start_time = get_post_meta($post->ID, '_start_time', true);
        $end_date = get_post_meta($post->ID, '_end_date', true);
        $end_time = get_post_meta($post->ID, '_end_time', true);
        $all_day = get_post_meta($post->ID, '_all_day', true);
        $event_location = get_post_meta($post->ID, '_event_location', true);

        // Afficher les champs
        ?>
        <div class="planning-event-fields">
            <div class="event-date-time-fields">
                <h3><?php echo esc_html__('Dates et heures', 'planning-events'); ?></h3>
                
                <div class="all-day-option" style="margin-bottom: 15px;">
                    <label>
                        <input type="checkbox" id="all_day" name="all_day" value="1" <?php checked($all_day, '1'); ?>>
                        <?php echo esc_html__('Journée entière', 'planning-events'); ?>
                    </label>
                </div>

                <div class="date-time-container">
                    <div class="date-time-group">
                        <h4><?php echo esc_html__('Début', 'planning-events'); ?></h4>
                        <p>
                            <label for="start_date"><?php echo esc_html__('Date de début', 'planning-events'); ?></label>
                            <input type="date" id="start_date" name="start_date" value="<?php echo esc_attr($start_date); ?>" class="widefat">
                        </p>
                        <p class="time-field" <?php echo $all_day ? 'style="display:none;"' : ''; ?>>
                            <label for="start_time"><?php echo esc_html__('Heure de début', 'planning-events'); ?></label>
                            <input type="time" id="start_time" name="start_time" value="<?php echo esc_attr($start_time); ?>" class="widefat">
                        </p>
                    </div>

                    <div class="date-time-group">
                        <h4><?php echo esc_html__('Fin', 'planning-events'); ?></h4>
                        <p>
                            <label for="end_date"><?php echo esc_html__('Date de fin', 'planning-events'); ?></label>
                            <input type="date" id="end_date" name="end_date" value="<?php echo esc_attr($end_date ?: $start_date); ?>" class="widefat">
                        </p>
                        <p class="time-field" <?php echo $all_day ? 'style="display:none;"' : ''; ?>>
                            <label for="end_time"><?php echo esc_html__('Heure de fin', 'planning-events'); ?></label>
                            <input type="time" id="end_time" name="end_time" value="<?php echo esc_attr($end_time ?: $start_time); ?>" class="widefat">
                        </p>
                    </div>
                </div>
            </div>

            <p>
                <label for="event_location"><?php echo esc_html__('Lieu', 'planning-events'); ?></label>
                <input type="text" id="event_location" name="event_location" value="<?php echo esc_attr($event_location); ?>" class="widefat">
            </p>
        </div>
        <?php
    }

    /**
     * Masque le lien 'Voir' dans la liste des événements
     */
    public function remove_view_link($actions, $post) {
        if ($post->post_type === 'planning_event') {
            unset($actions['view']);
            // Optionnel : supprimer aussi le lien de modification rapide
            // unset($actions['inline hide-if-no-js']);
        }
        return $actions;
    }

    public function save_meta_box_data($post_id) {
        // On ne fait rien si ce n'est pas le bon post type
        if (get_post_type($post_id) !== 'planning_event') {
            return;
        }
    
        // On ne fait rien si on est en autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
    
        // Si pas dans l'admin ou via un vrai formulaire, on ne fait rien
        if (!isset($_POST['planning_event_meta_box_nonce']) || 
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['planning_event_meta_box_nonce'])), 'planning_event_meta_box')) {
            return;
        }
    
        // Permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    
        // Vérification minimale obligatoire : champ start_date présent et valide
        if (empty($_POST['start_date'])) {
            return;
        }

        // Validation de la date de début
        $start_date = sanitize_text_field(wp_unslash($_POST['start_date']));
        if (!$this->is_valid_date($start_date)) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>Date de début invalide. Format attendu : YYYY-MM-DD</p></div>';
            });
            return;
        }

        // Validation de la date de fin si présente
        if (!empty($_POST['end_date'])) {
            $end_date = sanitize_text_field(wp_unslash($_POST['end_date']));
            if (!$this->is_valid_date($end_date)) {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-error"><p>Date de fin invalide. Format attendu : YYYY-MM-DD</p></div>';
                });
                return;
            }
            
            // Vérification que la date de fin n'est pas antérieure à la date de début
            if (strtotime($end_date) < strtotime($start_date)) {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-error"><p>La date de fin ne peut pas être antérieure à la date de début.</p></div>';
                });
                return;
            }
        }
    
        // Champs à enregistrer
        $fields = array('start_date', 'end_date', 'start_time', 'end_time', 'event_location');
    
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                $value = sanitize_text_field(wp_unslash($_POST[$field]));
                update_post_meta($post_id, '_' . $field, $value);
            }
        }
    
        // Gestion du all_day
        $all_day = isset($_POST['all_day']) ? '1' : '0';
        update_post_meta($post_id, '_all_day', $all_day);
    
        if ($all_day === '1') {
            update_post_meta($post_id, '_start_time', '');
            update_post_meta($post_id, '_end_time', '');
        }
    }

    /**
     * Valide si une date est au format correct (YYYY-MM-DD)
     */
    private function is_valid_date($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
