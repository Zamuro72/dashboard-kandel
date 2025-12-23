// Update datetime
function updateDateTime() {
    const now = new Date();
    const options = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    
    const datetimeElement = document.getElementById('datetime');
    if (datetimeElement) {
        datetimeElement.textContent = now.toLocaleDateString('id-ID', options);
    }
}

// Update setiap menit
updateDateTime();
setInterval(updateDateTime, 60000);

// Preview image sebelum upload
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Confirm delete
function confirmDelete(message) {
    return confirm(message || 'Yakin ingin menghapus data ini?');
}

// Auto hide alert
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});