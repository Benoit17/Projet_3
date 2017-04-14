<?php

namespace Projet_3\DAO;

use Projet_3\Domain\Comment;

class CommentDAO extends DAO
{
    /**
     * @var \Projet_3\DAO\BilletDAO
     */
    private $billetDAO;

    /**
     * @var \Projet_3\DAO\UserDAO
     */
    private $userDAO;

    public function setBilletDAO(BilletDAO $billetDAO) {
        $this->billetDAO = $billetDAO;
    }

    public function setUserDAO(UserDAO $userDAO) {
        $this->userDAO = $userDAO;
    }

    /**
     * Return a list of all comments for an billet, sorted by date (most recent last).
     *
     * @param integer $billetId The billet id.
     *
     * @return array A list of all comments for the billet.
     */
    public function findAllByBillet($billetId) {
        // The associated billet is retrieved only once
        $billet = $this->billetDAO->find($billetId);

        // billet_id is not selected by the SQL query
        // The billet won't be retrieved during domain objet construction
        $sql = "select com_id, com_content, parent_id, usr_id, reporting_id from t_comment where billet_id=? order by com_id";
        $result = $this->getDb()->fetchAll($sql, array($billetId));

        // Convert query result to an array of domain objects
        $comments_by_id = [];
        foreach ($result as $row) {
            $comId = $row['com_id'];
            $comment = $this->buildDomainObject($row);
            // The associated billet is defined for the constructed comment
            $comment->setBillet($billet);
            $comments_by_id[$comId] = $comment;
        }
        return $comments_by_id;
    }

    /**
     * Permet de récupérer les commentaires avec les enfants
     * @param $billetId
     * @param bool $unset_children Doit-t-on supprimer les commentaire qui sont des enfants des résultats ?
     * @return array
     */
    public function findAllWithChildren($billetId, $unset_children = true)
    {
        // On a besoin de 2 variables
        // comments_by_id ne sera jamais modifié alors que comments
        $comments = $comments_by_id = $this->findAllByBillet($billetId);
        foreach ($comments as $commentId => $comment) {
            if ($comment->getParentId() != 0) {
                $comments_by_id[$comment->getParentId()]->children[] = $comment;
                if ($unset_children) {
                    unset($comments[$commentId]);
                }
            }
        }
        return $comments;
    }

    /**
     * Returns a list of all comments, sorted by date (most recent first).
     *
     * @return array A list of all comments.
     */
    public function findAll() {
        $sql = "select * from t_comment order by com_id desc";
        $result = $this->getDb()->fetchAll($sql);

        // Convert query result to an array of domain objects
        $entities = array();
        foreach ($result as $row) {
            $commentId = $row['com_id'];
            $entities[$commentId] = $this->buildDomainObject($row);
        }
        return $entities;
    }

    /**
     * Returns a comment matching the supplied id.
     *
     * @param integer $commentid The comment id
     *
     * @return \Projet_3\Domain\Comment|throws an exception if no matching comment is found
     */
    public function find($commentId) {
        $sql = "select * from t_comment where com_id=?";
        $row = $this->getDb()->fetchAssoc($sql, array($commentId));

        if ($row)
            return $this->buildDomainObject($row);
        else
            throw new \Exception("No comment matching id " . $commentId);
    }

    /**
     * Returns a comment matching the supplied id.
     *
     * @param integer $commentid The comment id
     *
     * @return \Projet_3\Domain\Comment|throws an exception if no matching comment is found
     */
    public function findByReportingId($reportingId) {
        $sql = "select reporting_id from t_comment where reporting_id=?";
        $row = $this->getDb()->fetchAssoc($sql, array($reportingId));

        if ($row)
            return $this->buildDomainObject($row);
        else
            throw new \Exception("No comment matching id " . $reportingId);
    }

    /**
     * Creates a Comment object based on a DB row.
     *
     * @param array $row The DB row containing Comment data.
     * @return \Projet_3\Domain\Comment
     */
    protected function buildDomainObject(array $row) {
        $comment = new Comment();
        $comment->setCommentId($row['com_id']);
        $comment->setParentId($row['parent_id']);
        $comment->setReportingId($row['reporting_id']);
        $comment->setContent($row['com_content']);

        if (array_key_exists('billet_id', $row)) {
            // Find and set the associated billet
            $billetId = $row['billet_id'];
            $billet = $this->billetDAO->find($billetId);
            $comment->setBillet($billet);
        }

        if (array_key_exists('usr_id', $row)) {
            // Find and set the associated author
            $userId = $row['usr_id'];
            $user = $this->userDAO->find($userId);
            $comment->setAuthor($user);
        }

        return $comment;
    }

    /**
     * Saves a comment into the database.
     *
     * @param \Projet_3\Domain\Comment $comment The comment to save
     */
    public function save(Comment $comment) {
        $commentData = array(
            'billet_id' => $comment->getBillet()->getBilletId(),
            'usr_id' => $comment->getAuthor()->getId(),
            'com_content' => $comment->getContent()
        );

        if ($comment->getCommentId()) {
            // The comment has already been saved : update it
            $this->getDb()->update('t_comment', $commentData, array('com_id' => $comment->getCommentId()));
        } else {
            // The comment has never been saved : insert it
            $this->getDb()->insert('t_comment', $commentData);
            // Get the id of the newly created comment and set it on the entity.
            $commentId = $this->getDb()->lastInsertId();
            $comment->setCommentId($commentId);
        }
    }

    /**
     * Saves an answer into the database.
     *
     * @param \Projet_3\Domain\Comment $comment The answer to save
     */
    public function saveAnswer(Comment $comment) {
        $commentData = array(
            'billet_id' => $comment->getBillet()->getBilletId(),
            'parent_id' => $comment->getCommentId(),
            'usr_id' => $comment->getAuthor()->getId(),
            'com_content' => $comment->getContent()
        );

        if ($comment->getCommentId()) {
            // The answer has never been saved : insert it
            $this->getDb()->insert('t_comment', $commentData);
            // Get the id of the newly created answer and set it on the entity.

            $commentId = $this->getDb()->lastInsertId();
            $comment->setCommentId($commentId);
        }
    }

    /**
     * Save signal into the database.
     *
     * @param \Projet_3\Domain\Comment $comment The answer to save
     */
    public function saveReporting(Comment $comment) {
        $commentData = array(
            'reporting_id'=> $comment->getCommentId(),
        );
            // The comment has already been saved : update it
            $this->getDb()->update('t_comment', $commentData, array('com_id' => $comment->getCommentId()));
    }

    /**
     * Removes all comments for a billet
     *
     * @param $billetId The id of the billet
     */
    public function deleteAllByBillet($billetId) {
        $this->getDb()->delete('t_comment', array('billet_id' => $billetId));
    }

    /**
     * Removes a comment from the database.
     *
     * @param @param integer $commentId The comment id
     */
    public function delete($commentId) {
        // Delete the comment
        $this->getDb()->delete('t_comment', array('com_id' => $commentId));
    }

    /**
     * Removes all comments for a user
     *
     * @param integer $userId The id of the user
     */
    public function deleteAllByUser($userId) {
        $this->getDb()->delete('t_comment', array('usr_id' => $userId));
    }
}

