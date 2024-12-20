async function loadSubstitutionPlayers() {
    try {
        const response = await fetch('/players.json');
        const data = await response.json();
        displaySubstitutionPlayers(data);
    } catch (error) {
        console.error('Error loading substitution players:', error);
    }
}

function displaySubstitutionPlayers(data) {
    const substitutionContainer = document.getElementById('subtitution-all');

    if (data && data.players) {
        data.players.forEach(player => {
            const playerCard = document.createElement('div');
            playerCard.classList.add('player-card', 'filled');
            
            playerCard.innerHTML = `
                <div style="width: 100px; margin-right: 6px;">
                    <div style="display: flex; gap: 1px;">
                        <div style="display: flex; flex-direction: column; margin-top: 25px;">
                            <span class="player-rating">${player.rating}</span>
                            <span class="player-position">${player.position}</span>            
                        </div>
                        <img src="${player.photo}" class="player-photo" alt="">
                    </div>
                    <div>
                        <div>
                            <span class="player-name">${player.name}</span>
                        </div>
                        <div class="player-stat">
                            ${Object.entries(player.stats || {}).map(([key, value]) => `
                            <div class="player-stat-values">
                                <span>${key.toUpperCase()}</span>
                                <span>${value}</span>
                            </div>
                            `).join('')}
                        </div>
                        <div class="images-section">
                            <img src="${player.flag}" alt="">
                            <img src="${player.logo}" alt="">
                        </div>
                    </div>
                </div>`;

            substitutionContainer.appendChild(playerCard);
        });
    }
}

document.addEventListener('DOMContentLoaded', loadSubstitutionPlayers);
