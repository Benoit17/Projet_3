<?php

namespace Projet_3\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Projet_3\Domain\Comment;
use Projet_3\Form\Type\CommentType;
use Projet_3\Domain\User;
use Projet_3\Form\Type\UserType;

class HomeController
{

    /**
     * Home page controller.
     *
     * @param Application $app Silex application
     */
    public function indexAction(Application $app)
    {
        $billets = $app['dao.billet']->findAll();
        return $app['twig']->render('index.html.twig', array('billets' => $billets));
    }

    /**
     * Billet details controller.
     *
     * @param integer $billetId Billet id
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */
    public function billetAction($billetId, Request $request, Application $app)
    {
        $billet = $app['dao.billet']->find($billetId);
        $commentFormView = null;
        if ($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')) {
            // A user is fully authenticated : he can add comments
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
    }

    // Add a new Answer (New)
    /**
     * Answer controller.
     *
     * @param integer $billetId Billet id
     * @param integer $commentId Comment id
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */
    public function addAnswerAction($billetId, $commentId, Request $request, Application $app)
    {
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
    }

    // Add a reporting (New)
    /**
     * Reporting controller.
     *
     * @param integer $commentId Comment id
     * @param Application $app Silex application
     */
    public function reportingCommentAction($commentId, Application $app)
    {
        if ($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')) {
            $comment = new Comment();
            $comment->setCommentId($commentId);
                $app['dao.comment']->saveReporting($comment);
                $app['session']->getFlashBag()->add('success', 'Your reporting was successfully send.');
            }
        return $app->redirect($app['url_generator']->generate('home'));
    }

    // Login form
    /**
     * User login controller.
     *
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */
    public function loginAction(Request $request, Application $app)
    {
        return $app['twig']->render('login.html.twig', array(
            'error' => $app['security.last_error']($request),
            'last_username' => $app['session']->get('_security.last_username'),
        ));
    }

    // Registration form
    /**
     * User registration controller.
     *
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */
    public function registrationAction(Request $request, Application $app)
    {
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
    }
}

