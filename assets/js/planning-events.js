jQuery(document).ready(function($) {
    // Animation au survol des événements
    $('.planning-event').on('mouseenter', function() {
        $(this).addClass('hover');
    }).on('mouseleave', function() {
        $(this).removeClass('hover');
    });

    // Fonctionnalité pour filtrer les événements (à implémenter si nécessaire)
    // Par exemple, filtrage par catégorie ou par date
    
    // Exemple de fonction pour filtrer les événements par catégorie
    $('.event-filter').on('change', function() {
        var category = $(this).val();
        
        if (category === 'all') {
            $('.planning-event').show();
        } else {
            $('.planning-event').hide();
            $('.planning-event[data-category="' + category + '"]').show();
        }
    });
});
