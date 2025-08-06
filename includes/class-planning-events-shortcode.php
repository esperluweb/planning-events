<?php
/**
 * Gestion du shortcode pour afficher le planning (filtrage PHP uniquement)
 */
class Planning_Events_Shortcode {
    public function init() {
        add_shortcode('planning_events', array($this, 'render_shortcode'));
    }

    public function render_shortcode($atts) {
        $atts = shortcode_atts([
            'limit' => 100, // max 100 événements
            'order' => 'ASC',
        ], $atts, 'planning_events');

        $limit = min(100, absint($atts['limit']));
        $order = strtoupper($atts['order']) === 'DESC' ? 'DESC' : 'ASC';
        $today = current_time('Y-m-d');

        // Récupérer les événements à venir
        $upcoming_args = [
            'post_type' => 'planning_event',
            'posts_per_page' => -1, // On veut tous les événements à venir
            'post_status' => 'publish',
            'meta_key' => '_start_date', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
            'orderby' => 'meta_value',
            'order' => $order,
            'meta_type' => 'DATE',
            'meta_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                [
                    'key' => '_start_date',
                    'value' => $today,
                    'compare' => '>=',
                    'type' => 'DATE'
                ]
            ]
        ];

        $upcoming = get_posts($upcoming_args);

        ob_start();

        echo '<div class="planning-events-container">';
        if (!empty($upcoming)) {
            foreach ($upcoming as $post) {
                setup_postdata($post);
                echo wp_kses_post($this->render_event($post));
            }
            wp_reset_postdata();
        } else {
            echo '<p>Aucun événement à venir</p>';
        }
        echo '</div>';


        return ob_get_clean();
    }

    private function render_event($post) {
        $start_date = get_post_meta($post->ID, '_start_date', true);
        $start_time = get_post_meta($post->ID, '_start_time', true);
        $end_date = get_post_meta($post->ID, '_end_date', true);
        $end_time = get_post_meta($post->ID, '_end_time', true);
        $all_day = get_post_meta($post->ID, '_all_day', true) === '1';
        $event_location = get_post_meta($post->ID, '_event_location', true);

        // Validation et gestion d'erreurs pour les dates
        if (empty($start_date) || !$this->is_valid_date($start_date)) {
            return '<!-- Événement ignoré : date de début invalide -->';
        }
        
        if (empty($end_date) || !$this->is_valid_date($end_date)) {
            $end_date = $start_date; // Fallback sur la date de début
        }

        try {
            $start_datetime = new DateTime($start_date);
            $end_datetime = new DateTime($end_date);
        } catch (Exception $e) {
            return '<!-- Événement ignoré : erreur lors de la création des dates -->';
        }

        $primary_color = Planning_Events_Settings::get_option('primary_color', '#2c3e50');
        $hover_color = Planning_Events_Settings::get_option('hover_color', '#1a252f');

        $event_style = sprintf('style="--primary-color: %s; --hover-color: %s;"', esc_attr($primary_color), esc_attr($hover_color));

        ob_start();
        ?>
        <div class="planning-event" <?php echo wp_kses_post($event_style); ?>>
            <div class="event-date">
                <span class="day"><?php echo esc_html($start_datetime->format('d')); ?></span>
                <span class="month"><?php echo esc_html(wp_date('M', $start_datetime->getTimestamp())); ?></span>
            </div>
            <div class="event-details">
                <h3 class="event-title"><?php echo esc_html(get_the_title($post)); ?></h3>
                <div class="event-meta">
                    <span class="event-date"><?php echo esc_html(wp_date('l j F Y', $start_datetime->getTimestamp())); ?></span>
                    <span class="event-time">
                        <?php
                        if ($all_day) {
                            echo esc_html('Toute la journée');
                        } else if ($start_date === $end_date) {
                            echo 'de ' . esc_html($start_time) . ' à ' . esc_html($end_time);
                        } else {
                            echo 'Du ' . esc_html($start_date) . ' à ' . esc_html($start_time) . ' au ' . esc_html($end_date) . ' à ' . esc_html($end_time);
                        }
                        ?>
                    </span>
                    <?php if ($event_location): ?>
                        <span class="event-location"><?php echo esc_html($event_location); ?></span>
                    <?php endif; ?>
                </div>
                <div class="event-excerpt">
                    <?php 
                    // Utiliser le contenu complet si disponible, sinon l'extrait
                    $content = get_the_content(null, false, $post);
                    if (!empty($content)) {
                        // Appliquer les filtres WordPress pour traiter le contenu (shortcodes, liens, etc.)
                        $content = apply_filters('the_content', $content);
                        echo wp_kses_post($content);
                    } else {
                        echo wp_kses_post(get_the_excerpt($post));
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Valide si une date est au format correct (YYYY-MM-DD)
     */
    private function is_valid_date($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

}