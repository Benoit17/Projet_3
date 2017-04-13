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
use Projet_3\Form\Type\AdminUserType;

// Home page
$app->get('/', function () use ($app) {
    $billets = $app['dao.billet']->findAll();
    return $app['twig']->render('index.html.twig', array('billets' => $billets));
})->bind('home');


// Billet details with comments
$app->match('/billet/{billetId}', function ($billetId, Request $request) use ($app) {
    $billet = $app['dao.billet']->find($billetId);
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
    
    $comments = $app['dao.comment']->findAllByBillet($billetId);

    return $app['twig']->render('billet.html.twig', array(
        'billet' => $billet,
        'comments' => $comments,
        'commentForm' => $commentFormView));
})->bind('billet');

// Add a new answer
$app->match('/answer/{id}', function($id, Request $request) use ($app) {
    $comment = $app['dao.comment']->find($id);
    $answerFormView = null;
    if ($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')) {
        // An user can add comments
        $answer = new Answer();
        $answer->setComment($comment);
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

    return $app['twig']->render('answer_form.html.twig', array(
        'comment' => $comment,
        'answerForm' => $answerFormView));
})->bind('answer');

// Answer in answer
$app->match('/answer/{billetId}/{commentId}', function($billetId, $commentId, Request $request) use ($app) {
    $billet = $app['dao.billet']->find($billetId);
    $comment = $app['dao.comment']->find($commentId);
    $commentFormView = null;
    if ($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')) {
        // An user can add comments
        $comment = new Comment();
        $comment->setBillet($billet);
        $comment->setCommentId($commentId);
        $user = $app['user'];
        $comment->setAuthor($user);
        $commentForm = $app['form.factory']->create(CommentType::class, $comment);
        $commentForm->handleRequest($request);
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $app['dao.comment']->saveAnswer($comment);
            $app['session']->getFlashBag()->add('success', 'Your comment was successfully added.');
        }
        $commentFormView = $commentForm->createView();
    }

    return $app['twig']->render('comment_form.html.twig', array(
        'billet' => $billet,
        'comment' => $comment,
        'commentForm' => $commentFormView));
})->bind('answer');

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
    $role = 'ROLE_USER';
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
        // select default user role
        $user->setRole($role);
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
$app->match('/admin/billet/{billetId}/edit', function($billetId, Request $request) use ($app) {
    $billet = $app['dao.billet']->find($billetId);
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
$app->get('/admin/billet/{billetId}/delete', function($billetId, Request $request) use ($app) {
    // Delete all associated comments
    $app['dao.comment']->deleteAllByBillet($billetId);
    // Delete the billet
    $app['dao.billet']->delete($billetId);
    $app['session']->getFlashBag()->add('success', 'The billet was successfully removed.');
    // Redirect to admin home page
    return $app->redirect($app['url_generator']->generate('admin'));
})->bind('admin_billet_delete');

// Edit an existing comment
$app->match('/admin/comment/{commentId}/edit', function($commentId, Request $request) use ($app) {
    $comment = $app['dao.comment']->find($commentId);
    $commentForm = $app['form.factory']->create(CommentType::class, $comment);
    $commentForm->handleRequest($request);
    if ($commentForm->isSubmitted() && $commentForm->isValid()) {
        $app['dao.comment']->save($comment);
        $app['session']->getFlashBag()->add('success', 'The comment was successfully updated.');
    }
    return $app['twig']->render('admin_comment_form.html.twig', array(
        'title' => 'Edit comment',
        'commentForm' => $commentForm->createView()));
})->bind('admin_comment_edit');

// Remove a comment
$app->get('/admin/comment/{commentId}/delete', function($commentId, Request $request) use ($app) {
    $app['dao.comment']->delete($commentId);
    $app['session']->getFlashBag()->add('success', 'The comment was successfully removed.');
    // Redirect to admin home page
    return $app->redirect($app['url_generator']->generate('admin'));
})->bind('admin_comment_delete');

// Add a user in admin
$app->match('/admin/user/add', function(Request $request) use ($app) {
    $user = new User();
    $adminUserForm = $app['form.factory']->create(AdminUserType::class, $user);
    $adminUserForm->handleRequest($request);
    if ($adminUserForm->isSubmitted() && $adminUserForm->isValid()) {
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
    return $app['twig']->render('admin_user_form.html.twig', array(
        'title' => 'New user',
        'adminUserForm' => $adminUserForm->createView()));
})->bind('admin_user_add');

// Edit an existing user
$app->match('/admin/user/{id}/edit', function($id, Request $request) use ($app) {
    $user = $app['dao.user']->find($id);
    $adminUserForm = $app['form.factory']->create(AdminUserType::class, $user);
    $adminUserForm->handleRequest($request);
    if ($adminUserForm->isSubmitted() && $adminUserForm->isValid()) {
        $plainPassword = $user->getPassword();
        // find the encoder for the user
        $encoder = $app['security.encoder_factory']->getEncoder($user);
        // compute the encoded password
        $password = $encoder->encodePassword($plainPassword, $user->getSalt());
        $user->setPassword($password);
        $app['dao.user']->save($user);
        $app['session']->getFlashBag()->add('success', 'The user was successfully updated.');
    }
    return $app['twig']->render('admin_user_form.html.twig', array(
        'title' => 'Edit user',
        'adminUserForm' => $adminUserForm->createView()));
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
