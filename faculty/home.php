<?php 
include('db_connect.php');

function ordinal_suffix1($num) {
    $num = $num % 100; // protect against large numbers
    if ($num < 11 || $num > 13) {
        switch ($num % 10) {
            case 1: return $num . 'st';
            case 2: return $num . 'nd';
            case 3: return $num . 'rd';
        }
    }
    return $num . 'th';
}

$astat = array("Not Yet Started", "On-going", "Closed");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Panel with Full-Page Calendar</title>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.7/index.global.min.css" rel="stylesheet" />
    <style>
        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            font-family: Arial, sans-serif;
            background-color: white;
        }

        .header {
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.9);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .header h5, .header h6 {
            margin: 0;
        }

        #calendar-container {
            width: 100%;
            height: calc(100% - 120px); /* Adjust height to leave space for the header */
            display: flex;
            justify-content: center;
            align-items: center;
        }

        #calendar {
            width: 95%;
            height: 100%;
            margin: 0 auto;
        }

        #event-modal {
            display: none;
            position: fixed;
            top: 30%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            z-index: 1000;
        }

        #event-modal button {
            margin-top: 10px;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
    </style>
</head>
<div class="col-md-5">
                <div class="callout callout-info">
                <h5><b>Academic Year: 2025 - 2026 6th Semester</b></h5>
                <h6><b>Evaluation Status: <?php echo $astat[$_SESSION['academic']['status']] ?></b></h6>
                </div>
            </div>

<!-- Calendar Container -->
<div id="calendar-container">
    <div id="calendar"></div>
</div>
<!-- Modal for Adding/Editing/Deleting Events -->
<div class="overlay" id="overlay"></div>
<div id="event-modal">
    <h3 id="modal-title">Add Event</h3>
    <form id="event-form">
        <label for="title">Event Title:</label><br>
        <input type="text" id="title" name="title" required><br><br>
        <button type="submit">Save Event</button>
        <button type="button" id="cancel-button">X</button>
        <button type="button" id="delete-button" style="display: none;">Delete Event</button>
    </form>
</div>
<style>
    /* Modal Styles */
    #event-modal {
        display: none;
        position: fixed;
        top: 20%;
        left: 50%;
        transform: translateX(-50%);
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        z-index: 999;
        width: 300px;
    }

    .overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 998;
    }

    form {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    button {
        padding: 8px 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    button[type="submit"] {
        background-color: #4CAF50;
        color: white;
    }

    #cancel-button {
        background-color: #f44336;
        color: white;
    }

    #delete-button {
        background-color: #ff9800;
        color: white;
    }
</style>


<!-- Include FullCalendar -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.7/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const calendarEl = document.getElementById('calendar');
        const eventModal = document.getElementById('event-modal');
        const overlay = document.getElementById('overlay');
        const eventForm = document.getElementById('event-form');
        const cancelButton = document.getElementById('cancel-button');
        const deleteButton = document.getElementById('delete-button');
        const modalTitle = document.getElementById('modal-title');
        let calendar;

        // Initialize FullCalendar
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            editable: true,
            selectable: true,
            dateClick: function (info) {
                openModal(info.dateStr); // Open the modal for a new event
            },
            eventClick: function (info) {
                openModal(info.event.startStr, info.event); // Open the modal for editing an event
            },
            events: [
                { id: '1', title: 'Sample Event', start: '2025-01-18' },
                { id: '2', title: 'Another Event', start: '2025-01-20', end: '2025-01-22' }
            ]
        });

        calendar.render();

        // Open the modal for adding or editing an event
        function openModal(date, event = null) {
            if (event) {
                // If editing an existing event
                modalTitle.innerHTML = 'Edit Event';
                eventForm.dataset.eventId = event.id;
                document.getElementById('title').value = event.title;
                deleteButton.style.display = 'inline'; // Show the Delete button
            } else {
                // If adding a new event
                modalTitle.innerHTML = 'Add Event';
                eventForm.removeAttribute('data-event-id');
                document.getElementById('title').value = '';
                deleteButton.style.display = 'none'; // Hide the Delete button
            }

            eventForm.dataset.date = date;
            eventModal.style.display = 'block';
            overlay.style.display = 'block';
        }

        // Close the modal
        function closeModal() {
            eventModal.style.display = 'none';
            overlay.style.display = 'none';
        }

        // Handle event form submission (save event)
        eventForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const title = document.getElementById('title').value;
            const date = eventForm.dataset.date;

            if (title) {
                const eventId = eventForm.dataset.eventId;

                if (eventId) {
                    // If editing an event, update it
                    const event = calendar.getEventById(eventId);
                    if (event) {
                        event.setProp('title', title);
                    }
                } else {
                    // If adding a new event, add it with a unique ID
                    const newEvent = calendar.addEvent({
                        id: String(Date.now()), // Generate a unique ID using timestamp
                        title: title,
                        start: date
                    });
                    eventForm.dataset.eventId = newEvent.id; // Save the ID for future edits or deletion
                }

                closeModal();
                eventForm.reset();
            }
        });

        // Cancel button functionality
        cancelButton.addEventListener('click', function () {
            closeModal();
            eventForm.reset(); // Reset the form if cancel is clicked
        });

        // Delete button functionality
        deleteButton.addEventListener('click', function () {
            const eventId = eventForm.dataset.eventId;
            if (eventId) {
                const event = calendar.getEventById(eventId);
                if (event) {
                    event.remove(); // Remove the event from the calendar
                }
            }
            closeModal();
            eventForm.reset(); // Reset the form after deletion
        });
    });
</script>

<!-- ---------------------------------------------------------Ad Container ------------------------->
<!-- Ad Container -->
<div id="ad-container">
    <div id="ad-box">
        <button id="cancel-button">✕</button>
        <img src="https://th.bing.com/th?id=OIP.NcgXQP1BmRecQaIKakAYwgAAAA&w=212&h=212&c=8&rs=1&qlt=90&o=6&pid=3.1&rm=2" alt="Ad Image" id="ad-image" />
        <button id="skip-ad-button" disabled>Skip Ad (4s)</button>
    </div>
</div>

<!-- Styles -->
<style>
    /* Full-screen overlay for the ad container */
    #ad-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0, 0, 0, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 999;
    }

    /* Ad box styling */
    #ad-box {
        position: relative;
        width: 400px;
        height: 400px;
        background-color: white;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        text-align: center;
        overflow: hidden;
    }

    /* Ad image styling */
    #ad-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: 0;
    }

    /* Cancel button styling */
    #cancel-button {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 30px;
        height: 30px;
        background-color: red;
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        font-size: 16px;
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 2; /* Ensure the button is on top of other elements */
    }

    #cancel-button:hover {
        background-color: darkred;
    }

    /* Skip Ad button styling */
    #skip-ad-button {
        position: absolute;
        bottom: 10px;
        padding: 10px 20px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: not-allowed;
        transition: opacity 0.3s;
        opacity: 0.7;
    }

    #skip-ad-button.enabled {
        cursor: pointer;
        opacity: 1;
    }
</style>

<!-- Script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const adContainer = document.getElementById('ad-container');
        const cancelButton = document.getElementById('cancel-button');
        const skipButton = document.getElementById('skip-ad-button');
        let countdown = 5; // Countdown timer in seconds

        // Enable the "Skip Ad" button after the countdown
        const timer = setInterval(() => {
            countdown--;
            if (countdown <= 0) {
                clearInterval(timer);
                skipButton.disabled = false;
                skipButton.classList.add('enabled');
                skipButton.textContent = 'Skip Ad';
            } else {
                skipButton.textContent = `Skip Ad (${countdown}s)`;
            }
        }, 700);

        // Hide the ad container when the cancel button is clicked
        cancelButton.addEventListener('click', () => {
            adContainer.style.display = 'none';
        });

        // Hide the ad container when the "Skip Ad" button is clicked
        skipButton.addEventListener('click', () => {
            adContainer.style.display = 'none';
        });
    });
</script>
<!-- Chatbot configuration script -->
<script>
    window.embeddedChatbotConfig = {
        chatbotId: "21occLWrcWKtSl2NAQCZF",
        domain: "www.chatbase.co"
    }
</script>

<!-- Chatbot embed script -->
<script
    src="https://www.chatbase.co/embed.min.js"
    chatbotId="21occLWrcWKtSl2NAQCZF"
    domain="www.chatbase.co"
    defer>
</script>
