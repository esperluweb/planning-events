jQuery(document).ready(function($) {
    // Vérifier si on est sur une page d'événement
    var isEventEditPage = window.location.href.indexOf('post.php') > -1 && 
                         $('input#post_type').val() === 'planning_event';
    var isEventNewPage = window.location.href.indexOf('post-new.php') > -1 && 
                        window.location.href.indexOf('post_type=planning_event') > -1;
    
    if (!isEventEditPage && !isEventNewPage) {
        return;
    }
    
    // Fonction pour basculer la visibilité des champs d'heure
    function toggleTimeFields() {
        var isAllDay = $('#all_day').is(':checked');
        
        if (isAllDay) {
            $('.time-field').slideUp(300);
            $('#start_time, #end_time').val('');
        } else {
            $('.time-field').slideDown(300);
        }
    }

    // Améliorer l'accessibilité des champs de date
    $('#start_date, #end_date').each(function() {
        // Ouvrir le calendrier au clic sur le champ entier
        $(this).on('click', function() {
            this.focus();
            if (this.showPicker) {
                this.showPicker();
            }
        });
        
        // Auto-remplir la date de fin si vide
        if (this.id === 'start_date') {
            $(this).on('change', function() {
                var selectedDate = $(this).val();
                if (selectedDate && (!$('#end_date').val() || $('#end_date').val() < selectedDate)) {
                    $('#end_date').val(selectedDate);
                }
            });
        }
    });

    // Gestion du changement d'état de la case à cocher "Journée entière"
    $('#all_day').on('change', toggleTimeFields);
    
    // Initialisation au chargement
    toggleTimeFields();

    // Validation du formulaire
    $('#post').on('submit', function(e) {
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();
        var isValid = true;
        
        // Vérification des champs obligatoires
        if (!startDate) {
            alert('Veuillez spécifier une date de début pour l\'événement.');
            isValid = false;
        } else if (!endDate) {
            alert('Veuillez spécifier une date de fin pour l\'événement.');
            isValid = false;
        } else if (new Date(endDate) < new Date(startDate)) {
            alert('La date de fin ne peut pas être antérieure à la date de début.');
            isValid = false;
        }
        
        // Si ce n'est pas une journée entière, on vérifie aussi les heures
        if (isValid && !$('#all_day').is(':checked')) {
            var startTime = $('#start_time').val();
            var endTime = $('#end_time').val();
            
            if (!startTime) {
                alert('Veuillez spécifier une heure de début pour l\'événement.');
                isValid = false;
            } else if (!endTime) {
                alert('Veuillez spécifier une heure de fin pour l\'événement.');
                isValid = false;
            } else if (startDate === endDate && startTime >= endTime) {
                alert('L\'heure de fin doit être postérieure à l\'heure de début.');
                isValid = false;
            }
        }
        
        if (!isValid) {
            e.preventDefault();
            return false;
        }
    });
});
