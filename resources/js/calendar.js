import { Calendar } from '@fullcalendar/core'
import timeGridPlugin from '@fullcalendar/timegrid'

var calendarEl = document.getElementById("calendar");

const calendar = new Calendar(calendarEl, {
    plugins: [timeGridPlugin],
    initialView: 'timeGridDay',
    headerToolbar: {
        left: 'prev,next',
        center: 'title',
        right: 'timeGridDay,timeGridWeek' // user can switch between the two
    },
});
calendar.render();