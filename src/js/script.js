let players = [];
const positionMap = {
    'LW': document.getElementById('LW'),
    'ST': document.getElementById('ST'),
    'RW': document.getElementById('RW'),
    'CM': document.getElementById('CM'),
    'CDM': document.getElementById('CDM'),
    'CAM': document.getElementById('CAM'),
    'LB': document.getElementById('LB'),
    'CBL': document.getElementById('CBL'),
    'CBR': document.getElementById('CBR'),
    'RB': document.getElementById('RB'),
    'GK': document.getElementById('GK')
};

// Position options for each player type
const POSITIONS = {
    outfield: [
        { value: 'LW', label: 'LW' },
        { value: 'ST', label: 'ST' },
        { value: 'RW', label: 'RW' },
        { value: 'CM', label: 'CM' },
        { value: 'CDM', label: 'CDM' },
        { value: 'CAM', label: 'CAM' },
        { value: 'LB', label: 'LB' },
        { value: 'CBL', label: 'CBL' },
        { value: 'CBR', label: 'CBR' },
        { value: 'RB', label: 'RB' }
    ],
    goalkeeper: [
        { value: 'GK', label: 'GK' }
    ]
};

// DOM Elements
const elements = {
    form: document.getElementById('playerRegistrationForm'),
    playerTypeRadios: document.querySelectorAll('input[name="playerType"]'),
    outfieldStats: document.getElementById('outfieldStats'),
    goalkeeperStats: document.getElementById('goalkeeperStats'),
    position: document.getElementById('position'),
    substitutionContainer: document.getElementById('subtitution')
};

// Validation Functions
function validateTextField(input, minLength = 2) {
    const value = input.value.trim();
    const validNameRegex = /^[A-Za-zÀ-ÿ\s'-]+$/;
    return value.length >= minLength && validNameRegex.test(value);
}

function validateImageURL(url) {
    if (!url) return true;
    try {
        new URL(url);
        const validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        const extension = url.split('.').pop().toLowerCase();
        return validExtensions.includes(extension);
    } catch {
        return false;
    }
}

function validateRating(input) {
    const value = parseInt(input.value);
    return !isNaN(value) && value >= 10 && value <= 99;
}

function validateStats(statsContainer) {
    const inputs = statsContainer.querySelectorAll('input[type="number"]');
    return Array.from(inputs).every(input => {
        const value = parseInt(input.value);
        return !isNaN(value) && value >= 10 && value <= 99;
    });
}

function updatePositionOptions(playerType) {
    const positionSelect = document.getElementById('position');
    positionSelect.innerHTML = `
        <option value="">Sélectionner une position</option>
        ${POSITIONS[playerType]
            .map(pos => `<option value="${pos.value}">${pos.label}</option>`)
            .join('')}
    `;
}

// Create player card
function createPlayerCard(player) {
    const card = document.createElement('div');
    card.classList.add('player-card', 'filled');
    card.id = `player-${player.position}`;

    card.innerHTML = `
        <div style="width: 100px; margin-right: 6px;">
            <div style="display: flex; gap: 5px;">
                <div style="display: flex; flex-direction: column; margin-top: 30px;">
                    <span class="player-rating">${player.rating}</span>
                    <span class="player-position">${player.position}</span>
                </div>
                <img src="${player.photo || './src/assets/img/default-player.png'}" class="player-photo" alt="">
            </div>
            <div>
                <div>
                    <span class="player-name">${player.name}</span>
                </div>
                <div class="player-stat">
                    ${Object.entries(player.stats).map(([key, value]) => `
                    <div class="player-stat-values">
                        <span>${key}</span>
                        <span>${value}</span>
                    </div>
                    `).join('')}
                </div>
                <div class="images-section">
                    <img src="${player.nationalityFlag || './src/assets/img/default-flag.png'}" alt="">
                    <img src="${player.clubFlag || './src/assets/img/default-club.png'}" alt="">
                </div>
            </div>
        </div>
        <div class="button-container">
            <img src="/src/assets/img/exchange.png" class="replace-btn" alt="Replace" title="Replace Player">
            <img src="/src/assets/img/exchange.png" class="delete-btn" alt="Delete" title="Remove Player">
            <img src="/src/assets/img/pen.png" class="update-btn" alt="Update" title="Update Player">
        </div>`;

    return card;
}
// localStorage.clear()


function handlePlayerPlacement(player) {
    const defaultCard = positionMap[player.position];
    if (!defaultCard) {
        showNotification('Invalid position', 'error');
        return false;
    }

    const existingCard = document.querySelector(`#player-${player.position}`);
    if (existingCard) {
        const substituteCard = existingCard.cloneNode(true);
        substituteCard.id = `substitute-${player.position}-${Date.now()}`;
        substituteCard.style.opacity = '1';
        substituteCard.style.pointerEvents = 'auto';
        elements.substitutionContainer.appendChild(substituteCard);
        existingCard.remove();
    }

    const playerCard = createPlayerCard(player);
    defaultCard.parentNode.insertBefore(playerCard, defaultCard);
    defaultCard.style.display = 'none';

    return true;
}

function handleFormSubmit(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const playerType = formData.get('playerType');
    const statsContainer = playerType === 'outfield' ? elements.outfieldStats : elements.goalkeeperStats;//ternor operator
    
    if (!validateForm(playerType, statsContainer)) {
        return;
    }

    const player = createPlayerObject(formData, playerType);
    
    if (handlePlayerPlacement(player)) {
        players.push(player);
        saveToLocalStorage();
        event.target.reset();
        updatePositionOptions(playerType);
        showNotification('Player added successfully', 'success');
    }
}

function validateForm(playerType, statsContainer) {
    if (!validateTextField(document.getElementById('name'))) {
        showNotification('Invalid name', 'error');
        return false;
    }
    
    if (!validateRating(document.getElementById('rating'))) {
        showNotification('Invalid rating', 'error');
        return false;
    }
    
    if (!validateStats(statsContainer)) {
        showNotification('Invalid stats', 'error');
        return false;
    }

    return true;
}

function createPlayerObject(formData, playerType) {
    return {
        name: formData.get('name'),
        position: formData.get('position'),
        rating: formData.get('rating'),
        photo: formData.get('photo'),
        nationalityFlag: formData.get('nationalityFlag'),
        clubFlag: formData.get('clubFlag'),
        stats: playerType === 'outfield' ? {
            PAC: formData.get('pace'),
            SHO: formData.get('shooting'),
            PAS: formData.get('passing'),
            DRI: formData.get('dribbling'),
            DEF: formData.get('defending'),
            PHY: formData.get('physical')
        } : {
            DIV: formData.get('diving'),
            HAN: formData.get('handling'),
            KIC: formData.get('kicking'),
            REF: formData.get('reflexes'),
            SPE: formData.get('speed'),
            POS: formData.get('positioning')
        }
    };
}
// Button event handlers
function handleFieldPlayerReplace(e) {
    const fieldPlayerCard = e.target.closest('.player-card');
    if (!fieldPlayerCard) return;
  
    const position = fieldPlayerCard.querySelector('.player-position').textContent;
    
    const substituteCard = fieldPlayerCard.cloneNode(true);
    substituteCard.id = `substitute-${position}-${Date.now()}`;
    substituteCard.dataset.position = position;
    elements.substitutionContainer.appendChild(substituteCard);
    
    const defaultCard = positionMap[position];
    if (defaultCard) {
        defaultCard.style.display = 'block';
        defaultCard.classList.remove('disabled');
    }
    
    fieldPlayerCard.remove();
    
    const playerIndex = players.findIndex(p => p.position === position);
    if (playerIndex !== -1) {
        players.splice(playerIndex, 1);
        saveToLocalStorage();
    }
    
    showNotification('Player moved to substitutes', 'success');
}

function handleSubstituteReplace(e) {
    const substituteCard = e.target.closest('.player-card');
    if (!substituteCard) return;
  
    const position = substituteCard.querySelector('.player-position').textContent;
    const fieldPlayerCard = document.querySelector(`#player-${position}`);  
    
    if (fieldPlayerCard) {
        const newFieldCard = substituteCard.cloneNode(true);
        newFieldCard.id = `player-${position}`;
        
        const newSubCard = fieldPlayerCard.cloneNode(true);
        newSubCard.id = `substitute-${position}-${Date.now()}`;
        
        fieldPlayerCard.parentNode.replaceChild(newFieldCard, fieldPlayerCard);
        substituteCard.parentNode.replaceChild(newSubCard, substituteCard);
        
        const fieldPlayerIndex = players.findIndex(p => p.position === position);
        if (fieldPlayerIndex !== -1) {
            const substituteData = getPlayerDataFromCard(newFieldCard);
            players[fieldPlayerIndex] = substituteData;
            saveToLocalStorage();
        }
    } else {
        const defaultCard = positionMap[position];
        if (defaultCard) {
            const newFieldCard = substituteCard.cloneNode(true);
            newFieldCard.id = `player-${position}`;
            
            defaultCard.parentNode.insertBefore(newFieldCard, defaultCard);
            defaultCard.style.display = 'none';
            
            const playerData = getPlayerDataFromCard(newFieldCard);
            players.push(playerData);
            saveToLocalStorage();
            
            substituteCard.remove();
        }
    }
}

function handleDelete(e) {
    const card = e.target.closest('.player-card');
    
    if (!card) return;

    const position = card.querySelector('.player-position').textContent;
    const defaultCard = positionMap[position];
    
    if (defaultCard) {
        defaultCard.style.display = 'block';
        defaultCard.style.opacity = '1';
    }
    
    card.remove();
    
    const playerIndex = players.findIndex(p => p.position === position);
    if (playerIndex !== -1) {
        
        players.splice(playerIndex, 1);
        saveToLocalStorage();
    }
    
    showNotification('Player removed successfully', 'success');
}

function handleUpdate(e) {
    const card = e.target.closest('.player-card');
    if (!card) return;

    const position = card.querySelector('.player-position').textContent;
    const player = players.find(p => p.position === position);
    if (!player) return;

    fillFormWithPlayerData(player);
    
    const submitBtn = elements.form.querySelector('button[type="submit"]');
    submitBtn.textContent = 'Update Player';
    submitBtn.dataset.updating = position;
}

function fillFormWithPlayerData(player) {    
    const playerType = player.position === 'GK' ? 'goalkeeper' : 'outfield';
    document.querySelector(`input[name="playerType"][value="${playerType}"]`).checked = true;
    
    updatePositionOptions(playerType);
    elements.outfieldStats.style.display = playerType === 'outfield' ? 'grid' : 'none';
    elements.goalkeeperStats.style.display = playerType === 'goalkeeper' ? 'grid' : 'none';
    console.log(player);
    
    Object.keys(player).forEach(key => {
        
        const input = document.getElementById(key);
        if (input) {
            input.value = player[key];
        }
    });

    Object.entries(player.stats).forEach(([key, value]) => {
        
        const input = document.getElementById(key.toLowerCase());
        if (input) {
            input.value = value;
        }
    });
}

function getPlayerDataFromCard(card) {
    return {
        name: card.querySelector('.player-name').textContent,
        position: card.querySelector('.player-position').textContent,
        rating: card.querySelector('.player-rating').textContent,
        photo: card.querySelector('.player-photo').src,
        nationalityFlag: card.querySelector('.images-section img:first-child').src,
        clubFlag: card.querySelector('.images-section img:last-child').src,
        stats: getStatsFromCard(card)

    };
}

function getStatsFromCard(card) {
    const stats = {};
    card.querySelectorAll('.player-stat-values').forEach(statDiv => {
        const spans = statDiv.querySelectorAll('span');
        if (spans.length === 2) {
            
            stats[spans[0].textContent.trim()] = spans[1].textContent.trim();
        }
    });
    
    return stats;
}

// Storage functions
function saveToLocalStorage() {
    localStorage.setItem('teamData', JSON.stringify({
        players,
    }));
}

function loadFromLocalStorage() {
    const data = localStorage.getItem('teamData');
    if (data) {
        const { players: savedPlayers, formation } = JSON.parse(data);
        players = savedPlayers;    
        players.forEach(handlePlayerPlacement);
    }
}

// Notification system
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => notification.remove(), 3000);
}

// Initialize button handlers
function initializeButtonHandlers() {
    // Field handlers
    document.querySelector('.field').addEventListener('click', (e) => {
        if (e.target.classList.contains('replace-btn')) {
            console.log(document.querySelector('.field'));
            console.log(document.querySelector('.replace-btn'));
            
            handleFieldPlayerReplace(e);
        } else if (e.target.classList.contains('delete-btn')) {
            handleDelete(e);
        } else if (e.target.classList.contains('update-btn')) {
            handleUpdate(e);
        }
    });

    // Substitution handlers
    document.getElementById('subtitution').addEventListener('click', (e) => {
        if (e.target.classList.contains('replace-btn')) {
            handleSubstituteReplace(e);
        } else if (e.target.classList.contains('delete-btn')) {
            handleDelete(e);
        } else if (e.target.classList.contains('update-btn')) {
            handleUpdate(e);
        }
    });
}

// Handle player type radio change
function initializePlayerTypeHandlers() {
    elements.playerTypeRadios.forEach(radio => {
        radio.addEventListener('change', (e) => {
            const playerType = e.target.value;
            elements.outfieldStats.style.display = playerType === 'outfield' ? 'grid' : 'none';
            elements.goalkeeperStats.style.display = playerType === 'goalkeeper' ? 'grid' : 'none';
            updatePositionOptions(playerType);
        });
    });
}

function initialize() {
    // Set up form handler
    elements.form.addEventListener('submit', handleFormSubmit);

    // Initialize all button handlers
    initializeButtonHandlers();
    
    // Initialize player type handlers
    initializePlayerTypeHandlers();

    // Set default player type and position options
    const defaultPlayerType = document.querySelector('input[name="playerType"]:checked')?.value || 'outfield';
    updatePositionOptions(defaultPlayerType);

    // Load saved data
    loadFromLocalStorage();
}

// Error handler
window.addEventListener('error', function(e) {
    showNotification('An error occurred', 'error');
    console.error(e);
});

// Save before unload
window.addEventListener('beforeunload', () => {
    saveToLocalStorage();
});

// Start the application
initialize();
