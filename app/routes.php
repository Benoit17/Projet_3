<?php

use Symfony\Component\HttpFoundation\Request;
use Projet_3\Domain\Comment;
use Projet_3\Form\Type\CommentType;
use Projet_3\Form\Type\AnswerType;
use Projet_3\Domain\User;
use Projet_3\Form\Type\UserType;

// Home page
$app->get('/', function () use ($app) {
    $billets = $app['dao.billet']->findAll();
    return $app['twig']->render('index.html.twig', array('billets' => $billets));
})->bind('home');


// Billet details with comments
$app->match('/billet/{id}', function ($id, Request $request) use ($app) {
    $billet = $app['dao.billet']->find($id);
    $commentFormView = null;
    if ($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')) {
        // An user can add comments
        $comment = new Comment();
        $comment->setBillet($billet);
        $user = $app['user'];
        $comment->setAuthor($user);
        $commentForm = $app['form.factory']->create(CommentType::class, $comment);
        $commentForm->handleRequest($request);
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $app['dao.comment']->save($comment);
            $app['session']->getFlashBag()->add('success', 'Your comment was successfully added.');
        }
        $commentFormView = $commentForm->createView();
    }
    $answerFormView = null;
    if ($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')) {
        // An user can answer
        $comment = new Comment();
        $comment->setBillet($billet);
        $user = $app['user'];
        $comment->setAuthor($user);
        $answerForm = $app['form.factory']->create(AnswerType::class, $comment);
        $answerForm->handleRequest($request);
        if ($answerForm->isSubmitted() && $answerForm->isValid()) {
            $app['dao.comment']->save($comment);
            $app['session']->getFlashBag()->add('success', 'Your comment was successfully added.');
        }
        $answerFormView = $answerForm->createView();
    }
    $comments = $app['dao.comment']->findAllByBillet($id);

    return $app['twig']->render('billet.html.twig', array(
        'billet' => $billet,
        'comments' => $comments,
        'commentForm' => $commentFormView,
        'answerForm' => $answerFormView));
})->bind('billet');

// Login form
$app->get('/login', function(Request $request) use ($app) {
    return $app['twig']->render('login.html.twig', array(
        'error'         => $app['security.last_error']($request),
        'last_username' => $app['session']->get('_security.last_username'),
    ));
})->bind('login');

// Registration form
$app->match('/registration', function(Request $request) use ($app) {
    $user = new User();
    $userForm = $app['form.factory']->create(UserType::class, $user);
    $userForm->handleRequest($request);
    if ($userForm->isSubmitted() && $userForm->isValid()) {
        // generate a random salt value
        $salt = substr(md5(time()), 0, 23);
        $user->setSalt($salt);
        $plainPassword = $user->getPassword();
        // find the default encoder
        $encoder = $app['security.encoder.bcrypt'];
        // compute the encoded password
        $password = $encoder->encodePassword($plainPassword, $user->getSalt());
        $user->setPassword($password);
        $app['dao.user']->save($user);
        $app['session']->getFlashBag()->add('success', 'Thank you and good read!.');


    }
    return $app['twig']->render('user_form.html.twig', array(
        'title' => 'New user',
        'userForm' => $userForm->createView()));
})->bind('registration');