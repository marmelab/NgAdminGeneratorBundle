<?php
namespace FooBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

class Post
{
    protected $id;
    protected $title;
    protected $body;
    protected $comments;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection|\FooBundle\Entity\Tag[]
     * @Serializer\Type("Lemon\RestBundle\Serializer\IdCollection<FooBundle\Entity\Tag>")
     **/
    protected $tags;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }
}
