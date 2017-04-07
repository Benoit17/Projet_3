<?php

use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;

// Register global error and exception handlers
ErrorHandler::register();
ExceptionHandler::register();

// Register service providers.
$app->register(new Silex\Provider\DoctrineServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));
$app['twig'] = $app->extend('twig', function(Twig_Environment $twig, $app) {
    $twig->addExtension(new Twig_Extensions_Extension_Text());
    return $twig;
});
$app->register(new Silex\Provider\ValidatorServiceProvider());

$app->register(new Silex\Provider\AssetServiceProvider(), array(
    'assets.version' => 'v1'
));
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'secured' => array(
            'pattern' => '^/',
            'anonymous' => true,
            'logout' => true,
            'form' => array('login_path' => '/login', 'check_path' => '/login_check'),
            'users' => function () use ($app) {
                return new Projet_3\DAO\UserDAO($app['db']);
            },
        ),
    ),
    'security.role_hierarchy' => array(
    'ROLE_ADMIN' => array('ROLE_USER'),
    ),
    'security.access_rules' => array(
    array('^/admin', 'ROLE_ADMIN'),
    ),
));
$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\LocaleServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider());

// Register services.
$app['dao.billet'] = function ($app) {
    return new Projet_3\DAO\BilletDAO($app['db']);
};
$app['dao.user'] = function ($app) {
    return new Projet_3\DAO\UserDAO($app['db']);
};
$app['dao.comment'] = function ($app) {
    $commentDAO = new Projet_3\DAO\CommentDAO($app['db']);
    $commentDAO->setBilletDAO($app['dao.billet']);
    $commentDAO->setUserDAO($app['dao.user']);
    return $commentDAO;
};
$app['dao.answer'] = function ($app) {
    $answerDAO = new Projet_3\DAO\AnswerDAO($app['db']);
    $answerDAO->setCommentDAO($app['dao.comment']);
    $answerDAO->setUserDAO($app['dao.user']);
    return $answerDAO;
};