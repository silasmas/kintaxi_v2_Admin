<?php

return [

    'title' => 'Connexion',

    'heading' => 'Connectez-vous à votre compte',

    'actions' => [

        'register' => [
            'before' => 'ou',
            'label' => 'créer un compte',
        ],

        'request_password_reset' => [
            'label' => 'Mot de passe oublié ?',
        ],

    ],

    'form' => [

        'email' => [
            'label' => 'Adresse e-mail',
        ],

        'login' => [
            'label' => 'Email, téléphone ou nom d\'utilisateur',
            'placeholder' => 'Entrez votre email, numéro de téléphone ou nom d\'utilisateur',
        ],

        'password' => [
            'label' => 'Mot de passe',
        ],

        'remember' => [
            'label' => 'Se souvenir de moi',
        ],

        'actions' => [

            'authenticate' => [
                'label' => 'Connexion',
            ],

        ],

    ],

    'messages' => [

        'failed' => 'Les identifiants saisis sont incorrects. Vérifiez votre email/téléphone/nom d\'utilisateur et votre mot de passe.',

        'access_denied' => 'Vous n\'avez pas l\'autorisation d\'accéder à cet espace d\'administration.',

    ],

    'notifications' => [

        'throttled' => [
            'title' => 'Tentatives de connexion trop nombreuses. Veuillez essayer de nouveau dans :seconds secondes.',
            'body' => 'Merci de réessayer dans :seconds secondes.',
        ],

    ],

];
