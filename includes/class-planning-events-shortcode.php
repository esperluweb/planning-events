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

        // Arguments de la requête
        $args = array(
            'post_type'      => 'planning_event',
            'posts_per_page' => intval($atts['limit']),
            'orderby'        => 'meta_value',
            'meta_key'       => '_start_date',
            'order'          => $atts['order'] === 'DESC' ? 'DESC' : 'ASC',
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'     => '_start_date',
                    'value'   => date('Y-m-d'),
                    'compare' => '>=',
                    'type'    => 'DATE'
                ),
                array(
                    'key'     => '_start_date',
                    'compare' => 'EXISTS',
                )
            )
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
                        $time_text = __('Toute la journée', 'planning-events');
                    } 
                    // Plusieurs jours
                    else {
                        $date_text = sprintf(
                            __('Du %s au %s', 'planning-events'),
                            ucfirst(strftime('%A %d %B %Y', $start_datetime->getTimestamp())),
                            ucfirst(strftime('%A %d %B %Y', $end_datetime->getTimestamp()))
                        );
                        $time_text = __('Toute la journée', 'planning-events');
                    }
                } else {
                    // Même jour
                    if ($start_date === $end_date) {
                        $date_text = ucfirst(strftime('%A %d %B %Y', $start_datetime->getTimestamp()));
                        $start_time_obj = DateTime::createFromFormat('H:i', $start_time);
                        $end_time_obj = DateTime::createFromFormat('H:i', $end_time);
                        
                        if ($start_time_obj && $end_time_obj) {
                            $time_text = sprintf(
                                __('de %s à %s', 'planning-events'),
                                $start_time_obj->format('H\hi'),
                                $end_time_obj->format('H\hi')
                            );
                        }
                    } 
                    // Jours différents
                    else {
                        $date_text = sprintf(
                            __('Du %s à %s au %s à %s', 'planning-events'),
                            ucfirst(strftime('%A %d %B', $start_datetime->getTimestamp())),
                            date('H\hi', strtotime($start_time)),
                            ucfirst(strftime('%A %d %B %Y', $end_datetime->getTimestamp())),
                            date('H\hi', strtotime($end_time))
                        );
                    }
                }
                
                // Style pour l'événement avec les couleurs du thème
                $event_style = sprintf(
                    'style="--primary-color: %s; --hover-color: %s;"',
                    esc_attr($primary_color),
                    esc_attr($hover_color)
                );
                ?>
                <div class="planning-event" <?php echo $event_style; ?>>
                    <div class="event-date">
                        <span class="day"><?php echo $start_datetime->format('d'); ?></span>
                        <span class="month"><?php echo ucfirst(strftime('%b', $start_datetime->getTimestamp())); ?></span>
                    </div>
                    <div class="event-details">
                        <h3 class="event-title"><?php the_title(); ?></h3>
                        <div class="event-meta">
                            <?php if ($date_text) : ?>
                                <span class="event-date"><?php echo $date_text; ?></span>
                            <?php endif; ?>
                            <?php if ($time_text) : ?>
                                <span class="event-time"><?php echo $time_text; ?></span>
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

            // Requête pour les événements passés
            $args_past = array(
                'post_type'      => 'planning_event',
                'posts_per_page' => -1,
                'orderby'        => 'meta_value',
                'meta_key'       => '_start_date',
                'order'          => 'DESC',
                'meta_query'     => array(
                    array(
                        'key'     => '_start_date',
                        'value'   => date('Y-m-d'),
                        'compare' => '<',
                        'type'    => 'DATE'
                    )
                )
            );

            $past_events_query = new WP_Query($args_past);

            if ($past_events_query->have_posts()) :
                ?>
                <div class="planning-events-container past-events-accordion">
                    <button class="accordion-toggle"><?php _e('Événements passés', 'planning-events'); ?></button>
                    <div class="accordion-content" style="display: none;">
                        <?php
                        while ($past_events_query->have_posts()) : $past_events_query->the_post();
                            $start_date = get_post_meta(get_the_ID(), '_start_date', true);
                            $start_time = get_post_meta(get_the_ID(), '_start_time', true);
                            $end_date = get_post_meta(get_the_ID(), '_end_date', true);
                            $end_time = get_post_meta(get_the_ID(), '_end_time', true);
                            $all_day = get_post_meta(get_the_ID(), '_all_day', true) === '1';
                            $event_location = get_post_meta(get_the_ID(), '_event_location', true);

                            $start_datetime = new DateTime($start_date);
                            $end_datetime = new DateTime($end_date);

                            $date_text = '';
                            $time_text = '';

                            if ($all_day) {
                                if ($start_date === $end_date) {
                                    $date_text = ucfirst(strftime('%A %d %B %Y', $start_datetime->getTimestamp()));
                                    $time_text = __('Toute la journée', 'planning-events');
                                } else {
                                    $date_text = sprintf(
                                        __('Du %s au %s', 'planning-events'),
                                        ucfirst(strftime('%A %d %B %Y', $start_datetime->getTimestamp())),
                                        ucfirst(strftime('%A %d %B %Y', $end_datetime->getTimestamp()))
                                    );
                                    $time_text = __('Toute la journée', 'planning-events');
                                }
                            } else {
                                if ($start_date === $end_date) {
                                    $date_text = ucfirst(strftime('%A %d %B %Y', $start_datetime->getTimestamp()));
                                    $start_time_obj = DateTime::createFromFormat('H:i', $start_time);
                                    $end_time_obj = DateTime::createFromFormat('H:i', $end_time);
                                    if ($start_time_obj && $end_time_obj) {
                                        $time_text = sprintf(
                                            __('de %s à %s', 'planning-events'),
                                            $start_time_obj->format('H\hi'),
                                            $end_time_obj->format('H\hi')
                                        );
                                    }
                                } else {
                                    $date_text = sprintf(
                                        __('Du %s à %s au %s à %s', 'planning-events'),
                                        ucfirst(strftime('%A %d %B', $start_datetime->getTimestamp())),
                                        date('H\hi', strtotime($start_time)),
                                        ucfirst(strftime('%A %d %B %Y', $end_datetime->getTimestamp())),
                                        date('H\hi', strtotime($end_time))
                                    );
                                }
                            }

                            ?>
                            <div class="planning-event" <?php echo $event_style; ?>>
                                <div class="event-date">
                                    <span class="day"><?php echo $start_datetime->format('d'); ?></span>
                                    <span class="month"><?php echo ucfirst(strftime('%b', $start_datetime->getTimestamp())); ?></span>
                                </div>
                                <div class="event-details">
                                    <h3 class="event-title"><?php the_title(); ?></h3>
                                    <div class="event-meta">
                                        <?php if ($date_text) : ?>
                                            <span class="event-date"><?php echo $date_text; ?></span>
                                        <?php endif; ?>
                                        <?php if ($time_text) : ?>
                                            <span class="event-time"><?php echo $time_text; ?></span>
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
            echo '<p>' . __('Aucun événement à venir pour le moment.', 'planning-events') . '</p>';
        endif;

        // Retourner le contenu du buffer
        return ob_get_clean();
    }
}
