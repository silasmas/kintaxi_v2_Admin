<?php

return [

    'title' => 'Login',

    'heading' => 'Sign in',

    'actions' => [

        'register' => [
            'before' => 'or',
            'label' => 'sign up for an account',
        ],

        'request_password_reset' => [
            'label' => 'Forgot password?',
        ],

    ],

    'form' => [

        'email' => [
            'label' => 'Email address',
        ],

        'login' => [
            'label' => 'Email, phone or username',
            'placeholder' => 'Enter your email, phone number or username',
        ],

        'password' => [
            'label' => 'Password',
        ],

        'remember' => [
            'label' => 'Remember me',
        ],

        'actions' => [

            'authenticate' => [
                'label' => 'Sign in',
            ],

        ],

    ],

    'messages' => [

        'failed' => 'The credentials you entered are incorrect. Please check your email/phone/username and password.',

        'access_denied' => 'You do not have permission to access this admin panel.',

    ],

    'notifications' => [

        'throttled' => [
            'title' => 'Too many login attempts',
            'body' => 'Please try again in :seconds seconds.',
        ],

    ],

];
