<?php
namespace FooBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table()
 * @ORM\Entity()
 */
class Post
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    protected $title;

    /**
     * @ORM\Column(name="body", type="text")
     */
    protected $body;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection|\FooBundle\Entity\Comment[]
     * @ORM\OneToMany(
     *  targetEntity="FooBundle\Entity\Comment",
     *  mappedBy="post",
     *  cascade={"all"}
     * )
     */
    protected $comments;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection|\FooBundle\Entity\Tag[]
     * @ORM\ManyToMany(targetEntity="FooBundle\Entity\Tag", cascade={"all"})
     * @ORM\JoinTable(name="Post_Tag",
     *     joinColumns={
     *          @ORM\JoinColumn(name="post_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *          @ORM\JoinColumn(name="tag_id", referencedColumnName="id")
     *     }
     * )
     * @Serializer\Type("Lemon\RestBundle\Serializer\IdCollection<FooBundle\Entity\Tag>")
     **/
    protected $tags;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }
}
