<?php

namespace Poeticus\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('text', 'textarea', array(
                'constraints' => new Assert\NotBlank(), 'label' => 'Message'
            ))		
            ->add('save', 'submit', array('label' => 'Envoyer', 'attr' => array('class' => 'btn btn-success')));
    }

    public function getName()
    {
        return 'comment';
    }
}
