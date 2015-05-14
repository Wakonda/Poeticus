<?php

namespace Poeticus\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('mail', 'text', array('constraints' => array(new Assert\Email(), new Assert\NotBlank()), "label" => "Email"))
            ->add('subject', 'text', array('constraints' => new Assert\NotBlank(), "label" => "Sujet"))
			->add('message', 'textarea', array(
                'constraints' => new Assert\NotBlank(), "label" => "Texte", 'attr' => array('class' => 'redactor')
            ))
			->add('send', 'submit', array('label' => 'Envoyer'))
			;
    }

    public function getName()
    {
        return 'contact';
    }
}
