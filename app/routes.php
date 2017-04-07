<?php

use Symfony\Component\HttpFoundation\Request;
use Projet_3\Domain\Comment;
use Projet_3\Domain\Billet;
use Projet_3\Domain\User;
use Projet_3\Domain\Answer;
use Projet_3\Form\Type\CommentType;
use Projet_3\Form\Type\BilletType;
use Projet_3\Form\Type\AnswerType;
use Projet_3\Form\Type\UserType;
use Projet_3\Form\Type\UserAdminType;

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
        $answer = new Answer();
        $user = $app['user'];
        $answer->setAuthor($user);
        $answerForm = $app['form.factory']->create(AnswerType::class, $answer);
        $answerForm->handleRequest($request);
        if ($answerForm->isSubmitted() && $answerForm->isValid()) {
            $app['dao.answer']->save($answer);
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

// Admin home page
$app->get('/admin', function() use ($app) {
    $billets = $app['dao.billet']->findAll();
    $comments = $app['dao.comment']->findAll();
    $users = $app['dao.user']->findAll();
    return $app['twig']->render('admin.html.twig', array(
        'billets' => $billets,
        'comments' => $comments,
        'users' => $users));
})->bind('admin');

// Add a new billet
$app->match('/admin/billet/add', function(Request $request) use ($app) {
    $billet = new Billet();
    $billetForm = $app['form.factory']->create(BilletType::class, $billet);
    $billetForm->handleRequest($request);
    if ($billetForm->isSubmitted() && $billetForm->isValid()) {
        $app['dao.billet']->save($billet);
        $app['session']->getFlashBag()->add('success', 'The billet was successfully created.');
    }
    return $app['twig']->render('billet_form.html.twig', array(
        'title' => 'New billet',
        'billetForm' => $billetForm->createView()));
})->bind('admin_billet_add');

// Edit an existing billet
$app->match('/admin/billet/{id}/edit', function($id, Request $request) use ($app) {
    $billet = $app['dao.billet']->find($id);
    $billetForm = $app['form.factory']->create(BilletType::class, $billet);
    $billetForm->handleRequest($request);
    if ($billetForm->isSubmitted() && $billetForm->isValid()) {
        $app['dao.billet']->save($billet);
        $app['session']->getFlashBag()->add('success', 'The billet was successfully updated.');
    }
    return $app['twig']->render('billet_form.html.twig', array(
        'title' => 'Edit billet',
        'billetForm' => $billetForm->createView()));
})->bind('admin_billet_edit');

// Remove a billet
$app->get('/admin/billet/{id}/delete', function($id, Request $request) use ($app) {
    // Delete all associated comments
    $app['dao.comment']->deleteAllByBillet($id);
    // Delete the billet
    $app['dao.billet']->delete($id);
    $app['session']->getFlashBag()->add('success', 'The billet was successfully removed.');
    // Redirect to admin home page
    return $app->redirect($app['url_generator']->generate('admin'));
})->bind('admin_billet_delete');

// Edit an existing comment
$app->match('/admin/comment/{id}/edit', function($id, Request $request) use ($app) {
    $comment = $app['dao.comment']->find($id);
    $commentForm = $app['form.factory']->create(CommentType::class, $comment);
    $commentForm->handleRequest($request);
    if ($commentForm->isSubmitted() && $commentForm->isValid()) {
        $app['dao.comment']->save($comment);
        $app['session']->getFlashBag()->add('success', 'The comment was successfully updated.');
    }
    return $app['twig']->render('comment_form.html.twig', array(
        'title' => 'Edit comment',
        'commentForm' => $commentForm->createView()));
})->bind('admin_comment_edit');

// Remove a comment
$app->get('/admin/comment/{id}/delete', function($id, Request $request) use ($app) {
    $app['dao.comment']->delete($id);
    $app['session']->getFlashBag()->add('success', 'The comment was successfully removed.');
    // Redirect to admin home page
    return $app->redirect($app['url_generator']->generate('admin'));
})->bind('admin_comment_delete');

// Add a user in admin
$app->match('/admin/user/add', function(Request $request) use ($app) {
    $user = new User();
    $useradminForm = $app['form.factory']->create(UserAdminType::class, $user);
    $useradminForm->handleRequest($request);
    if ($useradminForm->isSubmitted() && $useradminForm->isValid()) {
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
        $app['session']->getFlashBag()->add('success', 'The user was successfully created.');
    }
    return $app['twig']->render('useradmin_form.html.twig', array(
        'title' => 'New user',
        'useradminForm' => $useradminForm->createView()));
})->bind('admin_user_add');

// Edit an existing user
$app->match('/admin/user/{id}/edit', function($id, Request $request) use ($app) {
    $user = $app['dao.user']->find($id);
    $useradminForm = $app['form.factory']->create(UserType::class, $user);
    $useradminForm->handleRequest($request);
    if ($useradminForm->isSubmitted() && $useradminForm->isValid()) {
        $plainPassword = $user->getPassword();
        // find the encoder for the user
        $encoder = $app['security.encoder_factory']->getEncoder($user);
        // compute the encoded password
        $password = $encoder->encodePassword($plainPassword, $user->getSalt());
        $user->setPassword($password);
        $app['dao.user']->save($user);
        $app['session']->getFlashBag()->add('success', 'The user was successfully updated.');
    }
    return $app['twig']->render('user_form.html.twig', array(
        'title' => 'Edit user',
        'useradminForm' => $useradminForm->createView()));
})->bind('admin_user_edit');

// Remove a user
$app->get('/admin/user/{id}/delete', function($id, Request $request) use ($app) {
    // Delete all associated comments
    $app['dao.comment']->deleteAllByUser($id);
    // Delete the user
    $app['dao.user']->delete($id);
    $app['session']->getFlashBag()->add('success', 'The user was successfully removed.');
    // Redirect to admin home page
    return $app->redirect($app['url_generator']->generate('admin'));
})->bind('admin_user_delete');
