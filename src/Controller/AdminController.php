<?php

namespace Projet_3\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Projet_3\Domain\Billet;
use Projet_3\Domain\User;
use Projet_3\Form\Type\BilletType;
use Projet_3\Form\Type\CommentType;
use Projet_3\Form\Type\AdminUserType;

class AdminController {

    /**
     * Admin home page controller.
     *
     * @param Application $app Silex application
     */
    public function indexAction(Application $app) {
        $billets = $app['dao.billet']->findAll();
        $comments = $app['dao.comment']->findAll();
        $users = $app['dao.user']->findAll();
        return $app['twig']->render('admin.html.twig', array(
            'billets' => $billets,
            'comments' => $comments,
            'users' => $users));
    }

    /**
     * Add billet controller.
     *
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */
    public function addBilletAction(Request $request, Application $app) {
        $billet = new Billet();
        $billetForm = $app['form.factory']->create(BilletType::class, $billet);
        $billetForm->handleRequest($request);
        if ($billetForm->isSubmitted() && $billetForm->isValid()) {
            $app['dao.billet']->save($billet);
            $app['session']->getFlashBag()->add('success', 'The billet was successfully created.');
        }
        return $app['twig']->render('admin_billet_form.html.twig', array(
            'title' => 'New billet',
            'billetForm' => $billetForm->createView()));
    }

    /**
     * Edit Billet controller.
     *
     * @param integer $billetId billet id
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */
    public function editBilletAction($billetId, Request $request, Application $app) {
        $billet = $app['dao.billet']->find($billetId);
        $billetForm = $app['form.factory']->create(BilletType::class, $billet);
        $billetForm->handleRequest($request);
        if ($billetForm->isSubmitted() && $billetForm->isValid()) {
            $app['dao.billet']->save($billet);
            $app['session']->getFlashBag()->add('success', 'The billet was successfully updated.');
        }
        return $app['twig']->render('admin_billet_form.html.twig', array(
            'title' => 'Edit billet',
            'billetForm' => $billetForm->createView()));
    }

    /**
     * Delete billet controller.
     *
     * @param integer $billetId Billet id
     * @param Application $app Silex application
     */
    public function deleteBilletAction($billetId, Application $app) {
        // Delete all associated comments
        $app['dao.comment']->deleteAllByBillet($billetId);
        // Delete the billet
        $app['dao.billet']->delete($billetId);
        $app['session']->getFlashBag()->add('success', 'The billet was successfully removed.');
        // Redirect to admin home page
        return $app->redirect($app['url_generator']->generate('admin'));
    }

    /**
     * Edit comment controller.
     *
     * @param integer $commentId Comment id
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */
    public function editCommentAction($commentId, Request $request, Application $app) {
        $comment = $app['dao.comment']->find($commentId);
        $commentForm = $app['form.factory']->create(CommentType::class, $comment);
        $commentForm->handleRequest($request);
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $app['dao.comment']->save($comment);
            $app['session']->getFlashBag()->add('success', 'The comment was successfully updated.');
        }
        return $app['twig']->render('comment_form.html.twig', array(
            'title' => 'Edit comment',
            'commentForm' => $commentForm->createView()));
    }

    /**
     * Delete comment controller.
     *
     * @param integer $commentId Comment id
     * @param Application $app Silex application
     */
    public function deleteCommentAction($commentId, Application $app) {
        $app['dao.comment']->delete($commentId);
        $app['session']->getFlashBag()->add('success', 'The comment was successfully removed.');
        // Redirect to admin home page
        return $app->redirect($app['url_generator']->generate('admin'));
    }

    /**
     * Add user controller.
     *
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */
    public function addUserAction(Request $request, Application $app)
    {
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
    }

    /**
     * Edit user controller.
     *
     * @param integer $id User id
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */
    public function editUserAction($id, Request $request, Application $app) {
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
    }

    /**
     * Delete user controller.
     *
     * @param integer $id User id
     * @param Application $app Silex application
     */
    public function deleteUserAction($id, Application $app) {
        // Delete all associated comments
        $app['dao.comment']->deleteAllByUser($id);
        // Delete the user
        $app['dao.user']->delete($id);
        $app['session']->getFlashBag()->add('success', 'The user was successfully removed.');
        // Redirect to admin home page
        return $app->redirect($app['url_generator']->generate('admin'));
    }
}
