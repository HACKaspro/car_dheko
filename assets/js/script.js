// Initialize Bootstrap tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
});

// Function to calculate booking price
function calculateBookingPrice(startDate, endDate, pricePerDay) {
    const start = new Date(startDate);
    const end = new Date(endDate);
    const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
    return days * pricePerDay;
}

// Function to format date
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

// Handle booking modal price calculation
document.addEventListener('DOMContentLoaded', function() {
    const bookingModal = document.getElementById('bookModal');
    if (bookingModal) {
        bookingModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const carId = button.getAttribute('data-car-id');
            const carName = button.getAttribute('data-car-name');
            const carPrice = parseFloat(button.getAttribute('data-car-price'));
            
            document.getElementById('modalCarId').value = carId;
            document.getElementById('modalCarName').textContent = carName;
            document.getElementById('modalCarPrice').textContent = `₹${carPrice.toFixed(2)} per day`;
            
            // Set up date change listeners
            const startDateInput = bookingModal.querySelector('input[name="start_date"]');
            const endDateInput = bookingModal.querySelector('input[name="end_date"]');
            const priceDisplay = bookingModal.querySelector('#priceCalculation');
            
            function updatePrice() {
                if (startDateInput.value && endDateInput.value) {
                    const startDate = new Date(startDateInput.value);
                    const endDate = new Date(endDateInput.value);
                    
                    if (startDate <= endDate) {
                        const days = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
                        const totalPrice = days * carPrice;
                        priceDisplay.innerHTML = `
                            <strong>${days} day${days > 1 ? 's' : ''}</strong> × ₹${carPrice.toFixed(2)} = 
                            <strong>₹${totalPrice.toFixed(2)}</strong>
                        `;
                    } else {
                        priceDisplay.innerHTML = '<div class="text-danger">End date must be after start date</div>';
                    }
                }
            }
            
            startDateInput.addEventListener('change', updatePrice);
            endDateInput.addEventListener('change', updatePrice);
            
            // Set minimum dates
            const today = new Date().toISOString().split('T')[0];
            startDateInput.min = today;
            endDateInput.min = today;
        });
    }
});

// Handle flash messages
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});