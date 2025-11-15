<?php
$chatUrl = isset($chatUrl) ? $chatUrl : '../user/chat.php';
$chatLabel = isset($chatLabel) ? $chatLabel : 'Chat dengan Admin';
?>

<style>
    .chat-bubble {
        position: fixed;
        right: 30px;
        bottom: 30px;
        width: 65px;
        height: 65px;
        border-radius: 50%;
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        box-shadow: 0 10px 40px rgba(17, 153, 142, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 99999;
        cursor: grab;
        user-select: none;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        opacity: 0.95;
        animation: pulse-bubble 3s infinite;
    }

    @keyframes pulse-bubble {
        0%, 100% {
            box-shadow: 0 10px 40px rgba(17, 153, 142, 0.5);
        }
        50% {
            box-shadow: 0 10px 50px rgba(17, 153, 142, 0.8), 0 0 0 15px rgba(17, 153, 142, 0.1);
        }
    }

    .chat-bubble:hover {
        transform: scale(1.1);
        box-shadow: 0 15px 50px rgba(17, 153, 142, 0.7);
    }

    .chat-bubble.dragging {
        opacity: 0.9;
        cursor: grabbing;
        transform: scale(1.05);
        box-shadow: 0 20px 60px rgba(17, 153, 142, 0.6);
    }

    .chat-bubble a {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        color: white;
        text-decoration: none;
        border-radius: 50%;
        position: relative;
    }

    .chat-icon {
        width: 32px;
        height: 32px;
        display: block;
        pointer-events: none;
        filter: drop-shadow(2px 2px 4px rgba(0, 0, 0, 0.2));
        animation: bounce-icon 2s infinite;
    }

    @keyframes bounce-icon {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }

    .chat-bubble::before {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        z-index: -1;
        animation: ripple 2s infinite;
    }

    @keyframes ripple {
        0% {
            transform: scale(1);
            opacity: 1;
        }
        100% {
            transform: scale(1.3);
            opacity: 0;
        }
    }

    /* Notification badge */
    .chat-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: linear-gradient(135deg, #ff4444 0%, #cc0000 100%);
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
        font-family: 'Poppins', sans-serif;
        box-shadow: 0 4px 15px rgba(255, 68, 68, 0.5);
        animation: badge-pulse 2s infinite;
        border: 3px solid white;
    }

    @keyframes badge-pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.2); }
    }

    /* Tooltip */
    .chat-tooltip {
        position: absolute;
        right: 80px;
        top: 50%;
        transform: translateY(-50%);
        background: white;
        color: #333;
        padding: 12px 20px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        white-space: nowrap;
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        font-size: 0.9rem;
        opacity: 0;
        pointer-events: none;
        transition: all 0.3s;
    }

    .chat-tooltip::after {
        content: '';
        position: absolute;
        right: -8px;
        top: 50%;
        transform: translateY(-50%);
        border-left: 8px solid white;
        border-top: 8px solid transparent;
        border-bottom: 8px solid transparent;
    }

    .chat-bubble:hover .chat-tooltip {
        opacity: 1;
    }

    @media (max-width: 768px) {
        .chat-bubble {
            width: 60px;
            height: 60px;
            right: 20px;
            bottom: 20px;
        }

        .chat-icon {
            width: 28px;
            height: 28px;
        }

        .chat-tooltip {
            display: none;
        }

        .chat-badge {
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
        }
    }
</style>

<div id="chat-bubble" class="chat-bubble" role="button" aria-label="<?= htmlspecialchars($chatLabel) ?>" tabindex="0">
    <a id="chat-bubble-link" href="<?= htmlspecialchars($chatUrl) ?>" title="<?= htmlspecialchars($chatLabel) ?>">
        <!-- Chat Icon SVG -->
        <svg class="chat-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
            <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H5.17L4 17.17V4h16v12z"/>
            <circle cx="12" cy="11" r="1"/>
            <circle cx="8" cy="11" r="1"/>
            <circle cx="16" cy="11" r="1"/>
        </svg>

        
        <!-- Tooltip -->
        <span class="chat-tooltip"><?= htmlspecialchars($chatLabel) ?></span>
    </a>
</div>

<script>
(function() {
    const bubble = document.getElementById('chat-bubble');
    const link = document.getElementById('chat-bubble-link');
    if (!bubble) return;

    const STORAGE_KEY = 'chatBubblePosition_v2';
    const viewportPadding = 20;
    
    let pos = { left: null, top: null, right: 30, bottom: 30 };

    // Load saved position
    try {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved) {
            const p = JSON.parse(saved);
            if (p && typeof p === 'object') pos = p;
        }
    } catch (e) {
        console.warn('Failed to load chat bubble position:', e);
    }

    // Apply position
    function applyPosition() {
        bubble.style.left = pos.left !== null ? `${pos.left}px` : 'auto';
        bubble.style.top = pos.top !== null ? `${pos.top}px` : 'auto';
        bubble.style.right = pos.right !== null ? `${pos.right}px` : 'auto';
        bubble.style.bottom = pos.bottom !== null ? `${pos.bottom}px` : 'auto';
    }
    applyPosition();

    // Get pointer coordinates
    function getPoint(e) {
        if (e.touches && e.touches[0]) {
            return { x: e.touches[0].clientX, y: e.touches[0].clientY };
        }
        return { x: e.clientX, y: e.clientY };
    }

    let dragging = false;
    let start = { x: 0, y: 0 };
    let orig = { left: 0, top: 0 };
    let hasMoved = false;

    function startDrag(e) {
        dragging = true;
        hasMoved = false;
        bubble.classList.add('dragging');
        
        const pt = getPoint(e);
        start.x = pt.x;
        start.y = pt.y;

        const rect = bubble.getBoundingClientRect();
        orig.left = rect.left;
        orig.top = rect.top;

        bubble.dataset.isDragging = '0';
        e.preventDefault && e.preventDefault();
    }

    function onMove(e) {
        if (!dragging) return;
        
        const pt = getPoint(e);
        const dx = pt.x - start.x;
        const dy = pt.y - start.y;
        
        // Mark as moved if distance > 5px
        if (Math.abs(dx) > 5 || Math.abs(dy) > 5) {
            hasMoved = true;
        }

        let newLeft = orig.left + dx;
        let newTop = orig.top + dy;

        // Keep inside viewport
        const vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
        const vh = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);
        const rect = bubble.getBoundingClientRect();
        const w = rect.width;
        const h = rect.height;

        newLeft = Math.max(viewportPadding, Math.min(newLeft, vw - w - viewportPadding));
        newTop = Math.max(viewportPadding, Math.min(newTop, vh - h - viewportPadding));

        pos.left = Math.round(newLeft);
        pos.top = Math.round(newTop);
        pos.right = null;
        pos.bottom = null;
        applyPosition();

        if (hasMoved) {
            bubble.dataset.isDragging = '1';
        }
    }

    function endDrag(e) {
        if (!dragging) return;
        dragging = false;
        bubble.classList.remove('dragging');

        // Save position
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(pos));
        } catch (err) {
            console.warn('Failed to save chat bubble position:', err);
        }

        // Reset drag flag after delay
        setTimeout(() => {
            bubble.dataset.isDragging = '0';
            hasMoved = false;
        }, 100);
    }

    // Mouse events
    bubble.addEventListener('mousedown', function(e) {
        if (e.button !== 0) return;
        startDrag(e);
        
        const mouseMoveHandler = (ev) => onMove(ev);
        const mouseUpHandler = (ev) => {
            window.removeEventListener('mousemove', mouseMoveHandler);
            window.removeEventListener('mouseup', mouseUpHandler);
            endDrag(ev);
        };
        
        window.addEventListener('mousemove', mouseMoveHandler);
        window.addEventListener('mouseup', mouseUpHandler);
    });

    // Touch events
    bubble.addEventListener('touchstart', function(e) {
        startDrag(e);
        
        const touchMoveHandler = (ev) => onMove(ev);
        const touchEndHandler = (ev) => {
            window.removeEventListener('touchmove', touchMoveHandler);
            window.removeEventListener('touchend', touchEndHandler);
            endDrag(ev);
        };
        
        window.addEventListener('touchmove', touchMoveHandler, { passive: false });
        window.addEventListener('touchend', touchEndHandler);
    }, { passive: true });

    // Prevent navigation when dragging
    link.addEventListener('click', function(e) {
        if (bubble.dataset.isDragging === '1') {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    });

    // Keyboard accessibility
    bubble.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            link.click();
        }
    });

    // Reposition on window resize
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            const vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
            const vh = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);
            const rect = bubble.getBoundingClientRect();
            const w = rect.width;
            const h = rect.height;

            if (pos.left !== null) {
                pos.left = Math.max(viewportPadding, Math.min(pos.left, vw - w - viewportPadding));
            }
            if (pos.top !== null) {
                pos.top = Math.max(viewportPadding, Math.min(pos.top, vh - h - viewportPadding));
            }
            applyPosition();
        }, 250);
    });
})();
</script>