<?php
include("config.php");

// Get counts for dashboard
$playersCount = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as count FROM player WHERE playerStatus = 'Active'"))['count'];
$clubsCount = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as count FROM club"))['count'];
$nationsCount = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as count FROM nationality"))['count'];

// Handle new player form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $playerName = mysqli_real_escape_string($connection, $_POST['playerName']);
    $playerUrl = mysqli_real_escape_string($connection, $_POST['playerUrl']);
    $nationalityId = mysqli_real_escape_string($connection, $_POST['nationalityId']);
    $clubId = mysqli_real_escape_string($connection, $_POST['clubId']);
    $playerPosition = mysqli_real_escape_string($connection, $_POST['playerPosition']);
    $playerRating = mysqli_real_escape_string($connection, $_POST['playerRating']);

    $sql = "INSERT INTO player (playerName, playerUrl, nationalityId, clubId, playerPosition, playerRating, playerStatus)
            VALUES ('$playerName', '$playerUrl', $nationalityId, $clubId, '$playerPosition', $playerRating, 'Active')";

    if (mysqli_query($connection, $sql)) {
        echo "<div class='alert alert-success'>New player added successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error adding new player: " . mysqli_error($connection) . "</div>";
    }
}

// Handle player deletion
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $playerId = mysqli_real_escape_string($connection, $_GET['id']);

    // Delete the player's associated stats
    $deleteStatsQuery = "DELETE FROM goalkeeperStats WHERE playerId = $playerId";
    $deleteStatsQuery .= "; DELETE FROM playerFieldStats WHERE playerId = $playerId";
    if (mysqli_multi_query($connection, $deleteStatsQuery)) {
        // Delete the player
        $deletePlayerQuery = "DELETE FROM player WHERE playerId = $playerId";
        if (mysqli_query($connection, $deletePlayerQuery)) {
            header("Location: index.php?section=players");
            exit;
        } else {
            echo "<div class='alert alert-danger'>Error deleting player: " . mysqli_error($connection) . "</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Error deleting player: " . mysqli_error($connection) . "</div>";
    }
} else {
    header("Location: index.php?section=players");
    exit;
}

// Fetch players if section is players
if(isset($_GET['section']) && $_GET['section'] == 'players') {
    $query = "
        SELECT 
            p.*,
            c.clubName,
            c.clubUrl,
            n.nationalityName,
            n.nationalityUrl,
            CASE 
                WHEN p.playerPosition = 'GK' THEN gs.diving
                ELSE fs.pace
            END as stat1,
            CASE 
                WHEN p.playerPosition = 'GK' THEN gs.handling
                ELSE fs.shooting
            END as stat2,
            CASE 
                WHEN p.playerPosition = 'GK' THEN gs.kicking
                ELSE fs.passing
            END as stat3,
            CASE 
                WHEN p.playerPosition = 'GK' THEN gs.reflexes
                ELSE fs.dribbling
            END as stat4,
            CASE 
                WHEN p.playerPosition = 'GK' THEN gs.speed
                ELSE fs.defending
            END as stat5,
            CASE 
                WHEN p.playerPosition = 'GK' THEN gs.positioning
                ELSE fs.physical
            END as stat6
        FROM player p
        LEFT JOIN club c ON p.clubId = c.clubId
        LEFT JOIN nationality n ON p.nationalityId = n.nationalityId
        LEFT JOIN goalkeeperStats gs ON p.playerId = gs.playerId
        LEFT JOIN playerFieldStats fs ON p.playerId = fs.playerId
        WHERE p.playerStatus = 'Active'
    ";

    // Apply position filter if set
    if(isset($_GET['position']) && !empty($_GET['position'])) {
        $position = mysqli_real_escape_string($connection, $_GET['position']);
        $query .= " AND p.playerPosition = '$position'";
    }

    $query .= " ORDER BY p.playerRating DESC";
    $result = mysqli_query($connection, $query);
}

$videoPath = "../src/background.mp4";
$videoExists = file_exists($videoPath);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FUT Builder</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php if ($videoExists): ?>
        <video class="bg-video" autoplay muted loop>
            <source src="<?php echo htmlspecialchars($videoPath); ?>" type="video/mp4">
        </video>
    <?php endif; ?>

    <!-- Navigation -->
    <nav class="nav">
        <div class="nav-content">
            <div class="nav-left">
                <span class="nav-brand">⚽ FUT BUILDER</span>
            </div>
            <div class="nav-right">
                <span class="user-name">Welcome, Admin</span>
                <button class="btn btn-red">Logout</button>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <?php if (!isset($_GET['section'])): ?>
            <!-- Dashboard View -->
            <header class="header">
                <h1 class="main-title">Sport Management System</h1>
                <p class="subtitle">Choose a section to manage</p>
            </header>

            <!-- Management Cards -->
            <div class="cards-grid">
                <!-- Players Card -->
                <div class="card">
                    <div class="icon-container icon-players">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="card-title">Player Management</h3>
                    <p class="card-text">Manage your team roster</p>
                    <a href="?section=players" class="card-button btn-players">Access Players</a>
                </div>

                <!-- Nationalities Card -->
                <div class="card">
                    <div class="icon-container icon-nations">
                        <i class="fas fa-globe"></i>
                    </div>
                    <h3 class="card-title">Nationality Management</h3>
                    <p class="card-text">Manage player nationalities</p>
                    <a href="?section=nations" class="card-button btn-nations">Access Nationalities</a>
                </div>

                <!-- Clubs Card -->
                <div class="card">
                    <div class="icon-container icon-clubs">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="card-title">Club Management</h3>
                    <p class="card-text">Manage football clubs</p>
                    <a href="?section=clubs" class="card-button btn-clubs">Access Clubs</a>
                </div>
            </div>

            <section class="stats-section">
                <div class="stats-card">
                    <h3 class="stats-title">Quick Overview</h3>
                    <div class="progress-container">
                        <div class="progress-header">
                            <span>Total Players</span>
                            <span class="stats-value"><?php echo $playersCount; ?></span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo min(($playersCount/100) * 100, 100); ?>%"></div>
                        </div>
                    </div>
                    <div class="progress-container">
                        <div class="progress-header">
                            <span>Active Clubs</span>
                            <span class="stats-value"><?php echo $clubsCount; ?></span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo min(($clubsCount/50) * 100, 100); ?>%"></div>
                        </div>
                    </div>
                </div>

                <div class="stats-card">
                    <h3 class="stats-title">Recent Activities</h3>
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon icon-players">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="activity-details">
                                <p class="activity-text">New Player Added</p>
                                <span class="activity-time">2 minutes ago</span>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon icon-clubs">
                                <i class="fas fa-sync"></i>
                            </div>
                            <div class="activity-details">
                                <p class="activity-text">Club Updated</p>
                                <span class="activity-time">1 hour ago</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if(isset($_GET['section']) && $_GET['section'] == 'players'): ?>
            <!-- Players Section -->
            <div class="section-content">
                <div class="section-header">
                    <h2>Players Management</h2>
                    <div class="header-actions">
                        <a href="index.php" class="btn btn-gray">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                        <a href="#" class="btn btn-blue" onclick="toggleAddPlayerForm()">
                            <i class="fas fa-plus"></i> Add Player
                        </a>
                    </div>
                </div>

                <div class="search-container">
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="Search player name..." class="search-input">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                    <select id="positionFilter">
                        <option value="">All Positions</option>
                        <option value="GK">Goalkeeper</option>
                        <option value="DF">Defender</option>
                        <option value="MF">Midfielder</option>
                        <option value="FW">Forward</option>
                    </select>
                    <button class="btn btn-blue" onclick="filterPlayers()">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <button class="btn btn-gray" onclick="clearFilters()">
                        <i class="fas fa-times"></i> Clear Filters
                    </button>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Player</th>
                                <th>Club</th>
                                <th>Position</th>
                                <th>Rating</th>
                                <th>Stats</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="playerTableBody">
                            <?php
                            if(isset($result) && mysqli_num_rows($result) > 0) {
                                while($player = mysqli_fetch_assoc($result)) {
                                    $statNames = $player['playerPosition'] === 'GK' 
                                        ? ['Diving', 'Handling', 'Kicking', 'Reflexes', 'Speed', 'Positioning']
                                        : ['Pace', 'Shooting', 'Passing', 'Dribbling', 'Defending', 'Physical'];
                                    
                                    $stats = [$player['stat1'], $player['stat2'], $player['stat3'], 
                                            $player['stat4'], $player['stat5'], $player['stat6']];
                            ?>
                            <tr class="player-row" data-player-id="<?php echo $player['playerId']; ?>">
                                <td>
                                    <div class="player-info">
                                        <img src="<?php echo htmlspecialchars($player['playerUrl']); ?>"
                                             alt="<?php echo htmlspecialchars($player['playerName']); ?>"
                                             class="player-image">
                                        <div class="player-details">
                                            <div class="player-name-container">
                                                <?php echo htmlspecialchars($player['playerName']); ?>
                                                <img src="<?php echo htmlspecialchars($player['nationalityUrl']); ?>"
                                                     alt="<?php echo htmlspecialchars($player['nationalityName']); ?>"
                                                     class="nationality-flag">
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="club-info">
                                        <?php if($player['clubUrl']): ?>
                                            <img src="<?php echo htmlspecialchars($player['clubUrl']); ?>"
                                                 alt="<?php echo htmlspecialchars($player['clubName']); ?>"
                                                 class="club-image">
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="position-badge <?php echo strtolower($player['playerPosition']); ?>">
                                        <?php echo $player['playerPosition']; ?>
                                    </span>
                                </td>
                                <td class="rating"><?php echo $player['playerRating']; ?></td>
                                <td>
                                    <div class="stats-grid">
                                        <?php for($i = 0; $i < 6; $i++): ?>
                                            <div class="stat-item">
                                                <div class="stat-name"><?php echo $statNames[$i]; ?></div>
                                                <div class="stat-value"><?php echo $stats[$i]; ?></div>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                </td>
                                <td class="actions">
                                    <a href="edit_player.php?id=<?php echo $player['playerId']; ?>" class="btn btn-blue btn-sm">Edit</a>
                                    <a href="delete_player.php?id=<?php echo $player['playerId']; ?>"
   class="btn btn-red btn-sm"
   onclick="return confirm('Are you sure you want to delete this player?')">Delete</a>                                </td>
                            </tr>
                            <?php
                                }
                            } else {
                                echo '<tr><td colspan="6" class="no-data">No players found</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Add Player Form -->
                <div class="add-player-modal" style="display: none;">
                <div class="modal-overlay" onclick="toggleAddPlayerForm()"></div>
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Add New Player</h3>
                        <button class="close-button" onclick="toggleAddPlayerForm()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?section=players"; ?>">
                            <div class="form-group">
                                <label for="playerName">Player Name:</label>
                                <input type="text" id="playerName" name="playerName" required>
                            </div>
                            <div class="form-group">
                                <label for="playerUrl">Player Image URL:</label>
                                <input type="text" id="playerUrl" name="playerUrl" required>
                            </div>
                            <div class="form-group">
                                <label for="nationalityId">Nationality:</label>
                                <select id="nationalityId" name="nationalityId" required>
                                    <option value="">Select Nationality</option>
                                    <?php
                                    $nationalitiesResult = mysqli_query($connection, "SELECT nationalityId, nationalityName FROM nationality");
                                    while ($nationality = mysqli_fetch_assoc($nationalitiesResult)) {
                                        echo "<option value='" . $nationality['nationalityId'] . "'>" . $nationality['nationalityName'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="clubId">Club:</label>
                                <select id="clubId" name="clubId" required>
                                    <option value="">Select Club</option>
                                    <?php
                                    $clubsResult = mysqli_query($connection, "SELECT clubId, clubName FROM club");
                                    while ($club = mysqli_fetch_assoc($clubsResult)) {
                                        echo "<option value='" . $club['clubId'] . "'>" . $club['clubName'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="playerPosition">Position:</label>
                                <select id="playerPosition" name="playerPosition" required>
                                    <option value="">Select Position</option>
                                    <option value="GK">Goalkeeper</option>
                                    <option value="DF">Defender</option>
                                    <option value="MF">Midfielder</option>
                                    <option value="FW">Forward</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="playerRating">Rating:</label>
                                <input type="number" id="playerRating" name="playerRating" min="1" max="100" required>
                            </div>
                            <button type="submit" class="btn btn-blue">Save Player</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    function toggleAddPlayerForm() {
        var addPlayerModal = document.querySelector('.add-player-modal');
        if (addPlayerModal.style.display === 'none') {
            addPlayerModal.style.display = 'block';
        } else {
            addPlayerModal.style.display = 'none';
        }
    }

    document.getElementById('searchInput').addEventListener('input', function(e) {
        filterPlayers();
    });

    document.getElementById('positionFilter').addEventListener('change', function(e) {
        filterPlayers();
    });

    function filterPlayers() {
        const searchText = document.getElementById('searchInput').value.toLowerCase();
        const positionFilter = document.getElementById('positionFilter').value;
        const rows = document.querySelectorAll('.player-row');

        rows.forEach(row => {
            const playerName = row.querySelector('.player-name-container').textContent.toLowerCase();
            const playerPosition = row.querySelector('.position-badge').textContent.toLowerCase();
            row.style.display = (playerName.includes(searchText) && (positionFilter === '' || playerPosition === positionFilter.toLowerCase())) ? '' : 'none';
        });
    }

    function clearFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('positionFilter').value = '';
        filterPlayers();
    }

    // Add event listener for delete buttons
    const deleteButtons = document.querySelectorAll('.delete-player');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const playerId = this.dataset.playerId;
            deletePlayer(playerId);
        });
    });

    function deletePlayer(playerId) {
        if (confirm('Are you sure you want to delete this player?')) {
            window.location.href = `index.php?section=players&action=delete&id=${playerId}`;
        }
    }
</script>

<style>
    /* Existing CSS styles */

    .btn-red {
        background-color: rgba(239, 68, 68, 0.8);
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-red:hover {
        background-color: rgba(220, 38, 38, 0.8);
    }
</style>
</body>
</html>