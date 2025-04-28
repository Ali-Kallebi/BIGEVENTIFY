// jQuery et Datepicker
import $ from 'jquery';
import 'jquery-ui/ui/widgets/datepicker.js';
import 'jquery-ui/themes/base/datepicker.css';

// FullCalendar
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import frLocale from '@fullcalendar/core/locales/fr';
import '@fullcalendar/core/main.css';
import '@fullcalendar/daygrid/main.css';

$(document).ready(function () {
    // Datepicker pour les champs texte
    $("input[type='text']").datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true,
        yearRange: "1900:2100",
        showButtonPanel: true,
    });

    // Initialisation du calendrier si l'élément existe
    const calendarEl = document.getElementById('calendar');
    if (calendarEl) {
        const calendar = new Calendar(calendarEl, {
            plugins: [dayGridPlugin, interactionPlugin],
            initialView: 'dayGridMonth',
            locale: frLocale,
            events: '/events-json' // ou '/api/events' si c'est ta vraie route
        });

        calendar.render();
    }
});
