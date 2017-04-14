<?php

// Home page
$app->get('/', "Projet_3\Controller\HomeController::indexAction")
    ->bind('home');

// Detailed info about an billet
$app->match('/billet/{billetId}', "Projet_3\Controller\HomeController::billetAction")
    ->bind('billet');

// Add an answer
$app->match('/answer/{billetId}/{commentId}', "Projet_3\Controller\HomeController::addAnswerAction")
    ->bind('answer');

// Reporting a comment
$app->get('/billet/{commentId}/reporting', "Projet_3\Controller\HomeController::reportingCommentAction")
    ->bind('reporting');

// Login form
$app->get('/login', "Projet_3\Controller\HomeController::loginAction")
    ->bind('login');

// Registration form
$app->match('/registration', "Projet_3\Controller\HomeController::registrationAction")
    ->bind('registration');

// Admin zone
$app->get('/admin', "Projet_3\Controller\AdminController::indexAction")
    ->bind('admin');

// Add a new billet
$app->match('/admin/billet/add', "Projet_3\Controller\AdminController::addbilletAction")
    ->bind('admin_billet_add');

// Edit an existing billet
$app->match('/admin/billet/{billetId}/edit', "Projet_3\Controller\AdminController::editbilletAction")
    ->bind('admin_billet_edit');

// Remove a billet
$app->get('/admin/billet/{billetId}/delete', "Projet_3\Controller\AdminController::deletebilletAction")
    ->bind('admin_billet_delete');

// Edit an existing comment
$app->match('/admin/comment/{commentId}/edit', "Projet_3\Controller\AdminController::editCommentAction")
    ->bind('admin_comment_edit');

// Remove a comment
$app->get('/admin/comment/{commentId}/delete', "Projet_3\Controller\AdminController::deleteCommentAction")
    ->bind('admin_comment_delete');

// Add a user
$app->match('/admin/user/add', "Projet_3\Controller\AdminController::addUserAction")
    ->bind('admin_user_add');

// Edit an existing user
$app->match('/admin/user/{id}/edit', "Projet_3\Controller\AdminController::editUserAction")
    ->bind('admin_user_edit');

// Remove a user
$app->get('/admin/user/{id}/delete', "Projet_3\Controller\AdminController::deleteUserAction")
    ->bind('admin_user_delete');

// API : get all billets
$app->get('/api/billets', "Projet_3\Controller\ApiController::getbilletsAction")
    ->bind('api_billets');

// API : get an billet
$app->get('/api/billet/{billetId}', "Projet_3\Controller\ApiController::getbilletAction")
    ->bind('api_billet');

// API : create an billet
$app->post('/api/billet', "Projet_3\Controller\ApiController::addbilletAction")
    ->bind('api_billet_add');

// API : remove an billet
$app->delete('/api/billet/{billetId}', "Projet_3\Controller\ApiController::deletebilletAction")
    ->bind('api_billet_delete');
