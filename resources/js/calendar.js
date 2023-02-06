import { Calendar } from '@fullcalendar/core'
import timeGridPlugin from '@fullcalendar/timegrid'
import scrollgrid from '@fullcalendar/scrollgrid';

var calendarEl = document.getElementById("calendar");

const calendar = new Calendar(calendarEl, {
    plugins: [timeGridPlugin], 
    initialView: 'timeGridDay',
    allDaySlot: false,
    height: 600,
    local:"ja",
    headerToolbar: {
        left: 'prev,next',
        center: 'title',
        right: 'timeGridDay,timeGridWeek' // user can switch between the two
    }, 
    slotLabelFormat: {
        hour: 'numeric',
        minute: '2-digit',
        omitZeroMinute: false,
        meridiem: 'short'
    },

});
calendar.render();