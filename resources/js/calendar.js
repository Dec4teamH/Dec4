import { Calendar } from "@fullcalendar/core";
import timeGridPlugin from '@fullcalendar/timegrid';

if (document.getElementById("calendar") != null) {
    console.log(events);
    var calendarEl = document.getElementById("calendar");

    let calendar = new Calendar(calendarEl, {
        plugins: [timeGridPlugin],
        initialView: "timeGridWeek",
        headerToolbar: {
            left: "prev,next",
            center: "title",
            right: "",
        },
        // 終わってないのはallday,色とかも変える
        events: events,
    }
    );
    
    calendar.render();
}