<?php

namespace Projet_3\DAO;

use Projet_3\Domain\Answer;

class AnswerDAO extends DAO
{
    /**
     * @var \Projet_3\DAO\CommentDAO
     */
    private $commentDAO;
    
    /**
     * @var \Projet_3\DAO\UserDAO
     */
    private $userDAO;

    public function setCommentDAO(CommentDAO $commentDAO) {
        $this->commentDAO = $commentDAO;
    }
    
    public function setUserDAO(UserDAO $userDAO) {
        $this->userDAO = $userDAO;
    }

    /**
     * Return a list of all answers for a comment, sorted by date (most recent last).
     *
     * @param integer $commentId The comment id.
     *
     * @return array A list of all answers for the comment.
     */
    public function findAllByComment($commentId) {
        // The associated billet is retrieved only once
        $comment = $this->commentDAO->find($commentId);

        // comment_id is not selected by the SQL query
        // The billet won't be retrieved during domain objet construction
        $sql = "select answer_id, answer_content, usr_id from t_answer where com_id=? order by answer_id";
        $result = $this->getDb()->fetchAll($sql, array($commentId));

        // Convert query result to an array of domain objects
        $answers = array();
        foreach ($result as $row) {
            $answerId = $row['answer_id'];
            $answer = $this->buildDomainObject($row);
            // The associated billet is defined for the constructed comment
            $answer->setComment($comment);
            $answers[$answerId] = $answer;
        }
        return $answers;
    }

    /**
     * Creates an Answer object based on a DB row.
     *
     * @param array $row The DB row containing Answer data.
     * @return \Projet_3\Domain\Answer
     */
    protected function buildDomainObject(array $row) {
        $answer = new Answer();
        $answer->setAnswerId($row['answer_id']);
        $answer->setParentId($row['parent_id']);
        $answer->setContent($row['answer_content']);

        if (array_key_exists('com_id', $row)) {
            // Find and set the associated billet
            $commentId = $row['com_id'];
            $comment = $this->commentDAO->find($commentId);
            $answer->setComment($comment);
        }

        if (array_key_exists('usr_id', $row)) {
            // Find and set the associated author
            $userId = $row['usr_id'];
            $user = $this->userDAO->find($userId);
            $answer->setAuthor($user);
        }
        return $answer;
    }

    /**
     * Return a list of all answers, sorted by date (most recent first).
     *
     * @return array A list of all answers.
     */
    public function findAll() {
        $sql = "select * from t_answer order by com_id desc";
        $result = $this->getDb()->fetchAll($sql);

        // Convert query result to an array of domain objects
        $answers = array();
        foreach ($result as $row) {
            $answerId = $row['answer_id'];
            $answers[$answerId] = $this->buildDomainObject($row);
        }
        return $answers;
    }

    /**
     * Returns a comment matching the supplied id.
     *
     * @param integer $answerId The answer id
     *
     * @return \Projet_3\Domain\Answer|throws an exception if no matching comment is found
     */
    public function find($answerId) {
        $sql = "select * from t_answer where parent_id=?";
        $row = $this->getDb()->fetchAssoc($sql, array($answerId));

        if ($row)
            return $this->buildDomainObject($row);
    }

    /**
     * Saves an answer into the database.
     *
     * @param \Projet_3\Domain\Answer $answer The answer to save
     */
    public function save(Answer $answer) {
        $answerData = array(
            'com_id' => $answer->getComment()->getId(),
            'usr_id' => $answer->getAuthor()->getId(),
            'answer_content' => $answer->getContent()
        );

        if ($answer->getAnswerId()) {
            // The answer has already been saved : update it
            $this->getDb()->update('t_answer', $answerData, array('answer_id' => $answer->getAnswerId()));
        } else {
            // The answer has never been saved : insert it
            $this->getDb()->insert('t_answer', $answerData);
            // Get the id of the newly created answer and set it on the entity.
            $answerId = $this->getDb()->lastInsertId();
            $answer->setAnswerId($answerId);
        }
    }

    /**
     * Saves an answer into the database.
     *
     * @param \Projet_3\Domain\Answer $answer The answer to save
     */
    public function saveAnswer(Answer $answer) {
        $answerData = array(
            'com_id' => $answer->getComment()->getId(),
            'parent_id' => $answer->getAnswerId(),
            'usr_id' => $answer->getAuthor()->getId(),
            'answer_content' => $answer->getContent()
        );

        if ($answer->getAnswerId()) {
            // The answer has never been saved : insert it
            $this->getDb()->insert('t_answer', $answerData);
            // Get the id of the newly created answer and set it on the entity.

            $answerId = $this->getDb()->lastInsertId();
            $answer->setAnswerId($answerId);
        }
    }
}


