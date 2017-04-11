<?php

namespace Projet_3\Domain;

class Answer
{
    /**
     * Answer parentid.
     *
     * @var integer
     */
    private $id;


    /**
     * Answer id.
     *
     * @var integer
     */
    private $parentid;

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

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function getParentId() {
        return $this->parentid;
    }

    public function setParentId($parentid) {
        $this->parentid = $parentid;
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