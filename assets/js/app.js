// SOUND - Modern Audio Player & Public SPA Navigation
document.addEventListener('DOMContentLoaded', function() {
    const playPauseBtn = document.getElementById('play-pause-btn');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const muteBtn = document.getElementById('mute-btn');
    const progressBar = document.querySelector('.progress-bar');
    const progress = document.querySelector('.progress');
    const volumeBar = document.querySelector('.volume-bar');
    const volume = document.querySelector('.volume');
    const currentTimeElement = document.querySelector('.current-time');
    const totalTimeElement = document.querySelector('.total-time');
    const trackTitleElement = document.getElementById('player-track-title') || document.querySelector('.track-info .title');
    const trackArtistElement = document.getElementById('player-track-artist') || document.querySelector('.track-info .artist');
    const trackCoverElement = document.getElementById('player-track-cover') || document.querySelector('.track-info .cover');

    let audio = new Audio();
    let isPlaying = false;
    let currentTrackIndex = -1;
    let trackList = [];
    let isMuted = false;
    let currentVolume = 0.7;

    // SVG Icons
    const playIcon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="8 5 19 12 8 19 8 5"></polygon></svg>';
    const pauseIcon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>';
    const volumeHighIcon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path></svg>';
    const volumeMuteIcon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><line x1="23" y1="9" x2="17" y2="15"></line><line x1="17" y1="9" x2="23" y2="15"></line></svg>';

    // Initial volume
    audio.volume = currentVolume;
    if (volume) volume.style.width = (currentVolume * 100) + '%';

    // Play/Pause functionality
    if (playPauseBtn) {
        playPauseBtn.addEventListener('click', function() {
            if (!audio.src) {
                // If there are tracks on the page, play the first one
                if (trackList.length > 0) {
                    currentTrackIndex = 0;
                    loadTrack(currentTrackIndex);
                    audio.play().catch(function() {});
                    isPlaying = true;
                    playPauseBtn.innerHTML = pauseIcon;
                    return;
                }
                return;
            }

            if (isPlaying) {
                audio.pause();
                isPlaying = false;
                playPauseBtn.innerHTML = playIcon;
            } else {
                audio.play().catch(function(e) {
                    console.warn('Playback error:', e);
                });
                isPlaying = true;
                playPauseBtn.innerHTML = pauseIcon;
            }
        });
    }

    // Previous track
    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            if (trackList.length === 0) return;
            currentTrackIndex = (currentTrackIndex - 1 + trackList.length) % trackList.length;
            loadTrack(currentTrackIndex);
            audio.play().catch(function() {});
            isPlaying = true;
            if (playPauseBtn) playPauseBtn.innerHTML = pauseIcon;
        });
    }

    // Next track
    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            if (trackList.length === 0) return;
            currentTrackIndex = (currentTrackIndex + 1) % trackList.length;
            loadTrack(currentTrackIndex);
            audio.play().catch(function() {});
            isPlaying = true;
            if (playPauseBtn) playPauseBtn.innerHTML = pauseIcon;
        });
    }

    // Mute toggle
    if (muteBtn) {
        muteBtn.addEventListener('click', function() {
            isMuted = !isMuted;
            audio.muted = isMuted;
            muteBtn.innerHTML = isMuted ? volumeMuteIcon : volumeHighIcon;
            if (volume) volume.style.width = isMuted ? '0%' : (currentVolume * 100) + '%';
        });
    }

    // Progress bar update
    audio.addEventListener('timeupdate', function() {
        if (audio.duration && !isNaN(audio.duration)) {
            const progressPercent = (audio.currentTime / audio.duration) * 100;
            if (progress) progress.style.width = progressPercent + '%';

            const currentMinutes = Math.floor(audio.currentTime / 60);
            const currentSeconds = Math.floor(audio.currentTime % 60).toString().padStart(2, '0');
            if (currentTimeElement) currentTimeElement.textContent = `${currentMinutes}:${currentSeconds}`;
        }
    });

    function formatDuration(sec) {
        if (!sec || isNaN(sec) || !isFinite(sec)) return '0:00';
        const m = Math.floor(sec / 60);
        const s = Math.floor(sec % 60).toString().padStart(2, '0');
        return `${m}:${s}`;
    }

    function syncDuration() {
        if (audio.duration && !isNaN(audio.duration) && isFinite(audio.duration)) {
            if (totalTimeElement) totalTimeElement.textContent = formatDuration(audio.duration);
        }
    }

    // Loaded metadata & duration update events
    audio.addEventListener('loadedmetadata', syncDuration);
    audio.addEventListener('durationchange', syncDuration);
    audio.addEventListener('canplay', syncDuration);

    // Seek bar click
    if (progressBar) {
        progressBar.addEventListener('click', function(e) {
            const rect = this.getBoundingClientRect();
            const clickX = e.clientX - rect.left;
            const width = rect.width;
            if (audio.duration && !isNaN(audio.duration)) {
                audio.currentTime = (clickX / width) * audio.duration;
            }
        });
    }

    // Volume bar click
    if (volumeBar) {
        volumeBar.addEventListener('click', function(e) {
            const rect = this.getBoundingClientRect();
            const clickX = Math.max(0, Math.min(e.clientX - rect.left, rect.width));
            currentVolume = clickX / rect.width;
            audio.volume = currentVolume;
            isMuted = false;
            audio.muted = false;
            if (muteBtn) muteBtn.innerHTML = volumeHighIcon;
            if (volume) volume.style.width = (currentVolume * 100) + '%';
        });
    }

    // On track ended
    audio.addEventListener('ended', function() {
        if (trackList.length > 1) {
            if (nextBtn) nextBtn.click();
        } else {
            isPlaying = false;
            if (playPauseBtn) playPauseBtn.innerHTML = playIcon;
            if (progress) progress.style.width = '0%';
            if (currentTimeElement) currentTimeElement.textContent = '0:00';
        }
    });

    function normalizeAssetUrl(url) {
        if (!url) return '';
        url = url.trim();
        if (url.startsWith('http://') || url.startsWith('https://')) return url;
        if (url.startsWith('/sound/')) return url;
        if (url.startsWith('sound/')) return '/' + url;
        return '/sound/' + url.replace(/^\/+/, '');
    }

    function renderPlayerCover(title, cover) {
        if (!trackCoverElement) return;
        const initials = (title || 'SO').substring(0, 2).toUpperCase();

        if (cover && cover.trim() !== '') {
            const normalizedCover = normalizeAssetUrl(cover);
            const testImg = new Image();
            testImg.onload = function() {
                trackCoverElement.style.backgroundImage = `url('${normalizedCover}')`;
                trackCoverElement.style.backgroundColor = 'transparent';
                trackCoverElement.innerHTML = '';
            };
            testImg.onerror = function() {
                trackCoverElement.style.backgroundImage = 'none';
                trackCoverElement.style.backgroundColor = '#242424';
                trackCoverElement.innerHTML = `<span style="color: #FF5A3C; font-weight: 800; font-size: 14px;">${initials}</span>`;
            };
            testImg.src = normalizedCover;
        } else {
            trackCoverElement.style.backgroundImage = 'none';
            trackCoverElement.style.backgroundColor = '#242424';
            trackCoverElement.innerHTML = `<span style="color: #FF5A3C; font-weight: 800; font-size: 14px;">${initials}</span>`;
        }
    }

    // Player like button helper (Task 1a)
    function updatePlayerLikeBtn(trackId) {
        const playerLikeBtn = document.getElementById('player-like-btn');
        if (!playerLikeBtn) return;
        if (!trackId) {
            playerLikeBtn.style.display = 'none';
            playerLikeBtn.removeAttribute('data-item-id');
            return;
        }
        playerLikeBtn.style.display = 'inline-flex';
        playerLikeBtn.setAttribute('data-item-id', trackId);
        playerLikeBtn.setAttribute('data-item-type', 'music');

        const isLiked = !!(window.SOUND_USER_LIKED && window.SOUND_USER_LIKED.music && window.SOUND_USER_LIKED.music[trackId]);
        if (isLiked) {
            playerLikeBtn.classList.add('liked');
            playerLikeBtn.setAttribute('title', 'Unlike');
            playerLikeBtn.setAttribute('aria-label', 'Remove from favorites');
        } else {
            playerLikeBtn.classList.remove('liked');
            playerLikeBtn.setAttribute('title', 'Like');
            playerLikeBtn.setAttribute('aria-label', 'Add to favorites');
        }
    }

    // Load track function
    function loadTrack(index) {
        if (index < 0 || index >= trackList.length) return;

        const track = trackList[index];
        audio.src = normalizeAssetUrl(track.url);
        if (trackTitleElement) trackTitleElement.textContent = track.title || 'Unknown Track';
        if (trackArtistElement) trackArtistElement.textContent = track.artist || 'Unknown Artist';
        if (currentTimeElement) currentTimeElement.textContent = '0:00';
        if (totalTimeElement) totalTimeElement.textContent = '0:00';
        if (progress) progress.style.width = '0%';

        renderPlayerCover(track.title, track.cover);
        updatePlayerLikeBtn(track.id);
    }

    // Global helper to play a track immediately from cards / buttons
    window.playTrackDirect = function(url, title, artist, cover, id) {
        if (!url) return;
        audio.src = normalizeAssetUrl(url);
        if (trackTitleElement) trackTitleElement.textContent = title || 'Unknown Track';
        if (trackArtistElement) trackArtistElement.textContent = artist || 'Unknown Artist';
        if (currentTimeElement) currentTimeElement.textContent = '0:00';
        if (totalTimeElement) totalTimeElement.textContent = '0:00';
        if (progress) progress.style.width = '0%';

        renderPlayerCover(title, cover);
        updatePlayerLikeBtn(id);

        audio.play().then(function() {
            isPlaying = true;
            if (playPauseBtn) playPauseBtn.innerHTML = pauseIcon;
        }).catch(function(err) {
            console.warn('Playback error:', err);
        });
    };

    // Global tracklist updater
    window.updateTrackList = function(newTrackList) {
        if (Array.isArray(newTrackList) && newTrackList.length > 0) {
            trackList = newTrackList;
        } else {
            // Scan current DOM for any data-track elements
            scanPageForTracks();
        }
    };

    function scanPageForTracks() {
        const trackElements = document.querySelectorAll('[data-track-url]');
        if (trackElements.length > 0) {
            const found = [];
            trackElements.forEach(function(el) {
                found.push({
                    id: el.getAttribute('data-track-id') || el.getAttribute('data-id') || '',
                    url: el.getAttribute('data-track-url'),
                    title: el.getAttribute('data-track-title') || 'Unknown',
                    artist: el.getAttribute('data-track-artist') || 'Unknown',
                    cover: el.getAttribute('data-track-cover') || ''
                });
            });
            trackList = found;
        }
    }

    scanPageForTracks();

    // Toast notification utility for guest and error feedback
    function showToast(message, actionHtml) {
        let container = document.querySelector('.sound-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'sound-toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = 'sound-toast';
        toast.innerHTML = `<span>${message}</span>` + (actionHtml ? ` ${actionHtml}` : '');
        container.appendChild(toast);

        setTimeout(function() {
            toast.classList.add('hide');
            setTimeout(function() {
                if (toast.parentNode) toast.parentNode.removeChild(toast);
            }, 250);
        }, 4000);
    }

    // Helper to revert button state on failed requests
    function revertLikeState(itemType, itemId, wasLiked) {
        const matchingBtns = document.querySelectorAll(`[data-action="toggle-like"][data-item-type="${itemType}"][data-item-id="${itemId}"]`);
        matchingBtns.forEach(btn => {
            if (wasLiked) {
                btn.classList.add('liked');
                btn.setAttribute('title', 'Unlike');
                btn.setAttribute('aria-label', 'Remove from favorites');
            } else {
                btn.classList.remove('liked');
                btn.setAttribute('title', 'Like');
                btn.setAttribute('aria-label', 'Add to favorites');
            }
        });
        if (window.SOUND_USER_LIKED && window.SOUND_USER_LIKED[itemType]) {
            window.SOUND_USER_LIKED[itemType][itemId] = wasLiked;
        }
    }

    // Global like button click delegation (Task 1a & 1b)
    document.addEventListener('click', function(e) {
        const likeBtn = e.target.closest('[data-action="toggle-like"]');
        if (!likeBtn) return;

        // Stop propagation to prevent card play/navigation
        e.preventDefault();
        e.stopPropagation();

        const itemType = likeBtn.getAttribute('data-item-type') || 'music';
        const itemId = parseInt(likeBtn.getAttribute('data-item-id'), 10);

        if (!itemId) return;

        // Requirement 1b: For guests, show friendly message "Login to like" with link
        if (!window.SOUND_IS_LOGGED_IN) {
            showToast('Login to like', '<a href="/sound/login.php" class="sound-toast-login-link">Login</a>');
            return;
        }

        const wasLiked = likeBtn.classList.contains('liked');
        const newLiked = !wasLiked;

        // Optimistic UI update across all buttons matching this item
        const matchingBtns = document.querySelectorAll(`[data-action="toggle-like"][data-item-type="${itemType}"][data-item-id="${itemId}"]`);
        matchingBtns.forEach(btn => {
            if (newLiked) {
                btn.classList.add('liked');
                btn.setAttribute('title', 'Unlike');
                btn.setAttribute('aria-label', 'Remove from favorites');
            } else {
                btn.classList.remove('liked');
                btn.setAttribute('title', 'Like');
                btn.setAttribute('aria-label', 'Add to favorites');
            }
        });

        if (!window.SOUND_USER_LIKED) {
            window.SOUND_USER_LIKED = { music: {}, video: {} };
        }
        if (!window.SOUND_USER_LIKED[itemType]) {
            window.SOUND_USER_LIKED[itemType] = {};
        }
        window.SOUND_USER_LIKED[itemType][itemId] = newLiked;

        // Sync player like button if currently playing this track
        const playerLikeBtn = document.getElementById('player-like-btn');
        if (playerLikeBtn && playerLikeBtn.getAttribute('data-item-id') == itemId && itemType === 'music') {
            if (newLiked) {
                playerLikeBtn.classList.add('liked');
                playerLikeBtn.setAttribute('title', 'Unlike');
            } else {
                playerLikeBtn.classList.remove('liked');
                playerLikeBtn.setAttribute('title', 'Like');
            }
        }

        // Send AJAX request to /sound/favorites.php
        const csrfToken = window.SOUND_CSRF_TOKEN || (document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '');
        const formData = new URLSearchParams();
        formData.append('csrf_token', csrfToken);
        formData.append('item_type', itemType);
        formData.append('item_id', itemId);

        fetch('/sound/favorites.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': csrfToken
            },
            body: formData.toString()
        })
        .then(res => res.json())
        .then(data => {
            if (!data || !data.success) {
                revertLikeState(itemType, itemId, wasLiked);
                if (data && data.message) {
                    showToast(data.message, data.login_url ? `<a href="${data.login_url}" class="sound-toast-login-link">Login</a>` : '');
                }
                return;
            }

            // Requirement 1c: On liked.php, dynamically remove unliked card & handle empty state
            if (!data.liked && window.location.pathname.includes('liked.php')) {
                const card = likeBtn.closest('.card');
                if (card) {
                    card.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.9)';
                    setTimeout(() => {
                        const grid = card.closest('.card-grid');
                        const section = card.closest('.liked-section, [data-liked-section]');
                        card.remove();

                        // Check if this specific section (music or video) is now empty
                        if (grid && grid.querySelectorAll('.card').length === 0) {
                            if (section) {
                                section.style.display = 'none';
                            }
                        }

                        // Check if entire liked collection is now empty
                        const remainingCards = document.querySelectorAll('.card-grid .card');
                        if (remainingCards.length === 0) {
                            const emptyState = document.getElementById('liked-empty-state');
                            const populatedState = document.getElementById('liked-populated-state');
                            if (populatedState) populatedState.style.display = 'none';
                            if (emptyState) emptyState.style.display = 'block';
                        }
                    }, 260);
                }
            }
        })
        .catch(err => {
            console.warn('Favorite toggle error:', err);
            revertLikeState(itemType, itemId, wasLiked);
        });
    });

    // Event delegation for play buttons on cards or lists
    document.addEventListener('click', function(e) {
        // Exclude heart buttons or title/detail links from triggering playback
        if (e.target.closest('[data-action="toggle-like"], .like-btn, .card-title a, [data-action="view-details"]')) {
            return;
        }

        const playBtn = e.target.closest('.card-play-overlay, .btn-round-play, [data-action="play-track"]');
        if (playBtn) {
            const card = playBtn.closest('[data-track-url]') || playBtn;
            const url = card.getAttribute('data-track-url');
            if (url) {
                e.preventDefault();
                e.stopPropagation();
                const title = card.getAttribute('data-track-title') || 'Track';
                const artist = card.getAttribute('data-track-artist') || 'Artist';
                const cover = card.getAttribute('data-track-cover') || '';
                const id = card.getAttribute('data-track-id') || playBtn.getAttribute('data-id') || '';
                window.playTrackDirect(url, title, artist, cover, id);
            }
        }
    });

    // Spacebar keyboard shortcut for play/pause (when not typing in an input/textarea)
    document.addEventListener('keydown', function(e) {
        if (e.code === 'Space') {
            const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
            if (activeTag === 'input' || activeTag === 'textarea' || activeTag === 'select') {
                return;
            }
            e.preventDefault();
            if (playPauseBtn) playPauseBtn.click();
        }
    });

    // =========================================================================
    // SPA Pjax Navigation for Public Pages
    // =========================================================================
    function isExcludedUrl(urlStr) {
        try {
            const parsed = new URL(urlStr, window.location.origin);
            const path = parsed.pathname.toLowerCase();
            return path.includes('/admin') ||
                   path.endsWith('/login.php') ||
                   path.endsWith('/register.php') ||
                   path.endsWith('/logout.php');
        } catch (e) {
            return true;
        }
    }

    function updatePublicActiveNav(url) {
        try {
            const currentPath = new URL(url, window.location.origin).pathname;
            const navLinks = document.querySelectorAll('.sidebar nav a');
            navLinks.forEach(link => {
                const linkPath = new URL(link.href, window.location.origin).pathname;
                const isMatch = (linkPath === currentPath) ||
                                (currentPath.endsWith('/') && linkPath.endsWith('/index.php')) ||
                                (currentPath.endsWith('/index.php') && linkPath.endsWith('/'));

                if (isMatch) {
                    link.parentElement.classList.add('active');
                } else {
                    link.parentElement.classList.remove('active');
                }
            });
        } catch (e) {}
    }

    function loadPjaxPage(url, pushState = true) {
        if (isExcludedUrl(url) || isExcludedUrl(window.location.href)) {
            window.location.href = url;
            return;
        }

        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP error ' + response.status);
                }
                if (response.redirected && isExcludedUrl(response.url)) {
                    window.location.href = response.url;
                    return null;
                }
                return response.text();
            })
            .then(html => {
                if (!html) return;
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newMain = doc.querySelector('main');
                const currentMain = document.querySelector('main');

                if (!newMain || !currentMain) {
                    window.location.href = url;
                    return;
                }

                currentMain.innerHTML = newMain.innerHTML;
                if (doc.title) {
                    document.title = doc.title;
                }

                if (pushState) {
                    window.history.pushState({ pjax: true, url: url }, doc.title || '', url);
                }

                updatePublicActiveNav(url);

                // Sync top search input query with URL if on search page
                try {
                    const parsedUrl = new URL(url, window.location.origin);
                    const qParam = parsedUrl.searchParams.get('q');
                    const topInput = document.getElementById('top-search-input');
                    const clearBtn = document.getElementById('top-search-clear');
                    if (topInput && qParam !== null) {
                        topInput.value = qParam;
                        if (clearBtn) clearBtn.style.display = qParam.trim().length > 0 ? 'flex' : 'none';
                    }
                } catch (e) {}

                currentMain.scrollTop = 0;
                window.scrollTo(0, 0);

                // Scan page for new tracks
                scanPageForTracks();

                // Sync newly inserted like buttons with current window.SOUND_USER_LIKED
                if (window.SOUND_USER_LIKED) {
                    currentMain.querySelectorAll('[data-action="toggle-like"]').forEach(btn => {
                        const type = btn.getAttribute('data-item-type');
                        const id = btn.getAttribute('data-item-id');
                        if (type && id && window.SOUND_USER_LIKED[type] && window.SOUND_USER_LIKED[type][id]) {
                            btn.classList.add('liked');
                            btn.setAttribute('title', 'Unlike');
                            btn.setAttribute('aria-label', 'Remove from favorites');
                        }
                    });
                }

                if (typeof updateTrackList === 'function') {
                    updateTrackList();
                }
            })
            .catch(err => {
                console.warn('Navigation fetch failed, falling back to full page load:', err);
                window.location.href = url;
            });
    }

    // =========================================================================
    // Top Bar Search Bar (Task 2)
    // =========================================================================
    const topSearchInput = document.getElementById('top-search-input');
    const topSearchClear = document.getElementById('top-search-clear');
    const topSearchForm = document.getElementById('top-search-form');

    function syncSearchClearBtn() {
        if (!topSearchInput || !topSearchClear) return;
        if (topSearchInput.value.trim().length > 0) {
            topSearchClear.style.display = 'flex';
        } else {
            topSearchClear.style.display = 'none';
        }
    }

    if (topSearchInput) {
        syncSearchClearBtn();
        topSearchInput.addEventListener('input', syncSearchClearBtn);
        topSearchInput.addEventListener('change', syncSearchClearBtn);

        if (topSearchClear) {
            topSearchClear.addEventListener('click', function(e) {
                e.preventDefault();
                topSearchInput.value = '';
                syncSearchClearBtn();
                topSearchInput.focus();
            });
        }
    }

    if (topSearchForm && !isExcludedUrl(window.location.href)) {
        topSearchForm.addEventListener('submit', function(e) {
            const query = topSearchInput ? topSearchInput.value.trim() : '';
            const searchUrl = '/sound/search.php' + (query ? '?q=' + encodeURIComponent(query) : '');
            if (typeof loadPjaxPage === 'function') {
                e.preventDefault();
                loadPjaxPage(searchUrl, true);
            }
        });
    }

    // Link click interception for SPA navigation
    if (!isExcludedUrl(window.location.href)) {
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (!link || !link.href) return;

            // Don't intercept external, target="_blank", modifier keys, download, or anchor hashes
            if (!link.href.startsWith(window.location.origin)) return;
            if (link.target === '_blank' || link.target === '_top' || link.target === '_parent') return;
            if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
            if (link.hasAttribute('download')) return;

            try {
                const targetUrl = new URL(link.href);
                if (targetUrl.pathname === window.location.pathname && targetUrl.search === window.location.search && targetUrl.hash) {
                    return;
                }
            } catch (err) {}

            // Must NOT run on admin or login/register/logout
            if (isExcludedUrl(link.href) || isExcludedUrl(window.location.href)) {
                return;
            }

            e.preventDefault();
            loadPjaxPage(link.href, true);
        });

        // Handle popstate for browser Back/Forward navigation
        window.addEventListener('popstate', function(e) {
            if (isExcludedUrl(window.location.href)) {
                window.location.reload();
                return;
            }
            loadPjaxPage(window.location.href, false);
        });
    }
});