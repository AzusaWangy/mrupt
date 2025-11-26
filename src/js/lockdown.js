// js/lockdown.js - VERSION FIXED
class MRUPTLockdown {
    constructor() {
        this.isLocked = false;
        this.attemptCount = 0;
        this.maxAttempts = 3;
        this.lockdownPassword = "MRUPT_LOGOUT_2024";
        this.fullscreenAttempted = false;
        
        this.init();
    }

    init() {
        console.log("🔒 MR UPT Lockdown INITIALIZING...");
        
        // Start lockdown
        this.startLockdown();
        this.setupEventListeners();
        
        // Coba fullscreen setelah user interaction
        document.addEventListener('click', this.attemptFullscreen.bind(this), { once: true });
        document.addEventListener('keydown', this.attemptFullscreen.bind(this), { once: true });
        
        // Juga coba saat DOM loaded
        document.addEventListener('DOMContentLoaded', () => {
            this.showLockdownWarning();
            setTimeout(() => this.attemptFullscreen(), 1000);
        });
    }

    attemptFullscreen() {
        if (this.fullscreenAttempted) return;
        this.fullscreenAttempted = true;
        
        console.log("🔄 Attempting fullscreen...");
        this.enterFullscreen();
    }

    startLockdown() {
        this.isLocked = true;
        console.log("🔒 MR UPT Lockdown ACTIVE");
        localStorage.setItem('mr_upt_lockdown', 'active');
    }

    enterFullscreen() {
        const elem = document.documentElement;
        
        const fullscreenPromise = elem.requestFullscreen?.() ||
                                elem.webkitRequestFullscreen?.() || 
                                elem.msRequestFullscreen?.();

        if (fullscreenPromise) {
            fullscreenPromise
                .then(() => {
                    console.log("✅ Fullscreen berhasil!");
                    this.showFullscreenSuccess();
                })
                .catch(err => {
                    console.log("❌ Fullscreen gagal:", err);
                    this.showFullscreenError();
                });
        } else {
            console.log("❌ Fullscreen tidak supported");
            this.showFullscreenNotSupported();
        }
    }

    showFullscreenSuccess() {
        const successMsg = document.createElement('div');
        successMsg.innerHTML = `
            <div class="fixed top-4 right-4 bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg z-[10000]">
                <i class="fas fa-check-circle mr-2"></i>Fullscreen Active
            </div>
        `;
        document.body.appendChild(successMsg);
        setTimeout(() => successMsg.remove(), 3000);
    }

    showFullscreenError() {
        const errorMsg = document.createElement('div');
        errorMsg.innerHTML = `
            <div class="fixed top-4 right-4 bg-orange-600 text-white px-4 py-2 rounded-lg shadow-lg z-[10000]">
                <i class="fas fa-exclamation-triangle mr-2"></i>Klik di mana saja untuk fullscreen
            </div>
        `;
        document.body.appendChild(errorMsg);
        
        // Retry on click
        document.addEventListener('click', () => {
            errorMsg.remove();
            this.enterFullscreen();
        }, { once: true });
    }

    showFullscreenNotSupported() {
        const notSupportedMsg = document.createElement('div');
        notSupportedMsg.innerHTML = `
            <div class="fixed top-4 right-4 bg-red-600 text-white px-4 py-2 rounded-lg shadow-lg z-[10000]">
                <i class="fas fa-times-circle mr-2"></i>Fullscreen tidak supported
            </div>
        `;
        document.body.appendChild(notSupportedMsg);
    }

    setupEventListeners() {
        // Block keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Block refresh
            if (e.key === 'F5' || (e.ctrlKey && e.key === 'r') || (e.ctrlKey && e.shiftKey && e.key === 'r')) {
                e.preventDefault();
                this.showWarningModal("Refresh diblokir! Silakan logout terlebih dahulu.");
                return false;
            }
            
            // Block exit shortcuts
            if (e.altKey || e.key === 'F4' || e.key === 'Tab' || e.key === 'Meta') {
                e.preventDefault();
                this.showWarningModal("Shortcut diblokir! Gunakan logout untuk keluar.");
                return false;
            }
            
            // Block F12 (Developer Tools)
            if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && e.key === 'I')) {
                e.preventDefault();
                this.showWarningModal("Developer Tools diblokir!");
                return false;
            }
        });

        // Detect fullscreen exit
        document.addEventListener('fullscreenchange', this.handleFullscreenChange.bind(this));
        document.addEventListener('webkitfullscreenchange', this.handleFullscreenChange.bind(this));

        // Prevent window close
        window.addEventListener('beforeunload', this.handleBeforeUnload.bind(this));

        // Detect tab switching
        window.addEventListener('blur', this.handleBlur.bind(this));
        
        // Prevent right click
        document.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            this.showWarningModal("Menu konteks diblokir!");
        });

        // Force fullscreen on any click (if not in fullscreen)
        document.addEventListener('click', () => {
            if (!document.fullscreenElement && !document.webkitFullscreenElement) {
                this.enterFullscreen();
            }
        });
    }

    handleFullscreenChange() {
        if (!document.fullscreenElement && !document.webkitFullscreenElement) {
            console.log("🚫 User keluar fullscreen, memaksa kembali...");
            setTimeout(() => {
                this.enterFullscreen();
                this.showWarningModal("Sistem dalam mode lockdown! Silakan logout untuk keluar.");
            }, 100);
        }
    }

    handleBeforeUnload(e) {
        if (this.isLocked) {
            e.preventDefault();
            e.returnValue = 'SISTEM MR UPT DALAM MODE LOCKDOWN! Silakan logout terlebih dahulu melalui menu.';
            return e.returnValue;
        }
    }

    handleBlur() {
        if (this.isLocked) {
            this.showWarningModal("Jangan tinggalkan halaman! Sistem dalam mode lockdown.");
        }
    }

    showLockdownWarning() {
        // Hapus existing warning dulu
        const existingWarning = document.getElementById('lockdown-warning');
        if (existingWarning) existingWarning.remove();
        
        const warning = document.createElement('div');
        warning.id = 'lockdown-warning';
        warning.innerHTML = `
            <div class="fixed top-4 left-1/2 transform -translate-x-1/2 bg-red-600 text-white px-6 py-3 rounded-lg shadow-lg z-[10000] flex items-center gap-3">
                <i class="fas fa-lock animate-pulse"></i>
                <span class="font-semibold">SISTEM DALAM MODE LOCKDOWN</span>
                <span class="text-sm">Gunakan logout untuk keluar</span>
            </div>
        `;
        document.body.appendChild(warning);
    }

    showWarningModal(message) {
        this.attemptCount++;
        
        // Hapus modal existing
        const existingModal = document.getElementById('lockdown-warning-modal');
        if (existingModal) existingModal.remove();
        
        const modal = document.createElement('div');
        modal.id = 'lockdown-warning-modal';
        modal.innerHTML = `
            <div class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-[99999]">
                <div class="bg-white rounded-2xl p-6 max-w-md mx-4 shadow-2xl">
                    <div class="text-center mb-4">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">LOCKDOWN ACTIVE</h3>
                        <p class="text-gray-700 mb-4">${message}</p>
                        <p class="text-sm text-red-600 font-medium">
                            Attempt: ${this.attemptCount}/${this.maxAttempts}
                        </p>
                    </div>
                    <div class="text-center">
                        <button onclick="this.parentElement.parentElement.parentElement.remove()" 
                                class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium">
                            Mengerti
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        if (this.attemptCount >= this.maxAttempts) {
            this.activateStrictLockdown();
        }
    }

    activateStrictLockdown() {
        const existingOverlay = document.getElementById('strict-lockdown-overlay');
        if (existingOverlay) existingOverlay.remove();
        
        const overlay = document.createElement('div');
        overlay.id = 'strict-lockdown-overlay';
        overlay.innerHTML = `
            <div class="fixed inset-0 bg-red-900 bg-opacity-90 flex items-center justify-center z-[99999]">
                <div class="text-center text-white p-8">
                    <i class="fas fa-ban text-6xl mb-4 animate-bounce"></i>
                    <h2 class="text-3xl font-bold mb-4">STRICT LOCKDOWN ACTIVATED</h2>
                    <p class="text-xl mb-2">Terlalu banyak attempt keluar!</p>
                    <p class="mb-6">Silakan LOGOUT melalui menu atau hubungi administrator.</p>
                    <div class="bg-white text-red-900 p-4 rounded-lg max-w-md mx-auto">
                        <p class="font-bold">📞 Hubungi Administrator:</p>
                        <p>UPT Komputer UNIPMA - Ext: 1234</p>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);
    }

    safeLogout() {
        this.isLocked = false;
        localStorage.removeItem('mr_upt_lockdown');
        
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        }
        
        setTimeout(() => {
            window.location.href = 'backend/logout.php';
        }, 500);
    }

    safeRedirect(url) {
        this.isLocked = false;
        localStorage.removeItem('mr_upt_lockdown');
        
        if (document.exitFullscreen) {
            document.exitFullscreen();
        }
        
        setTimeout(() => {
            window.location.href = url;
        }, 300);
    }

    emergencyExit() {
        const password = prompt("🔐 EMERGENCY EXIT - Masukkan password admin:");
        if (password === this.lockdownPassword) {
            this.safeLogout();
        } else {
            this.showWarningModal("Password emergency salah!");
        }
    }
}

// Initialize
const mrUptLockdown = new MRUPTLockdown();