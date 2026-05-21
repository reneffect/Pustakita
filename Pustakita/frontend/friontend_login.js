// Select form elements
const form = document.querySelector("form");
const usernameInput = document.getElementById("username");
const passwordInput = document.getElementById("password");

// Add event listener for form submission
form.addEventListener("submit", function (event) {
    // Clear previous error messages
    clearErrors();

    // Validate inputs
    let isValid = true;

    if (usernameInput.value.trim() === "") {
        showError(usernameInput, "Username tidak boleh kosong.");
        isValid = false;
    }

    if (passwordInput.value.trim() === "") {
        showError(passwordInput, "Password tidak boleh kosong.");
        isValid = false;
    }

    // Prevent form submission if validation fails
    if (!isValid) {
        event.preventDefault();
    }
});

// Function to show error message
function showError(input, message) {
    const errorElement = document.createElement("p");
    errorElement.className = "text-red-500 text-sm mb-2";
    errorElement.textContent = message;
    input.insertAdjacentElement("afterend", errorElement);
    input.classList.add("border-red-500");
}

// Function to clear all error messages
function clearErrors() {
    const errorMessages = document.querySelectorAll(".text-red-500");
    errorMessages.forEach((error) => error.remove());

    const inputs = document.querySelectorAll("input");
    inputs.forEach((input) => input.classList.remove("border-red-500"));
}