<?php
include 'config.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="./src/styles/styles.css">
</head>
<body>
    
    <div class="container">
        <div class="header">
            <img src="./src/assets/img/football (4).png" alt="" style="height: 50px; width: 50px  ;">
            <select class="formation-selector" id="formation-select">
                <option value="4-3-3">4-3-3</option>
                <option value="4-4-2">4-4-2</option>
                <option value="3-5-2">3-5-2</option>
                <option value="4-2-3-1">4-2-3-1</option>
            </select>
            
    </div>
</div>
    <div class="container-section">
        
         <div style="background-image: url(./src/assets/img/STADIUMBG.png) ; background-repeat: no-repeat; height: 100%; width: 100%; ">
            
            <div style="display: flex; justify-content: space-between; gap: 15px;" >
              <div class="field-container">
                <div class="field">
                    <div class="line forwards">                   
                        <div class="default-card" id="LW" draggable="true"></div>
                        <span class="lw-post">
                            LW</span>

                        <div class="default-card" id="ST" draggable="true"></div>
                        <span class="st-post">ST</span>
                    
                        <div class="default-card" id="RW" draggable="true"></div>
                        <span class="rw-post">RW</span>

                    </div>
            
                    <div class="line midfielders">
                        <div class="default-card" id="CM" draggable="true"></div>
                        <span class="cm-post">CM</span>
                        <div class="default-card" id="CDM" draggable="true"></div>
                        <span class="cdm-post">CDM</span>
                        <div class="default-card" id="CAM" draggable="true"></div>
                        <span class="cam-post">CAM</span>
                    </div>
            
                    <div class="line defenders">
                        <div class="default-card" id="LB" draggable="true"></div>
                        <span class="lb-post">LB</span>
                        <div class="default-card" id="CBL" draggable="true"></div>
                        <span class="cbl-post">CBL</span>
                        <div class="default-card" id="CBR" draggable="true"></div>
                        <span class="cbr-post">CBR</span>
                        <div class="default-card" id="RB" draggable="true"></div>
                            <span class="rb-post">RB</span>
                    </div>

                    <div class="line goalkeeper">
                        <div class="default-card" id="GK" draggable="true"></div>
                        <span class="gk-post">GK</span>

                     </div>
                </div>
            </div>
    
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
                        <label for="nationality">Nationalité</label>
                        <div class="input-with-icon">
                            <input type="text" id="nationality" name="nationality"  
                                   title="Minimum 2 caractères, lettres uniquement">
                            <input type="url" id="nationalityFlag" name="nationalityFlag" 
                                   placeholder="URL du Drapeau"
                                   title="Lien URL valide vers le drapeau">
                        </div>
                    </div>
        
                    <div class="form-group">
                        <label for="club">Club</label>
                        <div class="input-with-icon">
                            <input type="text" id="club" name="club"  
                                   title="Nom de club valide">
                            <input type="url" id="clubFlag" name="clubFlag" 
                                   placeholder="URL du Logo du Club"
                                   title="Lien URL valide vers le logo du club">
                        </div>
                    </div>
        
                    <div class="form-group">
                        <label for="position">Position</label>
                        <select id="position" name="position" >
                            <option value="">Sélectionner une position</option>
                        </select>
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
    </div>
</div>

<div>
    <div>
        
    </div>

</div>

<div style="display: flex; flex-direction: row; gap: 20px;">
    <div id="subtitution-container-left">
        <div style="display: flex; flex-direction: column;">
            <span>Substitutions</span>
            <div class="position-filters">
                <button class="filter-btn active" data-position="ALL">ALL</button>
                <button class="filter-btn" data-position="LW">LW</button>
                <button class="filter-btn" data-position="ST">ST</button>
                <button class="filter-btn" data-position="RW">RW</button>
                <button class="filter-btn" data-position="CM">CM</button>
                <button class="filter-btn" data-position="CDM">CDM</button>
                <button class="filter-btn" data-position="CAM">CAM</button>
                <button class="filter-btn" data-position="LB">LB</button>
                <button class="filter-btn" data-position="CBL">CBL</button>
                <button class="filter-btn" data-position="CBR">CBR</button>
                <button class="filter-btn" data-position="RB">RB</button>
                <button class="filter-btn" data-position="GK">GK</button>
            </div>
            <div id="subtitution" style="display: flex ; flex-wrap: wrap; gap: 30px; justify-content: space-between;">
            <div>

        </div>   
        </div>
    </div>
    </div>
    <div id="subtitution-container-right">
        
            
                <span>all-team</span>

                <div class="position-filters">
                    <button class="filter-btn active" data-position="ALL">ALL</button>
                    <button class="filter-btn" data-position="LW">LW</button>
                    <button class="filter-btn" data-position="ST">ST</button>
                    <button class="filter-btn" data-position="RW">RW</button>
                    <button class="filter-btn" data-position="CM">CM</button>
                    <button class="filter-btn" data-position="CDM">CDM</button>
                    <button class="filter-btn" data-position="CAM">CAM</button>
                    <button class="filter-btn" data-position="LB">LB</button>
                    <button class="filter-btn" data-position="CBL">CBL</button>
                    <button class="filter-btn" data-position="CBR">CBR</button>
                    <button class="filter-btn" data-position="RB">RB</button>
                    <button class="filter-btn" data-position="GK">GK</button>
                    <div id="subtitution-all">

                    </div>
                </div>
            </div>
    </div>
        
</div>
</body>

<script src="/src/js/script.js"></script>
<script src="/src/js/loadPlayers.js"></script>


<video autoplay muted loop id="myVideo" style="width: 100%;">
    <source src="/src/assets/img/vecteezy_sport-stadium-video-background-flashing-lights-glowing_4216353.mp4"
        type="video/mp4">
</video>
</body>
</html>