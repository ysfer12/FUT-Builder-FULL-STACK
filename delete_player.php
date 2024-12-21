<?php
include("config.php");

if(isset($_GET['id'])) {
    $playerId = mysqli_real_escape_string($connection, $_GET['id']);
    
    // Supprimer les statistiques du joueur
    $deleteStatsQuery = "DELETE FROM goalkeeperStats WHERE playerId = $playerId";
    mysqli_query($connection, $deleteStatsQuery);
    
    $deleteFieldStatsQuery = "DELETE FROM playerFieldStats WHERE playerId = $playerId";
    mysqli_query($connection, $deleteFieldStatsQuery);
    
    // Supprimer le joueur
    $deletePlayerQuery = "DELETE FROM player WHERE playerId = $playerId";
    if(mysqli_query($connection, $deletePlayerQuery)) {
        header("Location: index.php?section=players");
    } else {
        echo "Error deleting record: " . mysqli_error($connection);
    }
}

$connection->close();
?>