<?php
include("config.php");

// Get counts for dashboard
$playersCount = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as count FROM player WHERE playerStatus = 'Active'"))['count'];
$clubsCount = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as count FROM club"))['count'];
$nationsCount = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as count FROM nationality"))['count'];

// Fetch players query
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

    if(isset($_GET['position']) && !empty($_GET['position'])) {
        $position = mysqli_real_escape_string($connection, $_GET['position']);
        $query .= " AND p.playerPosition = '$position'";
    }
    $query .= " ORDER BY p.playerRating DESC";
    $result = mysqli_query($connection, $query);
}

// Player Management Operations
if(isset($_GET['section']) && $_GET['section'] == 'players') {
    // Handle player form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['club_action']) && !isset($_POST['nationality_action'])) {
    $playerName = mysqli_real_escape_string($connection, $_POST['playerName']);
    $playerUrl = mysqli_real_escape_string($connection, $_POST['playerUrl']);
    $nationalityId = mysqli_real_escape_string($connection, $_POST['nationalityId']);
    $clubId = mysqli_real_escape_string($connection, $_POST['clubId']);
    $playerPosition = mysqli_real_escape_string($connection, $_POST['playerPosition']);
    $playerRating = mysqli_real_escape_string($connection, $_POST['playerRating']);

    // Start transaction
    mysqli_begin_transaction($connection);

    try {
        if(isset($_POST['playerId'])) {
            // Update existing player
            $playerId = mysqli_real_escape_string($connection, $_POST['playerId']);
            $sql = "UPDATE player SET 
                    playerName = '$playerName',
                    playerUrl = '$playerUrl',
                    nationalityId = $nationalityId,
                    clubId = $clubId,
                    playerPosition = '$playerPosition',
                    playerRating = $playerRating,
                    playerStatus = 'Active'
                    WHERE playerId = $playerId";

            mysqli_query($connection, $sql);

            // Update player stats based on position
            if ($playerPosition === 'GK') {
                // Update goalkeeper stats
                $statsQuery = "UPDATE goalkeeperStats SET 
                              diving = 50, 
                              handling = 50, 
                              kicking = 50,
                              reflexes = 50, 
                              speed = 50, 
                              positioning = 50 
                              WHERE playerId = $playerId";
                mysqli_query($connection, $statsQuery);

                // Remove field player stats if they exist
                mysqli_query($connection, "DELETE FROM playerFieldStats WHERE playerId = $playerId");
            } else {
                // Update field player stats
                $statsQuery = "UPDATE playerFieldStats SET 
                              pace = 50, 
                              shooting = 50, 
                              passing = 50,
                              dribbling = 50, 
                              defending = 50, 
                              physical = 50 
                              WHERE playerId = $playerId";
                mysqli_query($connection, $statsQuery);

                // Remove goalkeeper stats if they exist
                mysqli_query($connection, "DELETE FROM goalkeeperStats WHERE playerId = $playerId");
            }
        } else {
            // Insert new player
            $sql = "INSERT INTO player (playerName, playerUrl, nationalityId, clubId, playerPosition, playerRating, playerStatus)
                    VALUES ('$playerName', '$playerUrl', $nationalityId, $clubId, '$playerPosition', $playerRating, 'Active')";
            
            mysqli_query($connection, $sql);
            $newPlayerId = mysqli_insert_id($connection);

            // Insert player stats based on position
            if ($playerPosition === 'GK') {
                // Insert goalkeeper stats
                $statsQuery = "INSERT INTO goalkeeperStats (playerId, diving, handling, kicking, reflexes, speed, positioning) 
                              VALUES ($newPlayerId, 50, 50, 50, 50, 50, 50)";
            } else {
                // Insert field player stats
                $statsQuery = "INSERT INTO playerFieldStats (playerId, pace, shooting, passing, dribbling, defending, physical) 
                              VALUES ($newPlayerId, 50, 50, 50, 50, 50, 50)";
            }
            mysqli_query($connection, $statsQuery);
        }

        // If everything is successful, commit the transaction
        mysqli_commit($connection);
        header("Location: index.php?section=players&message=success");
        exit;

    } catch (Exception $e) {
        // If there's an error, rollback the transaction
        mysqli_rollback($connection);
        echo "Error: " . $e->getMessage();
    }
}

    // Handle player deletion
    if(isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $playerId = mysqli_real_escape_string($connection, $_GET['id']);
        
        // Delete associated stats first
        mysqli_query($connection, "DELETE FROM goalkeeperStats WHERE playerId = $playerId");
        mysqli_query($connection, "DELETE FROM playerFieldStats WHERE playerId = $playerId");
        
        // Then delete the player
        $deletePlayerQuery = "DELETE FROM player WHERE playerId = $playerId";
        if(mysqli_query($connection, $deletePlayerQuery)) {
            header("Location: index.php?section=players");
            exit;
        }
    }
}
// Club Management Operations
if(isset($_GET['section']) && $_GET['section'] == 'clubs') {
    // Handle club form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['club_action'])) {
        $clubName = mysqli_real_escape_string($connection, $_POST['clubName']);
        $clubUrl = mysqli_real_escape_string($connection, $_POST['clubUrl']);

        if(isset($_POST['clubId'])) {
            // Update existing club
            $clubId = mysqli_real_escape_string($connection, $_POST['clubId']);
            $sql = "UPDATE club SET 
                    clubName = '$clubName', 
                    clubUrl = '$clubUrl' 
                    WHERE clubId = $clubId";
            
            if(mysqli_query($connection, $sql)) {
                header("Location: index.php?section=clubs&message=updated");
                exit;
            }
        } else {
            // Insert new club
            $sql = "INSERT INTO club (clubName, clubUrl) VALUES ('$clubName', '$clubUrl')";
            
            if(mysqli_query($connection, $sql)) {
                header("Location: index.php?section=clubs&message=added");
                exit;
            }
        }
    }

    // Handle club deletion
    if(isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $clubId = mysqli_real_escape_string($connection, $_GET['id']);
        
        // First update players to remove club association
        mysqli_query($connection, "UPDATE player SET clubId = NULL WHERE clubId = $clubId");
        
        // Then delete the club
        $deleteQuery = "DELETE FROM club WHERE clubId = $clubId";
        if(mysqli_query($connection, $deleteQuery)) {
            header("Location: index.php?section=clubs&message=deleted");
            exit;
        }
    }

    // Fetch clubs for listing
    $clubsQuery = "SELECT * FROM club ORDER BY clubName";
    $clubsResult = mysqli_query($connection, $clubsQuery);
}

// Nationality Management Operations
if(isset($_GET['section']) && $_GET['section'] == 'nations') {
    // Handle nationality form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nationality_action'])) {
        $nationalityName = mysqli_real_escape_string($connection, $_POST['nationalityName']);
        $nationalityUrl = mysqli_real_escape_string($connection, $_POST['nationalityUrl']);
        
        if(isset($_POST['nationalityId'])) {
            // Update existing nationality
            $nationalityId = mysqli_real_escape_string($connection, $_POST['nationalityId']);
            $sql = "UPDATE nationality SET 
                    nationalityName = '$nationalityName', 
                    nationalityUrl = '$nationalityUrl' 
                    WHERE nationalityId = $nationalityId";
            
            if(mysqli_query($connection, $sql)) {
                header("Location: index.php?section=nations&message=updated");
                exit;
            }
        } else {
            // Insert new nationality
            $sql = "INSERT INTO nationality (nationalityName, nationalityUrl) 
                   VALUES ('$nationalityName', '$nationalityUrl')";
            
            if(mysqli_query($connection, $sql)) {
                header("Location: index.php?section=nations&message=added");
                exit;
            }
        }
    }

    // Handle nationality deletion
    if(isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $nationalityId = mysqli_real_escape_string($connection, $_GET['id']);
        
        // First update players to remove nationality association
        mysqli_query($connection, "UPDATE player SET nationalityId = NULL WHERE nationalityId = $nationalityId");
        
        // Then delete the nationality
        $deleteQuery = "DELETE FROM nationality WHERE nationalityId = $nationalityId";
        if(mysqli_query($connection, $deleteQuery)) {
            header("Location: index.php?section=nations&message=deleted");
            exit;
        }
    }

    // Fetch nationalities for listing
    $nationalitiesQuery = "SELECT * FROM nationality ORDER BY nationalityName";
    $nationalitiesResult = mysqli_query($connection, $nationalitiesQuery);
}

// Check for edit mode (for players)
$editPlayer = null;
if(isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $playerId = mysqli_real_escape_string($connection, $_GET['id']);
    $editQuery = "SELECT * FROM player WHERE playerId = '$playerId'";
    $editResult = mysqli_query($connection, $editQuery);
    $editPlayer = mysqli_fetch_assoc($editResult);
}
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
    <?php if (isset($videoPath) && file_exists($videoPath)): ?>
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
                <button class="btn btn-red" onclick="handleLogout()">Logout</button>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <div class="container">
        <?php if (!isset($_GET['section'])): ?>
            <!-- Dashboard Section -->
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

                <!-- Clubs Card -->
                <div class="card">
                    <div class="icon-container icon-clubs">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="card-title">Club Management</h3>
                    <p class="card-text">Manage football clubs</p>
                    <a href="?section=clubs" class="card-button btn-clubs">Access Clubs</a>
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
            </div>

            <!-- Dashboard Stats -->
            <section class="stats-section">
                <!-- Stats Overview -->
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
                    <div class="progress-container">
                        <div class="progress-header">
                            <span>Nationalities</span>
                            <span class="stats-value"><?php echo $nationsCount; ?></span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo min(($nationsCount/50) * 100, 100); ?>%"></div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="stats-card">
                    <h3 class="stats-title">Recent Activities</h3>
                    <div class="activity-list">
                        <?php if(isset($_SESSION['recent_activities'])): 
                            foreach($_SESSION['recent_activities'] as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-icon <?php echo $activity['icon']; ?>">
                                    <i class="fas <?php echo $activity['icon_class']; ?>"></i>
                                </div>
                                <div class="activity-details">
                                    <p class="activity-text"><?php echo htmlspecialchars($activity['text']); ?></p>
                                    <span class="activity-time"><?php echo $activity['time']; ?></span>
                                </div>
                            </div>
                        <?php endforeach; 
                        else: ?>
                            <div class="activity-item">
                                <div class="activity-icon icon-info">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div class="activity-details">
                                    <p class="activity-text">No recent activities</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- Players Section -->
<?php if(isset($_GET['section']) && $_GET['section'] == 'players'): ?>
    <div class="section-content">
        <!-- Section Header -->
        <div class="section-header">
            <h2>Players Management</h2>
            <div class="header-actions">
                <a href="index.php" class="btn btn-gray">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <button class="btn btn-blue" onclick="showAddPlayerForm()">
                    <i class="fas fa-plus"></i> Add Player
                </button>
            </div>
        </div>

        <!-- Search and Filter -->
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

        <!-- Players Table -->
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
                            <tr class="player-row">
                                <td>
                                    <div class="player-info">
                                        <img src="<?php echo htmlspecialchars($player['playerUrl']); ?>"
                                             alt="<?php echo htmlspecialchars($player['playerName']); ?>"
                                             class="player-image">
                                        <div class="player-details">
                                            <div class="player-name-container">
                                                <?php echo htmlspecialchars($player['playerName']); ?>
                                                <?php if($player['nationalityUrl']): ?>
                                                    <img src="<?php echo htmlspecialchars($player['nationalityUrl']); ?>"
                                                         alt="<?php echo htmlspecialchars($player['nationalityName']); ?>"
                                                         class="nationality-flag">
                                                <?php endif; ?>
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
                                    <button class="btn btn-blue btn-sm" 
                                            onclick="editPlayer(<?php echo $player['playerId']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-red btn-sm" 
                                            onclick="deletePlayer(<?php echo $player['playerId']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
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
    </div>
<?php endif; ?>
<!-- Clubs Section -->
<?php if(isset($_GET['section']) && $_GET['section'] == 'clubs'): ?>
    <div class="section-content">
        <div class="section-header">
            <h2>Clubs Management</h2>
            <div class="header-actions">
                <a href="index.php" class="btn btn-gray">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <button class="btn btn-blue" onclick="showClubForm()">
                    <i class="fas fa-plus"></i> Add Club
                </button>
            </div>
        </div>

        <!-- Clubs Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Logo</th>
                        <th>Club Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(isset($clubsResult) && mysqli_num_rows($clubsResult) > 0): 
                        while($club = mysqli_fetch_assoc($clubsResult)): ?>
                        <tr>
                            <td>
                                <img src="<?php echo htmlspecialchars($club['clubUrl']); ?>"
                                     alt="<?php echo htmlspecialchars($club['clubName']); ?>"
                                     class="club-logo">
                            </td>
                            <td><?php echo htmlspecialchars($club['clubName']); ?></td>
                            <td class="actions">
                                <button class="btn btn-blue btn-sm" 
                                        onclick="editClub(<?php echo $club['clubId']; ?>, 
                                                        '<?php echo htmlspecialchars($club['clubName']); ?>', 
                                                        '<?php echo htmlspecialchars($club['clubUrl']); ?>')">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-red btn-sm" 
                                        onclick="deleteClub(<?php echo $club['clubId']; ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    <?php endwhile;
                    else: ?>
                        <tr><td colspan="3" class="no-data">No clubs found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- Nationalities Section -->
<?php if(isset($_GET['section']) && $_GET['section'] == 'nations'): ?>
    <div class="section-content">
        <div class="section-header">
            <h2>Nationalities Management</h2>
            <div class="header-actions">
                <a href="index.php" class="btn btn-gray">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <button class="btn btn-blue" onclick="showNationalityForm()">
                    <i class="fas fa-plus"></i> Add Nationality
                </button>
            </div>
        </div>

        <!-- Nationalities Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Flag</th>
                        <th>Nationality Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(isset($nationalitiesResult) && mysqli_num_rows($nationalitiesResult) > 0): 
                        while($nationality = mysqli_fetch_assoc($nationalitiesResult)): ?>
                        <tr>
                            <td>
                                <img src="<?php echo htmlspecialchars($nationality['nationalityUrl']); ?>"
                                     alt="<?php echo htmlspecialchars($nationality['nationalityName']); ?>"
                                     class="nationality-flag">
                            </td>
                            <td><?php echo htmlspecialchars($nationality['nationalityName']); ?></td>
                            <td class="actions">
                                <button class="btn btn-blue btn-sm" 
                                        onclick="editNationality(<?php echo $nationality['nationalityId']; ?>, 
                                                               '<?php echo htmlspecialchars($nationality['nationalityName']); ?>', 
                                                               '<?php echo htmlspecialchars($nationality['nationalityUrl']); ?>')">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-red btn-sm" 
                                        onclick="deleteNationality(<?php echo $nationality['nationalityId']; ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    <?php endwhile;
                    else: ?>
                        <tr><td colspan="3" class="no-data">No nationalities found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
<!-- Player Form Modal -->
<div class="add-player-modal" style="display: <?php echo isset($editPlayer) ? 'block' : 'none'; ?>">
    <div class="modal-overlay" onclick="toggleAddPlayerForm()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3><?php echo isset($editPlayer) ? 'Edit Player' : 'Add New Player'; ?></h3>
            <button class="close-button" onclick="toggleAddPlayerForm()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?section=players"; ?>">
                <?php if(isset($editPlayer)): ?>
                    <input type="hidden" name="playerId" value="<?php echo $editPlayer['playerId']; ?>">
                <?php endif; ?>

                <!-- Basic Player Info -->
                <div class="form-group">
                    <label for="playerName">Player Name:</label>
                    <input type="text" id="playerName" name="playerName" 
                           value="<?php echo isset($editPlayer) ? htmlspecialchars($editPlayer['playerName']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="playerUrl">Player Image URL:</label>
                    <input type="text" id="playerUrl" name="playerUrl" 
                           value="<?php echo isset($editPlayer) ? htmlspecialchars($editPlayer['playerUrl']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="nationalityId">Nationality:</label>
                    <select id="nationalityId" name="nationalityId" required>
                        <option value="">Select Nationality</option>
                        <?php
                        $nationalitiesResult = mysqli_query($connection, "SELECT nationalityId, nationalityName FROM nationality");
                        while ($nationality = mysqli_fetch_assoc($nationalitiesResult)) {
                            $selected = (isset($editPlayer) && $editPlayer['nationalityId'] == $nationality['nationalityId']) ? 'selected' : '';
                            echo "<option value='" . $nationality['nationalityId'] . "' $selected>" . 
                                 htmlspecialchars($nationality['nationalityName']) . "</option>";
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
                            $selected = (isset($editPlayer) && $editPlayer['clubId'] == $club['clubId']) ? 'selected' : '';
                            echo "<option value='" . $club['clubId'] . "' $selected>" . 
                                 htmlspecialchars($club['clubName']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="playerPosition">Position:</label>
                    <select id="playerPosition" name="playerPosition" required onchange="toggleStatsInputs()">
                        <option value="">Select Position</option>
                        <option value="GK" <?php echo (isset($editPlayer) && $editPlayer['playerPosition'] == 'GK') ? 'selected' : ''; ?>>Goalkeeper</option>
                        <option value="DF" <?php echo (isset($editPlayer) && $editPlayer['playerPosition'] == 'DF') ? 'selected' : ''; ?>>Defender</option>
                        <option value="MF" <?php echo (isset($editPlayer) && $editPlayer['playerPosition'] == 'MF') ? 'selected' : ''; ?>>Midfielder</option>
                        <option value="FW" <?php echo (isset($editPlayer) && $editPlayer['playerPosition'] == 'FW') ? 'selected' : ''; ?>>Forward</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="playerRating">Rating:</label>
                    <input type="number" id="playerRating" name="playerRating" 
                           value="<?php echo isset($editPlayer) ? $editPlayer['playerRating'] : ''; ?>"
                           min="1" max="99" required>
                </div>

                <!-- Goalkeeper Stats (Initially Hidden) -->
                <div id="goalkeeperStats" style="display: none;">
                    <h4>Goalkeeper Statistics</h4>
                    <div class="stats-grid">
                        <div class="form-group">
                            <label for="diving">Diving:</label>
                            <input type="number" id="diving" name="diving" min="1" max="99" value="50">
                        </div>
                        <div class="form-group">
                            <label for="handling">Handling:</label>
                            <input type="number" id="handling" name="handling" min="1" max="99" value="50">
                        </div>
                        <div class="form-group">
                            <label for="kicking">Kicking:</label>
                            <input type="number" id="kicking" name="kicking" min="1" max="99" value="50">
                        </div>
                        <div class="form-group">
                            <label for="reflexes">Reflexes:</label>
                            <input type="number" id="reflexes" name="reflexes" min="1" max="99" value="50">
                        </div>
                        <div class="form-group">
                            <label for="speed">Speed:</label>
                            <input type="number" id="speed" name="speed" min="1" max="99" value="50">
                        </div>
                        <div class="form-group">
                            <label for="positioning">Positioning:</label>
                            <input type="number" id="positioning" name="positioning" min="1" max="99" value="50">
                        </div>
                    </div>
                </div>

                <!-- Field Player Stats (Initially Hidden) -->
                <div id="fieldPlayerStats" style="display: none;">
                    <h4>Player Statistics</h4>
                    <div class="stats-grid">
                        <div class="form-group">
                            <label for="pace">Pace:</label>
                            <input type="number" id="pace" name="pace" min="1" max="99" value="50">
                        </div>
                        <div class="form-group">
                            <label for="shooting">Shooting:</label>
                            <input type="number" id="shooting" name="shooting" min="1" max="99" value="50">
                        </div>
                        <div class="form-group">
                            <label for="passing">Passing:</label>
                            <input type="number" id="passing" name="passing" min="1" max="99" value="50">
                        </div>
                        <div class="form-group">
                            <label for="dribbling">Dribbling:</label>
                            <input type="number" id="dribbling" name="dribbling" min="1" max="99" value="50">
                        </div>
                        <div class="form-group">
                            <label for="defending">Defending:</label>
                            <input type="number" id="defending" name="defending" min="1" max="99" value="50">
                        </div>
                        <div class="form-group">
                            <label for="physical">Physical:</label>
                            <input type="number" id="physical" name="physical" min="1" max="99" value="50">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-blue">
                    <?php echo isset($editPlayer) ? 'Update Player' : 'Save Player'; ?>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Club Form Modal -->
<div id="clubFormModal" class="modal">
    <div class="modal-overlay" onclick="toggleClubForm()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="clubFormTitle">Add New Club</h3>
            <button class="close-button" onclick="toggleClubForm()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="clubForm" method="post">
                <input type="hidden" name="club_action" value="add">
                <input type="hidden" name="clubId" id="clubId">
                
                <div class="form-group">
                    <label for="clubName">Club Name:</label>
                    <input type="text" id="clubName" name="clubName" required>
                </div>
                
                <div class="form-group">
                    <label for="clubUrl">Club Logo URL:</label>
                    <input type="text" id="clubUrl" name="clubUrl" required>
                </div>
                
                <button type="submit" class="btn btn-blue">Save Club</button>
            </form>
        </div>
    </div>
</div>

<!-- Nationality Form Modal -->
<div id="nationalityFormModal" class="modal">
    <div class="modal-overlay" onclick="toggleNationalityForm()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="nationalityFormTitle">Add New Nationality</h3>
            <button class="close-button" onclick="toggleNationalityForm()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="nationalityForm" method="post">
                <input type="hidden" name="nationality_action" value="add">
                <input type="hidden" name="nationalityId" id="nationalityId">
                
                <div class="form-group">
                    <label for="nationalityName">Nationality Name:</label>
                    <input type="text" id="nationalityName" name="nationalityName" required>
                </div>
                
                <div class="form-group">
                    <label for="nationalityUrl">Flag URL:</label>
                    <input type="text" id="nationalityUrl" name="nationalityUrl" required>
                </div>
                
                <button type="submit" class="btn btn-blue">Save Nationality</button>
            </form>
        </div>
    </div>
</div>
<script>
// Player Management Functions
function showAddPlayerForm() {
    document.querySelector('.add-player-modal').style.display = 'block';
}

function toggleAddPlayerForm() {
    const modal = document.querySelector('.add-player-modal');
    if (modal.style.display === 'none' || modal.style.display === '') {
        modal.style.display = 'block';
    } else {
        modal.style.display = 'none';
        if(window.location.href.includes('action=edit')) {
            window.location.href = 'index.php?section=players';
        }
    }
}

function editPlayer(playerId) {
    window.location.href = `index.php?section=players&action=edit&id=${playerId}`;
}

function deletePlayer(playerId) {
    if(confirm('Are you sure you want to delete this player?')) {
        window.location.href = `index.php?section=players&action=delete&id=${playerId}`;
    }
}

// Club Management Functions
function showClubForm() {
    document.getElementById('clubFormModal').style.display = 'block';
    resetClubForm();
}

function toggleClubForm() {
    const modal = document.getElementById('clubFormModal');
    modal.style.display = modal.style.display === 'none' ? 'block' : 'none';
    if(modal.style.display === 'none') {
        resetClubForm();
    }
}

function editClub(id, name, url) {
    const modal = document.getElementById('clubFormModal');
    document.getElementById('clubFormTitle').textContent = 'Edit Club';
    document.querySelector('#clubForm input[name="club_action"]').value = 'edit';
    document.getElementById('clubId').value = id;
    document.getElementById('clubName').value = name;
    document.getElementById('clubUrl').value = url;
    modal.style.display = 'block';
}

function deleteClub(clubId) {
    if(confirm('Are you sure you want to delete this club?')) {
        window.location.href = `index.php?section=clubs&action=delete&id=${clubId}`;
    }
}

function resetClubForm() {
    document.getElementById('clubFormTitle').textContent = 'Add New Club';
    document.querySelector('#clubForm input[name="club_action"]').value = 'add';
    document.getElementById('clubId').value = '';
    document.getElementById('clubForm').reset();
}

// Nationality Management Functions
function showNationalityForm() {
    document.getElementById('nationalityFormModal').style.display = 'block';
    resetNationalityForm();
}

function toggleNationalityForm() {
    const modal = document.getElementById('nationalityFormModal');
    modal.style.display = modal.style.display === 'none' ? 'block' : 'none';
    if(modal.style.display === 'none') {
        resetNationalityForm();
    }
}

function editNationality(id, name, url) {
    const modal = document.getElementById('nationalityFormModal');
    document.getElementById('nationalityFormTitle').textContent = 'Edit Nationality';
    document.querySelector('#nationalityForm input[name="nationality_action"]').value = 'edit';
    document.getElementById('nationalityId').value = id;
    document.getElementById('nationalityName').value = name;
    document.getElementById('nationalityUrl').value = url;
    modal.style.display = 'block';
}

function deleteNationality(nationalityId) {
    if(confirm('Are you sure you want to delete this nationality?')) {
        window.location.href = `index.php?section=nations&action=delete&id=${nationalityId}`;
    }
}

function resetNationalityForm() {
    document.getElementById('nationalityFormTitle').textContent = 'Add New Nationality';
    document.querySelector('#nationalityForm input[name="nationality_action"]').value = 'add';
    document.getElementById('nationalityId').value = '';
    document.getElementById('nationalityForm').reset();
}

// Search and Filter Functions
function filterPlayers() {
    const searchText = document.getElementById('searchInput').value.toLowerCase();
    const positionFilter = document.getElementById('positionFilter').value.toLowerCase();
    const rows = document.querySelectorAll('.player-row');

    rows.forEach(row => {
        const playerName = row.querySelector('.player-name-container').textContent.toLowerCase();
        const playerPosition = row.querySelector('.position-badge').textContent.toLowerCase();
        const matchesSearch = playerName.includes(searchText);
        const matchesPosition = !positionFilter || playerPosition === positionFilter;
        row.style.display = matchesSearch && matchesPosition ? '' : 'none';
    });
}

function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('positionFilter').value = '';
    filterPlayers();
}

// Form Validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;

    const inputs = form.querySelectorAll('input[required], select[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('error');
            isValid = false;
        } else {
            input.classList.remove('error');
        }
    });

    return isValid;
}
function toggleStatsInputs() {
    const position = document.getElementById('playerPosition').value;
    const gkStats = document.getElementById('goalkeeperStats');
    const fieldStats = document.getElementById('fieldPlayerStats');
    
    if (position === 'GK') {
        gkStats.style.display = 'block';
        fieldStats.style.display = 'none';
        
        // Make GK stats required
        document.querySelectorAll('#goalkeeperStats input').forEach(input => input.required = true);
        document.querySelectorAll('#fieldPlayerStats input').forEach(input => input.required = false);
    } else if (position !== '') {
        gkStats.style.display = 'none';
        fieldStats.style.display = 'block';
        
        // Make field player stats required
        document.querySelectorAll('#goalkeeperStats input').forEach(input => input.required = false);
        document.querySelectorAll('#fieldPlayerStats input').forEach(input => input.required = true);
    } else {
        gkStats.style.display = 'none';
        fieldStats.style.display = 'none';
        
        // Make no stats required
        document.querySelectorAll('#goalkeeperStats input, #fieldPlayerStats input')
            .forEach(input => input.required = false);
    }
}
// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Search and filter event listeners
    const searchInput = document.getElementById('searchInput');
    const positionFilter = document.getElementById('positionFilter');
    
    if(searchInput) {
        searchInput.addEventListener('input', filterPlayers);
    }
    if(positionFilter) {
        positionFilter.addEventListener('change', filterPlayers);
    }
    if (document.getElementById('playerPosition').value) {
        toggleStatsInputs();
    }

    // Form validation
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this.id)) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    });

    // Close modals when clicking outside
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            const modals = [
                { element: document.querySelector('.add-player-modal'), toggle: toggleAddPlayerForm },
                { element: document.getElementById('clubFormModal'), toggle: toggleClubForm },
                { element: document.getElementById('nationalityFormModal'), toggle: toggleNationalityForm }
            ];

            modals.forEach(modal => {
                if (modal.element && modal.element.style.display === 'block') {
                    modal.toggle();
                }
            });
        }
    }
});
</script>
<style>
/* Base Styles */
:root {
    --primary-color: #3b82f6;
    --danger-color: #ef4444;
    --success-color: #22c55e;
    --warning-color: #f59e0b;
    --text-light: #ffffff;
    --text-dark: #1f2937;
    --background-dark: #111827;
    --background-light: rgba(255, 255, 255, 0.1);
}

body {
    margin: 0;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    line-height: 1.5;
    color: var(--text-light);
    background: var(--background-dark);
}

.bg-video {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: -1;
    opacity: 0.3;
}

/* Navigation Styles */
.nav {
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(10px);
    padding: 1rem;
    position: sticky;
    top: 0;
    z-index: 100;
}

.nav-content {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-top: 1rem;
}

#goalkeeperStats,
#fieldPlayerStats {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 0.5rem;
    padding: 1rem;
    margin-top: 1rem;
}

h4 {
    margin-top: 0;
    color: var(--text-light);
    font-size: 1.1rem;
}

.nav-brand {
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--text-light);
}

.nav-right {
    display: flex;
    align-items: center;
    gap: 1rem;
}

/* Container and Layout */
.container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.section-content {
    background: var(--background-light);
    backdrop-filter: blur(10px);
    border-radius: 1rem;
    padding: 2rem;
    margin-top: 2rem;
}

/* Cards Grid */
.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
    margin: 2rem 0;
}

.card {
    background: var(--background-light);
    backdrop-filter: blur(10px);
    border-radius: 1rem;
    padding: 2rem;
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

/* Icons and Badges */
.icon-container {
    width: 60px;
    height: 60px;
    margin: 0 auto 1rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.icon-players { background-color: rgba(59, 130, 246, 0.2); color: #3b82f6; }
.icon-clubs { background-color: rgba(34, 197, 94, 0.2); color: #22c55e; }
.icon-nations { background-color: rgba(249, 115, 22, 0.2); color: #f59e0b; }

/* Table Styles */
.table-container {
    overflow-x: auto;
    background: var(--background-light);
    border-radius: 1rem;
    margin-top: 2rem;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

th {
    background: rgba(0, 0, 0, 0.2);
    font-weight: 600;
}

/* Button Styles */
.btn {
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    border: none;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
}

.btn-blue {
    background-color: var(--primary-color);
    color: white;
}

.btn-red {
    background-color: var(--danger-color);
    color: white;
}

.btn-gray {
    background-color: rgba(107, 114, 128, 0.8);
    color: white;
}

.btn-sm {
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
}

/* Modal Styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
    display: none;
    z-index: 1000;
}

.modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: var(--background-dark);
    border-radius: 1rem;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-body {
    padding: 1.5rem;
}

/* Form Styles */
.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: var(--text-light);
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 0.75rem;
    border-radius: 0.5rem;
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(255, 255, 255, 0.05);
    color: var(--text-light);
    transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--primary-color);
}

/* Player Specific Styles */
.player-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.player-image {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.club-image, 
.nationality-flag {
    width: 30px;
    height: 30px;
    object-fit: contain;
}

.position-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
    font-weight: 500;
}

.position-badge.gk { background-color: rgba(34, 197, 94, 0.2); color: #22c55e; }
.position-badge.df { background-color: rgba(59, 130, 246, 0.2); color: #3b82f6; }
.position-badge.mf { background-color: rgba(249, 115, 22, 0.2); color: #f59e0b; }
.position-badge.fw { background-color: rgba(239, 68, 68, 0.2); color: #ef4444; }

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.5rem;
}

.stat-item {
    background: rgba(255, 255, 255, 0.05);
    padding: 0.5rem;
    border-radius: 0.25rem;
    text-align: center;
}

.stat-name {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.7);
}

.stat-value {
    font-weight: 600;
}

/* Search and Filter Styles */
.search-container {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.search-box {
    position: relative;
    flex: 1;
}

.search-input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.5rem;
    border-radius: 0.5rem;
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(255, 255, 255, 0.05);
    color: var(--text-light);
}

.search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: rgba(255, 255, 255, 0.5);
}

/* Responsive Design */
@media (max-width: 768px) {
    .section-header {
        flex-direction: column;
        gap: 1rem;
    }

    .header-actions {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .search-container {
        flex-direction: column;
    }

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .modal-content {
        width: 95%;
        margin: 0 auto;
    }
}

@media (max-width: 480px) {
    .cards-grid {
        grid-template-columns: 1fr;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .nav-content {
        flex-direction: column;
        gap: 1rem;
    }
}
</style>
</body>
</html>