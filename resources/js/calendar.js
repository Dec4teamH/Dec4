import { Calendar } from "@fullcalendar/core";
import timeGridPlugin from '@fullcalendar/timegrid';

if (document.getElementById("calendar") != null) {
    var calendarEl = document.getElementById("calendar");

    let calendar = new Calendar(calendarEl, {
        plugins: [timeGridPlugin],
        initialView: "timeGridFourDay",
        headerToolbar: {
            left: "prev,next",
            center: "title",
            right: "",
        },
        views: {
            timeGridFourDay: {
                type: 'timeGrid',
                duration: { days: 4 }
            },
        },
        // 終わってないのはallday,色とかも変える
        events: events,
    }
    );
    
    calendar.render();
}