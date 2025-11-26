// js/form-protection.js
let formEdited = false;

function enableFormProtection() {
    const form = document.querySelector('form');
    if (!form) return;
    
    // Deteksi perubahan form
    form.addEventListener('input', () => {
        formEdited = true;
    });
    
    // Prevent close/refresh jika ada perubahan
    window.addEventListener('beforeunload', (e) => {
        if (formEdited) {
            e.preventDefault();
            e.returnValue = 'Data yang sudah diinput akan hilang. Yakin ingin meninggalkan halaman?';
            return e.returnValue;
        }
    });
    
    // Safe submit - allow close setelah submit
    form.addEventListener('submit', () => {
        formEdited = false;
    });
}

// Safe exit untuk admin
function adminEmergencyExit() {
    const password = prompt("Masukkan password admin untuk keluar:");
    if (password === "MRUPT_ADMIN_2024") {
        formEdited = false;
        window.location.href = "dashboard.php";
    }
}