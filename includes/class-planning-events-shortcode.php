<?php
/**
 * Gestion du shortcode pour afficher le planning
 */
class Planning_Events_Shortcode {
    /**
     * Initialisation du shortcode
     */
    public function init() {
        add_shortcode('planning_events', array($this, 'render_shortcode'));
    }

    /**
     * Rendu du shortcode
     */
    public function render_shortcode($atts) {
        // Récupérer les attributs du shortcode
        $atts = shortcode_atts(
            array(
                'limit' => -1, // Nombre d'événements à afficher (-1 pour tous)
                'order' => 'ASC', // Ordre de tri
                'category' => '', // Catégorie d'événement (si utilisé plus tard)
            ),
            $atts,
            'planning_events'
        );

        // Préparer les arguments de la requête
        $today = gmdate('Y-m-d');
        $order = in_array(strtoupper($atts['order']), array('ASC', 'DESC')) ? strtoupper($atts['order']) : 'ASC';
        $limit = max(1, min(100, absint($atts['limit']))); // Limite entre 1 et 100

        // Récoutons la requête SQL pour le débogage
        add_filter('posts_request', function($sql) {
            error_log('Planning Events - Requête SQL : ' . $sql);
            return $sql;
        });
        
        // Récupérer d'abord les IDs des événements à venir avec mise en cache
        global $wpdb;
        $cache_key = 'planning_events_upcoming_' . md5($today . $order . $limit);
        $event_ids = wp_cache_get($cache_key, 'planning_events');
        
        // Log pour le débogage
        error_log('Planning Events - Aujourd\'hui : ' . $today);
        error_log('Planning Events - Clé de cache : ' . $cache_key);
        error_log('Planning Events - IDs en cache : ' . print_r($event_ids, true));
        
        if (false === $event_ids) {
            // Validation de la direction de tri
            $order_direction = $order === 'ASC' ? 'ASC' : 'DESC';
            
            // Construction sécurisée de la requête pour inclure les événements du jour même et à venir
            $query = $wpdb->prepare(
                "SELECT DISTINCT pm.post_id 
                FROM {$wpdb->postmeta} pm
                INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                WHERE pm.meta_key = %s 
                AND (
                    DATE(pm.meta_value) >= %s
                )
                AND p.post_type = 'planning_event'
                AND p.post_status = 'publish'
                ORDER BY ",
                '_start_date',
                $today
            );
            
            // Ajout sécurisé de l'ordre de tri
            $query .= $order_direction === 'ASC' ? 'pm.meta_value ASC' : 'pm.meta_value DESC';
            
            // Ajout de la limite
            $query .= $wpdb->prepare(" LIMIT %d", $limit);
            
            // Exécution sécurisée de la requête
            // La requête directe est nécessaire pour des raisons de performance (tri personnalisé)
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            error_log('Planning Events - Requête SQL modifiée : ' . $query);
            $event_ids = $wpdb->get_col($query);
            wp_cache_set($cache_key, $event_ids, 'planning_events', HOUR_IN_SECONDS);
        }

        // Récupérer tous les événements pour le débogage
        $all_events = $wpdb->get_results(
            "SELECT p.ID, p.post_title, p.post_status, 
                    pm1.meta_value as start_date, 
                    pm2.meta_value as end_date,
                    pm3.meta_value as all_day
             FROM {$wpdb->posts} p
             LEFT JOIN {$wpdb->postmeta} pm1 ON (p.ID = pm1.post_id AND pm1.meta_key = '_start_date')
             LEFT JOIN {$wpdb->postmeta} pm2 ON (p.ID = pm2.post_id AND pm2.meta_key = '_end_date')
             LEFT JOIN {$wpdb->postmeta} pm3 ON (p.ID = pm3.post_id AND pm3.meta_key = '_all_day')
             WHERE p.post_type = 'planning_event' AND p.post_status = 'publish'
             ORDER BY pm1.meta_value ASC"
        );

        // Afficher toujours les informations de débogage pour le moment
        $show_debug = false; // Mettre à false pour désactiver le débogage
        if ($show_debug || empty($event_ids)) {
            $debug_info = '<div style="background: #f8f9fa; border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 4px; font-family: monospace; font-size: 14px;">';
            $debug_info .= '<h3 style="margin-top: 0; color: #d63638;">Debug Information</h3>';
            $debug_info .= '<p><strong>Date du serveur :</strong> ' . date('Y-m-d H:i:s') . '</p>';
            $debug_info .= '<p><strong>Date de comparaison :</strong> ' . $today . '</p>';
            $debug_info .= '<p><strong>Requête exécutée :</strong> <code>' . esc_html($wpdb->last_query) . '</code></p>';
            
            // Afficher tous les événements trouvés
            $debug_info .= '<h4>Événements dans la base de données :</h4>';
            if (!empty($all_events)) {
                $debug_info .= '<table style="width: 100%; border-collapse: collapse; margin-top: 10px;">';
                $debug_info .= '<tr style="background-color: #f0f0f1;">';
                $debug_info .= '<th style="padding: 8px; border: 1px solid #ddd; text-align: left;">ID</th>';
                $debug_info .= '<th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Titre</th>';
                $debug_info .= '<th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Statut</th>';
                $debug_info .= '<th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Date de début</th>';
                $debug_info .= '<th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Date de fin</th>';
                $debug_info .= '<th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Journée entière</th>';
                $debug_info .= '</tr>';
                
                foreach ($all_events as $event) {
                    $debug_info .= '<tr style="border-bottom: 1px solid #ddd;">';
                    $debug_info .= '<td style="padding: 8px; border: 1px solid #ddd;">' . esc_html($event->ID) . '</td>';
                    $debug_info .= '<td style="padding: 8px; border: 1px solid #ddd;">' . esc_html($event->post_title) . '</td>';
                    $debug_info .= '<td style="padding: 8px; border: 1px solid #ddd;">' . esc_html($event->post_status) . '</td>';
                    $debug_info .= '<td style="padding: 8px; border: 1px solid #ddd; color: ' . (strtotime($event->start_date) < strtotime($today) ? 'red' : 'green') . '; font-weight: bold;">' . esc_html($event->start_date) . '</td>';
                    $debug_info .= '<td style="padding: 8px; border: 1px solid #ddd;">' . esc_html($event->end_date) . '</td>';
                    $debug_info .= '<td style="padding: 8px; border: 1px solid #ddd; text-align: center;">' . ($event->all_day ? 'Oui' : 'Non') . '</td>';
                    $debug_info .= '</tr>';
                }
                $debug_info .= '</table>';
            } else {
                $debug_info .= '<p style="color: red; font-weight: bold;">Aucun événement trouvé dans la base de données.</p>';
            }
            
            $debug_info .= '</div>';
            
            return $debug_info . '<p>' . esc_html__('Aucun événement à venir pour le moment.', 'planning-events') . '</p>';
        }

        // Requête principale avec les IDs déjà filtrés
        $args = array(
            'post_type'      => 'planning_event',
            'post__in'       => $event_ids,
            'posts_per_page' => $limit,
            'orderby'        => 'post__in', // Conserve l'ordre de la requête SQL
            'no_found_rows'  => true,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => false
        );

        // Exécuter la requête
        $events_query = new WP_Query($args);

        // Démarrer la mise en mémoire tampon
        ob_start();

        // Afficher les événements
        if ($events_query->have_posts()) :
            echo '<div class="planning-events-container">';
            
            // Définir la locale en français pour le formatage des dates
            setlocale(LC_TIME, 'fr_FR.utf8', 'fr_FR', 'fr');

            echo '<h2>Événements à venir</h2>';
            
            while ($events_query->have_posts()) : $events_query->the_post();
                $start_date = get_post_meta(get_the_ID(), '_start_date', true);
                $start_time = get_post_meta(get_the_ID(), '_start_time', true);
                $end_date = get_post_meta(get_the_ID(), '_end_date', true);
                $end_time = get_post_meta(get_the_ID(), '_end_time', true);
                $all_day = get_post_meta(get_the_ID(), '_all_day', true) === '1';
                $event_location = get_post_meta(get_the_ID(), '_event_location', true);
                // Récupérer les couleurs depuis les paramètres
                $primary_color = Planning_Events_Settings::get_option('primary_color', '#2c3e50');
                $hover_color = Planning_Events_Settings::get_option('hover_color', '#1a252f');
                
                // Formater les dates
                $start_datetime = new DateTime($start_date);
                $end_datetime = new DateTime($end_date);
                
                // Format de date en français
                $date_format = 'l j F Y';
                $time_format = 'H\hi';
                
                // Texte de la date
                $date_text = '';
                $time_text = '';
                
                if ($all_day) {
                    // Même jour
                    if ($start_date === $end_date) {
                        $date_text = ucfirst(strftime('%A %d %B %Y', $start_datetime->getTimestamp()));
                        /* translators: Texte affiché pour un événement qui dure toute la journée */
                        $time_text = esc_html__('Toute la journée', 'planning-events');
                    } 
                    // Plusieurs jours
                    else {
                        /* translators: 1: Date de début complète, 2: Date de fin simplifiée */
                        $date_text = sprintf(
                            /* translators: 1: Date de début complète, 2: Date de fin simplifiée */
                            __('Du %1$s au %2$s', 'planning-events'),
                            ucfirst(strftime('%A %d %B %Y', $start_datetime->getTimestamp())),
                            ucfirst(strftime('%A %B %Y', $end_datetime->getTimestamp()))
                        );
                        /* translators: Texte affiché pour un événement qui dure toute la journée */
                        $time_text = esc_html__('Toute la journée', 'planning-events');
                    }
                } else {
                    // Même jour
                    if ($start_date === $end_date) {
                        $date_text = ucfirst(strftime('%A %d %B %Y', $start_datetime->getTimestamp()));
                        $start_time_obj = DateTime::createFromFormat('H:i', $start_time);
                        $end_time_obj = DateTime::createFromFormat('H:i', $end_time);
                        
                        if ($start_time_obj && $end_time_obj) {
                            /* translators: 1: Heure de début, 2: Heure de fin */
                            $time_text = sprintf(
                                /* translators: 1: Heure de début, 2: Heure de fin */
                                __('de %1$s à %2$s', 'planning-events'),
                                $start_time_obj->format('H\\hi'),
                                $end_time_obj->format('H\\hi')
                            );
                        }
                    } 
                    // Jours différents
                    else {
                        /* translators: 1: Date de début, 2: Heure de début, 3: Date de fin, 4: Heure de fin */
                        $date_text = sprintf(
                            /* translators: 1: Date de début, 2: Heure de début, 3: Date de fin, 4: Heure de fin */
                            __('Du %1$s à %2$s au %3$s à %4$s', 'planning-events'),
                            ucfirst(strftime('%A %d %B', $start_datetime->getTimestamp())),
                            gmdate('H\\hi', strtotime($start_time)),
                            ucfirst(strftime('%A %d %B %Y', $end_datetime->getTimestamp())),
                            gmdate('H\\hi', strtotime($end_time))
                        );
                    }
                }
                
                // Style pour l'événement avec les couleurs du thème
                /* translators: 1: Couleur primaire au format hexadécimal, 2: Couleur de survol au format hexadécimal */
                $event_style = sprintf(
                    'style="--primary-color: %s; --hover-color: %s;"',
                    esc_attr($primary_color),
                    esc_attr($hover_color)
                );
                ?>
                <div class="planning-event" <?php echo wp_kses_post($event_style); ?>>
                    <div class="event-date">
                        <span class="day"><?php echo esc_html($start_datetime->format('d')); ?></span>
                        <span class="month"><?php echo esc_html(ucfirst(strftime('%b', $start_datetime->getTimestamp()))); ?></span>
                    </div>
                    <div class="event-details">
                        <h3 class="event-title"><?php the_title(); ?></h3>
                        <div class="event-meta">
                            <?php if ($date_text) : ?>
                                <span class="event-date"><?php echo esc_html($date_text); ?></span>
                            <?php endif; ?>
                            <?php if ($time_text) : ?>
                                <span class="event-time"><?php echo esc_html($time_text); ?></span>
                            <?php endif; ?>
                            <?php if ($event_location) : ?>
                                <span class="event-location"><?php echo esc_html($event_location); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="event-excerpt">
                            <?php the_excerpt(); ?>
                        </div>
                    </div>
                </div>
                <?php
            endwhile;
            
            echo '</div>';
            
            // Réinitialiser la requête
            wp_reset_postdata();

            // Récupérer les IDs des événements passés avec mise en cache
            $cache_key = 'planning_events_past_' . md5($today . $order . $limit);
            $past_event_ids = wp_cache_get($cache_key, 'planning_events');
            
            if (false === $past_event_ids) {
                // Validation de la direction de tri
                $order_direction = $order === 'ASC' ? 'ASC' : 'DESC';
                
                // Construction sécurisée de la requête pour les événements passés
                $query = $wpdb->prepare(
                    "SELECT DISTINCT pm.post_id 
                    FROM {$wpdb->postmeta} pm
                    INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                    WHERE pm.meta_key = %s 
                    AND DATE(pm.meta_value) < %s
                    AND p.post_type = 'planning_event'
                    AND p.post_status = 'publish'
                    ORDER BY ",
                    '_start_date',
                    $today
                );
                
                // Ajout sécurisé de l'ordre de tri
                $query .= $order_direction === 'ASC' ? 'pm.meta_value ASC' : 'pm.meta_value DESC';
                
                // Ajout de la limite
                $query .= $wpdb->prepare(" LIMIT %d", $limit);
                
                // Exécution sécurisée de la requête
                error_log('Planning Events - Requête SQL pour les événements passés : ' . $query);
                $past_event_ids = $wpdb->get_col($query);
                wp_cache_set($cache_key, $past_event_ids, 'planning_events', HOUR_IN_SECONDS);
            }

            $past_args = array(
                'post_type'      => 'planning_event',
                'post__in'       => $past_event_ids,
                'posts_per_page' => $limit,
                'orderby'        => 'post__in', // Conserve l'ordre de la requête SQL
                'no_found_rows'  => true,
                'update_post_meta_cache' => true,
                'update_post_term_cache' => false
            );

            // Exécuter la requête pour les événements passés
            $past_args['post__not_in'] = $event_ids;
            $past_events_query = new WP_Query($past_args);

            if ($past_events_query->have_posts()) :
                ?>
                <div class="planning-events-container past-events-accordion">
                    <button class="accordion-toggle"><?php esc_html_e('Événements passés', 'planning-events'); ?></button>
                    <div class="accordion-content" style="display: none;">
                        <?php
                        while ($past_events_query->have_posts()) : $past_events_query->the_post();
                            $post_id = get_the_ID();
                            
                            // Récupération des métadonnées nécessaires
                            $start_date = get_post_meta($post_id, '_start_date', true);
                            $start_time = get_post_meta($post_id, '_start_time', true);
                            $end_date = get_post_meta($post_id, '_end_date', true);
                            $end_time = get_post_meta($post_id, '_end_time', true);
                            $all_day = get_post_meta($post_id, '_all_day', true) === '1';
                            $event_location = get_post_meta($post_id, '_event_location', true);

                            $start_datetime = new DateTime($start_date);
                            $end_datetime = new DateTime($end_date);

                            $date_text = '';
                            $time_text = '';

                            if ($all_day) {
                                if ($start_date === $end_date) {
                                    $date_text = ucfirst(strftime('%A %d %B %Y', $start_datetime->getTimestamp()));
                                    /* translators: Texte affiché pour un événement qui dure toute la journée */
                                    $time_text = esc_html__('Toute la journée', 'planning-events');
                                } else {
                                    /* translators: 1: Date de début complète, 2: Date de fin simplifiée */
                                    $date_text = sprintf(
                                        /* translators: 1: Date de début complète, 2: Date de fin simplifiée */
                                        __('Du %1$s au %2$s', 'planning-events'),
                                        ucfirst(strftime('%A %d %B %Y', $start_datetime->getTimestamp())),
                                        ucfirst(strftime('%A %B %Y', $end_datetime->getTimestamp()))
                                    );
                                    /* translators: Texte affiché pour un événement qui dure toute la journée */
                                    $time_text = esc_html__('Toute la journée', 'planning-events');
                                }
                            } else {
                                if ($start_date === $end_date) {
                                    $date_text = ucfirst(strftime('%A %d %B %Y', $start_datetime->getTimestamp()));
                                    $start_time_obj = DateTime::createFromFormat('H:i', $start_time);
                                    $end_time_obj = DateTime::createFromFormat('H:i', $end_time);
                                    if ($start_time_obj && $end_time_obj) {
                                        /* translators: 1: Heure de début, 2: Heure de fin */
                                        $time_text = sprintf(
                                            /* translators: 1: Heure de début, 2: Heure de fin */
                                            __('de %1$s à %2$s', 'planning-events'),
                                            $start_time_obj->format('H\\hi'),
                                            $end_time_obj->format('H\\hi')
                                        );
                                    }
                                } else {
                                    /* translators: 1: Date de début, 2: Heure de début, 3: Date de fin, 4: Heure de fin */
                                    $date_text = sprintf(
                                        /* translators: 1: Date de début, 2: Heure de début, 3: Date de fin, 4: Heure de fin */
                                        __('Du %1$s à %2$s au %3$s à %4$s', 'planning-events'),
                                        ucfirst(strftime('%A %d %B', $start_datetime->getTimestamp())),
                                        gmdate('H\\hi', strtotime($start_time)),
                                        ucfirst(strftime('%A %d %B %Y', $end_datetime->getTimestamp())),
                                        gmdate('H\\hi', strtotime($end_time))
                                    );
                                }
                            }

                            ?>
                            <div class="planning-event" <?php echo wp_kses_post($event_style); ?>>
                                <div class="event-date">
                                    <?php /* translators: Jour du mois (01-31) */ ?>
                                    <span class="day"><?php echo esc_html($start_datetime->format('d')); ?></span>
                                    <?php /* translators: Mois abrégé (jan, fév, etc.) */ ?>
                                    <span class="month"><?php echo esc_html(ucfirst(strftime('%b', $start_datetime->getTimestamp()))); ?></span>
                                </div>
                                <div class="event-details">
                                    <h3 class="event-title"><?php the_title(); ?></h3>
                                    <div class="event-meta">
                                        <?php if ($date_text) : ?>
                                            <span class="event-date"><?php echo esc_html($date_text); ?></span>
                                        <?php endif; ?>
                                        <?php if ($time_text) : ?>
                                            <span class="event-time"><?php echo esc_html($time_text); ?></span>
                                        <?php endif; ?>
                                        <?php if ($event_location) : ?>
                                            <span class="event-location"><?php echo esc_html($event_location); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="event-excerpt">
                                        <?php the_excerpt(); ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        endwhile;
                        ?>
                    </div>
                </div>
                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const toggle = document.querySelector('.accordion-toggle');
                    const content = document.querySelector('.accordion-content');
                    toggle.addEventListener('click', function () {
                        content.style.display = content.style.display === 'none' ? 'block' : 'none';
                    });
                });
                </script>
                <style>
                .past-events-accordion {
                    margin-top: 2em;
                    width: 100%;
                }
                .accordion-toggle {
                    background-color: var(--primary-color, #2c3e50);
                    color: white;
                    border: none;
                    padding: 15px 20px;
                    cursor: pointer;
                    font-size: 1.1em;
                    width: 100%;
                    text-align: left;
                    border-radius: 4px;
                    transition: background-color 0.3s ease;
                    margin: 1em 0;
                }
                .accordion-toggle:hover {
                    background-color: var(--hover-color, #1a252f);
                }
                .accordion-content {
                    width: 100%;
                    background: white;
                    border-radius: 4px;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                    overflow: hidden;
                    margin-bottom: 2em;
                }
                .accordion-content .planning-event {
                    margin-bottom: 0;
                    border-radius: 0;
                    border-left: 4px solid var(--primary-color, #2c3e50);
                }
                .accordion-content .planning-event:not(:last-child) {
                    border-bottom: 1px solid #eee;
                }
                .accordion-content .planning-event:hover {
                    transform: none;
                    box-shadow: none;
                }
                </style>
                <?php
                wp_reset_postdata();
            endif;

        else :
            echo '<p>' . esc_html__('Aucun événement à venir pour le moment.', 'planning-events') . '</p>';
        endif;

        // Retourner le contenu du buffer
        return ob_get_clean();
    }
}
