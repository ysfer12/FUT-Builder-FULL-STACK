

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="./src/styles/styles.css">
</head>
<body>
    <div class="container-section">
            <div class="formContainer">
                <h1 class="form-title">Inscription Joueur</h1>
                <form id="playerRegistrationForm">
                    <div class="player-type-select">
                        <label>
                            <input type="radio" name="playerType" value="outfield">
                            Joueur de Champ
                        </label>
                        <label>
                            <input type="radio" name="playerType" value="goalkeeper">
                            Gardien
                        </label>
                    </div>
                    <div class="form-group">
                        <label for="name">id player</label>
                        <div class="input-with-icon">
                            <input type="text" id="id" name="id"  
                                   title="Minimum 2 caractères, lettres uniquement">
                           
                    </div>
        
                    <div class="form-group">
                        <label for="name">Nom Complet</label>
                        <div class="input-with-icon">
                            <input type="text" id="name" name="name"  
                                   title="Minimum 2 caractères, lettres uniquement">
                            <input type="url" id="photo" name="photo" 
                                   placeholder="URL de la Photo"
                                   title="Lien URL valide vers une image">
                        </div>
                    </div>
        
                    <div class="form-group">
                        <label for="position">Nationality</label>
                        <input type="text" id="natinality" name="natinality"  
                        title="Minimum 2 caractères, lettres uniquement">
                    </div>
        
                    <div class="form-group">
                        <label for="position">Club</label>
                        <input type="text" id="club" name="club">

                    </div>
        
                    <div class="form-group">
                        <label for="position">Position</label>
                        <input type="text" id="position" name="position" > 
                    </div>
        
                    <div class="form-group">
                        <label for="rating">Note Globale (10-99)</label>
                        <input type="number" id="rating" name="rating" 
                              >
                    </div>
        
                    <div id="outfieldStats" class="stats-grid">
                        <h3 class="stats-header">Statistiques - Joueur de Champ</h3>
                        <div class="form-group">
                            <label for="pace">Vitesse</label>
                            <input type="number" id="pace" name="pace">
                        </div>
                        <div class="form-group">
                            <label for="shooting">Tir</label>
                            <input type="number" id="shooting" name="shooting">
                        </div>
                        <div class="form-group">
                            <label for="passing">Passes</label>
                            <input type="number" id="passing" name="passing">
                        </div>
                        <div class="form-group">
                            <label for="dribbling">Dribble</label>
                            <input type="number" id="dribbling" name="dribbling">
                        </div>
                        <div class="form-group">
                            <label for="defending">Défense</label>
                            <input type="number" id="defending" name="defending">
                        </div>
                        <div class="form-group">
                            <label for="physical">Physique</label>
                            <input type="number" id="physical" name="physical">
                        </div>
                    </div>
                    <div id="goalkeeperStats" class="stats-grid" style="display:none;">
                        <h3 class="stats-header">Statistiques - Gardien</h3>
                        <div class="form-group">
                            <label for="diving">Plongeon</label>
                            <input type="number" id="diving" name="diving">
                        </div>
                        <div class="form-group">
                            <label for="handling">Maniement</label>
                            <input type="number" id="handling" name="handling">
                        </div>
                        <div class="form-group">
                            <label for="kicking">Dégagement</label>
                            <input type="number" id="kicking" name="kicking">
                        </div>
                        <div class="form-group">
                            <label for="reflexes">Réflexes</label>
                            <input type="number" id="reflexes" name="reflexes">
                        </div>
                        <div class="form-group">
                            <label for="speed">Vitesse</label>
                            <input type="number" id="speed" name="speed">
                        </div>
                        <div class="form-group">
                            <label for="positioning">Positionnement</label>
                            <input type="number" id="positioning" name="positioning">
                        </div>
                    </div>
        
                    <button type="submit" class="btn-submit">Add Player</button>
                </form>
       
                </div>
            
    </div>
</body>
</html> -->