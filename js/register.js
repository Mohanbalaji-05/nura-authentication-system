$(document).ready(function () {

    $('#registerForm').on('submit', function (event) {

        event.preventDefault();

        const username = $('#username').val().trim();
        const email = $('#email').val().trim();
        const password = $('#password').val();

        const submitButton = $('#registerForm button[type="submit"]');

        // Validate username
        if (username === '') {
            alert('Username is required.');
            return;
        }

        if (username.length > 50) {
            alert('Username must not exceed 50 characters.');
            return;
        }

        // Validate email
        if (email === '') {
            alert('Email is required.');
            return;
        }

        // Simple email validation
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!emailPattern.test(email)) {
            alert('Please enter a valid email address.');
            return;
        }

        if (email.length > 100) {
            alert('Email must not exceed 100 characters.');
            return;
        }

        // Validate password
        if (password.length < 8) {
            alert('Password must be at least 8 characters.');
            return;
        }

        // Prevent multiple submissions
        submitButton.prop('disabled', true);

        $.ajax({
            url: 'php/register.php',
            method: 'POST',
            contentType: 'application/json',

            data: JSON.stringify({
                username: username,
                email: email,
                password: password
            }),

            success: function (response) {

                if (response.success) {

                    alert(response.message);

                    window.location.href = 'login.html';

                } else {

                    alert(response.message);

                    submitButton.prop('disabled', false);
                }
            },

            error: function (xhr) {

                console.error(xhr.responseText);

                alert('Something went wrong. Please try again.');

                submitButton.prop('disabled', false);
            }
        });

    });

});