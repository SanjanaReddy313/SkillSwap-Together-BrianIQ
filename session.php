<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "config/database.php";

$user_id = intval($_SESSION["user_id"]);
$user_name = $_SESSION["user_name"] ?? "User";

if (!isset($_GET["booking_id"])) {
    die("Booking ID is missing.");
}

$booking_id = intval($_GET["booking_id"]);


/* =====================================================
   GET BOOKING - CORRECT DATABASE COLUMNS
===================================================== */

$stmt = $conn->prepare(
    "SELECT
        b.id,
        b.user_id,
        b.other_user_id,
        b.session_date,
        b.session_time,
        b.duration,
        b.message,
        b.status,
        u1.name AS user_one_name,
        u2.name AS user_two_name
     FROM bookings AS b
     INNER JOIN users AS u1 ON b.user_id = u1.id
     INNER JOIN users AS u2 ON b.other_user_id = u2.id
     WHERE b.id = ?
       AND (b.user_id = ? OR b.other_user_id = ?)"
);

if (!$stmt) {
    die("Database query error: " . $conn->error);
}

$stmt->bind_param("iii", $booking_id, $user_id, $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Booking not found or you do not have access to this session.");
}

$booking = $result->fetch_assoc();
$stmt->close();


/* =====================================================
   FIND OTHER USER
===================================================== */

if ((int)$booking["user_id"] === $user_id) {
    $other_user_id = (int)$booking["other_user_id"];
    $other_user_name = $booking["user_two_name"];
} else {
    $other_user_id = (int)$booking["user_id"];
    $other_user_name = $booking["user_one_name"];
}

/* =====================================================
   GET SHARED MATERIALS
===================================================== */

$materials = [];

$materials_stmt = $conn->prepare(
    "SELECT
        file_name,
        file_path,
        uploaded_by,
        created_at
     FROM session_materials
     WHERE booking_id = ?
     ORDER BY created_at DESC"
);

if ($materials_stmt) {

    $materials_stmt->bind_param(
        "i",
        $booking_id
    );

    $materials_stmt->execute();

    $materials_result =
        $materials_stmt->get_result();

    while ($row = $materials_result->fetch_assoc()) {
        $materials[] = $row;
    }

    $materials_stmt->close();
}


/* =====================================================
   SAME ROOM FOR BOTH USERS
===================================================== */

$room_name = "SkillSwap-Session-" . $booking_id;

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>SkillSwap - Learning Session</title>


<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    background: #f4f7fb;
    color: #1f2937;
}


/* HEADER */

.header {
    background: #2563eb;
    color: white;
    padding: 18px 25px;

    display: flex;
    justify-content: space-between;
    align-items: center;

    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.header h1 {
    margin: 0;
    font-size: 22px;
}

.back-btn {
    color: white;
    text-decoration: none;
    background: rgba(255,255,255,0.15);
    padding: 9px 15px;
    border-radius: 8px;
}


/* MAIN */

.container {
    width: 95%;
    max-width: 1400px;
    margin: 25px auto;
}


/* SESSION INFO */

.session-info {
    background: white;
    padding: 20px;
    border-radius: 14px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    margin-bottom: 20px;
}

.session-info h2 {
    margin-top: 0;
    color: #2563eb;
}

.info-grid {
    display: grid;
    grid-template-columns:
        repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
}

.info-box {
    background: #f8fafc;
    padding: 13px;
    border-radius: 10px;
}

.info-box strong {
    display: block;
    margin-bottom: 5px;
}


/* MAIN GRID */

.main-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 300px;
    gap: 20px;
}


/* VIDEO */

.video-card {
    background: #111827;
    border-radius: 14px;
    overflow: hidden;
    min-height: 600px;

    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

.video-header {
    background: #1f2937;
    color: white;

    padding: 15px 18px;

    display: flex;
    justify-content: space-between;
    align-items: center;
}

.video-header h3 {
    margin: 0;
}

.meeting-status {
    color: #86efac;
    font-size: 13px;
}


/* JITSI */

#jitsiMeeting {
    width: 100%;
    height: 600px;
    background: #111827;
}


/* SIDE PANEL */

.side-panel {
    display: flex;
    flex-direction: column;
    gap: 18px;
}


/* TOOLS */

.tools-card,
.material-card {
    background: white;
    padding: 18px;
    border-radius: 14px;

    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

.tools-card h3,
.material-card h3 {
    margin-top: 0;
    color: #2563eb;
}

.tool-btn {
    width: 100%;
    border: none;

    background: #eff6ff;
    color: #1d4ed8;

    padding: 12px;
    margin-bottom: 10px;

    border-radius: 9px;

    cursor: pointer;

    font-size: 14px;
    font-weight: 600;
}

.tool-btn:hover {
    background: #dbeafe;
}

.end-btn {
    width: 100%;
    border: none;

    background: #fee2e2;
    color: #dc2626;

    padding: 12px;

    border-radius: 9px;

    cursor: pointer;

    font-weight: 600;
}


/* MATERIAL */

.material {
    background: #f8fafc;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 8px;
}

.material a {
    color: #2563eb;
    text-decoration: none;
    font-weight: 600;
}


/* MOBILE */

@media (max-width: 900px) {

    .main-grid {
        grid-template-columns: 1fr;
    }

    #jitsiMeeting {
        height: 550px;
    }
}

@media (max-width: 600px) {

    .header h1 {
        font-size: 18px;
    }

    .container {
        width: 96%;
    }

    #jitsiMeeting {
        height: 500px;
    }
}

</style>

</head>


<body>


<!-- =====================================================
     HEADER
===================================================== -->

<div class="header">

    <h1>🎓 SkillSwap Learning Session</h1>

    <a href="my_bookings.php"
       class="back-btn">

        ← My Bookings

    </a>

</div>


<div class="container">


<!-- =====================================================
     SESSION DETAILS
===================================================== -->

<div class="session-info">

    <h2>

        👥 Session with
        <?php
        echo htmlspecialchars(
            $other_user_name
        );
        ?>

    </h2>


    <div class="info-grid">


        <div class="info-box">

            <strong>Your Name</strong>

            <?php
            echo htmlspecialchars(
                $user_name
            );
            ?>

        </div>


        <div class="info-box">

            <strong>Other User</strong>

            <?php
            echo htmlspecialchars(
                $other_user_name
            );
            ?>

        </div>


        <div class="info-box">

            <strong>Date</strong>

            <?php
            echo htmlspecialchars(
                $booking["session_date"]
            );
            ?>

        </div>


        <div class="info-box">

            <strong>Time</strong>

            <?php
            echo htmlspecialchars(
                $booking["session_time"]
            );
            ?>

        </div>


        <div class="info-box">

            <strong>Duration</strong>

            <?php
            echo htmlspecialchars(
                $booking["duration"]
            );
            ?>

            minutes

        </div>

    </div>

</div>



<!-- =====================================================
     VIDEO + TOOLS
===================================================== -->

<div class="main-grid">


    <!-- VIDEO MEETING -->

    <div class="video-card">

        <div class="video-header">

            <h3>
                📹 Live Learning Session
            </h3>

            <span class="meeting-status">
                ● Connected Room
            </span>

        </div>


        <div class="video-call-area">
    <div class="video-box">
        <h3>Sanjana</h3>
        <video id="localVideo" autoplay playsinline muted></video>
    </div>

    <div class="video-box">
        <h3>Test User / Other Participant</h3>
        <video id="remoteVideo" autoplay playsinline></video>
    </div>

    <div class="video-controls">
        <button type="button" onclick="startCamera()">Start Camera</button>
        <button type="button" onclick="endCall()">End Call</button>
    </div>
</div>

<script src="js/video-call.js"></script>

    </div>



    <!-- SIDE -->

    <div class="side-panel">


        <!-- SESSION TOOLS -->

        <div class="tools-card">

            <h3>Session Tools</h3>


            <button
                class="tool-btn"
                onclick="toggleMicrophone()">

                🎤 Microphone

            </button>


            <button
                class="tool-btn"
                onclick="toggleCamera()">

                📹 Camera

            </button>


            <button
                class="tool-btn"
                onclick="openChat()">

                💬 Chat

            </button>


            <button
                class="tool-btn"
                onclick="openNotes()">

                📝 Session Notes

            </button>


            <button
                class="tool-btn"
                onclick="shareMaterial()">

                📎 Share Material

            </button>


            <button
                class="end-btn"
                onclick="confirmEndSession()">

                🔴 End Session

            </button>

        </div>



        <!-- MATERIALS -->

        <div class="material-card">

            <h3>
                📚 Shared Materials
            </h3>


            <?php if (count($materials) == 0): ?>

                <p>
                    No materials shared yet.
                </p>

            <?php else: ?>


                <?php foreach ($materials as $material): ?>

                    <div class="material">

                        📄

                        <?php
                        echo htmlspecialchars(
                            $material["file_name"]
                        );
                        ?>

                        <br><br>

                        <a
                            href="<?php
                            echo htmlspecialchars(
                                $material["file_path"]
                            );
                            ?>"
                            target="_blank">

                            Open / Download

                        </a>

                    </div>

                <?php endforeach; ?>


            <?php endif; ?>

        </div>


    </div>

</div>

</div>



<!-- =====================================================
     JITSI MEET
===================================================== -->

 
<script>
let sessionStream = null;

// Start the camera and microphone
async function startCamera() {
    try {
        sessionStream = await navigator.mediaDevices.getUserMedia({
            video: true,
            audio: true
        });

        document.getElementById("localVideo").srcObject = sessionStream;
        alert("Camera and microphone started.");
    } catch (error) {
        alert("Could not access camera or microphone. Please allow permission.");
        console.error(error);
    }
}

// Microphone button
function toggleMicrophone() {
    if (!sessionStream) {
        startCamera();
        return;
    }

    const tracks = sessionStream.getAudioTracks();

    if (tracks.length === 0) {
        alert("No microphone is available.");
        return;
    }

    tracks.forEach(track => track.enabled = !track.enabled);
}

// Camera button
function toggleCamera() {
    if (!sessionStream) {
        startCamera();
        return;
    }

    const tracks = sessionStream.getVideoTracks();

    if (tracks.length === 0) {
        alert("No camera is available.");
        return;
    }

    tracks.forEach(track => track.enabled = !track.enabled);
}

// Open chat
function openChat() {
    window.location.href = "chat.php?user_id=<?php echo $other_user_id; ?>";
}

// Session notes
function openNotes() {
    const notes = prompt("Write your session notes:");

    if (notes !== null && notes.trim() !== "") {
        localStorage.setItem(
            "SkillSwapNotes_<?php echo $booking_id; ?>",
            notes
        );
        alert("Session notes saved.");
    }
}

// Share learning materials
function shareMaterial() {
    window.location.href =
        "upload_material.php?booking_id=<?php echo $booking_id; ?>";
}

// End session
function confirmEndSession() {
    if (confirm("Are you sure you want to end this session?")) {
        if (sessionStream) {
            sessionStream.getTracks().forEach(track => track.stop());
        }

        window.location.href =
            "end_session.php?booking_id=<?php echo $booking_id; ?>";
    }
}
</script>


</body>

</html>