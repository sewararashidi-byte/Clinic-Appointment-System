document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("registrationForm");

    form.addEventListener("submit", function (event) {
        const fullName = document.getElementById("full_name").value.trim();
        const age = document.getElementById("age").value.trim();
        const phone = document.getElementById("phone").value.trim();
        const email = document.getElementById("email").value.trim();
        const password = document.getElementById("password").value;
        const confirmPassword =
            document.getElementById("confirm_password").value;

        const errors = [];

        if (
            fullName === "" ||
            age === "" ||
            phone === "" ||
            email === "" ||
            password === "" ||
            confirmPassword === ""
        ) {
            errors.push("All registration fields are required.");
        }

        if (age !== "" && (isNaN(age) || Number(age) <= 0)) {
            errors.push("Please enter a valid age.");
        }

        const emailPattern =
            /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (email !== "" && !emailPattern.test(email)) {
            errors.push("Please enter a valid email address.");
        }

        if (password !== "" && password.length < 8) {
            errors.push(
                "Password must contain at least 8 characters."
            );
        }

        if (password !== "" && !/[A-Z]/.test(password)) {
            errors.push(
                "Password must contain at least one uppercase letter."
            );
        }

        if (password !== "" && !/[^a-zA-Z0-9]/.test(password)) {
            errors.push(
                "Password must contain at least one special character."
            );
        }

        if (password !== confirmPassword) {
            errors.push(
                "Password and confirm password do not match."
            );
        }

        if (errors.length > 0) {
            event.preventDefault();
            alert(errors.join("\n"));
        }
    });
});