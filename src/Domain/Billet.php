<?php

namespace Projet_3\Domain;

class Billet
{
    /**
     * Billet id.
     *
     * @var integer
     */
    private $billetId;

    /**
     * Billet title.
     *
     * @var string
     */
    private $title;

    /**
     * Billet content.
     *
     * @var string
     */
    private $content;

    public function getBilletId() {
        return $this->billetId;
    }

    public function setBilletId($billetId) {
        $this->billetId = $billetId;
        return $this;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }

    public function getContent() {
        return $this->content;
    }

    public function setContent($content) {
        $this->content = $content;
        return $this;
    }
}