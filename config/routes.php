<?php
return [
    // Authentication Routes
    'GET /login' => 'AuthController@showLoginForm',
    'POST /login' => 'AuthController@login',
    'GET /register' => 'AuthController@showRegistrationForm',
    'POST /register' => 'AuthController@register',
    'GET /logout' => 'AuthController@logout',

    // Password Reset Routes
    'GET /forgot-password' => 'AuthController@showForgotPasswordForm',
    'POST /forgot-password' => 'AuthController@sendPasswordResetLink',
    'GET /reset-password/{token}' => 'AuthController@showResetPasswordForm',
    'POST /reset-password' => 'AuthController@resetPassword',

    // Chat Routes
    'GET /chat' => 'ChatController@index',
    'POST /chat/message' => 'ChatController@sendMessage',
    'GET /chat/messages/{friendId}' => 'ChatController@getChatMessages',

    // Friends Routes
    'GET /friends' => 'FriendController@list',
    'POST /friends/add' => 'FriendController@add',

    // Invite Routes
    'GET /invite/generate-link' => 'InviteController@generateInviteLink',
    'POST /invite/email' => 'InviteController@sendEmailInvite',
    'GET /invite/{token}' => 'InviteController@redeemInviteLink',

    // Default Route
    '' => 'HomeController@index'
];
