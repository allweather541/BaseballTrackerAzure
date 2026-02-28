<?php
// --- DATABASE CONNECTION SETTINGS ---
require_once 'config.php';

$message = "";
$searchResults = "";

try {
    // Connect to Azure SQL using ODBC
    $conn = new PDO("odbc:Driver={ODBC Driver 18 for SQL Server};Server=$serverName;Database=$database;Encrypt=yes;TrustServerCertificate=no;Connection Timeout=30;", $uid, $pwd);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --- HANDLE SUBMITTING NEW STATS ---
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['playerName'])) {
        $playerName = $_POST['playerName'];
        $pitchCount = $_POST['pitchCount'];
        $strikeouts = $_POST['strikeouts'];
        $walks = $_POST['walks'];
        $innings = $_POST['innings'];
        $earnedRuns = $_POST['earnedRuns'];

        $sql = "INSERT INTO PlayerStats (PlayerName, PitchCount, Strikeouts, Walks, InningsPitched, EarnedRuns) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$playerName, $pitchCount, $strikeouts, $walks, $innings, $earnedRuns]);

        $message = "<div class='alert alert-success'>✅ Success! $playerName's stats were saved!</div>";
    }

    // --- HANDLE SEARCHING FOR STATS ---
    if ($_SERVER["REQUEST_METHOD"] == "GET" && !empty($_GET['searchName'])) {
        $searchName = $_GET['searchName'];
        
        // We use "LIKE" so if you search "Babe", it finds "Babe Ruth"
        $sql = "SELECT * FROM PlayerStats WHERE PlayerName LIKE ? ORDER BY SubmissionDate DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['%' . $searchName . '%']);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($rows) > 0) {
            $searchResults .= "<div class='table-responsive'><table class='table table-striped table-bordered mt-3'>
                                <thead class='table-dark'><tr><th>Name</th><th>Pitches</th><th>Strikeouts</th><th>Walks</th><th>Innings</th><th>ER</th><th>Date</th></tr></thead><tbody>";
            foreach ($rows as $row) {
                // Format the database timestamp into a readable date
                $date = date("M d, Y", strtotime($row['SubmissionDate']));
                $searchResults .= "<tr>
                    <td>" . htmlspecialchars($row['PlayerName']) . "</td>
                    <td>" . htmlspecialchars($row['PitchCount']) . "</td>
                    <td>" . htmlspecialchars($row['Strikeouts']) . "</td>
                    <td>" . htmlspecialchars($row['Walks']) . "</td>
                    <td>" . htmlspecialchars($row['InningsPitched']) . "</td>
                    <td>" . htmlspecialchars($row['EarnedRuns']) . "</td>
                    <td>" . $date . "</td>
                  </tr>";
            }
            $searchResults .= "</tbody></table></div>";
        } else {
            $searchResults = "<div class='alert alert-warning mt-3'>No stats found for '" . htmlspecialchars($searchName) . "'.</div>";
        }
    }
} catch (PDOException $e) {
    $message = "<div class='alert alert-danger'>❌ Database Error: " . $e->getMessage() . "</div>";
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

    <div class="w-100 mb-4">
        <img src="https://baseballstorageacct.blob.core.windows.net/websiteblob/BaseballTracker.png" alt="Baseball Header" style="width: 100%; height: auto; display: block;">
    </div>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                
                <?= $message ?>

                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white text-center">
                        <h2>⚾ Add Player Stats</h2>
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
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header bg-secondary text-white text-center">
                        <h3>🔍 Lookup Player Stats</h3>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="index.php">
                            <div class="input-group">
                                <input type="text" name="searchName" class="form-control" placeholder="Search by player name (e.g., Babe Ruth)" required>
                                <button type="submit" class="btn btn-dark">Search</button>
                            </div>
                        </form>
                        
                        <?= $searchResults ?>
                        
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
