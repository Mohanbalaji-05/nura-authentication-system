$(document).ready(function () {

    // Load profile when the page opens
    loadProfile();

    // Save profile
    $('#profileForm').on('submit', function (event) {

        event.preventDefault();

        const fullName = $('#fullName').val().trim();
        const phone = $('#phone').val().trim();
        const age = $('#age').val();
        const address = $('#address').val().trim();

        const submitButton = $('#profileForm button[type="submit"]');

        // Validate full name
        if (fullName === '') {
            alert('Full name is required.');
            return;
        }

        if (fullName.length > 100) {
            alert('Full name must not exceed 100 characters.');
            return;
        }

        // Validate phone
        if (phone === '') {
            alert('Phone number is required.');
            return;
        }

        const phonePattern = /^[0-9+\-\s()]{7,20}$/;

        if (!phonePattern.test(phone)) {
            alert('Please enter a valid phone number.');
            return;
        }

        // Validate age
        if (age !== '' && (isNaN(age) || age < 1 || age > 120)) {
            alert('Please enter a valid age.');
            return;
        }

        // Validate address
        if (address.length > 255) {
            alert('Address must not exceed 255 characters.');
            return;
        }

        // Prevent multiple submissions
        submitButton.prop('disabled', true);

        $.ajax({
            url: 'php/profile.php',
            method: 'POST',
            contentType: 'application/json',

            data: JSON.stringify({
                full_name: fullName,
                phone: phone,
                age: age,
                address: address
            }),

            success: function (response) {

                if (response.success) {

                    alert(response.message);

                } else {

                    alert(response.message);
                }

                submitButton.prop('disabled', false);
            },

            error: function (xhr) {

                console.error(xhr.responseText);

                alert('Something went wrong. Please try again.');

                submitButton.prop('disabled', false);
            }
        });

    });


    // Logout
    $('#logoutBtn').on('click', function () {

        const logoutButton = $('#logoutBtn');

        logoutButton.prop('disabled', true);

        $.ajax({
            url: 'php/logout.php',
            method: 'POST',

            success: function (response) {

                if (response.success) {

                    alert(response.message);

                    window.location.href = 'login.html';

                } else {

                    alert(response.message);

                    logoutButton.prop('disabled', false);
                }
            },

            error: function (xhr) {

                console.error(xhr.responseText);

                alert('Logout failed. Please try again.');

                logoutButton.prop('disabled', false);
            }
        });

    });


    // Load profile
    function loadProfile() {

        $.ajax({
            url: 'php/profile.php',
            method: 'GET',

            success: function (response) {

                if (response.success) {

                    $('#fullName').val(response.profile.full_name);
                    $('#phone').val(response.profile.phone);
                    $('#age').val(response.profile.age ?? '');
                    $('#address').val(response.profile.address);

                } else {

                    alert(response.message);

                    if (
                        response.message.includes('logged in') ||
                        response.message.includes('expired') ||
                        response.message.includes('Invalid session')
                    ) {
                        window.location.href = 'login.html';
                    }
                }
            },

            error: function (xhr) {

                console.error(xhr.responseText);

                alert('Unable to load profile.');
            }
        });

    }

});