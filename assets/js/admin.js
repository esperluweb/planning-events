jQuery(document).ready(function($) {
    // Fonction pour basculer la visibilité des champs d'heure
    function toggleTimeFields() {
        var isAllDay = $('#all_day').is(':checked');
        $('.time-field').toggle(!isAllDay);
        
        // Si c'est une journée entière, on vide les champs d'heure
        if (isAllDay) {
            $('#start_time, #end_time').val('');
        }
    }

    // Initialisation des datepickers
    $('input[type="date"]').each(function() {
        if (typeof $.fn.datepicker !== 'undefined') {
            $(this).datepicker({
                dateFormat: 'yy-mm-dd',
                firstDay: 1,
                dayNamesMin: ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'],
                monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
                monthNamesShort: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
                onSelect: function(selectedDate) {
                    // Si la date de fin est vide ou antérieure à la date de début
                    if (this.id === 'start_date' && (!$('#end_date').val() || $('#end_date').val() < selectedDate)) {
                        $('#end_date').val(selectedDate);
                    }
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
