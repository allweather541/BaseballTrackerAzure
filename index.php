<?php
// --- DATABASE CONNECTION SETTINGS ---
require_once 'config.php';

$message = "";

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Connect to Azure SQL using ODBC
        $conn = new PDO("odbc:Driver={ODBC Driver 18 for SQL Server};Server=$serverName;Database=$database;Encrypt=yes;TrustServerCertificate=no;Connection Timeout=30;", $uid, $pwd);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Grab the data from the form
        $playerName = $_POST['playerName'];
        $pitchCount = $_POST['pitchCount'];
        $strikeouts = $_POST['strikeouts'];
        $walks = $_POST['walks'];
        $innings = $_POST['innings'];
        $earnedRuns = $_POST['earnedRuns'];

        // Securely insert it into the database
        $sql = "INSERT INTO PlayerStats (PlayerName, PitchCount, Strikeouts, Walks, InningsPitched, EarnedRuns) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$playerName, $pitchCount, $strikeouts, $walks, $innings, $earnedRuns]);

        $message = "<div class='alert alert-success'>✅ Success! $playerName's stats were saved securely to the Azure SQL Database!</div>";
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'>❌ Database Error: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baseball Stat Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                
                <?= $message ?>

                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h2>⚾ Baseball Stat Tracker</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="index.php">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Player Name</label>
                                <input type="text" name="playerName" class="form-control" placeholder="Enter player name" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Pitch Count</label>
                                    <input type="number" name="pitchCount" class="form-control" placeholder="0" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Strikeouts</label>
                                    <input type="number" name="strikeouts" class="form-control" placeholder="0" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Walks</label>
                                    <input type="number" name="walks" class="form-control" placeholder="0" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Innings Pitched</label>
                                    <input type="number" step="0.1" name="innings" class="form-control" placeholder="0.0" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Earned Runs</label>
                                    <input type="number" name="earnedRuns" class="form-control" placeholder="0" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success w-100 fs-5">Submit Entry</button>
                        </form>
                    </div>
                    <div class="card-footer text-muted text-center">
                        <small id="vm-id">Running on Cloud Server</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
