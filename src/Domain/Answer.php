<?php

namespace Projet_3\Domain;

class Answer
{
    /**
     * Answer parentid.
     *
     * @var integer
     */
    private $answerId;


    /**
     * Answer id.
     *
     * @var integer
     */
    private $parentId;

    /**
     * Answer author.
     *
     * @var \Projet_3\Domain\User
     */
    private $author;

    /**
     * Answer content.
     *
     * @var integer
     */
    private $content;

    /**
     * Associated comment.
     *
     * @var \Projet_3\Domain\Comment
     */
    private $comment;

    public function getAnswerId() {
        return $this->answerId;
    }

    public function setAnswerId($answerId) {
        $this->answerId = $answerId;
        return $this;
    }

    public function getParentId() {
        return $this->parentId;
    }

    public function setParentId($parentId) {
        $this->parentId = $parentId + 1;
        return $this;
    }

    public function getAuthor() {
        return $this->author;
    }

    public function setAuthor(User $author) {
        $this->author = $author;
        return $this;
    }

    public function getContent() {
        return $this->content;
    }

    public function setContent($content) {
        $this->content = $content;
        return $this;
    }

    public function getComment() {
        return $this->comment;
    }

    public function setComment(Comment $comment) {
        $this->comment = $comment;
        return $this;
    }
}