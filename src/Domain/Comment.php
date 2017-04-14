<?php

namespace Projet_3\Domain;

class Comment
{
    /**
     * Comment id.
     *
     * @var integer
     */
    private $commentId;

    /**
     * Answer id.
     *
     * @var integer
     */
    private $parentId;

    /**
     * Comment author.
     *
     * @var \Projet_3\Domain\User
     */
    private $author;

    /**
     * Comment content.
     *
     * @var integer
     */
    private $content;

    /**
     * Associated billet.
     *
     * @var \Projet_3\Domain\Billet
     */
    private $billet;

    /**
     * Reporting id.
     *
     * @var integer
     */
    private $reportingId;

    public function getCommentId() {
        return $this->commentId;
    }

    public function setCommentId($commentId) {
        $this->commentId = $commentId;
        return $this;
    }

    public function getParentId() {
        return $this->parentId;
    }

    public function setParentId($parentId) {
        $this->parentId = $parentId;
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

    public function getBillet() {
        return $this->billet;
    }

    public function setBillet(Billet $billet) {
        $this->billet = $billet;
        return $this;
    }

    public function getReportingId() {
        return $this->reportingId;
    }

    public function setReportingId($reportingId) {
        $this->reportingId = $reportingId;
        return $this;
    }
}