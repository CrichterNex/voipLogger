<?php

test('the application returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(302); // Expecting a redirect response, typically to the login page
    $response->assertRedirect('/login'); // Ensure it redirects to the login page
});
