<?php
require_once __DIR__ . '/../config/config.php';
?>
        </main>
        <footer class="player-bar">
            <div class="player-controls">
                <button id="prev-btn" aria-label="Previous track">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <button id="play-pause-btn" aria-label="Play">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 5V19L19 12L8 5Z" fill="currentColor"/>
                    </svg>
                </button>
                <button id="next-btn" aria-label="Next track">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            <div class="track-info">
                <div class="cover"></div>
                <div class="details">
                    <div class="title">No track playing</div>
                    <div class="artist">-</div>
                </div>
            </div>
            <div class="progress-container">
                <div class="time current-time">0:00</div>
                <div class="progress-bar">
                    <div class="progress"></div>
                </div>
                <div class="time total-time">0:00</div>
            </div>
            <div class="volume-controls">
                <button id="mute-btn" aria-label="Mute">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 4L9 7H6C5.44772 7 5 7.44772 5 8V16C5 16.5523 5.44772 17 6 17H9L12 20V4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <div class="volume-bar">
                    <div class="volume"></div>
                </div>
            </div>
        </footer>
    </div>
    <script src="/sound/assets/js/app.js"></script>
</body>
</html>