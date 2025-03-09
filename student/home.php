<?php
include('db_connect.php'); // Include the database connection

// Function to add ordinal suffixes to numbers
function ordinal_suffix1($num) {
    $num = $num % 100; // Protect against large numbers
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

$query = "SELECT 
            e.evaluation_id,
            e.subject_id,
            sub.subject,
            std.firstname AS 'First Name',
            std.lastname AS 'Last Name',
            ea.rate AS 'Eva',
            ql.question,
            eval_counts.rate_5_count,
            eval_counts.rate_4_count,
            eval_counts.rate_3_count,
            eval_counts.rate_2_count,
            eval_counts.rate_1_count
          FROM 
            evaluation_list e
          INNER JOIN 
            subject_list sub ON e.subject_id = sub.id
          INNER JOIN 
            student_list std ON e.student_id = std.id
          INNER JOIN 
            evaluation_answers ea ON e.evaluation_id = ea.evaluation_id
          INNER JOIN 
            question_list ql ON ea.question_id = ql.id
          LEFT JOIN 
            (SELECT 
                 question_id,
                 SUM(CASE WHEN rate = 5 THEN 1 ELSE 0 END) AS rate_5_count,
                 SUM(CASE WHEN rate = 4 THEN 1 ELSE 0 END) AS rate_4_count,
                 SUM(CASE WHEN rate = 3 THEN 1 ELSE 0 END) AS rate_3_count,
                 SUM(CASE WHEN rate = 2 THEN 1 ELSE 0 END) AS rate_2_count,
                 SUM(CASE WHEN rate = 1 THEN 1 ELSE 0 END) AS rate_1_count
             FROM 
                 evaluation_answers
             GROUP BY 
                 question_id) AS eval_counts ON ea.question_id = eval_counts.question_id;";

$result = mysqli_query($conn, $query);
$data = [];
while($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}
?>
<?php
// Ensure session is started
//session_start();

// Temporarily override the session value
$_SESSION['login_name'] = 'aman';
?>
<div class="col-12">
    <div class="card">
        <div class="card-body">
            Welcome <?php echo $_SESSION['login_name'] ?>!
            <br>
            <div class="col-md-5">
                <div class="callout callout-info">
                <h5><b>Academic Year: 2025 - 2026 6th Semester</b></h5>
                <h6><b>Evaluation Status: <?php echo $astat[$_SESSION['academic']['status']] ?></b></h6>
                </div>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <div style="width: 45%;">
                    <canvas id="evaluationChart"></canvas>
                </div>
                <div style="width: 45%;">
                    <canvas id="evaluationChart2"></canvas>
                </div>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <div style="width: 45%; height: 50%;">
                    <canvas id="pieChart"></canvas>
                </div>
                <div style="width: 45%; height: 50%;">
                    <canvas id="brownianBridgeChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const data = <?php echo json_encode($data); ?>;
    const labels = data.map(item => item.question);
    const rate5 = data.map(item => item.rate_5_count);
    const rate4 = data.map(item => item.rate_4_count);
    const rate3 = data.map(item => item.rate_3_count);
    const rate2 = data.map(item => item.rate_2_count);
    const rate1 = data.map(item => item.rate_1_count);

    // Bar and Line Chart
    const ctx = document.getElementById('evaluationChart').getContext('2d');
    const evaluationChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Rate 5 (Bar)',
                    data: rate5,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    type: 'bar'
                },
                {
                    label: 'Rate 4 (Line)',
                    data: rate4,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    type: 'line',
                    fill: false,
                    tension: 0 // Straight line
                },
                {
                    label: 'Rate 3 (Spline)',
                    data: rate3,
                    backgroundColor: 'rgba(255, 206, 86, 0.2)',
                    borderColor: 'rgba(255, 206, 86, 1)',
                    borderWidth: 1,
                    type: 'line',
                    fill: false,
                    tension: 0.4 // Curved line
                },
                {
                    label: 'Rate 2 (Bar)',
                    data: rate2,
                    backgroundColor: 'rgba(255, 159, 64, 0.2)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1,
                    type: 'bar'
                },
                {
                    label: 'Rate 1 (Line)',
                    data: rate1,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1,
                    type: 'line',
                    fill: false,
                    tension: 0.4 // Curved line
                }
            ]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            }
        }
    });

    // Second Bar Chart
    const ctx2 = document.getElementById('evaluationChart2').getContext('2d');
    const evaluationChart2 = new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Rate 5',
                    data: rate5,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                },
                {
                    label: 'Rate 4',
                    data: rate4,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                },
                {
                    label: 'Rate 3',
                    data: rate3,
                    backgroundColor: 'rgba(255, 206, 86, 0.2)',
                    borderColor: 'rgba(255, 206, 86, 1)',
                    borderWidth: 1,
                },
                {
                    label: 'Rate 2',
                    data: rate2,
                    backgroundColor: 'rgba(255, 159, 64, 0.2)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1,
                },
                {
                    label: 'Rate 1',
                    data: rate1,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1,
                }
            ]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            }
        }
    });

    // Pie Chart
    const pieData = {
        labels: ['Rate 5', 'Rate 4', 'Rate 3', 'Rate 2', 'Rate 1'],
        datasets: [{
            data: [
                rate5.reduce((a, b) => a + b, 0), // Sum of all rate 5 counts
                rate4.reduce((a, b) => a + b, 0), // Sum of all rate 4 counts
                rate3.reduce((a, b) => a + b, 0), // Sum of all rate 3 counts
                rate2.reduce((a, b) => a + b, 0), // Sum of all rate 2 counts
                rate1.reduce((a, b) => a + b, 0)  // Sum of all rate 1 counts
            ],
            backgroundColor: [
                'rgba(75, 192, 192, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(255, 159, 64, 0.2)',
                'rgba(255, 99, 132, 0.2)'
            ],
            borderColor: [
                'rgba(75, 192, 192, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(255, 159, 64, 1)',
                'rgba(255, 99, 132, 1)'
            ],
            borderWidth: 1
        }]
    };

    const pieCtx = document.getElementById('pieChart').getContext('2d');
    const pieChart = new Chart(pieCtx, {
        type: 'pie',
        data: pieData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            const value = pieData.datasets[tooltipItem.datasetIndex].data[tooltipItem.dataIndex];
                            return `${pieData.labels[tooltipItem.dataIndex]}: ${value}`;
                        }
                    }
                }
            }
        }
    });

    // Brownian Bridge Chart (Placeholder)
    const brownianData = {
        labels: Array.from({length: 100}, (_, i) => i + 1),
        datasets: [{
            label: 'Brownian Bridge',
            data: Array.from({length: 100}, (_, i) => Math.random() - 0.5),
            backgroundColor: 'rgba(153, 102, 255, 0.2)',
            borderColor: 'rgba(153, 102, 255, 1)',
            borderWidth: 1,
            fill: false,
            tension: 0.1
        }]
    };

    const brownianCtx = document.getElementById('brownianBridgeChart').getContext('2d');
    const brownianBridgeChart = new Chart(brownianCtx, {
        type: 'line',
        data: brownianData,
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            }
        }
    });
</script>
<section class="about_us pt-5">
    <div class="hstack discount-rolling">
        <!-- Repeating messages with "poppins-light" class -->
        <h1 class="poppins-light pe-5 ps-1 py-1">5TH <span>SEM</span> Result declared!</h1>
        <h1 class="poppins-light pe-5 ps-1 py-1">Maintain <span>the</span> Attendance!</h1>
        <h1 class="poppins-light pe-5 ps-1 py-1">6TH <span>SEM</span> Internal-Exam-Alert!</h1>
      <h1 class="poppins-light pe-5 ps-1 py-1"><span></span> Assignment Checking!</h1>
        <h1 class="poppins-light pe-5 ps-1 py-1"><span></span> !! ALERT !!</h1>
    </div>
</section>

<style>
    .about_us {
        position: relative;
        width: 100%;
        overflow: hidden;
    }

    .discount-rolling {
        display: flex;
        animation: scroll-left 20s linear infinite;
    }

    .poppins-light {
        font-family: 'Poppins', sans-serif;
        font-weight: 300;
        white-space: nowrap;
        margin-right: 20px;
        padding: 10px 20px;
        border-radius: 5px;
        background: linear-gradient(180deg, #FF8C00 0%, #FF4500 100%);
        color: white;
        text-shadow: 2px 2px rgba(0, 0, 0, 0.5);
        box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.25);
    }

    @keyframes scroll-left {
        from {
            transform: translateX(100%);
        }
        to {
            transform: translateX(-100%);
        }
    }
</style>

<!-- Calendar Container -->
<div id="calendar-container">
    <div id="calendar"></div>
</div>
<!-- Modal for Adding/Editing/Deleting Events -->
<!-- Modal for Adding/Editing/Deleting Events -->
<div class="overlay" id="overlay"></div>
<div id="event-modal">
    <h3 id="modal-title">Add Event</h3>
    <form id="event-form">
        <label for="title">Event Title:</label><br>
        <input type="text" id="title" name="title" required><br><br>
        <div style="display: flex; gap: 10px; justify-content: flex-end;">
            <button type="submit">Save Event</button>
            <button type="button" id="cancel-button">X</button>
            <button type="button" id="delete-button" style="display: none;">Delete Event</button>
        </div>
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

    /* Cancel button styling inside the box */
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
        z-index: 1; /* Ensure it stays above the image and below the skip button */
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
        let countdown = 3; // Countdown timer in seconds

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
