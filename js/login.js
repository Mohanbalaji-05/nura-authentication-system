$(document).ready(function () {

    $('#loginForm').on('submit', function (event) {

        event.preventDefault();

        const email = $('#email').val().trim();
        const password = $('#password').val();

        const submitButton = $('#loginForm button[type="submit"]');

        // Validate email
        if (email === '') {
            alert('Email is required.');
            return;
        }

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
        if (password === '') {
            alert('Password is required.');
            return;
        }

        // Prevent multiple login requests
        submitButton.prop('disabled', true);

        $.ajax({
            url: 'php/login.php',
            method: 'POST',
            contentType: 'application/json',

            data: JSON.stringify({
                email: email,
                password: password
            }),

            success: function (response) {

                if (response.success) {

                    alert(response.message);

                    window.location.href = 'profile.html';

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