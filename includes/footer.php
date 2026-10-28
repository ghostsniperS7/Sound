<?php
require_once __DIR__ . '/../config/config.php';
?>
            </main>
        </div><!-- /.main-wrapper -->

        <!-- Bottom Player Bar (Spotify Style) -->
        <footer class="player-bar">
            <!-- Left: Current Track Info -->
            <div class="track-info">
                <div class="cover" id="player-track-cover">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 18V5l12-2v13"></path>
                        <circle cx="6" cy="18" r="3"></circle>
                        <circle cx="18" cy="16" r="3"></circle>
                    </svg>
                </div>
                <div class="details">
                    <div class="title" id="player-track-title">No track playing</div>
                    <div class="artist" id="player-track-artist">Select a track to play</div>
                </div>
                <button type="button" class="like-btn player-like-btn" id="player-like-btn" data-action="toggle-like" data-item-type="music" data-item-id="" aria-label="Like" title="Like" style="display: none;">
                    <svg class="icon-heart-outline" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                    <svg class="icon-heart-filled" width="20" height="20" viewBox="0 0 24 24" fill="var(--accent)" stroke="var(--accent)" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                </button>
            </div>

            <!-- Center: Player Controls & Seek Bar -->
            <div class="player-center">
                <div class="player-controls">
                    <button id="prev-btn" aria-label="Previous track" title="Previous">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="19 20 9 12 19 4 19 20"></polygon>
                            <line x1="5" y1="19" x2="5" y2="5"></line>
                        </svg>
                    </button>
                    <button id="play-pause-btn" aria-label="Play" title="Play">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <polygon points="8 5 19 12 8 19 8 5"></polygon>
                        </svg>
                    </button>
                    <button id="next-btn" aria-label="Next track" title="Next">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="5 4 15 12 5 20 5 4"></polygon>
                            <line x1="19" y1="5" x2="19" y2="19"></line>
                        </svg>
                    </button>
                </div>

                <div class="progress-container">
                    <span class="time current-time">0:00</span>
                    <div class="progress-bar" role="slider" aria-label="Track progress" tabindex="0">
                        <div class="progress"></div>
                    </div>
                    <span class="time total-time">0:00</span>
                </div>
            </div>

            <!-- Right: Volume Controls -->
            <div class="volume-controls">
                <button id="mute-btn" aria-label="Mute" title="Mute/Unmute">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                        <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                    </svg>
                </button>
                <div class="volume-bar" role="slider" aria-label="Volume" tabindex="0">
                    <div class="volume"></div>
                </div>
            </div>
        </footer>
    </div><!-- /.app -->

    <script src="/sound/assets/js/app.js"></script>
</body>
</html>