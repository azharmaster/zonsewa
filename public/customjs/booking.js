// Function to update price and total days
const minus = document.getElementById("Minus");
const plus = document.getElementById("Plus");
const count = document.getElementById("CountDays");
const days = document.getElementById("Days");
const totalPrice = document.getElementById("Total");
const productPrice = document.getElementById("productPrice");
const duration = document.getElementById("duration");
const defaultPrice = parseFloat(productPrice.value); // Ensure price is a number

function updateTotalPrice() {
    let subTotal = days.value * defaultPrice;
    totalPrice.innerText = "RM " + subTotal.toLocaleString('id-ID');
}

minus.addEventListener("click", function () {
    let currentCount = parseInt(count.innerText);
    if (currentCount > 1) {
        currentCount -= 1;
        count.innerText = currentCount;
        days.value = currentCount;
        duration.value = currentCount;
        updateTotalPrice();
    }
});

plus.addEventListener("click", function () {
    let currentCount = parseInt(count.innerText);
    currentCount += 1;
    count.innerText = currentCount;
    days.value = currentCount;
    duration.value = currentCount;
    updateTotalPrice();
});

days.addEventListener("change", function () {
    let currentCount = parseInt(days.value);
    if (currentCount < 1) {
        currentCount = 1;
        days.value = currentCount;
    }
    count.innerText = currentCount;
    updateTotalPrice();
});

// Initialize total price on page load
updateTotalPrice();

// Function for date picker
const datePicker = document.getElementById('date');
const btnText = document.getElementById('DateTriggerBtn');

datePicker.addEventListener('change', function () {
    if (datePicker.value) {
        btnText.innerText = datePicker.value;
        btnText.classList.add("font-semibold");
    } else {
        btnText.innerText = "Select date";
        btnText.classList.remove("font-semibold");
    }
});

// Initialize date picker text on page load
if (datePicker.value) {
    btnText.innerText = datePicker.value;
    btnText.classList.add("font-semibold");
} else {
    btnText.innerText = "Select date";
    btnText.classList.remove("font-semibold");
}

// Function for tabs like bootstrap
document.addEventListener("DOMContentLoaded", function () {
    window.openPage = function (pageName, elmnt) {
        let i, tabcontent, tablinks;

        // Hide all tab content
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].classList.add("hidden");
        }

        // Remove active class from all tab links
        tablinks = document.getElementsByClassName("tablink");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].classList.remove("active", "ring-2", "ring-[#FCCF2F]");
        }

        // Show the current tab content and add active class to the clicked tab link
        document.getElementById(pageName).classList.remove("hidden");
        elmnt.classList.add("active", "ring-2", "ring-[#FCCF2F]");
    };

    // Get the element with id="defaultOpen" and click on it
    document.getElementById("defaultOpen").click();
});

// Function for changing required attributes and setting address automatically
function toggleRequiredOptions() {
    const pickupRadio = document.querySelector('input[name="delivery_type"][value="pickup"]');
    const deliveryRadio = document.querySelector('input[name="delivery_type"][value="home_delivery"]');
    const storeRadios = document.querySelectorAll('input[name="store_id"]');
    const addressTextarea = document.getElementById('address');

    if (pickupRadio.checked) {
        // If pickup is selected, set address to "pickup in store" and disable the field
        storeRadios.forEach(radio => {
            radio.required = true;
        });
        addressTextarea.required = false;
        addressTextarea.value = "pickup in store"; // Automatically set address
        addressTextarea.disabled = true; // Disable the address field
    } else if (deliveryRadio.checked) {
        // If home_delivery is selected, allow manual input for address
        storeRadios.forEach(radio => {
            radio.required = false;
        });
        addressTextarea.required = true;
        addressTextarea.value = ""; // Clear the address field
        addressTextarea.disabled = false; // Enable the address field
    }
}

// Add event listeners for delivery type radios
document.querySelectorAll('input[name="delivery_type"]').forEach(radio => {
    radio.addEventListener('change', toggleRequiredOptions);
});

// Initialize required fields on page load
toggleRequiredOptions();