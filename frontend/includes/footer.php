    <!-- Global Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
        <div id="appToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2" id="toastMessage">
                    <!-- Dynamic Message -->
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
</div><!-- End app-layout -->

<!-- ===================================================
     SMART CAMPUS - ULTRA-INTERACTIVE CONFIRMATION MODAL
     (Replaces the browser's native black box confirm dialog)
     =================================================== -->
<div id="smartConfirmBackdrop" class="smart-confirm-backdrop" tabindex="-1" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="smart-confirm-overlay" onclick="window._smartConfirmOnBackdrop()"></div>
    <div class="smart-confirm-box" id="smartConfirmBox" role="document">
        <button type="button" class="smart-confirm-close-btn" onclick="window._smartConfirmReject('close')" aria-label="Close" title="Cancel (Esc)">
            <i class="bi bi-x-lg"></i>
        </button>
        
        <!-- Animated Pulsing Warning Badge -->
        <div class="smart-confirm-icon-wrap" id="smartConfirmIconWrap">
            <div class="smart-confirm-pulse-ring"></div>
            <div class="smart-confirm-icon-core" id="smartConfirmIconCore">
                <i class="bi bi-trash3-fill" id="smartConfirmIcon"></i>
            </div>
        </div>

        <!-- Header Content -->
        <div class="text-center">
            <h4 class="smart-confirm-title" id="smartConfirmTitle">Remove Student</h4>
            <p class="smart-confirm-subtitle" id="smartConfirmSubtitle">Are you sure you want to remove this record from the system?</p>
        </div>

        <!-- Target Info Capsule -->
        <div class="smart-confirm-target-capsule" id="smartConfirmTargetCapsule">
            <div class="d-flex align-items-center gap-3">
                <div class="smart-confirm-avatar" id="smartConfirmAvatar">AM</div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="smart-confirm-name text-truncate" id="smartConfirmName">Arjun Menon</div>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <span class="smart-confirm-badge" id="smartConfirmBadge">Student</span>
                        <span class="smart-confirm-meta text-truncate" id="smartConfirmMeta">ID: #23CS008</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Impact Warnings -->
        <div class="smart-confirm-impact-list" id="smartConfirmImpactList">
            <div class="smart-confirm-impact-item">
                <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                <span id="smartConfirmImpact1">All attendance logs, assignments and exam marks will be wiped.</span>
            </div>
            <div class="smart-confirm-impact-item">
                <i class="bi bi-shield-x text-danger"></i>
                <span id="smartConfirmImpact2">This action is permanent and cannot be undone.</span>
            </div>
        </div>

        <!-- Interactive Actions -->
        <div class="smart-confirm-actions">
            <button type="button" class="btn smart-confirm-btn-cancel" onclick="window._smartConfirmReject('cancel')" id="smartConfirmCancelBtn">
                <span id="smartConfirmCancelLabel">Keep Student</span>
                <kbd class="smart-confirm-kbd">Esc</kbd>
            </button>
            <button type="button" class="btn smart-confirm-btn-confirm" onclick="window._smartConfirmAccept()" id="smartConfirmOkBtn">
                <span class="smart-confirm-btn-content d-flex align-items-center justify-content-center gap-2" id="smartConfirmBtnContent">
                    <i class="bi bi-trash3-fill"></i>
                    <span id="smartConfirmOkLabel">Remove Student</span>
                </span>
                <span class="smart-confirm-spinner d-none" id="smartConfirmSpinner">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    <span>Removing...</span>
                </span>
                <kbd class="smart-confirm-kbd">↵</kbd>
            </button>
        </div>
    </div>
</div>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


<script>
    // Theme Management System
    function setTheme(themeName) {
        document.documentElement.setAttribute('data-theme', themeName);
        try {
            localStorage.setItem('smart_campus_theme', themeName);
        } catch(e) {}

        // Sync all theme controls in UI
        document.querySelectorAll('.theme-option-item, .theme-dot-btn').forEach(el => {
            const t = el.getAttribute('data-theme-name');
            if (t === themeName) {
                el.classList.add('active');
            } else {
                el.classList.remove('active');
            }
        });

        const labelMap = {
            'sandal': 'Light Sandal',
            'midnight': 'Midnight Indigo',
            'emerald': 'Cyber Emerald',
            'purple': 'Cosmic Amethyst',
            'sunset': 'Ruby Sunset',
            'sapphire': 'Ocean Sapphire',
            'oled': 'Obsidian Carbon',
            'light': 'Glacier Light'
        };
        const activeLabel = document.getElementById('currentThemeLabel');
        if (activeLabel && labelMap[themeName]) {
            activeLabel.textContent = labelMap[themeName];
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const activeTheme = localStorage.getItem('smart_campus_theme') || 'sandal';
        setTheme(activeTheme);
    });

    // Mobile Sidebar Toggle
    function toggleSidebar() {
        const sidebar = document.getElementById('appSidebar');
        if (sidebar) {
            sidebar.classList.toggle('show');
        }
    }

    // Toast Notification Utility
    function showToast(message, type = 'success') {
        const toastEl = document.getElementById('appToast');
        const toastMsg = document.getElementById('toastMessage');
        if (!toastEl || !toastMsg) return;

        toastEl.className = `toast align-items-center text-white border-0 ${type === 'success' ? 'bg-success' : 'bg-danger'}`;
        toastMsg.innerHTML = `<i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-circle-fill'} fs-5"></i> <span>${message}</span>`;
        
        const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
        toast.show();
    }

    // ===================================================
    // GLOBAL INTERACTIVE CONFIRMATION MODAL CONTROLLER
    // ===================================================
    let _smartConfirmResolve = null;

    window.showInteractiveConfirm = function(options = {}) {
        return new Promise((resolve) => {
            _smartConfirmResolve = resolve;

            const backdrop = document.getElementById('smartConfirmBackdrop');
            const box = document.getElementById('smartConfirmBox');
            if (!backdrop || !box) {
                // Fallback in case element is missing
                resolve(confirm(options.message || 'Are you sure?'));
                return;
            }

            // Defaults
            const title = options.title || 'Confirm Action';
            const subtitle = options.subtitle || 'Please confirm if you wish to proceed with this operation.';
            const name = options.name || 'Selected Item';
            const role = options.role || 'Record';
            const meta = options.meta || 'System Entity';
            const impact1 = options.impact1 || 'All associated attendance, marks, and records will be deleted.';
            const impact2 = options.impact2 || 'This action is irreversible and permanent.';
            const confirmText = options.confirmText || 'Confirm & Remove';
            const cancelText = options.cancelText || 'Cancel';
            
            // Generate initials for avatar
            const initials = options.avatar || name.split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase() || 'ID';

            // Populate DOM
            document.getElementById('smartConfirmTitle').textContent = title;
            document.getElementById('smartConfirmSubtitle').textContent = subtitle;
            document.getElementById('smartConfirmName').textContent = name;
            document.getElementById('smartConfirmAvatar').textContent = initials;
            document.getElementById('smartConfirmBadge').textContent = role;
            document.getElementById('smartConfirmMeta').textContent = meta;
            document.getElementById('smartConfirmImpact1').textContent = impact1;
            document.getElementById('smartConfirmImpact2').textContent = impact2;
            document.getElementById('smartConfirmOkLabel').textContent = confirmText;
            document.getElementById('smartConfirmCancelLabel').textContent = cancelText;

            // Reset button & spinner states
            const okBtn = document.getElementById('smartConfirmOkBtn');
            const btnContent = document.getElementById('smartConfirmBtnContent');
            const spinner = document.getElementById('smartConfirmSpinner');
            if (okBtn) okBtn.disabled = false;
            if (btnContent) btnContent.classList.remove('d-none');
            if (spinner) spinner.classList.add('d-none');

            // Open Modal with Animation
            backdrop.classList.remove('d-none');
            // Trigger reflow for transition
            void backdrop.offsetWidth;
            backdrop.classList.add('active');

            // Accessibility focus
            if (okBtn) okBtn.focus();

            // Keyboard listener for Escape & Enter
            const keyHandler = (e) => {
                if (e.key === 'Escape') {
                    e.preventDefault();
                    window._smartConfirmReject('esc');
                    document.removeEventListener('keydown', keyHandler);
                } else if (e.key === 'Enter' && !e.repeat) {
                    const activeEl = document.activeElement;
                    if (activeEl && activeEl.id === 'smartConfirmCancelBtn') {
                        window._smartConfirmReject('cancel');
                    } else {
                        window._smartConfirmAccept();
                    }
                    document.removeEventListener('keydown', keyHandler);
                }
            };
            window._smartConfirmKeyHandler = keyHandler;
            document.addEventListener('keydown', keyHandler);
        });
    };

    window._smartConfirmAccept = function() {
        const okBtn = document.getElementById('smartConfirmOkBtn');
        const btnContent = document.getElementById('smartConfirmBtnContent');
        const spinner = document.getElementById('smartConfirmSpinner');
        
        // Show loading state
        if (okBtn) okBtn.disabled = true;
        if (btnContent) btnContent.classList.add('d-none');
        if (spinner) spinner.classList.remove('d-none');

        if (window._smartConfirmKeyHandler) {
            document.removeEventListener('keydown', window._smartConfirmKeyHandler);
        }

        // Resolve true after a brief micro-transition
        if (_smartConfirmResolve) {
            _smartConfirmResolve(true);
            _smartConfirmResolve = null;
        }

        // Close after brief moment
        setTimeout(() => {
            window._closeSmartConfirm();
        }, 300);
    };

    window._smartConfirmReject = function(reason = 'cancel') {
        if (window._smartConfirmKeyHandler) {
            document.removeEventListener('keydown', window._smartConfirmKeyHandler);
        }
        if (_smartConfirmResolve) {
            _smartConfirmResolve(false);
            _smartConfirmResolve = null;
        }
        window._closeSmartConfirm();
    };

    window._smartConfirmOnBackdrop = function() {
        // Play interactive tactile wobble animation when clicking outside
        const box = document.getElementById('smartConfirmBox');
        if (box) {
            box.classList.remove('shake');
            void box.offsetWidth;
            box.classList.add('shake');
            setTimeout(() => box.classList.remove('shake'), 450);
        }
    };

    window._closeSmartConfirm = function() {
        const backdrop = document.getElementById('smartConfirmBackdrop');
        if (backdrop) {
            backdrop.classList.remove('active');
            setTimeout(() => {
                backdrop.classList.add('d-none');
            }, 300);
        }
    };
</script>
</body>
</html>

